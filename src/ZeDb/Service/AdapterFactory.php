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

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;
/**
 *
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
class AdapterFactory implements FactoryInterface
{

    /**
     * Create and return an Adapter instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['zendexperts_zedb']) && (is_array($config['zendexperts_zedb']) || $config['zendexperts_zedb'] instanceof ArrayAccess)
            ? $config['zendexperts_zedb']
            : array();

        $adapter = new \Zend\Db\Adapter\Adapter($config['adapter']);
        return $adapter;
    }

}