<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\ValidationService;
use App\Services\PageService;
use App\Services\LogsUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Helpers\Message;
use App\Helpers\ArrayHelper;
use Exception;


class PageController extends Controller
{
    protected  $request;
    protected  $validator;
    protected  $pageService;
    protected  $language;

    function __construct(Request $request, ValidationService $validator, PageService $pageService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->pageService = $pageService;
        $this->language = App::currentLocale();
    }

    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {


        return view('admin.pages.index');
    }

    /**
     * METHOD index - Ajax Get List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();
        $column = ['id', 'page_title', 'page_slug', 'page_status', 'page_template', 'language', 'created_at', 'page_author'];
        $result = $this->pageService->getList( $column, $params );

        return response()->json($result);
    }


    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {
        return view('admin.pages.add');
    }

    public function addAction()
    {

        try {
            $params = $this->request->all();
            $user = auth()->user();
            $params['created_by_user'] = $user->id;
            $params['updated_by_user'] = $user->id;
            $params['post_author'] = $user->full_name;
            $validator = $this->validator->make($params, 'add_page_fields');
            if ($validator->fails()) {
                return response()->json(Message::get(1, $this->language , $validator->errors()->all()), 400);
            }

            $add = $this->pageService->insert($params);

            if ($add) {

                $log['action'] = __('admin.logs.add_type_with_id', ['type' => 'Page', 'id' => $add]);
                $log['content'] = json_encode($params);
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

                $data['success'] = true;
                $data['message'] = __('admin.pages.add_page_success');
            } else {

                $data['message'] = __('admin.pages.add_page_error');
            }

            return response()->json($data);
        } catch(Exception $e) {

            $data['message'] = __('admin.pages.add_page_error');
            return response()->json($data);
        }

    }

    public function edit($id)
    {
        $page = $this->pageService->findByKey('id', $id);
        if (!$page) {
            abort('404');
        }
        return view('admin.pages.edit', ['page' => $page,]);
    }

    public function editAction($id)
    {
        try {
            $params = $this->request->all();
            $user = auth()->user();
            $params['created_by_user'] = $user->id;
            $params['updated_by_user'] = $user->id;
            $params['post_author'] = $user->full_name;
            if ($id && $id > 0) {
                $validator = $this->validator->make($params, 'edit_page_fields');
                if ($validator->fails()) {
                    return response()->json(Message::get(1, $this->language , $validator->errors()->all()), 400);
                }

                $edit = $this->pageService->update($id, $params);

                if ($edit) {
                    // Message::alertFlash('Bạn đã cập nhật trang thành công', 'success');

                    $log['action'] = __('admin.logs.update_type_with_id', ['type' => 'Page', 'id' => $id]);
                    $log['content'] = json_encode($params);
                    $log['ip'] = $this->request->ip();
                    LogsUserService::add($log);

                    $data['success'] = true;
                    $data['message'] = __('admin.pages.update_page_success');
                } else {

                    Message::alertFlash('Đã xảy ra lỗi khi cập nhật trang, vui lòng liên hệ quản trị viên!', 'danger');
                    $data['message'] = __('admin.pages.update_page_error');
            }
            return response()->json($data);
        }
        } catch(Exception $e) {
            $data['message'] = __('admin.pages.update_page_error');
            return response()->json($data);
        }

    }



    /**
     * METHOD deleteMany - Delete Array Post with IDs
     *
     * @return json
     */


    public function deleteMany()
    {
        try {
            $params = $this->request->only('ids', 'total');
            if (!isset($params['ids'])) {
                return response()->json(Message::get(26, $this->language, []), 400);
            }
            $delete = $this->pageService->deleteMany($params['ids']);
            if (!$delete) {
                return response()->json(Message::get(12, $this->language, []), 400);
            }


            $log['action'] = trans('admin.logs.delete_type_success_with_ids', ['type' => 'page', 'ids' => implode(", ", $params['ids'])]);
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = trans('admin.pages.delete_many_page_success', ['total' => $params['total']]);
            return response()->json($data);
        } catch (\Exception $e) {

            $data['message'] = trans('admin.pages.error_exception');
            return response()->json($data);
        }

    }

    public function delete($id)
    {
        try {

            $delete = $this->pageService->delete($id);
            if($delete) {
                $log['action'] = trans('admin.logs.delete_type_success_with_ids', ['type' => 'page', 'ids' => $id]);
                $log['content'] = '';
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

                $data['success'] = true;
                $data['message'] = trans('admin.pages.delete_page_success');
            } else {
                $data['message'] = trans('admin.pages.error_exception');
            }

        } catch(Exception $e) {
            $data['message'] = trans('admin.pages.error_exception');
            return response()->json($data);
        }

    }

    public function editManyAction()
    {
        $params = $this->request->only(['status', 'ids', 'total']);
        $params = ArrayHelper::removeArrayNull($params);
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $this->language, []), 400);
        }
        $update = $this->pageService->updateMany($params['ids'], ['post_status' => $params['status']]);
        if (!$update) {
            return response()->json(Message::get(12, $this->language, []), 400);
        }

        Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " trang thành công !!!", 'success');

        //add log
        $log['action'] = "Cập nhập các trang có IDs = ".implode(", ", $params['ids'])." viết thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " trang thành công !!!";
        return response()->json($data);
    }


}
