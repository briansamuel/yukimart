<?php
namespace App\Services;

use App\Models\CategoryPost;
use App\Transformers\AgencyTransformer;
use Illuminate\Support\Str;
use App\Repositories\CategoryPost\CategoryPostRepositoryInterface;

class CategoryPostService
{

	public static $categoryPostRepo;

    public function __construct(CategoryPostRepositoryInterface $categoryPostRepo)
    {
        self::$categoryPostRepo = $categoryPostRepo;
    }


    public static function totalRows($filter)
    {
        $result = self::$categoryPostRepo->count($filter);
        return $result;

	}

	public static function getAllByKey($columns = ['*'], $filter, $array = false)
	{
		$result = self::$categoryPostRepo->getAllByKey($columns, $filter, $array);
        return $result ? $result : [];
	}

	public static function getMany($columns = ['*'], $filter, $pagination)
	{

		$result = self::$categoryPostRepo->getMany($pagination['perpage'], $pagination['page'], $filter, null, $columns);
        return $result ? $result : [];
	}

	public function findByKey($key, $value)
	{
        $result = self::$categoryPostRepo->findByKey($key, $value);
        return $result ? $result : [];
    }

	public static function countByKey($key, $value)
	{
        $total = self::$categoryPostRepo->count([$key => $value]);
        return $total ? $total : 0;
	}

	public static function insert($params)
	{
		$insert['category_id'] = $params['category_id'];
		$insert['post_id'] =  $params['post_id'];

		return self::$categoryPostRepo->create($insert);
	}
	public static function update($id, $params)
	{
		$update['category_name'] = $params['category_name'];
		$update['category_slug'] = isset($params['category_slug']) ? $params['category_slug'] : Str::slug($params['category_title']);
		$update['category_description'] = $params['category_description'];
		$update['category_seo_title'] = isset($params['category_seo_title']) ? $params['category_seo_title'] : '';
		$update['category_seo_description'] = isset($params['category_seo_description']) ? $params['category_seo_description'] : '';
		$update['category_seo_keyword'] = isset($params['category_seo_keyword']) ? $params['category_seo_keyword'] : '';
		$update['category_thumbnail'] = isset($params['category_thumbnail']) ? $params['category_thumbnail'] : '';
		$update['category_parent'] = isset($params['category_parent']) ? $params['category_parent'] : 0;
		$update['updated_at'] = date("Y-m-d H:i:s");
		return self::$categoryPostRepo->update($id, $update);
	}



	public static function deleteManyByKey($params)
    {
        return self::$categoryPostRepo->deleteManyByKey($params);
	}

	public static function delete($id)
	{
		return self::$categoryPostRepo->delete($id);
	}

	public function getList(array $params)
    {
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
		$total = self::totalRows($query);

        $result = self::$categoryPostRepo->getMany($pagination, $sort, $query);

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
