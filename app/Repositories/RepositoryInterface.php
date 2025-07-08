<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Get all
     * @return mixed
     */
    public function getAll();

     /**
     * Get all by keys
     * @return mixed
     */
    public function getAllByKey($params);

     /**
     * Get Many
     * @return mixed
     */
    public function getMany($limit, $offset, $filter, $sort = [], $columns = ['*']);

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function find($id, $columns = ['*']);

     /**
     * FindByKey
     * @param $id
     * @return mixed
     */
    public function findByKey($key, $value, $columns = ['*']);
    
     /**
     * FindByKey
     * @param $id
     * @return mixed
     */
    public function findByCondition($condition = []);

    /**
     * Create
     * @param array $attributes
     * @return mixed
     */

    
    public function create($attributes = []);

    /**
     * Update
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, $attributes = []);

    /**
     * Update
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function updateMany($ids, $attributes = []);

    /**
     * Delete
     * @param $id
     * @return mixed
     */
    public function delete($id);
    
    /**
     * Delete many
     * @param $id
     * @return mixed
     */
    public function deleteMany($ids);


     /**
     * Count
     * @param $id
     * @return mixed
     */

    public function count($params);


     /**
     * Increment
     * @param $id
     * @return mixed
     */

    public function increment($id, $column);
    
}