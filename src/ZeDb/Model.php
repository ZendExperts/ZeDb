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
use Zend\Db\Adapter\Adapter,
    Zend\Db\ResultSet\ResultSet,
    ZeDb\TableGateway,
    ZeDb\Module as ZeDb,
    string;
/**
 * Model Class
 * Loads mapper entities from the database and stores them in a local container for later use.
 * Saves entities into the local container before flushing them to the database.
 * Also contains methods for easy access to the most common queries used on the database.
 *
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
class Model extends TableGateway implements ModelInterface
{
    /**
     * @var string
     */
    protected $entityClass = '\ZeDb\Entity';
    protected $tableName = null;
    protected $primaryKey = 'id';
    /**
     * @var array
     */
    protected $_entities = array();
    /**
     * @var array
     */
    protected $_localEntities = array();

    /**
     * @param array $options
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    public function __construct(Adapter $adapter, $options = null){
        if (!$options){
            $options=array();
        }
        //set the table name from config if specified or take it from the child class
        if (array_key_exists('tableName', $options)){
            $tableName = $options['tableName'];
        }else
            $tableName = $this->tableName;

        //set the entity class from the config or from the child class if none defined
        if (array_key_exists('entityClass', $options)){
            $entityClass = trim($options['entityClass'],'\\');
        }else
            $entityClass = trim($this->entityClass,'\\');

        //init the result set to return instances of the entity class
        $this->entityClass = $entityClass;
        $resultSet = new ResultSet(ResultSet::TYPE_ARRAYOBJECT, new $entityClass);
        //init the parent class
        parent::__construct($tableName, $adapter, null, $resultSet);
    }

    /**
     * Return the entity class handled by the model
     * @return string
     */
    public function getEntityClass(){
        return $this->entityClass;
    }

    public function setOptions(array $options)
    {
        //set the table name from config if specified or take it from the child class
        if (array_key_exists('tableName', $options)) {
            $tableName = $options['tableName'];
        } else $tableName = $this->tableName;

        //set the entity class from the config or from the child class if none defined
        if (array_key_exists('entityClass', $options)) {
            $entityClass = trim($options['entityClass'], '\\');
        } else $entityClass = trim($this->entityClass, '\\');

        $this->entityClass = $entityClass;
        $this->tableName = $tableName;
    }

    /**
     * Set the primary key field
     * @param $primaryKey
     * @return Model
     */
    public function setPrimaryKey($primaryKey){
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * Get the name of the primary key field
     * @return string
     */
    public function getPrimaryKey(){
        return $this->primaryKey;
    }

    /**
     * Get the registry instance that contains all the modules
     * @return \ZeDb\DatabaseManager
     */
    public function getDatabaseManager(){
        return ZeDb::getDatabaseManager();
    }

    /**
     * Persists an entity into the model
     * @param mixed $entities
     * @return Model
     */
    public function persist($entities){
        if ($entities instanceof EntityInterface){
            if ($entities[$this->primaryKey]){
                $this->_entities[$entities[$this->primaryKey]] = $entities;
            }
            $this->_localEntities[] = $entities;
        }elseif (is_array($entities)){
            foreach($entities as $entity){
                if ($entity[$this->primaryKey]){
                    $this->_entities[$entity[$this->primaryKey]] = $entity;
                }
                $this->_localEntities[] = $entity;
            }
        }
        return $this;
    }

    /**
     * Saves the persisted entities into the database
     * @return Model
     */
    public function flush(){
        $unset = array();
        foreach($this->_localEntities as $key => $entity){
            $entity = $this->save($entity);
            $this->_entities[$entity[$this->primaryKey]] = $entity;
            $unset[] = $key;
        }
        foreach($unset as $key){
            unset($this->_localEntities[$key]);
        }
        return $this;
    }

    /**
     * Save an entity directly in the database
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function save(EntityInterface $entity){
        $data = $entity->toArray();
        if ($data[$this->primaryKey]){
            $this->update($data, array($this->primaryKey => $data[$this->primaryKey]));
        }else{
            unset($data[$this->primaryKey]);
            $this->insert($data);
            $id = $this->getLastInsertValue();
            $data[$this->primaryKey] = $id;
            $entity->populate($data);
        }
        return $entity;
    }

    /**
     * Create entity from array
     * @param array|null $data
     * @return mixed
     */
    public function create($data = null){
        $entityClass = $this->entityClass;
        $entity = new $entityClass();
        if ($data) {
            $entity->populate($data);
        }
        return $entity;
    }

    /**
     * Get an entity by Id
     * @param int $id
     * @return EntityInterface | null
     */
    public function get($id){
        //Load from repository if found
        if(array_key_exists($id,$this->_entities)){
            return $this->_entities[$id];
        }

        //Load from the database otherwise
        $entity = $this->getById($id);
        if (!$entity) {
            return null;
        }

        //Save in the repository for later use
        $this->_entities[$entity[$this->primaryKey]] = $entity;
        return $entity;
    }

    /**
     * Handles all function calls to the model.
     * Defines magic functions for retrieving records by columns with order and limit.
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args){
        if (substr($name, 0, 3) == 'get'){
            $entities = parent::__call($name, $args);
            if (!$entities){
                return null;
            }elseif (is_array($entities)){
                foreach($entities as $entity) {
                    $this->_entities[$entity[$this->primaryKey]] = $entity;
                }
            }else if ($entities instanceof EntityInterface){
                $this->_entities[$entities[$this->primaryKey]] = $entities;
            }
            return $entities;
        }
        return parent::__call($name, $args);
    }

}