<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    // model to connect
    protected $model;

   // contruct method
    public function __construct()
    {
        $this->setModel();
    }

    // get model of repository
    abstract public function getModel();

    /**
     * Set model
     */

    public function setModel()
    {

        $this->model = app()->make(
            $this->getModel()
        );

    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getAllByKey($column = ['*'], $params = [])
    {
        return $this->model->select($column)->where($params)->get();
    }


    public function getMany($limit, $offset, $filter, $sort = [], $columns = ['*'])
    {

        $instance = $this->model->where($filter)->skip($offset)->take($limit)->get();
        // app()->instance($this->getModel(), $instance);
        return $instance;
    }

    public function find($id, $columns = ['*'])
    {
        $result = $this->model->select($columns)->find($id);
        return $result;
    }

    public function findByKey($key, $value, $columns = ['*'])
    {

        $result = $this->model->select($columns)->where($key, $value)->first();

        return $result;
    }

    public function findByCondition($condition = [])
    {
        $result = $this->model->where($condition)->first();
        return $result;
    }

    public function create($attributes = [])
    {
        return $this->model->insertGetId($attributes);
    }

    public function update($id, $attributes = [])
    {
        $result = $this->find($id);

        if ($result) {
            $result->update($attributes);

            return $result;
        }

        return false;
    }

    public function updateMany($ids, $attributes = [])
    {
        return $this->model->whereIn('id', $ids)->update($attributes);
    }

    public function delete($id)
    {
        $result = $this->find($id);
        if ($result) {
            $result->delete();

            return true;
        }

        return false;
    }

    public function deleteMany($ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function count($params = [])
    {
        if ($params) {
            $result = $this->model->where($params)->count();
        } else {
            $result = $this->model->count();
        }

        return $result;
    }

    public function increment($id, $column) {
        $result = $this->model->where('id', $id)->increment($column, 1);
        return $result;
    }

    public function decrement($id, $column) {
        $result = $this->model->where('id', $id)->decrement($column, 1);
        return $result;
    }

}
