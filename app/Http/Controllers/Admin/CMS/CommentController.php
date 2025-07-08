<?php 

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Http\Controllers\Controller;

use App\Services\CommentService;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    protected $request;
    protected $commentService;
    protected $validator;

    public function __construct(Request $request, CommentService $commentService, ValidationService $validator)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->commentService = $commentService;
    }

    public function index()
    {

        return view('admin.comments.index');
    }

    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->commentService->getList($params);
        
        return response()->json($result);
    }

    public function deleteMany()
    {
        $params = $this->request->only('ids', 'total');
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $delete = $this->commentService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " bình luận thành công !!!", 'success');

        //add log
        $log['action'] = "Xóa " . $params['total'] . " bình luận thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " bình luận thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $detail = $this->commentService->detail($id);
        $delete = $this->commentService->delete($id);
        if($delete) {
            //add log
            $log['action'] = "Xóa bình luận thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            Message::alertFlash('Bạn đã xóa bình luận thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa bình luận không thành công', 'danger');
        }

        return redirect("comment");
    }

    public function edit($id)
    {

        $data['commentInfo'] = $this->commentService->detail($id);
        

        return view('admin.comments.edit', $data);

       
    }

    public function editAction($id)
    {
        $params = $this->request->only(['comment_status']);
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_comment_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        $edit = $this->commentService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập bình luận thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập bình luận thành công !!!";
        return response()->json($data);
    }

    public function editManyAction()
    {
        $params = $this->request->only(['status', 'ids', 'total']);
        $params = ArrayHelper::removeArrayNull($params);
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $update = $this->commentService->updateMany($params['ids'], ['comment_status' => $params['status']]);
        if (!$update) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " bình luận thành công !!!", 'success');

        //add log
        $log['action'] = "Cập nhập nhiều bình luận thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " bình luận thành công !!!";
        return response()->json($data);
    }

   

}