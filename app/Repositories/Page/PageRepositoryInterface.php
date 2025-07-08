<?php

namespace App\Repositories\Page;

use App\Repositories\RepositoryInterface;

interface PageRepositoryInterface extends RepositoryInterface
{

    // Search
    public function search($search, $filter, $limit, $offset, $sort, $column);

    // Search
    public function totalRow($search, $filter);

    // Search
    public function detail($id);
}
