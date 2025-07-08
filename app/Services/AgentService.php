<?php

namespace App\Services;

use App\Models\AgentModel;
use App\Notifications\ActiveAgentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Notification;

class AgentService
{

    public static function totalRows() {
        $result = AgentModel::totalRows();
        return $result;
    }

    public static function add($params)
    {
        $params['password'] = bcrypt($params['password']);
        $params['active_code'] = Str::random(60);
        $params['group_id'] = 0;
        $params['agent_parent'] = 0;
        $params['agent_birthday'] = date("Y-m-d H:i:s", strtotime($params['agent_birthday']));
        $params['status'] = 'inactive';
        $params['group_id'] = '';
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return AgentModel::insert($params);
    }

    public function edit($id, $params)
    {
        $params['agent_birthday'] = date("Y-m-d H:i:s", strtotime($params['agent_birthday']));
        $params['group_id'] = isset($params['group_id']) ? implode(",", $params['group_id']) : '';
        $params['updated_at'] = date("Y-m-d H:i:s");
        return AgentModel::updateAgent($id, $params);
    }

    public function deleteMany($ids)
    {
        return AgentModel::deleteMany($ids);
    }

    public function updateMany($ids, $data)
    {
        return AgentModel::updateMany($ids, $data);
    }

    public function delete($ids)
    {
        return AgentModel::deleteAgent($ids);
    }

    public function detail($id)
    {
        return AgentModel::findById($id);
    }

    public static function getByEmail(string $email)
    {
        return AgentModel::findByKey('email', $email);
    }

    public static function updatePasswordByEmail(string $email, string $password)
    {
        return AgentModel::where('email', $email)->update([
            'password' => bcrypt($password),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
    }

    public static function updateProfile(array $params)
    {
        $user = Auth::guard('agent')->user();

        $user->full_name = $params['full_name'];
        $user->agent_avatar = isset($params['avatar']) ? $params['avatar'] : $user->agent_avatar;

        return $user->save();
    }

    public static function updatePassword(string $password)
    {
        $user = Auth::guard('agent')->user();

        $user->password = bcrypt($password);

        return $user->save();
    }

    public function getList(array $params)
    {
        $total = self::totalRows();
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];

        $result = AgentModel::getMany($pagination, $sort, $query);

        $data['data'] = $result;
        $data['meta']['page'] = isset($pagination['page']) ? $pagination['page'] : 1;
        $data['meta']['perpage'] = isset($pagination['perpage']) ? $pagination['perpage'] : 20;
        $data['meta']['total'] = $total;
        $data['meta']['pages'] = ceil($total / $data['meta']['perpage']);
        $data['meta']['rowIds'] = self::getListIDs($result);

        return $data;
    }

    public function getListIDs($data) {

        $ids = array();

        foreach($data as $row) {
            array_push($ids, $row->id);
        }

        return $ids;
    }

    public function sendMailActive($email)
    {
        $agent = AgentService::getByEmail($email);

        $agent->notify(new ActiveAgentRequest($agent->active_code));
        return response()->json([
            'success' => true,
            'message' => 'Chúng tôi đã gửi email kích hoạt tài khoản đến email ' . $email . '!'
        ]);
    }

    public function active($id) {
        $data['status'] = 'active';
        $data['email_verified_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        return AgentModel::updateAgent($id, $data);
    }

    public static function checkEmailExist($email) {
        $agent = AgentModel::checkEmailExist($email);
        if($agent) {
            return true;
        }

        return false;
    }

    public static function getUserInfoByEmail(string $email)
    {
        return AgentModel::findByKey('email', $email);
    }

}
