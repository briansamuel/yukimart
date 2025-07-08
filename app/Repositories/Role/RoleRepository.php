<?php
namespace App\Repositories\Role;

use App\Repositories\BaseRepository;
use App\Repositories\Role\RoleRepositoryInterface;
class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Role::class;
    }

    public function getRoleActive()
    {
        return $this->model->where('is_active', 1)->get();

    }


    public function getRoleById($ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }



}
