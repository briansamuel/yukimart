<?php
namespace App\Repositories\User;

use App\Repositories\BaseRepository;
use App\Repositories\User\UserRepositoryInterface;
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\User::class;
    }

    public function getUser()
    {
        return $this->model->select('username')->take(5)->get();
    }

 
    public function updatePasswordByEmail(string $email, string $password) {
        return $this->model->where("email", $email)->update(["password" => $password]);
    }

}