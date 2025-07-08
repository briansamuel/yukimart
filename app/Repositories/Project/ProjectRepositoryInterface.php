<?php
namespace App\Repositories\Project;

use App\Repositories\RepositoryInterface;

interface ProjectRepositoryInterface extends RepositoryInterface
{
    //get list 10 project lastest
    public function getProject();

    // Lấy tin mới nhất
    public function takeNew($quantity, $filter);

    // Search
    public function search($search, $filter, $limit = 20, $offset = 0, $sort = [], $column = ['*']);

    // Total rows
    public function totalRows($search, $filter);

    // Details
    public function detail($key, $value);

    // My Tasks current Project
    public function myTaskByProject($params);

}
