<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Exceptions\CheckException;
use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Services\Admin\MyProfileService;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Services\ValidationService;

class MyProfileController extends Controller
{
    protected $validator;
    protected $request;
    protected $userService;

    public function __construct(Request $request, ValidationService $validator, UserService $userService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->userService = $userService;
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
            return response()->json(Message::get(1, $lang='', $validator->errors()->all()));
        }

        if(isset($params['avatar'])) {
            $upload = UploadImage::uploadAvatar($params['avatar'], 'user');
            if (!$upload['success']) {
                return response()->json(Message::get(13, $lang = '', []), 400);
            }
            $params['avatar'] = $upload['url'];
        }

        $update = $this->userService->updateProfile($params);

        if(!$update) {
            return response()->json(Message::get(10, $lang='', []));
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
        $params = $request->only('password', 'new_password', 'confirm_new_password');
        $validator = $this->validator->make($params, 'change_password_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()));
        }

        try {
            $result = $this->userService->updatePassword($params['password'], $params['new_password']);
        } catch (CheckException $e) {
            return response()->json(Message::get($e->getErrorCode()));
        }


        return response()->json($result);

    }
}
