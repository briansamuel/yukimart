<?php
namespace App\Services;

use App\Models\ProvinceModel;

use Illuminate\Support\Str;

class ProvinceService
{
	public static function totalRows($params) {
        $result = ProvinceModel::totalRows($params);
        return $result;
	}
	
	public static function getMany($column, $filter)
	{
       
		$result = ProvinceModel::getMany($column, $filter);
        return $result ? $result : [];
    }
    
	public function findByKey($key, $value)
	{
        $result = ProvinceModel::findByKey($key, $value);
        return $result ? $result : [];
    }
    
	
	public function getList(array $params)
    {
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
		$total = self::totalRows($query);

        $result = ProvinceModel::getMany($pagination, $query);

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
}