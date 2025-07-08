<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class Message
{
    /*
     * function get notice with message
     */
    public static function get($error_code = '16', $lang = '', $errors = array())
    {
//        $lang =  $lang ? $lang : (app('translator')->getLocale());
        $lang =  'vi';
        $messages = Message::getMessage($error_code);
        return Message::getArray($error_code, $messages[$lang], $errors);
    }

    /*
     * function get message by error code
     */
    public static function getMessage($error_code)
    {
        $errors = [
            1 => [
                'vi' => 'Thông tin yêu cầu thiếu hoặc không hợp lệ.',
                'en' => 'Parameters are Missing or Invalid.'
            ],
            10 => [
                'vi'    => 'Insert không thành công',
                'en'    => 'Insert Unsuccessful'
            ],

            11 => [
                'vi'    => 'Update không thành công',
                'en'    => 'Update Unsuccessful'
            ],

            12 => [
                'vi'    => 'Xóa không thành công',
                'en'    => 'Delete Unsuccessful'
            ],

            13 => [
                'vi'    => 'Cập nhập không thành công',
                'en'    => 'Upload Unsuccessful'
            ],

            14 => [
                'vi'    => 'Đăng ký không thành công, vui lòng thử lại',
                'en'    => 'Register Unsuccessful, please try again'
            ],

            20 => [
                'vi' => 'Mật khẩu không hợp lệ',
                'en' => 'Password not valid'
            ],
            26 => [
                'vi' => 'Id không tồn tại',
                'en' => 'Id not exist',
            ],
            30 => [
                'vi' => 'Email đã tồn tại trong hệ thống',
                'en' => 'Email was exist'
            ],




        ];

        return $errors[$error_code];
    }


    /*
     * function get error object
     */
    public static function getArray($error_code, $message, $errors)
    {
        return [
            'error' => [
                'code' => $error_code,
                'message' => $message,
                'errors' => $errors
            ]
        ];
    }

    public static function alertFlash($message, $flash)
    {
        Session::flash('message', $message);
        Session::flash('alert-class', $flash);
    }

}
