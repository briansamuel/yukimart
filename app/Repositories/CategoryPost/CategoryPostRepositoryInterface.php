<?php
namespace App\Repositories\CategoryPost;

use App\Repositories\RepositoryInterface;

interface CategoryPostRepositoryInterface extends RepositoryInterface
{
         /**
     * Delete many
     * @param $id
     * @return mixed
     */
    public function deleteManyByKey($ids);

}