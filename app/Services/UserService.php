<?php

namespace App\Services;


use App\Notifications\ActiveUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Repositories\User\UserRepositoryInterface;
use App\Exceptions\CheckException;
use App\Services\Auth\AuthService;

class UserService
{

    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function totalRows() {
        $result = $this->userRepo->count();
        return $result;
    }

    public function add($params)
    {
        $insert['username'] = $params['username'];
        $insert['password'] = bcrypt($params['password']);
        $insert['email'] = $params['email'];
        $insert['full_name'] = $params['full_name'];
        $insert['active_code'] = Str::random(60);
        $insert['avatar'] = $params['url_avatar'];
        $insert['is_root'] = 0;
        $insert['status'] = 'inactive';
        $insert['group_id'] = isset($params['group_id']) ? implode(",", $params['group_id']) : '';
        $insert['created_at'] = date("Y-m-d H:i:s");
        $insert['updated_at'] = date("Y-m-d H:i:s");
        $this->userRepo->create($insert);
    }

    public function edit($id, $params)
    {
        $params['group_id'] = isset($params['group_id']) ? implode(",", $params['group_id']) : '';
        $params['updated_at'] = date("Y-m-d H:i:s");
        $this->userRepo->update($id, $params);
    }

    public function deleteMany($ids)
    {
        $this->userRepo->delete($ids);
    }

    public function updateMany($ids, $data)
    {
        return $this->userRepo->update($ids, $data);
    }

    public function delete($ids)
    {
        return $this->userRepo->delete($ids);
    }

    public function detail($id)
    {
        return $this->userRepo->find($id);
    }

    public function getUserInfoByEmail(string $email)
    {
        return $this->userRepo->findByKey('email', $email);
    }

    public function getUserInfoByActiveCode(string $active_code)
    {
        return $this->userRepo->findByKey('active_code', $active_code);
    }

    public function updatePasswordByEmail(string $email, string $password)
    {
        return $this->userRepo->updatePasswordByEmail($email, $password);
    }

    public function updateProfile(array $params)
    {
        $user = Auth::guard('admin')->user();

        return $this->userRepo->update($user->id, $params);
    }

    public function updatePassword(string $password)
    {
        $user = Auth::guard('admin')->user();

        return $this->userRepo->update($user->id, ['password' => bcrypt($password)]);
    }

    public function getList(array $params)
    {
        $total = self::totalRows();

        $limit = isset($params['length']) ? $params['length'] : 20;
        $offset = isset($params['start']) ? $params['start'] : 0;
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];

        $result = $this->userRepo->getMany($limit, $offset, $query, ['*']);

        $data['data'] = $result;
        $data['recordsTotal'] = $total;
        $data['recordsFiltered'] = $total;


        return $data;
    }

    public function getListIDs($data) {

        $ids = array();

        foreach($data as $row) {
            array_push($ids, $row->id);
        }

        return $ids;
    }

    public function sendMailActiveUser($email)
    {
        $user = UserService::getUserInfoByEmail($email);

        $user->notify(new ActiveUserRequest($user->active_code));

        return response()->json([
            'success' => true,
            'message' => 'Chúng tôi đã gửi email kích hoạt tài khoản đến email ' . $email . '!'
        ]);
    }

    public function activeUser($id) {
        $data['status'] = 'active';
        $data['email_verified_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->userRepo->update($id, $data);
    }

    public function changePassword(string $password, string $new_password)
    {
        if ( !AuthService::checkCurrentPassword($password)) {
            throw new CheckException('password_not_valid', 20, []);
        }

        $update = self::updatePassword($new_password);
        if ($update) {
            $result = [
                'success' => true,
                'message' => 'Thay đổi mật khẩu thành công!'
            ];
            return $result;
        }

        throw new CheckException('update_password_not_success', 11, ['res' => $update]);
    }

}
