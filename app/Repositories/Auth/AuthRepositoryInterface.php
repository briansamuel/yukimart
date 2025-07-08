<?php
namespace App\Repositories\Auth;

use App\Repositories\RepositoryInterface;

interface AuthRepositoryInterface extends RepositoryInterface
{
    //Check Login
    public static function checkLogin();

    public static function login($email, $password, $remember = false);

    public static function getAuthorize();

    public static function checkIsRoot($request);

    public function loginAdmin($request);

    public static function getUserInfo();

    public static function logout();

    public static function checkCurrentPassword($password);
}