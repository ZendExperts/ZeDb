<?php
/**
 * This file is part of ZeDb
 *
 * (c) 2012 ZendExperts <team@zendexperts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ZeDb\Service;

use Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface,
    ZeDb\DatabaseManager;

/**
 * ZeDb Database Manager service factory
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
class DatabaseManagerFactory implements FactoryInterface
{

    /**
     * Create and return a DatabaseManager instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DatabaseManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Configuration');
        $config = isset($config['zendexperts_zedb']) && (is_array($config['zendexperts_zedb']) || $config['zendexperts_zedb'] instanceof ArrayAccess)
            ? $config['zendexperts_zedb']
            : array();

        $dbManager = new DatabaseManager();
        $dbManager->setServiceLocator($serviceLocator);
        $dbManager->setConfig($config);
        return $dbManager;
    }

}