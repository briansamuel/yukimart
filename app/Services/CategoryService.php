<?php
namespace App\Services;


use App\Transformers\AgencyTransformer;
use Illuminate\Support\Str;
use App\Services\CategoryPostService;
use App\Repositories\Category\CategoryRepositoryInterface;
class CategoryService
{

	public static $categoryRepo;

    public function __construct(CategoryRepositoryInterface $categoryRepo)
    {
        self::$categoryRepo = $categoryRepo;
    }


    public static function totalRows($filter)
    {
        $result = self::$categoryRepo->count($filter);
        return $result;

	}

	public static function getAllByKey($columns = ['*'], $filter)
	{
		$result = self::$categoryRepo->getAllByKey($columns, $filter);
        return $result ? $result : [];
	}



	public static function getMany($pagination, $sort, $filter)
	{
		$result = self::$categoryRepo->getMany($pagination, $sort, $filter);
        return $result ? $result : [];
    }

	public function findByKey($key, $value)
	{
        $result = self::$categoryRepo->findByKey($key, $value);
        return $result ? $result : [];
	}
	public function insert($params)
	{
		$insert['category_name'] = $params['category_name'];
		$insert['category_slug'] = isset($params['category_slug']) ? $params['category_slug'] : Str::slug($params['category_name']);
		$insert['category_description'] = $params['category_description'];
		$insert['category_seo_title'] = isset($params['category_seo_title']) ? $params['category_seo_title'] : '';
		$insert['category_seo_description'] = isset($params['category_seo_description']) ? $params['category_seo_description'] : '';
		$insert['category_seo_keyword'] = isset($params['category_seo_keyword']) ? $params['category_seo_keyword'] : '';
		$insert['category_thumbnail'] = isset($params['category_thumbnail']) ? $params['category_thumbnail'] : '';
		$insert['category_parent'] = isset($params['category_parent']) ? $params['category_parent'] : 0;
		$insert['category_type'] = isset($params['category_type']) ? $params['category_type'] : 'category_news';
		$insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
		$insert['updated_at'] = date("Y-m-d H:i:s");
		return self::$categoryRepo->create($insert);
	}
	public function update($id, $params)
	{
		$update['category_name'] = $params['category_name'];
		$update['category_slug'] = isset($params['category_slug']) ? $params['category_slug'] : Str::slug($params['category_title']);
		$update['category_description'] = $params['category_description'];
		$update['category_seo_title'] = isset($params['category_seo_title']) ? $params['category_seo_title'] : '';
		$update['category_seo_description'] = isset($params['category_seo_description']) ? $params['category_seo_description'] : '';
		$update['category_seo_keyword'] = isset($params['category_seo_keyword']) ? $params['category_seo_keyword'] : '';
		$update['category_thumbnail'] = isset($params['category_thumbnail']) ? $params['category_thumbnail'] : '';
		$update['category_parent'] = isset($params['category_parent']) ? $params['category_parent'] : 0;
		$update['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$update['updated_at'] = date("Y-m-d H:i:s");
		return self::$categoryRepo->update($id, $update);
	}

	public function deleteMany($ids)
    {
        return self::$categoryRepo->deleteMany($ids);
	}

	public function delete($id)
	{
		return self::$categoryRepo->delete($id);
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

		if ($params['category_type']) {
			$filter['category_type'] = $params['category_type'];
		}

		if ($params['order'][0]['column']) {
			$column_index = intval($params['order'][0]['column']);
			$sort['field'] = $params['columns'][$column_index]['data'];
			$sort['sort'] = $params['order'][0]['dir'];
		} else {
			$sort['field'] = 'id';
			$sort['sort'] = 'desc';
		}

		$result = self::$categoryRepo->search($keyword, $filter, $limit, $offset, $sort, $column);
		$total = self::$categoryRepo->totalRow($keyword, $filter);

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
}
