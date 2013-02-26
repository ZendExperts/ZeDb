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

use Zend\ServiceManager\ServiceLocatorInterface,
    Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Database Manager class for loading and registering models
 *
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
class DatabaseManager implements ServiceLocatorAwareInterface
{
    /**
     * @var array
     */
    private $models = array();
    private $config = array();

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator = null;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setConfig($config)
    {
        $this->config = $config;
        $instanceManager = $this->getServiceLocator()->get('Di')->instanceManager();
        if (isset($config['models'])) {
            foreach ($config['models'] as $modelClass => $params) {
                $this->models[$params['entityClass']] = $modelClass;
                $instanceManager->setParameters($modelClass, array(
                    'options' => $params
                ));
            }
        }

        if (isset($config['adapter'])) {
            $instanceManager->setParameters('Zend\Db\Adapter\Adapter', array(
                'driver' => $config['adapter']
            ));
        }
    }

    /**
     * Return a instance of the model class associated with the given entity class.
     * If the table name is provided and the entity class is not found in the registry
     *     an instance of ZeDb\Model class is returned with the provided info.
     * If the entity class is not found in the registry it is presumed that the given class is
     *     a model class and it is returned from the locator.
     *
     * @param $id
     * @param null $tableName
     * @return mixed
     */
    public function get($id, $tableName = null)
    {
        //load by entity name from the registered models
        $class = trim($id, '\\');
        if (array_key_exists($class, $this->models) && !$tableName){
            $model = $this->getServiceLocator()->get($this->models[$class]);
            return $model;
        }
        //else if tableName is specified load default model with the entityClass and tableName set
        if ($tableName){
            return $this->getServiceLocator()->get('ZeDb\Model', array(
                'options'=>array(
                    'entityClass'=>$class,
                    'tableName'=>$tableName,
                )
            ));
        }
        //else presume the request is a model class and load it from locator
        return $this->getServiceLocator()->get($class);
    }

}