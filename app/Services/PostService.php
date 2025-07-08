<?php
namespace App\Services;

use App\Repositories\Post\PostRepositoryInterface;
use Illuminate\Support\Str;

class PostService
{
    protected $postRepo;

    public function __construct(PostRepositoryInterface $postRepo)
    {
        $this->postRepo = $postRepo;
    }

	public function totalRows($params) {
        $result = $this->postRepo->count($params);
        return $result;
	}

	public function getMany($limit, $offset, $filter)
	{
		$result = $this->postRepo->getMany($limit, $offset, $filter);
        return $result ? $result : [];
	}

	public function searchNews($keyword)
	{
	    $filter = array('post_status' => 'publish', 'post_type' => 'news');
		$result = $this->postRepo->search($keyword, $filter);
        return $result ? $result : [];
	}

	public function findByKey($key, $value)
	{
        $result = $this->postRepo->findByKey($key, $value);
        return $result ? $result : [];
	}

	public function findBySlug($slug)
	{
	    $condition['post_slug'] = $slug;
	    $condition['post_status'] = 'publish';
        $result = $this->postRepo->findByCondition($condition);
        return $result ? $result : [];
	}

	public function insert($params)
	{
		$insert['post_title'] = $params['post_title'];
		$insert['post_slug'] = isset($params['post_slug']) ? Str::slug($params['post_slug']) : Str::slug($params['post_title']);
		$insert['post_description'] = $params['post_description'];
		$insert['post_content'] = $params['post_content'];
		$insert['post_seo_title'] = isset($params['post_seo_title']) ? $params['post_seo_title'] : '';
		$insert['post_seo_description'] = isset($params['post_seo_description']) ? $params['post_seo_description'] : '';
		$insert['post_seo_keyword'] = isset($params['post_seo_keyword']) ? $params['post_seo_keyword'] : '';
        $url_p =  parse_url($params['post_thumbnail']);
        $insert['post_thumbnail'] = isset($url_p['path']) ?  $url_p['path'] :  $params['post_thumbnail'];
		$insert['post_author'] = isset($params['post_author']) ? $params['post_author'] : 0;
		$insert['post_status'] = $params['post_status'];
		$insert['post_type'] = isset($params['post_type']) ? $params['post_type'] : 'post';
		$insert['post_feature'] = isset($params['post_feature']) ? $params['post_feature'] : 0;
		$insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$insert['created_by_user'] = isset($params['created_by_user']) ? $params['created_by_user'] : 0;
		$insert['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
		$insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
		$insert['updated_at'] = date("Y-m-d H:i:s");
		return $this->postRepo->create($insert);
	}
	public function update($id, $params)
	{
		$update['post_title'] = $params['post_title'];
		$update['post_slug'] = isset($params['post_slug']) ? $params['post_slug'] : Str::slug($params['post_title']);
		$update['post_description'] = $params['post_description'];
		$update['post_content'] = $params['post_content'];
		$update['post_seo_title'] = isset($params['post_seo_title']) ? $params['post_seo_title'] : '';
		$update['post_seo_description'] = isset($params['post_seo_description']) ? $params['post_seo_description'] : '';
		$update['post_seo_keyword'] = isset($params['post_seo_keyword']) ? $params['post_seo_keyword'] : '';
		$update['post_thumbnail'] = isset($params['post_thumbnail']) ? $params['post_thumbnail'] : '';
		$update['post_status'] = $params['post_status'];
		$update['post_feature'] = isset($params['post_feature']) ? $params['post_feature'] : 0;
		$update['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$update['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
		$update['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
		$update['updated_at'] = date("Y-m-d H:i:s");
		return $this->postRepo->update($id, $update);
	}

	public function updateView($id, $view = 1)
    {

        return $this->postRepo->increment($id, 'view');
    }

	public function updateMany($ids, $data)
    {
        return $this->postRepo->update($ids, $data);
	}

	public function deleteMany($ids)
    {
        return $this->postRepo->deleteMany($ids);
	}

	public function delete($id)
	{
		return $this->postRepo->delete($id);
	}

	public function getList($params = [], $column = ['*'])
    {
		$search = $params['search'];
		$keyword = $search['value'];


		$limit = isset($params['length']) ? $params['length'] : 20;
		$offset = isset($params['start']) ? $params['start'] : 0;
		$sort = [];
		// $query = isset($keyword) ? ['page_title' => '%' . $keyword . '%'] : [];
		$filter = [];
		if ($params['columns'][4]['search']['value']) {
			$filter['page_status'] = $params['columns'][4]['search']['value'];
		}
		if ($params['columns'][5]['search']['value']) {
			$filter['created_at'] = $params['columns'][5]['search']['value'];
		}

		if ($params['query']['post_type']) {
			$filter['post_type'] = $params['query']['post_type'];
		}

		if ($params['order'][0]['column']) {
			$column_index = intval($params['order'][0]['column']);
			$sort['field'] = $params['columns'][$column_index]['data'];
			$sort['sort'] = $params['order'][0]['dir'];
		} else {
			$sort['field'] = 'id';
			$sort['sort'] = 'desc';
		}

		$result = $this->postRepo->search($keyword, $filter, $limit, $offset, $sort, $column);
		$total = $this->postRepo->totalRow($keyword, $filter);

		$data['data'] = $result;
		$data['recordsTotal'] = $total;
		$data['recordsFiltered'] = $total;

		return $data;
	}

	public function getListIDs($data) {

		$ids = array();

		foreach($data as $row) {
			array_push($ids, $row->id);
		}

		return $ids;
	}

	public function takeNew($quantity, $filter)
    {
        return $this->postRepo->takeNew($quantity, $filter);
    }

    public function takeHotNew($quantity, $filter)
    {
		$filter['is_feature'] = true;
        return $this->postRepo->takeNew($quantity, $filter);
    }
}
