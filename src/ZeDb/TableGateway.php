<?php
/**
 * This file is part of ZeDb
 *
 * (c) 2012 ZendExperts <team@zendexperts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ZeDb;

use Zend\Db\TableGateway\TableGateway as Gateway;
use Zend\Db\Sql\Expression;

/**
 * Table Gateway class for requests to the database using simplified functions calls
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com> 
 */
class TableGateway extends Gateway
{
    /**
     * Available function patterns that can be called to execute requests on the database
     * @var array
     */
    protected static $PATTERNS = array(
        '/^getAll(?:OrderBy(?P<orderBy>[A-Z][a-zA-Z0-9]+))?(?:Limit(?P<limit>[0-9]+)(?:From(?P<offset>[0-9]+))?)?$/U' => '__getAll',
        '/^getByColumns(?:OrderBy(?P<orderBy>[A-Z][a-zA-Z0-9]+))?(?:Limit(?P<limit>[0-9]+)(?:From(?P<offset>[0-9]+))?)?$/U' =>'__getBy',
        '/^getAllByColumns(?:OrderBy(?P<orderBy>[A-Z][a-zA-Z0-9]+))?(?:Limit(?P<limit>[0-9]+)(?:From(?P<offset>[0-9]+))?)?$/U' => '__getAll',
        '/^getBy(?P<fields>[A-Z][a-zA-Z0-9]+)(?:OrderBy(?P<orderBy>[A-Z][a-zA-Z0-9]+))?(?:Limit(?P<limit>[0-9]+)(?:From(?P<offset>[0-9]+))?)?$/U' => '__getBy',
        '/^getAllBy(?P<fields>[A-Z][a-zA-Z0-9]+)(?:OrderBy(?P<orderBy>[A-Z][a-zA-Z0-9]+))?(?:Limit(?P<limit>[0-9]+)(?:From(?P<offset>[0-9]+))?)?$/U' => '__getAll',
        '/^getLike(?P<fields>[A-Z][a-zA-Z0-9]+)(?:OrderBy(?P<orderBy>[A-Z][a-zA-Z0-9]+))?(?:Limit(?P<limit>[0-9]+)(?:From(?P<offset>[0-9]+))?)?$/U' => '__getLike',
        '/^getAllLike(?P<fields>[A-Z][a-zA-Z0-9]+)(?:OrderBy(?P<orderBy>[A-Z][a-zA-Z0-9]+))?(?:Limit(?P<limit>[0-9]+)(?:From(?P<offset>[0-9]+))?)?$/U' => '__getAllLike',
        '/^count$/U' => '__count',
        '/^countBy(?P<fields>[A-Z][a-zA-Z0-9]+)?$/U' => '__countBy',
        '/^countDistinctBy(?P<fields>[A-Z][a-zA-Z0-9]+)?$/U' => '__countDistinctBy',
        '/^removeBy(?P<fields>[A-Z][a-zA-Z0-9]+)$/U' => '__removeBy',
    );

    /**
     * Magic function handler
     * @param $name
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $args){
        //go through all the existing pattenrs
        foreach (static::$PATTERNS as $pattern=>$function){
            $matches = null;
            $found = preg_match($pattern, $name, $matches);
            //if a matched pattern was found, call the associated function with the matches and args and return the result
            if ($found){
                $options = array();
                foreach($matches as $key=>$value){
                    if (!is_int($key))
                        $options[$key] = $value;
                }
                return $this->$function($options, $args);
            }
        }
        throw new Exception('Invalid method called: ' . $name);
    }

    /**
     * Handler for RemoveBy magic functions
     * @param $matches
     * @param $args
     * @return int
     */
    private function __removeBy($matches, $args)
    {
        //get arguments from the function name
        $where = $this->_parseWhere($matches, $args);
        //execute the delete procedure with the abobe arguments
        $result = $this->delete(function ($select) use ($where)
        {
            $select->where($where);
        });
        //return the result
        return $result;
    }

    /**
     * Handler for GetBy magic function
     * @param $matches
     * @param $args
     * @return array|\Zend\Db\ResultSet\ResultSetInterface
     */
    private function __getBy($matches, $args){
        //select a single row and return it
        $resultSet = $this->_getResultSet($matches, $args);
        $entity = $resultSet->current();
        return $entity;
    }

    /**
     * Handler for GetAll magic functions
     * @param $matches
     * @param $args
     * @return array
     */
    private function __getAll($matches, $args){
        //Select all the matching results
        $resultSet = $this->_getResultSet($matches, $args);
        //Parse the result set and return all the entitites
        $entities = array();
        foreach ($resultSet as $entity){
            $entities[] = $entity;
        }
        return $entities;
    }

    /**
     * Handler for GetAllLike magic function
     * @param $matches
     * @param $args
     * @return array
     */
    private function __getAllLike($matches, $args)
    {
        $resultSet = $this->_getLikeResultSet($matches, $args);
        $entities = array();
        foreach ($resultSet as $entity) {
            $entities[] = $entity;
        }
        return $entities;
    }

    /**
     * Handler for count magic function
     * @return int
     */
    private function __count()
    {
        return $this->_getCount(null, false);
    }

    /**
     * Handler for countBy magic function
     * @param array $matches
     * @return int
     */
    private function __countBy(array $matches)
    {
        return $this->_getCount($matches, false);
    }

