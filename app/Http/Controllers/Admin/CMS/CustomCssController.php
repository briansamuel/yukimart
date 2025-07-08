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

class CustomCssController extends Controller
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
        $data['desktop'] = '';
        $data['table'] = '';
        $data['mobile'] = '';
        // desktop
        $fileDesktop = public_path('assets/css/customs/desktop.style.integration.css');
        if (File::exists($fileDesktop)) {
            $data['desktop'] = Common::getFileData($fileDesktop, false);
        }
        // table
        $fileTable = public_path('assets/css/customs/table.style.integration.css');
        if (File::exists($fileTable)) {
            $data['table'] = Common::getFileData($fileTable, false);
        }

        // mobile
        $fileMobile = public_path('assets/css/customs/mobile.style.integration.css');
        if (File::exists($fileMobile)) {
            $data['mobile'] = Common::getFileData($fileMobile, false);
        }

        return view('admin.custom_css.index', $data);
    }

    public function editAction()
    {
        $params = $this->request->only('desktop', 'table', 'mobile');
        $params = ArrayHelper::removeArrayNull($params);

        // desktop
        $fileDesktop = public_path('assets/css/customs/desktop.style.integration.css');
        Common::saveFileData($fileDesktop, $params['desktop'], false);

        // table
        $fileTable = public_path('assets/css/customs/table.style.integration.css');
        Common::saveFileData($fileTable, $params['table'], false);

        // mobile
        $fileMobile = public_path('assets/css/customs/mobile.style.integration.css');
        Common::saveFileData($fileMobile, $params['mobile'], false);

        //add log
        $log['action'] = "Custom Css thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập liên hệ thành công !!!";
        return response()->json($data);
    }
}
