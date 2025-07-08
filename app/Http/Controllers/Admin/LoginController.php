<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Message;
use App\Http\Controllers\Controller;
use App\Models\AgentModel;
use App\Models\GuestModel;
use App\Models\PasswordReset;
use App\Models\UserModel;
use App\Services\UserService;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;


class LoginController extends Controller
{
    protected $request;
    protected $userService;
    protected $authService;
    protected $guestService;


    function __construct(Request $request, AuthService $authService, UserService $userService)
    {
        $this->request = $request;
        $this->authService = $authService;
        $this->userService = $userService;
    }

    

    public function login()
    {
        //check login
        $checkLogin = $this->authService->checkLogin();

        if ($checkLogin) {
            return redirect('/dashboard');
        }

        return view('admin.authentication.login');
    }

    public function showLoginForm()
    {
        //check login
        $checkLogin = $this->authService->checkLogin();

        if ($checkLogin) {
            return redirect('/admin/dashboard');
        }

        return view('admin.authentication.login');
    }

    public function loginAction(Request $request)
    {
        $loginAdmin = $this->authService->loginAdmin($request);
        
        return $loginAdmin;
    }

    /*
    * function logout
    */
    public function logout()
    {
        $this->authService->logout();

        return redirect('/login');
    }

    public function resetPassword(Request $request)
    {
        $token = $request->input('token');
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset) {
            return view('admin.errors.404');
        }
        return view('admin.authentication.reset_password', ['token' => $token]);
    }

    public function activeUser(Request $request)
    {
        $active_code = $request->input('active_code');
        if ($active_code == '') {
            return view('admin.pages.error404');
        }
        $userInfo = $this->userService->getUserInfoByActiveCode($active_code);
        if (!$userInfo || $userInfo->status !== 'inactive') {
            return view('admin.errors.404');
        }

        $active = $this->userService->activeUser($userInfo->id);
        if ($active) {
            Message::alertFlash('Bạn đã kích hoạt tài khoản thành công, vui lòng đăng nhập vào hệ thống!', 'success');
        } else {
            Message::alertFlash('Bạn đã kích hoạt tài khoản không thành công!', 'danger');
        }


        return redirect()->route('login');
    }

    public function activeAgent(Request $request)
    {
        // $active_code = $request->input('active_code');
        // if ($active_code == '') {
        //     return view('admin.pages.error404');
        // }
        // $agentInfo = AgentModel::where('active_code', $active_code)->first();
        // if (!$agentInfo || $agentInfo->status !== 'inactive') {
        //     return view('admin.pages.error404');
        // }

        // $active = $this->agentService->active($agentInfo->id);
        // if ($active) {
        //     Message::alertFlash('Bạn đã kích hoạt tài khoản thành công, vui lòng đăng nhập vào hệ thống!', 'success');
        // } else {
        //     Message::alertFlash('Bạn đã kích hoạt tài khoản không thành công!', 'danger');
        // }


        // return redirect()->route('agent-login');
    }

    public function activeGuest(Request $request)
    {
        // $active_code = $request->input('active_code');
        // if ($active_code == '') {
        //     return view('admin.pages.error404');
        // }
        // $guestInfo = GuestModel::where('active_code', $active_code)->first();
        // if (!$guestInfo || $guestInfo->status !== 'inactive') {
        //     return view('admin.pages.error404');
        // }

        // $active = $this->guestService->active($guestInfo->id);
        // if ($active) {
        //     Message::alertFlash('Bạn đã kích hoạt tài khoản thành công, vui lòng đăng nhập vào senhos!', 'success');
        // } else {
        //     Message::alertFlash('Bạn đã kích hoạt tài khoản không thành công!', 'danger');
        // }


        // return redirect()->route('guest-login');
    }

}

