<?php

namespace App\Repositories\Page;

use App\Repositories\BaseRepository;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{

    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Page::class;
    }

    public function search(
        $keyword,
        $filter,
        $limit,
        $offset,
        $sort,
        $column
    ) {
        $query = $this->model->select($column);
        if ($keyword) {
            $query->where('page_title', 'LIKE', '%' . $keyword . '%')->orWhere('page_author', 'LIKE', '%' . $keyword . '%');
        }

        if (isset($filter['page_status']) && $filter['page_status'] != "") {
            $query->where('page_status',  $filter['page_status']);
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
        $filter
    ) {
        $query = $this->model->select(['id']);
        if ($keyword) {
            $query->where('page_title', 'LIKE', '%' . $keyword . '%')->orWhere('page_author', 'LIKE', '%' . $keyword . '%');
        }

        if (isset($filter['page_status']) && $filter['page_status'] != "") {
            $query->where('page_status',  $filter['page_status']);
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
