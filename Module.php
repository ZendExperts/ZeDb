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

use Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\Mvc\MvcEvent;

/**
 * ZeDb Module class
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
class Module implements AutoloaderProviderInterface
{
    private static $databaseManager = null;

    public function onBootstrap(MvcEvent $event)
    {
        static::$databaseManager = $event->getApplication()->getServiceManager()->get('ZeDbManager');
    }

    public static function getDatabaseManager(){
        return static::$databaseManager;
    }

    /**
     * Get Autoloader Config
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
//            'Zend\Loader\ClassMapAutoloader' => array(
//                __DIR__ . '/autoload/classmap.php',
//            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return include __DIR__ . '/config/service.config.php';
    }

    /**
     * Get Module Configuration
     * @return mixed
     */
    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php';
        return $config;
    }

}
