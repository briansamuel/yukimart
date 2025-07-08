<?php

namespace App\Repositories\Category;

use App\Repositories\RepositoryInterface;

interface CategoryRepositoryInterface extends RepositoryInterface
{
    // Search
    public function search($search, $filter, $limit = 20, $offset = 0, $sort = [], $column = ['*']);

    // Total rows
    public function totalRow($search, $filter);

    // Post count
    public function categoryWithPost($search, $filter);
}
