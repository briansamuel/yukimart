<?php

namespace App\Services;

use App\Models\GuestModel;
use App\Notifications\ActiveGuestRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Notification;

class GuestService
{

    public static function totalRows()
    {
        $result = GuestModel::totalRows();
        return $result;
    }

    public static function add($params)
    {
        $params['password'] = bcrypt($params['password']);
        $params['active_code'] = Str::random(60);
        $params['group_id'] = 0;
        $params['guest_birthday'] = date("Y-m-d H:i:s", strtotime($params['guest_birthday']));
        $params['status'] = 'inactive';
        $params['group_id'] = '';
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return GuestModel::insert($params);
    }

    public static function addBySocial($params)
    {
        $params['password'] = $params['password'] === '' ? '' : bcrypt($params['password']);
        $params['active_code'] = Str::random(60);
        $params['group_id'] = 0;
        $params['guest_birthday'] = date("Y-m-d H:i:s");
        $params['guest_address'] = '';
        $params['guest_phone'] = '';
        $params['status'] = 'active';
        $params['group_id'] = '';
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return GuestModel::insertGetId($params);
    }

    public static function register($params)
    {
        $params['password'] = bcrypt($params['password']);
        $params['active_code'] = Str::random(60);
        $params['group_id'] = 0;
        $params['guest_birthday'] = date("Y-m-d H:i:s");
        $params['guest_avatar'] = '';
        $params['guest_address'] = '';
        $params['guest_phone'] = isset($params['guest_phone']) ? $params['guest_phone'] : '';
        $params['status'] = 'inactive';
        $params['group_id'] = '';
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return GuestModel::insertGetId($params);
    }

    public function edit($id, $params)
    {
        if (isset($params['guest_birthday'])) {
            $params['guest_birthday'] = date("Y-m-d H:i:s", strtotime($params['guest_birthday']));
        }
        if (isset($params['group_id'])) {
            $params['group_id'] = implode(",", $params['group_id']);
        }
        $params['updated_at'] = date("Y-m-d H:i:s");

        return GuestModel::updateGuest($id, $params);
    }

    public function deleteMany($ids)
    {
        return GuestModel::deleteMany($ids);
    }

    public function updateMany($ids, $data)
    {
        return GuestModel::updateMany($ids, $data);
    }

    public function delete($ids)
    {
        return GuestModel::deleteGuest($ids);
    }

    public function detail($id)
    {
        return GuestModel::findById($id);
    }

    public static function getByEmail(string $email)
    {
        return GuestModel::findByKey('email', $email);
    }

    public static function updatePasswordByEmail(string $email, string $password)
    {
        return GuestModel::where('email', $email)->update([
            'password' => bcrypt($password),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }

    public static function updateProfile(array $params)
    {
        $user = Auth::user();

        $user->full_name = $params['full_name'];
        $user->avatar = isset($params['avatar']) ? $params['avatar'] : $user->avatar;

        return $user->save();
    }

    public static function updatePassword(string $password)
    {
        $user = Auth::user();

        $user->password = bcrypt($password);

        return $user->save();
    }

    public function getList(array $params)
    {
        $total = self::totalRows();
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];

        $result = GuestModel::getMany($pagination, $sort, $query);

        $data['data'] = $result;
        $data['meta']['page'] = isset($pagination['page']) ? $pagination['page'] : 1;
        $data['meta']['perpage'] = isset($pagination['perpage']) ? $pagination['perpage'] : 20;
        $data['meta']['total'] = $total;
        $data['meta']['pages'] = ceil($total / $data['meta']['perpage']);
        $data['meta']['rowIds'] = self::getListIDs($result);

        return $data;
    }

    public function getListIDs($data)
    {

        $ids = array();

        foreach ($data as $row) {
            array_push($ids, $row->id);
        }

        return $ids;
    }

    public function sendMailActive($email)
    {
        $guest = GuestService::getByEmail($email);

        $guest->notify(new ActiveGuestRequest($guest->active_code));
        return response()->json([
            'success' => true,
            'message' => 'Chúng tôi đã gửi email kích hoạt tài khoản đến email ' . $email . '!'
        ]);
    }

    public function active($id)
    {
        $data['status'] = 'active';
        $data['email_verified_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        return GuestModel::updateGuest($id, $data);
    }

    public static function checkEmailExist($email, $id = '')
    {
        if ($id) {
            $guest = GuestModel::checkEmailExist($email, $id);
        } else {
            $guest = GuestModel::checkEmailExist($email);
        }
        if ($guest) {
            return true;
        }

        return false;
    }

    public static function checkProviderExist($provider_id)
    {
        $guest = GuestModel::checkProviderExist($provider_id);
        if ($guest) {
            return true;
        }

        return false;
    }

    public static function getByProviderId($provider_id)
    {
        $guest = GuestModel::getByProviderId($provider_id);
        return $guest;
    }

    public static function takeNew($quantity)
    {
        return GuestModel::takeNew($quantity);
    }

    public static function getUserInfoByEmail(string $email)
    {
        return GuestModel::findByKey('email', $email);
    }

}
