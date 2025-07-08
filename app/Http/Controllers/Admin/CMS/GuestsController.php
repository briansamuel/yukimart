<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Session;

class GuestsController extends Controller
{
    protected $request;
    protected $guestService;
    protected $validator;

    function __construct(Request $request, ValidationService $validator, GuestService $guestService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->guestService = $guestService;
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        return view('admin.guests.index');
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->guestService->getList($params);

        return response()->json($result);
    }

    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {

        return view('admin.guests.add');
    }

    public function addAction()
    {
        $params = $this->request->only('email', 'username', 'full_name', 'password', 'guest_address', 'guest_phone', 'guest_birthday', 'guest_avatar');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'add_guest_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        if(GuestService::checkEmailExist($params['email'])) {
            return response()->json(Message::get(30, $lang = '', $validator->errors()->all()), 400);
        }

        $upload = UploadImage::uploadAvatar($params['guest_avatar'], 'guest/images');
        if (!$upload['success']) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        $params['guest_avatar'] = $upload['url'];

        $add = GuestService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Thêm mới 1 khách hàng có id = " . $add;
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $this->guestService->sendMailActive($params['email']);
            $data['success'] = true;
            $data['message'] = "Thêm mới khách hàng thành công !!!";
        } else {
            $data['message'] = "Lỗi khi thêm mới khách hàng !";
        }

        return response()->json($data);
    }

    public function deleteMany()
    {
        $params = $this->request->only('ids', 'total');
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $delete = $this->guestService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " khách hàng thành công !!!", 'success');

        //add log
        $log['action'] = "Xóa " . $params['total'] . " khách hàng thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " khách hàng thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $detail = $this->guestService->detail($id);
        $delete = $this->guestService->delete($id);
        if($delete) {
            //add log
            $log['action'] = "Xóa khách hàng thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);
            Message::alertFlash('Bạn đã xóa khách hàng thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa khách hàng không thành công', 'danger');
        }

        return redirect("guest");
    }

    public function detail($id)
    {
        $guestInfo = $this->guestService->detail($id);

        return view('admin.guests.detail', ['guestInfo' => $guestInfo]);
    }

    public function edit($id)
    {
        $guestInfo = $this->guestService->detail($id);

        return view('admin.guests.edit', ['guestInfo' => $guestInfo]);
    }

    public function editAction($id)
    {
        $params = $this->request->only(['full_name', 'password', 'guest_address', 'guest_phone', 'guest_birthday', 'guest_avatar', 'status']);
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_guest_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        if(isset($params['guest_avatar'])) {
            $upload = UploadImage::uploadAvatar($params['guest_avatar'], 'guest/images');
            if (!$upload['success']) {
                return response()->json(Message::get(13, $lang = '', []), 400);
            }
            $params['guest_avatar'] = $upload['url'];
        }

        if(isset($params['password']) && $params['password'] != '') {
            $params['password'] = bcrypt($params['password']);
        }


        $edit = $this->guestService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập khách hàng thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập khách hàng thành công !!!";
        return response()->json($data);
    }

    public function editManyAction()
    {
        $params = $this->request->only(['status', 'ids', 'total']);
        $params = ArrayHelper::removeArrayNull($params);
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $update = $this->guestService->updateMany($params['ids'], ['status' => $params['status']]);
        if (!$update) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " khách hàng thành công !!!", 'success');

        //add log
        $log['action'] = "Cập nhập nhiều khách hàng thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " khách hàng thành công !!!";
        return response()->json($data);
    }

}
