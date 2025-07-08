<?php
namespace App\Repositories\CategoryPost;

use App\Repositories\BaseRepository;

class CategoryPostRepository extends BaseRepository implements CategoryPostRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\CategoryPost::class;
    }


    public function deleteManyByKey($params)
    {
        return $this->model->where($params)->delete();
    }
}
