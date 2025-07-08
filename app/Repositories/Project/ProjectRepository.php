<?php
namespace App\Repositories\Project;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Project::class;
    }

    public function getProject()
    {
        return $this->model->select(['*'])->take(10)->get();
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
        $query = $this->model->with('project_users.users')->select($column);
        if ($keyword) {
            $query->where('project_name', 'LIKE', '%' . $keyword . '%');
        }

        if (isset($filter['project_status']) && $filter['project_status'] != "") {
            $query->where('project_status',  $filter['project_status']);
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

        return $query->skip($offset)->paginate($limit);
    }

    public function totalRows(
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

    public function detail($key, $value) {
        return $this->model->with('project_users.users')->withCount([
        'tasks',
        'tasks as pending_task_count' => function (Builder $query) {
            $query->where('task_status', 'pending');
        },
        'tasks as active_task_count' => function (Builder $query) {
            $query->where('task_status', 'in_progress');
        },
        'tasks as completed_task_count' => function (Builder $query) {
            $query->where('task_status', 'completed');
        },
        'tasks as overdue_task_count' => function (Builder $query) {
            $query->where('task_due_date', '<', date('Y-m-d H:i:s'));
        },
        ])->where($key, $value)->first();
    }

    public function myTaskByProject($params) {

    }

}
