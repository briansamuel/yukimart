<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Common;
use App\Helpers\Message;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use File;

class TemplateController extends Controller
{
    protected $request;
    protected $contactService;
    protected $validator;

    public function __construct(Request $request, ValidationService $validator)
    {
        $this->request = $request;
        $this->validator = $validator;
    }

    /**
     * ======================
     * Method:: INDEX
     * ======================
     */

    public function index()
    {

        // desktop
        $params = $this->request->only('lang');
        $language = isset($params['lang']) ? $params['lang'] : 'vi';
        $data = [];
        $data['language'] = $language;
        $about = resource_path('views/frontsite/pages/' . $language . '/about-page.blade.php');
        if (File::exists($about)) {
            $data['about'] = Common::getFileData($about, false);
        }

        $bar_service = resource_path('views/frontsite/services/' . $language . '/bar-service.blade.php');
        if (File::exists($bar_service)) {
            $data['bar_service'] = Common::getFileData($bar_service, false);
        }

        $spa_service = resource_path('views/frontsite/services/' . $language . '/spa-service.blade.php');
        if (File::exists($spa_service)) {
            $data['spa_service'] = Common::getFileData($spa_service, false);
        }

        $coffee_service = resource_path('views/frontsite/services/' . $language . '/coffee-service.blade.php');
        if (File::exists($coffee_service)) {

            $data['coffee_service'] = Common::getFileData($coffee_service, false);
        }

        $resort_service = resource_path('views/frontsite/services/' . $language . '/resort-service.blade.php');
        if (File::exists($resort_service)) {
            $data['resort_service'] = Common::getFileData($resort_service, false);
        }

        $human_service = resource_path('views/frontsite/services/' . $language . '/human-resource.blade.php');
        if (File::exists($human_service)) {
            $data['human_service'] = Common::getFileData($human_service, false);
        }

        return view('admin.templates.index', $data);
    }

    public function editAction()
    {
        $params = $this->request->only('about', 'bar_service', 'spa_service', 'coffee_service', 'resort_service', 'human_service', 'language');
        $params = ArrayHelper::removeArrayNull($params);
        $language = isset($params['language']) ? $params['language'] : 'vi';

        // about
        $about = resource_path('views/frontsite/pages/' . $language . '/about-page.blade.php');
        Common::saveFileData($about, $params['about'], false);
 
        // bar_service
        $bar_service = resource_path('views/frontsite/services/' . $language . '/bar-service.blade.php');
        Common::saveFileData($bar_service, $params['bar_service'], false);

        // spa_service
        $spa_service = resource_path('views/frontsite/services/' . $language . '/spa-service.blade.php');
        Common::saveFileData($spa_service, $params['spa_service'], false);

        // coffee_service
        $coffee_service = resource_path('views/frontsite/services/' . $language . '/coffee-service.blade.php');
        Common::saveFileData($coffee_service, $params['coffee_service'], false);

        // resort_service
        $resort_service = resource_path('views/frontsite/services/' . $language . '/resort-service.blade.php');
        Common::saveFileData($resort_service, $params['resort_service'], false);

        // bar_service
        $human_service = resource_path('views/frontsite/services/' . $language . '/human-resource.blade.php');
        Common::saveFileData($human_service, $params['human_service'], false);

        //add log
        $log['action'] = "Chỉnh sửa template thành công";
        $log['content'] = json_encode([]);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Chỉnh sửa template thành công !!!";
        return response()->json($data);
    }
}
