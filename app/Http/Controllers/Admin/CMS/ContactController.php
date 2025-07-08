<?php
namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Http\Controllers\Controller;
use App\Services\ContactReplyService;
use App\Services\ContactService;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use App\Mail\ReplyContact;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    protected $request;
    protected $contactService;
    protected $validator;

    public function __construct(Request $request, ContactService $contactService, ValidationService $validator)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->contactService = $contactService;
    }

    /**
     * ======================
     * Method:: INDEX
     * ======================
     */

    public function index()
    {

        return view('admin.contacts.index');
    }

    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->contactService->getList($params);

        return response()->json($result);
    }

    public function deleteMany()
    {
        $params = $this->request->only('ids', 'total');
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $delete = $this->contactService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " liên hệ thành công !!!", 'success');

        //add log
        $log['action'] = "Xóa " . $params['total'] . " liên hệ thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " liên hệ thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $detail = $this->contactService->detail($id);
        $delete = $this->contactService->delete($id);
        if($delete) {
            //add log
            $log['action'] = "Xóa liên hệ thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            Message::alertFlash('Bạn đã xóa liên hệ thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa liên hệ không thành công', 'danger');
        }

        return redirect("contact");
    }

    public function edit($id)
    {
        $data['contactInfo'] = $this->contactService->detail($id);
        $data['contactReply'] = ContactReplyService::getByContactId($id);

        return view('admin.contacts.edit', $data);
    }

    public function editAction($id)
    {
        $params = $this->request->only(['status']);
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_contact_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        $edit = $this->contactService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập liên hệ thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập liên hệ thành công !!!";
        return response()->json($data);
    }

    public function editManyAction()
    {
        $params = $this->request->only(['status', 'ids', 'total']);
        $params = ArrayHelper::removeArrayNull($params);
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $update = $this->contactService->updateMany($params['ids'], ['status' => $params['status']]);
        if (!$update) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " liên hệ thành công !!!", 'success');

        //add log
        $log['action'] = "Cập nhập nhiều liên hệ thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " liên hệ thành công !!!";
        return response()->json($data);
    }

    public function replyAction($contact_id)
    {
        $params = $this->request->all();
        $params['contact_id'] = $contact_id;
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'add_contact_reply_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        $add = ContactReplyService::add($params);
        if (!empty($add)) {
            //add log
            $log['action'] = "Trả lời 1 liên hệ có id = " . $add;
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $contact = $this->contactService->detail($contact_id);
            $contactReply = ContactReplyService::detail($add);

            // send mail
            Mail::to($contact->email)->send(new ReplyContact($contact, $contactReply));

            $data['success'] = true;
            $data['message'] = "Trả lời thành công !!!";
            $data['reply'] = ContactReplyService::detail($add);
        } else {
            $data['message'] = "Lỗi khi trả lời !";
        }

        return response()->json($data);
    }
}
