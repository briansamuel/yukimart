<?php

namespace App\Services;

use App\Models\ContactModel;
use App\Transformers\ContactTransformer;

class ContactService
{

    public static function totalRows() {
        $result = ContactModel::totalRows();
        return $result;
    }

    public static function add($params)
    {
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return ContactModel::add($params);
    }

    public static function addFromFrontEnd($params)
    {
        $params['subject'] = 'Thông tin liên hệ của khách gửi từ Liên hệ';
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");

        return ContactModel::add($params);
    }

    public function edit($id, $params)
    {
        $params['updated_at'] = date("Y-m-d H:i:s");
        return ContactModel::update($id, $params);
    }

    public function deleteMany($ids)
    {
        return ContactModel::deleteMany($ids);
    }

    public function updateMany($ids, $data)
    {
        return ContactModel::updateMany($ids, $data);
    }

    public function delete($ids)
    {
        return ContactModel::delete($ids);
    }

    public function detail($id)
    {
        $detail = ContactModel::findById($id);
        return ContactTransformer::transformItem($detail);
    }

    public function getList(array $params)
    {
        $total = self::totalRows();
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];

        $result = ContactModel::getMany($pagination, $sort, $query);

        $data['data'] = $result;
        $data['meta']['page'] = isset($pagination['page']) ? $pagination['page'] : 1;
        $data['meta']['perpage'] = isset($pagination['perpage']) ? $pagination['perpage'] : 20;
        $data['meta']['total'] = $total;
        $data['meta']['pages'] = ceil($total / $data['meta']['perpage']);
        $data['meta']['rowIds'] = self::getListIDs($result);

        return $data;
    }

    public function getListIDs($data) {

        $ids = array();

        foreach($data as $row) {
            array_push($ids, $row->id);
        }

        return $ids;
    }

    public static function takeNew($quantity)
    {
        return ContactModel::takeNew($quantity);
    }

}
