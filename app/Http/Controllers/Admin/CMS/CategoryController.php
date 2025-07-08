<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\ValidationService;
use App\Services\CategoryService;
use App\Services\LogsUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Helpers\Message;
use Exception;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    //
    function __construct(Request $request, ValidationService $validator, CategoryService $categoryService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->categoryService = $categoryService;
        $this->language = App::currentLocale();
    }

    /**
     * METHOD index - View List categories
     *
     * @return void
     */

    public function index()
    {
        $params = $this->request->only(['type']);
        $params['type'] = isset($params['type']) ? $params['type'] : 'category_of_categories';
        $args = array('category_parent' => 0, 'category_type' => $params['type']);
        $categories = $this->categoryService->getAllByKey(['id', 'category_name'], $args);
        return view('admin.categories.index', ['categories' => $categories, 'type' => $params['type']]);
    }

    /**
     * METHOD index - Ajax Get List categories
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->categoryService->getList($params);

        return response()->json($result);
    }


    /**
     * METHOD viewInsert - VIEW ADD, EDIT categories
     *
     * @return void
     */

    public function edit($id = 0)
    {
        if ($id > 0) {

            $category = $this->categoryService->findByKey('id', $id);
            $params = array('category_parent' => 0,  'category_type' => $category->category_type);
            $categories = $this->categoryService->getAllByKey(['id', 'category_name'], $params);
            return view('admin.categories.edit', ['category' => $category, 'categories' => $categories]);
        } else {
            Message::alertFlash('ID Danh mục này không tồn tại, vui lòng thử lại sau !', 'danger');
            return view('admin.categories.list');
        }
    }

    // AddAction

    public function addAction()
    {
        DB::beginTransaction();
        try {   //  Create
            $params = $this->request->only(['id', 'category_name', 'category_slug', 'category_description', 'category_parent', 'category_type', 'language']);

            $validator = $this->validator->make($params, 'add_category_fields');

            if ($validator->fails()) {
                return response()->json(Message::get(1, $this->language , $validator->errors()->all()), 400);
            }

            $add = $this->categoryService->insert($params);

            if ($add) {
                $data['success'] = true;
                $data['message'] = __('admin.categories.add_category_success');

            } else {

                $data['message'] = __('admin.categories.add_category_error');
            }

            $log['action'] = __('admin.logs.update_type_with_id', ['type' => 'Category', 'id' => $add]);
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = __('admin.categories.update_category_success');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $data['message'] = __('admin.categories.add_categories_error');

        }

        return response()->json($data);

    }

    public function save()
    {
        $params = $this->request->only(['id', 'category_name', 'category_slug', 'category_description', 'category_parent', 'category_type', 'language']);

        $validator = $this->validator->make($params, 'add_category_fields');

        if ($validator->fails()) {
            return redirect()->back()->withErrors(['error' => $validator->errors()->all()]);
        }

        if (isset($params['id']) && $params['id'] > 0) {
            $edit = $this->categoryService->update($params['id'], $params);

            if ($edit) {

                Message::alertFlash('Bạn đã cập nhật danh mục thành công', 'success');
            } else {

                Message::alertFlash('Đã xảy ra lỗi khi cập nhật danh mục, vui lòng liên hệ quản trị viên!', 'danger');
            }
        } else {
            $add = $this->categoryService->insert($params);

            if ($add) {

                Message::alertFlash('Bạn đã thêm danh mục mới thành công', 'success');
            } else {

                Message::alertFlash('Đã xảy ra lỗi khi tạo danh mục mới, vui lòng liên hệ quản trị viên!', 'danger');
            }
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
        $delete = $this->categoryService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " danh mục thành công !!!", 'success');

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " danh mục thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $delete = $this->categoryService->delete($id);
        if ($delete) {
            Message::alertFlash('Bạn đã xóa danh mục thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa danh mục thất bại', 'danger');
        }

        return redirect()->back();
    }
}
