<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use App\Services\SubcribeEmailsService;
use Illuminate\Http\Request;

class SubcribeEmailsController extends Controller
{
    protected $request;
    protected $subcribeEmailsService;
    protected $validator;

    function __construct(Request $request, ValidationService $validator, SubcribeEmailsService $subcribeEmailsService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->subcribeEmailsService = $subcribeEmailsService;
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        return view('admin.subcribe_emails.index');
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->subcribeEmailsService->getList($params);

        return response()->json($result);
    }

    public function delete($id)
    {
        $detail = $this->subcribeEmailsService->detail($id);
        $delete = $this->subcribeEmailsService->delete($id);
        if($delete) {
            //add log
            $log['action'] = "Xóa subcribe email thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);
            Message::alertFlash('Bạn đã xóa subcribe email thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa subcribe email không thành công', 'danger');
        }

        return redirect("subcribe-emails");
    }

    public function edit($id)
    {
        $subcribeInfo = $this->subcribeEmailsService->detail($id);

        return view('admin.subcribe_emails.edit', ['subcribeInfo' => $subcribeInfo]);
    }

    public function editAction($id)
    {
        $params = $this->request->only(['email', 'active']);
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_subcribe_emails_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        $edit = $this->subcribeEmailsService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập subcribe email thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập subcribe email thành công !!!";
        return response()->json($data);
    }

}