    /**
     * Handler for countDistinctBy magic function
     * @param array $matches
     * @return int
     */
    private function __countDistinctBy($matches)
    {
        return $this->_getCount($matches, true);
    }

    /**
     * Count sql rows
     * @param $matches
     * @param $distinct
     * @return int
     */
    private function _getCount($matches = null, $distinct = false)
    {
        //initialize table gateways of not initialized
        if (!$this->isInitialized) {
            $this->initialize();
        }

        //build count expression
        $expression = new Expression();
        $expression->setExpression("COUNT(" . ($distinct ? ' DISTINCT ' : '') . "?)");
        if (empty($matches)) {
            $expression->setParameters('*');
            $expression->setTypes(array(Expression::TYPE_LITERAL));
        } else {
            $field = $this->__normalizeKeys($matches['fields']);
            $expression->setParameters($field);
            $expression->setTypes(array(Expression::TYPE_IDENTIFIER));
        }

        $select = $this->sql->select();
        //set columns to just the count expression
        $select->columns(array('count' => $expression), false);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        //execute the statement and return the result
        $result = $statement->execute()->current();
        return $result['count'];
    }

    /**
     * Get ResultSet object when selecting rows
     * @param $matches
     * @param $args
     * @return \Zend\Db\ResultSet\ResultSet
     */
    private function _getResultSet($matches, $args){
        //parse arguments from the function name
        $where = $this->_parseWhere($matches, $args);
        $order = $this->_parseOrder($matches);
        $limit = $this->_parseLimit($matches);
        $offset = null;
        if (is_array($limit)) {
            list($limit, $offset) = $limit;
        }

        //run the query based on the above arguments and return the result set
        $resultSet = $this->select(function ($select) use ($where, $order, $limit, $offset)
        {
            $select->where($where);
            if ($order){
                $select->order($order);
            }
            if ($limit !== null) {
                $select->limit(1 * $limit);
                if ($offset !== null) {
                    $select->offset(1 * $offset);
                }
            }
        });
        return $resultSet;
    }

    /**
     * Get ResultSet object when selecting rows using the GetAllLike magic function
     * @param $matches
     * @param $args
     * @return \Zend\Db\ResultSet\ResultSet
     */
    private function _getLikeResultSet($matches, $args)
    {
        //parse arguments from the function name
        $where = $this->_parseLikeWhere($matches, $args);
        $order = $this->_parseOrder($matches);
        $limit = $this->_parseLimit($matches);
        $offset = null;
        if (is_array($limit)) {
            list($limit, $offset) = $limit;
        }
        //run the query based on the above arguments and return the result set
        $resultSet = $this->select(function ($select) use ($where, $order, $limit, $offset)
        {
            $select->where($where);
            if ($order){
                $select->order($order);
            }
            $select->limit(($limit === null ? null : 1 * $limit));
            $select->offset(($offset === null ? null : 1 * $offset));
        });
        return $resultSet;
    }

    /**
     * Parse query conditions using LIKE
     * @param $matches
     * @param $args
     * @return array
     */
    private function _parseLikeWhere($matches, $args)
    {
        $where = array();
        if (array_key_exists('fields', $matches) && !empty($matches['fields'])) {
            $fields = explode('And', $matches['fields']);
            $fields = $this->__normalizeKeys($fields);
            foreach($fields as $k=>$field){
                $where[$field . " LIKE ?"] = $args[$k];
            }
        }
        return $where;
    }

    /**
     * Parse query conditions
     * @param $matches
     * @param $args
     * @return array
     */
    private function _parseWhere($matches, $args){
        $where = array();
        if (array_key_exists('fields', $matches) && !empty($matches['fields'])) {
            $k = 0;
            $fields = explode('And', $matches['fields']);
            $fields = $this->__normalizeKeys($fields);
            $where = array_combine($fields, $args);
        }else{
            if (count($args)){
                //handle by columns
                $where = $args[0];
            }else{
                $where = array();
            }
        }
        return $where;
    }

    /**
     * Parse order by
     * @param $matches
     * @return array
     */
    private function _parseOrder($matches){
        $order = array();
        if (array_key_exists('orderBy', $matches) && !empty($matches['orderBy'])) {
            $orderBy = $matches['orderBy'];
            $orderBy = explode('And', $orderBy);
            foreach ($orderBy as $value) {
                if (substr($value, -4) == 'Desc')
                    $order[$this->__normalizeKeys(substr($value, 0, -4))] = 'DESC';
                else
                    $order[$this->__normalizeKeys($value)] = 'ASC';
            }
        }
        return $order;
    }

    /**
     * Parse limit and offset
     * @param $matches
     * @return array|null
     */
    private function _parseLimit($matches){
        $limit = (array_key_exists('limit', $matches) ? $matches['limit'] : null);
        $offset = (array_key_exists('offset', $matches) ? $matches['offset'] : null);
        if (!$limit) return null;
        if ($limit && $offset===null) return $limit;
        return array($limit, $offset);
    }

    /**
     * Transform keys from camelCase to underscode
     * @param $keys
     * @return array|string
     */
    private function __normalizeKeys($keys){
        if (!is_array($keys))
            return strtolower(preg_replace('/([A-Z]+)/', '_\1', lcfirst($keys)));
        foreach ($keys as $k => $v)
            $keys[$k] = strtolower(preg_replace('/([A-Z]+)/', '_\1', lcfirst($v)));
        return $keys;
    }
}
