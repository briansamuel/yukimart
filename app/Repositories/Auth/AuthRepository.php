<?php
namespace App\Repositories\Auth;

use App\Repositories\BaseRepository;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\UserRepository;
class AuthRepository extends BaseRepository implements AuthRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\User::class;
    }

    public static function checkLogin()
    {
        if (!Auth::guard('admin')->check()) {
            return false;
        }

        return true;
    }

    public static function login($email, $password, $remember = false){
        $result['status'] = false;
        $result['msg'] = "Dữ liệu đầu vào không hợp lệ";
        $result['data'] = array();
      
        if(!isset($email) || !$email || !isset($password) || !$password){
            return $result;
        }

        $userInfo =  User::getUserInfoByEmail($email);

        if(!$userInfo){
            $result['msg'] = "Email không tồn tại !";
            return $result;
        }

        if (!Auth::guard('admin')->attempt(['email' => $email, 'password' => $password], $remember)) {
            $result['msg'] = "Mật khẩu không chính xác !";
            return $result;
        }
        $result['data'] = $userInfo;
        $result['status'] = true;
        $result['msg'] = "";
        return $result;
    }

    /*
     * function get user page info
     */

    public static function getAuthorize()
    {
        // check user logined
        if (!self::checkLogin()) {
            return false;
        }

        $userInfo = self::getUserInfo();

        return array(
            'user_id' => $userInfo->id,
            'email' => $userInfo->email,
            'status' => $userInfo->status,
            'is_root' => $userInfo->is_root === 1 ? 'yes' : 'no'
        );
    }

    /*
     * function check is root ?
     */

    public static function checkIsRoot($request)
    {
        // check user logined
        if (!self::checkLogin()) {
            return false;
        }

        $userInfo = self::getUserInfo();

        if(isset($userInfo) && $userInfo->is_root === "yes"){
            return true;
        }else{
            return false;
        }
    }

    /*
     * function login user
     */

    public function loginAdmin($request)
    {
        $result['status'] = false;
        $result['msg'] = "";
        $result['url'] = "";

        $email = $request->input('email', '');
        $password = $request->input('password', '');
        $remember = $request->has('remember') ? true : false;
        $loginInfo = self::login($email, $password, $remember);
        if(!$loginInfo || !isset($loginInfo['status']) || !$loginInfo['status']){
            $result['msg'] = $loginInfo['msg'];
            return $result;
        }
        //xử lý status
        $userInfo = $loginInfo['data'];
        if($userInfo->status =="active"){
            UserRepository::update($userInfo->id, ['last_visit' => date("Y-m-d H:i:s")]);

            $result['status'] = true;
            $result['msg'] = "Đăng nhập thành công !!!";
            $result['url'] = "welcome";

            return $result;
        }elseif($userInfo->status == "blocked"){
            Auth::logout();
            $result['status'] = false;
            $result['msg'] = "Tài khoản của bạn đã bị khóa !";
            $result['url'] = "";

            return $result;
        }elseif($userInfo->status == "deactive"){
            Auth::logout();
            $result['status'] = false;
            $result['msg'] = "Tài khoản của bạn đã bị tạm khóa !";
            $result['url'] = "";

            return $result;
        }else{
            Auth::logout();
            $result['status'] = false;
            $result['msg'] = "Tài khoản của bạn chưa được kích hoạt !";
            $result['url'] = "";

            return $result;
        }
    }

    public static function getUserInfo()
    {
        return Auth::guard('admin')->user();
    }

    public static function logout()
    {
        Auth::guard('admin')->logout();
        return true;
    }

    public static function checkCurrentPassword($password)
    {
        if (Hash::check($password, Auth::guard('admin')->user()->password)){
            return true;
        }

        return false;
    }

}