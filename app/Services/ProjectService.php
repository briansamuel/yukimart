<?php
namespace App\Services;

use App\Models\ProjectModel;
use App\Transformers\AgencyTransformer;
use Illuminate\Support\Str;
use App\Repositories\Project\ProjectRepositoryInterface;
class ProjectService
{
    protected $projectRepo;

    public function __construct(ProjectRepositoryInterface $projectRepo)
    {
        $this->projectRepo = $projectRepo;
    }

	public function totalRows($params) {
        $result = $this->projectRepo->count($params);
        return $result;
	}

	public function getMany($limit, $offset, $filter)
	{
		$result = $this->projectRepo->getMany($limit, $offset, $filter);
        return $result ? $result : [];
	}
	public function findByKey($key, $value)
	{
        $result = $this->projectRepo->findByKey($key, $value);
        return $result ? $result : [];
	}

    public function detail($key, $value)
	{
        $result = $this->projectRepo->detail($key, $value);
        return $result ? $result : [];
	}

	public function insert($params)
	{
		$insert['project_name'] = $params['project_name'];
		$insert['project_description'] = isset($params['project_description']) ? $params['project_description'] : '';
        $insert['project_type'] = isset($params['project_type']) ? $params['project_type'] : 'default';
        $insert['project_framework'] = $params['project_framework'];
        $insert['project_database'] = $params['project_database'];
		$insert['project_notifications'] = isset($params['project_notifications']) ? $params['project_notifications'] : json_encode([]);
        $insert['project_category'] = $params['project_category'];
        $insert['project_due_date'] = isset($params['project_due_date']) ? $params['project_due_date'] : date('Y-m-d', strtotime('+2 months'));
		$insert['project_logo'] = isset($params['project_logo']) ? $params['project_logo'] : null;
        $insert['project_budget'] = isset($params['project_budget']) ? $params['project_budget'] : 0;

        $insert['project_status'] = isset($params['project_status']) ? $params['project_status'] : 'pending';
		$insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
		$insert['created_by_user'] = isset($params['created_by_user']) ? $params['created_by_user'] : 0;
		$insert['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
		$insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
		$insert['updated_at'] = date("Y-m-d H:i:s");
		return $this->projectRepo->create($insert);
	}
	public function update($id, $params)
	{
		$update['project_name'] = $params['project_name'];
		$update['project_description'] = $params['project_description'];
        $update['project_type'] = $params['project_type'];
        $update['project_framework'] = $params['project_framework'];
        $update['project_database'] = $params['project_database'];
		$update['project_notifications'] = isset($params['project_notifications']) ? $params['project_notifications'] : json_encode([]);
        $update['project_due_date'] = isset($params['project_budget']) ? $params['project_budget'] : now();
		$update['project_logo'] = $params['project_logo'];
        $update['project_budget'] = isset($params['project_budget']) ? $params['project_budget'] : 0;

        $update['project_status'] = isset($params['project_status']) ? $params['project_status'] : 'pending';
		$update['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
		$update['updated_at'] = date("Y-m-d H:i:s");

		return $this->projectRepo->update($id, $update);
	}

	public function updateMany($ids, $data)
    {
        return $this->projectRepo->updateMany($ids, $data);
	}

	public function deleteMany($ids)
    {
        return $this->projectRepo->deleteMany($ids);
	}

	public function delete($id)
	{
		return $this->projectRepo->delete($id);
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

		$result = $this->projectRepo->search(null, $filter, $limit, $offset, $sort, $column);

        return $result;

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
        return $this->projectRepo->takeNew($quantity, $filter);
    }
}
