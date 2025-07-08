<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use App\Services\AgentService;
use Illuminate\Http\Request;
use Session;

class AgentsController extends Controller
{
    protected $request;
    protected $agentService;
    protected $validator;

    function __construct(Request $request, ValidationService $validator, AgentService $agentService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->agentService = $agentService;
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        return view('admin.agents.index');
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->agentService->getList($params);

        return response()->json($result);
    }

    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {

        return view('admin.agents.add');
    }

    public function addAction()
    {
        $params = $this->request->only('email', 'username', 'full_name', 'password', 'agent_address', 'agent_phone', 'agent_birthday', 'agent_avatar');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'add_agent_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        if(AgentService::checkEmailExist($params['email'])) {
            return response()->json(Message::get(30, $lang = '', $validator->errors()->all()), 400);
        }

        $upload = UploadImage::uploadAvatar($params['agent_avatar'], 'agent/images');
        if (!$upload['success']) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        $params['agent_avatar'] = $upload['url'];

        $add = AgentService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Thêm mới 1 đại lý có id = " . $add;
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $this->agentService->sendMailActive($params['email']);
            $data['success'] = true;
            $data['message'] = "Thêm mới đại lý thành công !!!";
        } else {
            $data['message'] = "Lỗi khi thêm mới đại lý !";
        }

        return response()->json($data);
    }

    public function deleteMany()
    {
        $params = $this->request->only('ids', 'total');
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $delete = $this->agentService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " đại lý thành công !!!", 'success');

        //add log
        $log['action'] = "Xóa " . $params['total'] . " đại lý thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " đại lý thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $detail = $this->agentService->detail($id);
        $delete = $this->agentService->delete($id);
        if($delete) {
            //add log
            $log['action'] = "Xóa đại lý thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            Message::alertFlash('Bạn đã xóa đại lý thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa đại lý không thành công', 'danger');
        }

        return redirect("agent");
    }

    public function detail($id)
    {
        $agentInfo = $this->agentService->detail($id);

        return view('admin.agents.detail', ['agentInfo' => $agentInfo]);
    }

    public function edit($id)
    {
        $agentInfo = $this->agentService->detail($id);

        return view('admin.agents.edit', ['agentInfo' => $agentInfo]);
    }

    public function editAction($id)
    {
        $params = $this->request->only(['full_name', 'password', 'agent_address', 'agent_phone', 'agent_birthday', 'agent_avatar', 'status']);
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_agent_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        if(isset($params['agent_avatar'])) {
            $upload = UploadImage::uploadAvatar($params['agent_avatar'], 'agent/images');
            if (!$upload['success']) {
                return response()->json(Message::get(13, $lang = '', []), 400);
            }
            $params['agent_avatar'] = $upload['url'];
        }

        if(isset($params['password']) && $params['password'] != '') {
            $params['password'] = bcrypt($params['password']);
        }


        $edit = $this->agentService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập đại lý thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập đại lý thành công !!!";
        return response()->json($data);
    }

    public function editManyAction()
    {
        $params = $this->request->only(['status', 'ids', 'total']);
        $params = ArrayHelper::removeArrayNull($params);
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $update = $this->agentService->updateMany($params['ids'], ['status' => $params['status']]);
        if (!$update) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " đại lý thành công !!!", 'success');

        //add log
        $log['action'] = "Cập nhập nhiều Đại Lý thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " đại lý thành công !!!";
        return response()->json($data);
    }

}
