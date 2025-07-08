<?php

namespace App\Services;

use App\Models\BrandModel;
use App\Transformers\AgencyTransformer;
use Illuminate\Support\Str;

class BrandService
{
    public static function totalRows($params)
    {
        $result = BrandModel::totalRows($params);
        return $result;
    }

    public static function getMany($limit, $offset, $filter)
    {
        $result = BrandModel::getMany($limit, $offset, $filter);
        return $result ? $result : [];
    }
    public static function findByKey($key, $value)
    {
        $result = BrandModel::findByKey($key, $value);
        return $result ? $result : [];
    }
    public function insert($params)
    {
        $insert['Brand_title'] = $params['Brand_title'];
        $insert['Brand_description'] = $params['Brand_description'];
        $insert['Brand_link'] = isset($params['Brand_link']) ? $params['Brand_link'] : '';
        $insert['Brand_thumbnail'] = isset($params['Brand_thumbnail']) ? $params['Brand_thumbnail'] : '';
        $insert['Brand_status'] = $params['Brand_status'];
        $insert['language'] = isset($params['language']) ? $params['language'] : 'vi';

        $insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
        $insert['updated_at'] = date("Y-m-d H:i:s");
        return BrandModel::insert($insert);
    }
    public function update($id, $params)
    {
        $update['Brand_title'] = $params['Brand_title'];
        $update['Brand_description'] = $params['Brand_description'];
        $update['Brand_link'] = isset($params['Brand_link']) ? $params['Brand_link'] : '';
        $update['Brand_thumbnail'] = isset($params['Brand_thumbnail']) ? $params['Brand_thumbnail'] : '';
        $update['Brand_status'] = $params['Brand_status'];
        $update['language'] = isset($params['language']) ? $params['language'] : 'vi';
        $update['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
        $update['updated_at'] = date("Y-m-d H:i:s");
        return BrandModel::update($id, $update);
    }

    public function updateMany($ids, $data)
    {
        return BrandModel::updateManyBrand($ids, $data);
    }

    public function deleteMany($ids)
    {
        return BrandModel::deleteManyBrand($ids);
    }

    public function delete($id)
    {
        return BrandModel::delete($id);
    }

    public function getList(array $params)
    {
        $params['query'] = isset($params['query']) ? $params['query'] : '';
        $total = self::totalRows($params['query']);
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
        $column = ['id', 'Brand_title', 'Brand_status', 'language', 'created_at', 'Brand_thumbnail'];
        $result = BrandModel::getMany($column, $pagination, $sort, $query);

        $data['data'] = $result;
        $data['meta']['page'] = isset($pagination['page']) ? $pagination['page'] : 1;
        $data['meta']['perpage'] = isset($pagination['perpage']) ? $pagination['perpage'] : 20;
        $data['meta']['total'] = $total;
        $data['meta']['pages'] = ceil($total / $data['meta']['perpage']);
        $data['meta']['rowIds'] = self::getListIDs($result);
        return $data;
    }

    public function getListIDs($data)
    {

        $ids = array();

        foreach ($data as $row) {
            array_push($ids, $row->id);
        }

        return $ids;
    }

    public static function takeNew($quantity, $filter)
    {
        return BrandModel::takeNew($quantity, $filter);
    }
}
