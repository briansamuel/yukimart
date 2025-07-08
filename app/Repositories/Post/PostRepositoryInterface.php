<?php
namespace App\Repositories\Post;

use App\Repositories\RepositoryInterface;

interface PostRepositoryInterface extends RepositoryInterface
{
    //ví dụ: lấy 5 sản phầm đầu tiên
    public function getPost();

    // Lấy tin mới nhất
    public function takeNew($quantity, $filter);

    // Search
    public function search($search, $filter, $limit = 20, $offset = 0, $sort = [], $column = ['*']);

    // Total rows
    public function totalRow($search, $filter);

    // Search
    public function detail($id);
}
