<?php
namespace App\Repositories\Category;

use App\Repositories\BaseRepository;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Category::class;
    }

    public function search(
        $keyword,
        $filter,
        $limit = 20,
        $offset = 0,
        $sort = [],
        $column = ['*']
    ) {
        $query = $this->model->with('posts')->select($column);
        if ($keyword) {
            $query->where('category_name', 'LIKE', '%' . $keyword . '%')->orWhere('category_description', 'LIKE', '%' . $keyword . '%');
        }

        if (isset($filter['category_status']) && $filter['category_status'] != "") {
            $query->where('category_status',  $filter['category_status']);
        }

        if (isset($filter['category_status']) && $filter['category_status'] != "") {
            $query->where('category_status',  $filter['category_status']);
        }

        if (isset($filter['created_at']) && $filter['created_at'] != "") {
            $time_filter = explode(",", $filter['created_at']);
            $start_time = date("Y-m-d 00:00:00", strtotime($time_filter[0]));
            $end_time = date("Y-m-d 23:59:59", strtotime($time_filter[1]));

            $query->where('created_at', '>=', $start_time);
            $query->where('created_at', '<', $end_time);
        }

        return $query->skip($offset)->take($limit)->get();
    }

    public function totalRow(
        $keyword,
        $filter)
    {
        $query = $this->model->select(['id']);
        if ($keyword) {
            $query->where('category_name', 'LIKE', '%' . $keyword . '%')->orWhere('category_description', 'LIKE', '%' . $keyword . '%');
        }

        if (isset($filter['category_status']) && $filter['category_status'] != "") {
            $query->where('category_status',  $filter['category_status']);
        }

        if (isset($filter['category_status']) && $filter['category_status'] != "") {
            $query->where('category_status',  $filter['category_status']);
        }

        if (isset($filter['created_at']) && $filter['created_at'] != "") {
            $time_filter = explode(",", $filter['created_at']);
            $start_time = date("Y-m-d 00:00:00", strtotime($time_filter[0]));
            $end_time = date("Y-m-d 23:59:59", strtotime($time_filter[1]));

            $query->where('created_at', '>=', $start_time);
            $query->where('created_at', '<', $end_time);
        }


        return $query->count();
    }

    public function categoryWithPost($search, $filter) {
        $categories = $this->model->with('category_posts')->get();

    }
}
