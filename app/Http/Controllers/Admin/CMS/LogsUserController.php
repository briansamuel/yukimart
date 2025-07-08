<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use Illuminate\Http\Request;
use Session;

class LogsUserController extends Controller
{

    protected $request;
    protected $logsUserService;

    function __construct(Request $request, LogsUserService $logsUserService)
    {
        $this->request = $request;
        $this->logsUserService = $logsUserService;
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        return view('admin.logs_user.index');
    }

    public function detail($id)
    {
        $logsInfo = $this->logsUserService->detail($id);

        return view('admin.logs_user.detail', ['logsInfo' => $logsInfo]);
    }

    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->logsUserService->getList($params);

        return response()->json($result);
    }

}
