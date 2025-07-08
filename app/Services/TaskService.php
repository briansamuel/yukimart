<?php

namespace App\Services;


use App\Transformers\AgencyTransformer;
use Illuminate\Support\Str;
use App\Repositories\Task\TaskRepositoryInterface;

class TaskService
{
    protected $taskRepo;

    public function __construct(TaskRepositoryInterface $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function totalRows($params)
    {
        $result = $this->taskRepo->count($params);
        return $result;
    }

    public function getMany($limit, $offset, $filter)
    {
        $result = $this->taskRepo->getMany($limit, $offset, $filter);
        return $result ? $result : [];
    }
    public function findByKey($key, $value)
    {
        $result = $this->taskRepo->findByKey($key, $value);
        return $result ? $result : [];
    }
    public function insert($params)
    {
        $insert['task_name'] = $params['task_name'];
        $insert['task_description'] = isset($params['task_description']) ? $params['task_description'] : '';
        $insert['task_content'] = isset($params['task_content']) ? $params['task_content'] : '';
        $insert['task_attachments'] = isset($params['task_attachments']) ? $params['task_attachments'] : json_encode([]);
        $insert['task_notifications'] = isset($params['task_notifications']) ? $params['task_notifications'] : json_encode([]);
        $insert['task_progress'] = isset($params['task_progress']) ? $params['task_progress'] : 0;
        $insert['task_category'] = $params['task_category'];
        $insert['task_due_date'] = isset($params['task_due_date']) ? $params['task_due_date'] : date('Y-m-d', strtotime('+2 months'));
        $insert['project_id'] = isset($params['project_id']) ? $params['project_id'] : 0;
        $insert['task_status'] = isset($params['task_status']) ? $params['task_status'] : 'pending';
        $insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
        $insert['created_by_user'] = isset($params['created_by_user']) ? $params['created_by_user'] : 0;
        $insert['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
        $insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
        $insert['updated_at'] = date("Y-m-d H:i:s");
        return $this->taskRepo->create($insert);
    }
    public function update($id, $params)
    {
        $update['task_name'] = $params['task_name'];
        $update['task_description'] = $params['task_description'];
        $update['task_content'] = $params['task_content'];
        $update['task_attachments'] = isset($params['task_attachments']) ? $params['task_attachments'] : json_encode([]);
        $update['task_notifications'] = isset($params['task_notifications']) ? $params['task_notifications'] : json_encode([]);
        $update['task_progress'] = $params['task_progress'];
        $update['task_category'] = $params['task_category'];
        $update['task_due_date'] = $params['task_due_date'];
        $update['task_status']  = $params['task_status'];
        $update['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
        $update['updated_at'] = date("Y-m-d H:i:s");

        return $this->taskRepo->update($id, $update);
    }

    public function updateMany($ids, $data)
    {
        return $this->taskRepo->updateMany($ids, $data);
    }

    public function deleteMany($ids)
    {
        return $this->taskRepo->deleteMany($ids);
    }

    public function delete($id)
    {
        return $this->taskRepo->delete($id);
    }

    public function  getList($column = ['*'], array $params)
    {

        $user = auth()->user();
        $limit = isset($params['perpage']) ? $params['perpage'] : 20;
        $offset = isset($params['page']) ? $params['page'] : 0;
        $sort = [];
        $filter = array(
            'created_by_user' => $user->id,
        );

        $result = $this->taskRepo->search(null, $filter, $limit, $offset, $sort, $column);

        return $result;
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
