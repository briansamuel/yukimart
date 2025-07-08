<?php
namespace App\Repositories\Task;

use App\Repositories\RepositoryInterface;

interface TaskRepositoryInterface extends RepositoryInterface
{
    //get list 10 project lastest
    public function getTasks();

    // Lấy tin mới nhất
    public function takeNew($quantity, $filter);

    // Search
    public function search($search, $filter, $limit = 20, $offset = 0, $sort = [], $column = ['*']);

    // Total rows
    public function totalRows($search, $filter);

}
