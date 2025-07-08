<?php
namespace App\Services;

use App\Models\RoomModel;

use Illuminate\Support\Str;

class RoomService
{
	public static function totalRows($params) {
        $result = RoomModel::totalRows($params);
        return $result;
	}
	
	public function getMany($pagination, $sort, $query)
	{
		$result = RoomModel::getMany($pagination, $sort, $query);
        return $result ? $result : [];
	}

	public static function findByKey($key, $value, $column)
	{
        $result = RoomModel::findByKey($key, $value, $column);
        return $result ? $result : [];
	}

	public static function findRoomSort($key, $value, $column, $sort) {
		$result = RoomModel::findRoomSort($key, $value, $column, $sort);
        return $result ? $result : [];
	}

	public function insert($params)
	{
		$insert['room_name'] = $params['room_name']; 
		$insert['host_id'] = $params['host_id']; 
		$insert['room_description'] = $params['room_description']; 
		$insert['room_area'] = isset($params['room_area']) ? $params['room_area'] : 0;
        $insert['room_convenient'] =  $params['room_convenient'];
		$insert['room_option'] =  $params['room_option'];
		$insert['room_gallery'] =  isset($params['room_gallery']) ? $params['room_gallery'] : '[]';
        $insert['price_one_night'] =  isset($params['price_one_night']) ? $params['price_one_night'] : 0;
        $insert['sale_for_room'] =  isset($params['sale_for_room']) ? $params['sale_for_room'] : 0;
        $insert['guest_amount'] =  isset($params['guest_amount']) ? $params['guest_amount'] : 1;
        $insert['room_amount_empty'] =  isset($params['room_amount_empty']) ? $params['room_amount_empty'] : 0;
		$insert['room_status'] =  isset($params['room_status']) ? $params['room_status'] : 'available_room';
		$insert['language'] =  isset($params['language']) ? $params['language'] : 'vi';
		$insert['created_by_agent'] = isset($params['created_by_agent']) ? $params['created_by_agent'] : 0; 
		$insert['updated_by_agent'] = isset($params['updated_by_agent']) ? $params['updated_by_agent'] : 0; 
		$insert['created_at'] = date("Y-m-d H:i:s"); 
		$insert['updated_at'] = date("Y-m-d H:i:s"); 
		return RoomModel::insert($insert);		
	}
	public function update($id, $params)
	{
		$update['room_name'] = $params['room_name']; 
		$update['room_description'] = $params['room_description']; 
		$update['room_area'] = isset($params['room_area']) ? $params['room_area'] : 0;
        $update['room_convenient'] =  $params['room_convenient'];
		$update['room_option'] =  $params['room_option'];
		$update['room_gallery'] =  isset($params['room_gallery']) ? $params['room_gallery'] : '[]';
        $update['price_one_night'] =  isset($params['price_one_night']) ? $params['price_one_night'] : 0;
        $update['sale_for_room'] =  isset($params['sale_for_room']) ? $params['sale_for_room'] : 0;
        $update['guest_amount'] =  isset($params['guest_amount']) ? $params['guest_amount'] : 1;
        $update['room_amount_empty'] =  isset($params['room_amount_empty']) ? $params['room_amount_empty'] : 0;
		$update['room_status'] =  isset($params['room_status']) ? $params['room_status'] : 'available_room';
		$update['language'] =  isset($params['language']) ? $params['language'] : 'vi';
		$update['updated_by_agent'] = isset($params['updated_by_agent']) ? $params['updated_by_agent'] : 0; 
		$update['updated_at'] = date("Y-m-d H:i:s"); 
		return RoomModel::update($id, $update);		
	}

	public function updateMany($ids, $data)
    {
        return RoomModel::updateManyRoom($ids, $data);
	}
	
	public function deleteMany($ids)
    {
        return RoomModel::deleteManyRoom($ids);
	}
	
	public function delete($id)
	{
		return RoomModel::delete($id);		
	}

	public function getList(array $params)
    {
        
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
		$total = self::totalRows($query);

		$result = RoomModel::getMany($pagination, $sort, $query);
	
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