<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\ValidationService;
use App\Services\ProjectService;
use App\Services\TaskService;
use App\Services\LogsUserService;
use Illuminate\Http\Request;
use App\Helpers\Message;
use App\Helpers\ArrayHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
class ProjectController extends Controller
{

    protected  $request;
    protected  $validator;
    protected  $projectService;
    protected  $taskService;
    protected  $language;

    //
    function __construct(Request $request, ValidationService $validator, ProjectService $projectService, TaskService $taskService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->projectService = $projectService;
        $this->taskService = $taskService;
        $this->language = App::currentLocale();


        session()->start();
        session()->put('RF.subfolder', "doi-tac");
        session()->put('RF.thumbnailWidth', 370);
        session()->put('RF.thumbnailHeight', 300);
    }


    /**
     * METHOD index - View List project
     *
     * @return void
     */

    public function index()
    {

        $total_projects = $this->projectService->totalRows(null);
        $total_projects_active = $this->projectService->totalRows(['project_status' => 'in_progress']);
        $total_projects_pending = $this->projectService->totalRows(['project_status' => 'pending']);
        $total_projects_completed = $this->projectService->totalRows(['project_status' => 'completed']);

        $total_budgets = $this->projectService->totalRows(null);
        return view('admin.projects.index', compact('total_projects', 'total_projects_active', 'total_projects_pending',  'total_projects_completed', 'total_budgets', ));
    }

    /**
     * METHOD index - Ajax Get List project
     *
     * @return void
     */

    public function ajaxGetList()
    {
        try {
            $params = $this->request->all();

            $paginator = $this->projectService->getList(['*'], $params);
            $content = view('admin.projects.elements.card-project', compact('paginator'))->render();
            $pagination = view('admin.projects.elements.pagination', compact('paginator'))->render();
            $result['content'] = $content;
            $result['pagination'] = $pagination;
            $result['success'] = true;
        } catch (\Exception $e) {
            $result['message'] = 'admin.projects.load_projects_failed';
        }

        return response()->json($result);
    }


    /**
     * METHOD viewInsert - VIEW ADD PROJECT
     *
     * @return void
     */

    public function add()
    {

        return view('admin.projects.add');

    }

     /**
     * METHOD editAction - Edit Action Project with ID
     *
     * @return json
     */

    public function addAction() {



        DB::beginTransaction();
        try {

            //  Create

            $params = $this->request->all();
            $user = auth()->user();
            session()->start();
            session()->put('RF.subfolder', $user->username);
            $params['created_by_user'] = $user->id;
            $params['updated_by_user'] = $user->id;


            $validator = $this->validator->make($params, 'add_project_fields');
            if ($validator->fails()) {
                return response()->json(Message::get(1, $this->language , $validator->errors()->all()), 400);
            }

            $add = $this->projectService->insert($params);

            if ($add) {
                $data['success'] = true;
                $data['message'] = __('admin.projects.add_project_success');

            } else {

                $data['message'] = __('admin.projects.add_project_error');
            }

            $log['action'] = __('admin.logs.update_type_with_id', ['type' => 'Project', 'id' => $add]);
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = __('admin.projects.update_project_success');

            DB::commit();


        } catch (\Exception $e) {
            DB::rollBack();
            $data['message'] = __('admin.projects.add_project_error');

        }

        return response()->json($data);
    }

    /**
     * METHOD edit - VIEW EDIT PROJECT
     *
     * @return void
     */


    public function edit($id)
    {
        $project = $this->projectService->detail('id', $id);

        if($project) {


            return view('admin.projects.edit', ['project' => $project]);

        } else {
            Message::alertFlash('Project này không tồn tại !', 'danger');
            return view('admin.errors.404');
            //return redirect()->back();
        }


    }

    /**
     * METHOD editAction - Edit Action Project with ID
     *
     * @return json
     */

    public function editAction($id) {
        $params = $this->request->all();
        $user = auth()->user();
        $params['created_by_user'] = $user->id;
        $params['updated_by_user'] = $user->id;
        $params['project_author'] = $user->full_name;

        $validator = $this->validator->make($params, 'edit_project_fields');

        if ($validator->fails()) {
            return redirect()->back()->withErrors(['error' => $validator->errors()->all()]);
        }

        $add = $this->projectService->update($id, $params);

        if ($add) {


            Message::alertFlash('Bạn đã cập nhật dự án thành công', 'success');
            $log['action'] = "Cập nhật dự án có ID = ".$add." thành công";
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

        } else {

            Message::alertFlash('Đã xảy ra lỗi khi tạo dự án mới, vui lòng liên hệ quản trị viên!', 'danger');

        }


        return redirect()->back()->withInput();
    }

    /**
     * METHOD deleteMany - Delete Array Post with IDs
     *
     * @return json
     */


    public function deleteMany()
    {
        $params = $this->request->only('ids', 'total');
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $delete = $this->projectService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " dự án thành công !!!", 'success');

        $log['action'] = "Xóa các dự án có IDs = ".implode(", ", $params['ids'])." viết thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " dự án thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $delete = $this->projectService->delete($id);
        if($delete) {
            Message::alertFlash('Bạn đã xóa dự án thành công', 'success');

            $log['action'] = "Xóa dự án có ID = ".$id." thành công";
            $log['content'] = '';
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);
        } else {
            Message::alertFlash('Bạn đã xóa dự án thất bại', 'danger');
        }

        return redirect("project");
    }

    public function editManyAction()
    {
        $params = $this->request->only(['status', 'ids', 'total']);
        $params = ArrayHelper::removeArrayNull($params);
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $update = $this->projectService->updateMany($params['ids'], ['project_status' => $params['status']]);
        if (!$update) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " dự án thành công !!!", 'success');

        //add log
        $log['action'] = "Cập nhập các dự án có IDs = ".implode(", ", $params['ids'])." viết thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " dự án thành công !!!";
        return response()->json($data);
    }



}
