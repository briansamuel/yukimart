<?php
namespace App\Repositories\Post;

use App\Repositories\BaseRepository;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Post::class;
    }

    public function getPost()
    {
        return $this->model->select(['*'])->take(5)->get();
    }

    public function takeNew($quantity, $filter) {
        return $this->model->where($filter)->take(5)->get();
    }

    public function search(
        $keyword,
        $filter,
        $limit = 20,
        $offset = 0,
        $sort = [],
        $column = ['*']
    ) {
        $query = $this->model->select($column);
        if ($keyword) {
            $query->where('post_title', 'LIKE', '%' . $keyword . '%')->orWhere('post_author', 'LIKE', '%' . $keyword . '%');
        }

        if (isset($filter['post_status']) && $filter['post_status'] != "") {
            $query->where('post_status',  $filter['post_status']);
        }

        if (isset($filter['post_type']) && $filter['post_type'] != "") {
            $query->where('post_type',  $filter['post_type']);
        }

        if (isset($filter['created_at']) && $filter['created_at'] != "") {
            $time_filter = explode(",", $filter['created_at']);
            $start_time = date("Y-m-d 00:00:00", strtotime($time_filter[0]));
            $end_time = date("Y-m-d 23:59:59", strtotime($time_filter[1]));

            $query->where('created_at', '>=', $start_time);
            $query->where('created_at', '<', $end_time);
        }

        if (isset($sort['field']) && $sort['field'] != "") {
            $query->orderBy($sort['field'], $sort['sort']);
        }

        return $query->skip($offset)->take($limit)->get();
    }

    public function totalRow(
        $keyword,
        $filter)
    {
        $query = $this->model->select(['id']);
        if ($keyword) {
            $query->where('post_title', 'LIKE', '%' . $keyword . '%')->orWhere('post_author', 'LIKE', '%' . $keyword . '%');
        }

        if (isset($filter['post_status']) && $filter['post_status'] != "") {
            $query->where('post_status',  $filter['post_status']);
        }

        if (isset($filter['post_type']) && $filter['post_type'] != "") {
            $query->where('post_type',  $filter['post_type']);
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

    public function detail($id)
    {

        return $this->find($id);
    }
}
