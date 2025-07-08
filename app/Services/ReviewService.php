<?php

namespace App\Services;

use App\Models\ReviewModel;
use App\Transformers\ContactTransformer;

class ReviewService
{

    public static function getAll($columns, $filter)
    {
        $result = ReviewModel::getAll($columns, $filter);
        return $result;
    }

    public static function getMany($colum, $pagination, $sort, $query)
    {
        $result = ReviewModel::getMany($colum, $pagination, $sort, $query);
        return $result;
    }

    public static function totalRows() {
        $result = ReviewModel::totalRows();
        return $result;
    }

    public function insert($params)
	{
		
		$insert['host_id'] = $params['host_id']; 
		$insert['review_title'] = $params['review_title']; 
		$insert['review_content'] = isset($params['review_content']) ? $params['review_content'] : '';
        $insert['rating_review'] = isset($params['rating_review']) ? $params['rating_review'] : 0;
        $insert['review_image'] = isset($params['review_image']) ? $params['review_image'] : '[]';
        $insert['review_status'] = isset($params['review_status']) ? $params['review_status'] : 'publish';
        $insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
        $insert['name_guest'] = isset($params['name_guest']) ? $params['name_guest'] : 'N/A';
        $insert['created_by_guest'] = isset($params['created_by_guest']) ? $params['created_by_guest'] : 0;
		$insert['created_at'] = date("Y-m-d H:i:s"); 
		$insert['updated_at'] = date("Y-m-d H:i:s"); 
		return ReviewModel::insert($insert);		
	}
	public function update($id, $params)
	{
		
		$insert['review_title'] = $params['review_title']; 
		$insert['review_content'] = isset($params['review_content']) ? $params['review_content'] : '';
        $insert['review_status'] = isset($params['review_status']) ? $params['review_status'] : 'publish';
        $insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$update['updated_at'] = date("Y-m-d H:i:s"); 
		return ReviewModel::update($id, $update);		
	}


    public function deleteMany($ids)
    {
        return ReviewModel::deleteMany($ids);
    }

    public function updateMany($ids, $data)
    {
        return ReviewModel::updateMany($ids, $data);
    }

    public function delete($ids)
    {
        return ReviewModel::delete($ids);
    }

    public function detail($id)
    {
        $detail = ReviewModel::findById($id);
        $host = HostService::findByKey('id', $detail->host_id);
        if($host) {
            $detail->host_name = $host->host_name;
        } else {
            $detail->host_name = 'Không tìm thấy khách sạn';
        }
        return ContactTransformer::transformItem($detail);
    }

    public function getList(array $params)
    {
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
        $column = isset($params['column']) ? $params['column'] : ['*'];
		$total = self::totalRows($query);

        $result = ReviewModel::getMany($column, $pagination, $sort, $query);
        foreach($result as $review) {
            $host = HostService::findByKey('id', $review->host_id);
            if($host) {
                $review->host_name = $host->host_name;
            } else {
                $review->host_name = 'Không tìm thấy khách sạn';
            }
        }
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
        return ReviewModel::takeNew($quantity, $filter);
    }

}
