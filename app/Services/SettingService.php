<?php
namespace App\Services;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Setting\SettingRepositoryInterface;
use Illuminate\Support\Facades\App;
class SettingService
{

    protected $settingRepo;

    public function __construct(SettingRepositoryInterface $settingRepo)
    {
        $this->settingRepo = $settingRepo;
    }

    public static function getMultipleLanguages() {
        return [
            'theme_option::footer::address_1',
            'theme_option::footer::address_2',
            'theme_option::footer::phone_number',
            'theme_option::footer::email',
            'theme_option::general::option_background',
            'theme_option::general::intro_video_option',
            'theme_option::header::hotline',
            'theme_option::header::email',
            'theme_option::general::image',
            'theme_option::general::intro_video_upload',
            'theme_option::home::about_content'
        ];
    }

    public function add($data, $lang = 'vi')
    {
        $user_id = Auth::guard('admin')->user()->id;
        foreach($data as $key=>$value) {
            $param = [];
            // key là multiple và đã tồn tại
            if(!in_array($key, self::getMultipleLanguages()) && !$detail = $this->settingRepo->findByName($key)) {
                $param['setting_key'] = $key;
                $param['setting_value'] = $value;
                $param['language'] = 'vi';
                $param['created_by_user'] = $user_id;
                $param['updated_by_user'] = $user_id;
                $param['created_at'] = date("Y-m-d H:i:s");
                $param['updated_at'] = date("Y-m-d H:i:s");

                $this->settingRepo->create($param);
            } else if(in_array($key, self::getMultipleLanguages()) && !$detail = $this->settingRepo->findByName($key, $lang)) {
                $param['setting_key'] = $key;
                $param['setting_value'] = $value;
                $param['language'] = $lang;
                $param['created_by_user'] = $user_id;
                $param['updated_by_user'] = $user_id;
                $param['created_at'] = date("Y-m-d H:i:s");
                $param['updated_at'] = date("Y-m-d H:i:s");

                $this->settingRepo->create($param);
            } else {
                $param['setting_value'] = $value;

                $this->settingRepo->update($detail->id, $param);
            }
        }

        return true;
    }

    public function savedSettings()
    {
        $dbSettings = $this->settingRepo->getAll();
        return $dbSettings;
    }

    public function deleteGroup($group)
    {
        $conditions = [
            ['setting_key', 'like', "%$group%"]
        ];
        return $this->settingRepo->deletebyCondition($conditions);
    }

    /*
     *
     */

    public  function get($key, $default = '')
    {
        $locale = App::getlocale();
        if(!in_array($key, self::getMultipleLanguages())) {
            $locale = 'vi';
        }
        if($setting = $this->settingRepo->findByName($key, $locale)){
            return $setting->setting_value;
        }

        return $default;
    }

    public  function getSetting($type, $lang = 'vi')
    {
        $dbSettings = $this->settingRepo->findByName($type, $lang);
        return $dbSettings;
    }
}
