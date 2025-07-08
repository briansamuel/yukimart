<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\LogsUserService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    private $setting;
    private $request;


    public function __construct(Request $request, Setting $setting)
    {
        $this->request = $request;
        $this->setting = $setting;

    }

    /**
     * ======================
     * Method:: INDEX
     * ======================
     */

    public function general()
    {
        $data['settings'] = config('setting.general');
        $data['dbSetting'] = SettingService::savedSettings();
        return view('admin.settings.general', $data);
    }

    public function generalAction()
    {
        $params = $this->request->all();
        $params = ArrayHelper::removeArrayNull($params);

        foreach ($params as $key => $param) {
            if ($this->request->hasFile($key)) {
                $upload = UploadImage::uploadAvatar($params[$key], 'logo/images');
                if (!$upload['success']) {
                    return response()->json(Message::get(13, $lang = '', []), 400);
                }
                $params[$key] = $upload['url'];
            }
        }


        $add = SettingService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Cập nhập cài đặt thành công";
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Cập nhập cài đặt thành công !!!";
        } else {
            $data['message'] = "Lỗi khi cập nhập cài đặt!";
        }

        return response()->json($data);
    }

    public function email()
    {
        $data['settings'] = config('setting.email');
        $data['dbSetting'] = SettingService::savedSettings();

        return view('admin.settings.email', $data);
    }

    public function loginSocial()
    {
        $data['settings'] = config('setting.social_login');
        $data['dbSetting'] = SettingService::savedSettings();

        return view('admin.settings.login_social', $data);
    }

    public function loginSocialAction()
    {
        $params = $this->request->all();
        $params = ArrayHelper::removeArrayNull($params);

        SettingService::deleteGroup('social_login::');

        $add = SettingService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Cập nhập cài đặt thành công";
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Cập nhập cài đặt thành công !!!";
        } else {
            $data['message'] = "Lỗi khi cập nhập cài đặt!";
        }

        return response()->json($data);
    }

    public function notification()
    {
        $data['settings'] = config('setting.notification');
        $data['dbSetting'] = SettingService::savedSettings();

        return view('admin.settings.notification', $data);
    }

    public function notificationAction()
    {
        $params = $this->request->all();
        $params = ArrayHelper::removeArrayNull($params);

        SettingService::deleteGroup('notification::');

        $add = SettingService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Cập nhập cài đặt thành công";
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Cập nhập cài đặt thành công !!!";
        } else {
            $data['message'] = "Lỗi khi cập nhập cài đặt!";
        }

        return response()->json($data);
    }
}
