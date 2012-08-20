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
use Zend\Db\RowGateway\RowGatewayInterface;

/**
 * Interface for any entity class
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
interface EntityInterface extends RowGatewayInterface
{
    /**
     * Return the contained properties as an array
     * @abstract
     * @return array
     */
    public function toArray();

    public function populate(array $rowData);
}