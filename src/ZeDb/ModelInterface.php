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

/**
 * Model Interface with available methods for loading and saving data
 *
 * @package ZeDb
 * @author Cosmin Harangus <cosmin@zendexperts.com>
 */
interface ModelInterface
{
    /**
     * Persists an entity into the model
     * @abstract
     * @param mixed $entities
     * @return Model
     */
    public function persist($entities);

    /**
     * Return the class of the handled entities
     * @abstract
     * @return string
     */
    public function getEntityClass();

    /**
     * Saves the persisted entities into the database
     * @abstract
     * @return \bool
     */
    public function flush();

    /**
     * Save an entity directly in the database
     * @abstract
     * @param EntityInterface $entity
     */
    public function save(EntityInterface $entity);

    /**
     * Get an entity by Id
     * @abstract
     * @param int $id
     * @return EntityInterface | null
     */
    public function get($id);

    /**
     * Create a new entity object with the provided data
     * @abstract
     * @param array $data
     */
    public function create($data=null);
}