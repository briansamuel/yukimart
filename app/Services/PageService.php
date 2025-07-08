<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Str;
use App\Repositories\Page\PageRepositoryInterface;

class PageService
{

	protected $pageRepo;

	public function __construct(PageRepositoryInterface $pageRepo)
	{
		$this->pageRepo = $pageRepo;
	}

	public function getListSlug()
	{
		$result = $this->pageRepo->getAll(['page_slug', 'page_title']);
		return $result;
	}

	public function totalRows($params = [])
	{
		$result = $this->pageRepo->count($params);
		return $result;
	}

	public function getMany($limit, $offset, $filter)
	{
		$result = $this->pageRepo->getMany($limit, $offset, $filter);
		return $result ? $result : [];
	}
	public function findByKey($key, $value)
	{
		$result = $this->pageRepo->findByKey($key, $value);
		return $result ? $result : [];
	}

	public function detail($id)
	{
		$result = $this->pageRepo->detail($id);
		return $result ? $result : [];
	}

	public function insert($params)
	{
		$insert['page_title'] = $params['page_title'];
		$insert['page_slug'] = isset($params['page_slug']) ? $params['page_slug'] : Str::slug($params['page_title']);
		$insert['page_description'] = $params['page_description'];
		$insert['page_content'] = $params['page_content'];
		$insert['page_seo_title'] = isset($params['page_seo_title']) ? $params['page_seo_title'] : '';
		$insert['page_seo_description'] = isset($params['page_seo_description']) ? $params['page_seo_description'] : '';
		$insert['page_seo_keyword'] = isset($params['page_seo_keyword']) ? $params['page_seo_keyword'] : '';

		$insert['page_author'] = isset($params['page_author']) ? $params['page_author'] : 0;
		$insert['page_status'] = $params['page_status'];
		$insert['page_type'] = isset($params['page_type']) ? $params['page_type'] : 'page';
		$insert['page_template'] = $params['page_template'] ?? 'default';
		$insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$insert['created_by_user'] = isset($params['created_by_user']) ? $params['created_by_user'] : 0;
		$insert['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
		$insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
		$insert['updated_at'] = date("Y-m-d H:i:s");
		return $this->pageRepo->create($insert);
	}
	public function update($id, $params)
	{
		$update['page_title'] = $params['page_title'];
		$update['page_slug'] = isset($params['page_slug']) ? $params['page_slug'] : Str::slug($params['page_title']);
		$update['page_description'] = $params['page_description'];
		$update['page_content'] = $params['page_content'];
		$update['page_seo_title'] = isset($params['page_seo_title']) ? $params['page_seo_title'] : '';
		$update['page_seo_description'] = isset($params['page_seo_description']) ? $params['page_seo_description'] : '';
		$update['page_seo_keyword'] = isset($params['page_seo_keyword']) ? $params['page_seo_keyword'] : '';
		$update['page_template'] =  $params['page_template'] ?? 'default';
		$update['page_status'] = $params['page_status'];
		$update['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
		$update['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$update['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
		$update['updated_at'] = date("Y-m-d H:i:s");
		return $this->pageRepo->update($id, $update);
	}

	public function updateMany($ids, $data)
	{
		return $this->pageRepo->updateMany($ids, $data);
	}

	public function deleteMany($ids)
	{
		return $this->pageRepo->deleteMany($ids);
	}

	public function delete($id)
	{
		return $this->pageRepo->delete($id);
	}

	public function getList($column = ['*'], array $params)
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

		if ($params['order'][0]['column']) {
			$column_index = intval($params['order'][0]['column']);
			$sort['field'] = $params['columns'][$column_index]['data'];
			$sort['sort'] = $params['order'][0]['dir'];
		} else {
			$sort['field'] = 'id';
			$sort['sort'] = 'desc';
		}

		$result = $this->pageRepo->search($keyword, $filter, $limit, $offset, $sort, $column);
		$total = $this->pageRepo->totalRow($keyword, $filter);

		$data['data'] = $result;
		$data['recordsTotal'] = $total;
		$data['recordsFiltered'] = $total;

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
}
