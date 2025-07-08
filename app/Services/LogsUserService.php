<?php

namespace App\Services;

use App\Models\LogsUser;
use App\Transformers\LogsUserTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogsUserService
{

    public static function totalRows() {
        $result = LogsUser::totalRows();
        return $result;
    }

    public function detail($id)
    {
        return LogsUser::findById($id);
    }

    public function getList(array $params)
    {
        $total = self::totalRows();
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];

        $result = LogsUser::getMany($pagination, $sort, $query);
        $result = LogsUserTransformer::transformCollection($result);

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

    public static function add($data)
    {
        if(!isset($data) || !$data || empty($data)){
            return false;
        }

        $data['user_id'] = Auth::guard('admin')->user()->id;
        $data['full_name'] = Auth::guard('admin')->user()->full_name;
        $data['email'] = Auth::guard('admin')->user()->email;
        $data['timestamp'] = time();

        return LogsUser::insert($data);
    }

}
