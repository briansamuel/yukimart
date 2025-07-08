<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CheckException;
use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Services\Admin\MyProfileService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Services\ValidationService as Validator;

class MyProfileController extends Controller
{
    protected $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * ======================
     * Method:: login
     * ======================
     */

    public function profile()
    {

        return view('admin.profiles.account_infomation');
    }

    public function updateProfile(Request $request)
    {
        $params = $request->only('full_name', 'avatar');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'update_my_profile_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()));
        }

        if (isset($params['avatar'])) {
            $upload = UploadImage::uploadAvatar($params['avatar'], 'user');
            if (!$upload['success']) {
                return response()->json(Message::get(13, $lang = '', []), 400);
            }
            $params['avatar'] = $upload['url'];
        }

        $update = UserService::updateProfile($params);

        if (!$update) {
            return response()->json(Message::get(10, $lang = '', []));
        }

        $result['success'] = true;
        $result['message'] = 'Cập nhập thông tin cá nhân thành công';
        return response()->json($result);
    }

    public function changePassword()
    {

        return view('admin.profiles.change_password');
    }

    public function changePasswordAction(Request $request)
    {
        // $params = $request->only('password', 'new_password', 'confirm_new_password');
        // $validator = $this->validator->make($params, 'change_password_fields');
        // if ($validator->fails()) {
        //     return response()->json(Message::get(1, $lang = '', $validator->errors()->all()));
        // }

        // try {
        //     $result = MyProfileService::changePassword($params['password'], $params['new_password']);
        // } catch (CheckException $e) {
        //     return response()->json(Message::get($e->getErrorCode()));
        // }


        // return response()->json($result);
    }
}
