<?php
namespace App\Services;

use App\Models\PartnerModel;
use App\Transformers\AgencyTransformer;
use Illuminate\Support\Str;

class PartnerService
{
	public static function totalRows($params) {
        $result = PartnerModel::totalRows($params);
        return $result;
	}
	
	public static function getMany($limit, $offset, $filter)
	{
		$result = PartnerModel::getMany($limit, $offset, $filter);
        return $result ? $result : [];
	}
	public static function findByKey($key, $value)
	{
        $result = PartnerModel::findByKey($key, $value);
        return $result ? $result : [];
	}
	public function insert($params)
	{
		$insert['partner_title'] = $params['partner_title']; 
		$insert['partner_description'] = $params['partner_description']; 
		$insert['partner_link'] = isset($params['partner_link']) ? $params['partner_link'] : '';
        $insert['partner_thumbnail'] = isset($params['partner_thumbnail']) ? $params['partner_thumbnail'] : ''; 
		$insert['partner_status'] = $params['partner_status']; 
		$insert['language'] = isset($params['language']) ? $params['language'] : 'vi'; 
		
		$insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s"); 
		$insert['updated_at'] = date("Y-m-d H:i:s"); 
		return PartnerModel::insert($insert);		
	}
	public function update($id, $params)
	{
		$update['partner_title'] = $params['partner_title']; 
		$update['partner_description'] = $params['partner_description']; 
		$update['partner_link'] = isset($params['partner_link']) ? $params['partner_link'] : '';
        $update['partner_thumbnail'] = isset($params['partner_thumbnail']) ? $params['partner_thumbnail'] : ''; 
		$update['partner_status'] = $params['partner_status']; 
		$update['language'] = isset($params['language']) ? $params['language'] : 'vi'; 
		$update['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s"); 
		$update['updated_at'] = date("Y-m-d H:i:s"); 
		return PartnerModel::update($id, $update);		
	}

	public function updateMany($ids, $data)
    {
        return PartnerModel::updateManyPartner($ids, $data);
	}
	
	public function deleteMany($ids)
    {
        return PartnerModel::deleteManyPartner($ids);
	}
	
	public function delete($id)
	{
		return PartnerModel::delete($id);		
	}

	public function getList(array $params)
    {
		$params['query'] = isset($params['query']) ? $params['query'] : '';
        $total = self::totalRows($params['query']);
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
		$column = ['id', 'partner_title', 'partner_status', 'language', 'created_at', 'partner_thumbnail'];
        $result = PartnerModel::getMany($column, $pagination, $sort, $query);

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

	public static function takeNew($quantity, $filter)
    {
        return PartnerModel::takeNew($quantity, $filter);
    }
}