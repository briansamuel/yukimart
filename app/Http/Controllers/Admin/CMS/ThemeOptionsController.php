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

class ThemeOptionsController extends Controller
{
    private $setting;
    private $request;


    public function __construct(Request $request, Setting $setting)
    {
        $this->request = $request;
        $this->setting = $setting;

    }

    public function option()
    {
        $lang = $this->request->input('lang', 'vi');
        $option = SettingService::getSetting('theme_option', $lang);
        return view('admin.themes.option', ['option' => $option, 'language' => $lang]);
    }

    public function optionAction()
    {
        $params = $this->request->all();
        $params = ArrayHelper::removeArrayNull($params);
        $lang = 'vi';
        if(isset($params['language'])) {
            if($params['language'] === 'en') $lang = 'en';
            unset($params['language']);
        }

        foreach ($params as $key => $param) {
            if ($this->request->hasFile($key)) {
                $upload = UploadImage::uploadAvatar($params[$key], 'themes/customs/images');
                if (!$upload['success']) {
                    return response()->json(Message::get(13, $lang = '', []), 400);
                }
                $params[$key] = $upload['url'];
            }
        }
        $add = SettingService::add($params, $lang);
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
