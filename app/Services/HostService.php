<?php
namespace App\Services;

use App\Models\HostModel;

use Illuminate\Support\Str;

class HostService
{
	public static function totalRows($params) {
        $result = HostModel::totalRows($params);
        return $result;
	}
	
	public static function getMany($pagination, $sort, $query)
	{
		$result = HostModel::getMany($pagination, $sort, $query);
        return $result ? $result : [];
	}

	public static function filterHost($columns, $pagination, $sort, $query)
	{
		$result = HostModel::filterHost($columns, $pagination, $sort, $query);
        return $result ? $result : [];
	}

	public static function getAll($column, $filter)
	{
		$result = HostModel::getAll($column, $filter);
        return $result ? $result : [];
	}

	public static function findByKey($key, $value)
	{
		$result = HostModel::findByKey($key, $value);
		if($result) {

			$rooms = RoomService::findByKey('host_id', $result->id, ['id', 'room_name', 'room_gallery', 'room_status', 'price_one_night', 'room_amount_empty']);
			$result->rooms =  $rooms;
		}
        return $result ? $result : [];
	}
	public function insert($params)
	{
		$insert['host_name'] = $params['host_name']; 
		$insert['host_slug'] = isset($params['host_slug']) ? $params['host_slug'] : Str::slug($params['host_name']);
		$insert['host_description'] = $params['host_description']; 
		$insert['host_thumbnail'] = isset($params['host_thumbnail']) ? $params['host_thumbnail'] : '';
		$insert['host_policy'] = isset($params['host_policy']) ? $params['host_policy'] : '';
        $insert['host_convenient'] =  $params['host_convenient'];
        $insert['host_address'] =  $params['host_address']; 
        $insert['host_lat'] =  $params['host_lat'];
        $insert['host_lng'] =  $params['host_lng'];
		$insert['host_gallery'] =  $params['host_gallery'];
		$insert['host_star'] =  isset($params['host_star']) ? $params['host_star'] : 1;
        $insert['host_type'] =  isset($params['host_type']) ? $params['host_type'] : 'hotel';
        $insert['province_id'] =  isset($params['province_id']) ? $params['province_id'] : 0;
		$insert['district_id'] =  isset($params['district_id']) ? $params['district_id'] : 0;
		$insert['ward_id'] =  isset($params['ward_id']) ? $params['ward_id'] : 0;
        $insert['province_name'] =  isset($params['province_name']) ? $params['province_name'] : '';
		$insert['district_name'] =  isset($params['district_name']) ? $params['district_name'] : '';
		$insert['ward_name'] =  isset($params['ward_name']) ? $params['ward_name'] : '';
		$insert['language'] = isset($params['language']) ? $params['language'] : 'vi'; 
		$insert['created_by_agent'] = isset($params['created_by_agent']) ? $params['created_by_agent'] : 0; 
		$insert['updated_by_agent'] = isset($params['updated_by_agent']) ? $params['updated_by_agent'] : 0; 
		$insert['created_at'] = date("Y-m-d H:i:s"); 
		$insert['updated_at'] = date("Y-m-d H:i:s"); 
		return HostModel::insert($insert);		
	}
	public function update($id, $params)
	{
		$update['host_name'] = $params['host_name']; 
		$update['host_slug'] = isset($params['host_slug']) ? $params['host_slug'] : Str::slug($params['host_name']);
		$update['host_description'] = $params['host_description']; 
		$update['host_thumbnail'] = isset($params['host_thumbnail']) ? $params['host_thumbnail'] : '';
		$update['host_policy'] = isset($params['host_policy']) ? $params['host_policy'] : '';
        $update['host_convenient'] =  $params['host_convenient'];
        $update['host_address'] =  $params['host_address']; 
        $update['host_lat'] =  $params['host_lat'];
        $update['host_lng'] =  $params['host_lng'];
		$update['host_gallery'] =  $params['host_gallery'];
		$update['host_star'] =  isset($params['host_star']) ? $params['host_star'] : 1;
		$update['host_type'] =  isset($params['host_type']) ? $params['host_type'] : 'hotel';
        $update['province_id'] =  isset($params['province_id']) ? $params['province_id'] : 0;
		$update['district_id'] =  isset($params['district_id']) ? $params['district_id'] : 0;
		$update['ward_id'] =  isset($params['ward_id']) ? $params['ward_id'] : 0;
        $update['province_name'] =  isset($params['province_name']) ? $params['province_name'] : '';
		$update['district_name'] =  isset($params['district_name']) ? $params['district_name'] : '';
		$update['ward_name'] =  isset($params['ward_name']) ? $params['ward_name'] : '';
		$update['language'] =  isset($params['language']) ? $params['language'] : 'vi';
		$update['updated_by_agent'] = isset($params['updated_by_agent']) ? $params['updated_by_agent'] : 0;
		$update['updated_at'] = date("Y-m-d H:i:s");
		return HostModel::update($id, $update);		
	}

	public function updateMany($ids, $data)
    {
        return HostModel::updateManyHost($ids, $data);
	}
	
	public function deleteMany($ids)
    {
        return HostModel::deleteManyHost($ids);
	}
	
	public function delete($id)
	{
		return HostModel::delete($id);		
	}

	public function getList(array $params)
    {
        
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
		$total = self::totalRows($query);
        $result = HostModel::getMany($pagination, $sort, $query);

        $data['data'] = $result;
        $data['meta']['page'] = isset($pagination['page']) ? $pagination['page'] : 1;
        $data['meta']['perpage'] = isset($pagination['perpage']) ? $pagination['perpage'] : 20;
        $data['meta']['total'] = $total;
        $data['meta']['pages'] = ceil($total / $data['meta']['perpage']);
		$data['meta']['rowIds'] = self::getListIDs($result);
        return $data;
	}
	
	public function filterList(array $params)
    {
        
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
		$query = isset($params['query']) ? $params['query'] : [];
		$columns = isset($params['columns']) ? $params['columns'] : ['*'];
		$total = self::totalRows($query);
        $result = HostModel::filterHost($columns, $pagination, $sort, $query);

        $data['data'] = $result;
        $data['meta']['page'] = isset($pagination['page']) ? $pagination['page'] : 1;
        $data['meta']['perpage'] = isset($pagination['perpage']) ? $pagination['perpage'] : 20;
        $data['meta']['total'] = $total;
        $data['meta']['pages'] = (int) ceil($total / $data['meta']['perpage']);
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

    /**
     * function get shop near buy current location
     */
    public static function getShopNear($lat, $long, $radius = 3, $limit)
    {
        $result = HostModel::getShopNear($lat, $long, $radius, $limit);

        return $result;
    }

    public function fixLocation()
    {
        $data = HostModel::getAll(["*"], []);
        foreach($data as $item) {
            $location = explode(",", $item->host_location);
            $lat = $location[0];
            $lng = $location[1];
            HostModel::update($item->id, ['host_lat' => $lat, 'host_lng' => $lng]);
        }
	}
	
	public static function countGroupBy($column) {
		$result = HostModel::countGroupBy($column);
		foreach($result as $item) {
            $data[$item->$column] = $item->total;
		}
		return $data;
    }
}