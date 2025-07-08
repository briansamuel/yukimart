<?php

namespace App\Services;

use App\Models\SubcribeEmailsModel;

class SubcribeEmailsService
{

    public static function totalRows() {
        $result = SubcribeEmailsModel::totalRows();
        return $result;
    }

    public static function add($params)
    {
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");

        return SubcribeEmailsModel::add($params);
    }

    public static function addFromFrontEnd($params)
    {
        $params['active'] = 'yes';
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");

        return SubcribeEmailsModel::add($params);
    }

    public function edit($id, $params)
    {
        $params['updated_at'] = date("Y-m-d H:i:s");

        return SubcribeEmailsModel::update($id, $params);
    }

    public function deleteMany($ids)
    {
        return SubcribeEmailsModel::deleteMany($ids);
    }

    public function delete($ids)
    {
        return SubcribeEmailsModel::delete($ids);
    }

    public function detail($id)
    {
        return SubcribeEmailsModel::findById($id);
    }

    public static function getByEmail(string $email)
    {
        return SubcribeEmailsModel::findByKey('email', $email);
    }

    public function getList(array $params)
    {
        $total = self::totalRows();
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];

        $result = SubcribeEmailsModel::getMany($pagination, $sort, $query);

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

    public static function checkEmailExist($email) {
        $agent = SubcribeEmailsModel::checkEmailExist($email);
        if($agent) {
            return true;
        }

        return false;
    }

}
