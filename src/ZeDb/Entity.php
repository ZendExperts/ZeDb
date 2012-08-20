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
use Zend\Db\ResultSet;
use ArrayObject;

/**
 * Basic Entity Class
 * Contains all the properties of the entity object under one object property
 * as an array for easy access and speed when converting from and to an array
 *
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
class Entity implements EntityInterface, \ArrayAccess, \Countable
{
    /**
     * List of propertied for the entity class
     * @var array
     */
    protected $_data = array();

    #########################
    ### General Functions ###
    #########################

    /**
     * Save the entity in the database
     */
    public function save()
    {
        $model = $this->getModel();
        return $model->save($this);
    }

    /**
     * Remove the entity from the database
     */
    public function delete()
    {
        $model = $this->getModel();
        $model->removeById($this->id);
    }

    /**
     * Return an instance of the related model class
     * @return \ZeDb\ModelInterface
     */
    protected function getModel()
    {
        return Module::getDatabaseManager()->get(get_called_class());
    }


    /**
     * Return the contained properties as an array
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return boolean Returns true on success or false on failure.
     *
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->_data))
            return $this->_data[$offset];
        return null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    /**
     * @param $offset
     * @param $value
     */
    public function __set($offset, $value)
    {
        $this->offsetSet($offset, $value);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function __get($offset){
        return $this->offsetGet($offset);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /**
     * Exchange the properties contained in the entity with the ones defined in the $input array
     * @param array $input
     * @return \ZeDb\Entity
     */
    public function exchangeArray($input)
    {
        $this->_data = $input;
        return $this;
    }

    /**
     * @param array $rowData
     * @return Row
     */
    public function populate(array $rowData)
    {
        $this->exchangeArray($rowData);
        return $this;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_data);
    }
}