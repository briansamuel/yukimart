<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\ValidationService;
use App\Services\PostService;
use App\Services\CategoryService;
use App\Services\CategoryPostService;
use App\Services\LogsUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Helpers\Message;
use App\Helpers\ArrayHelper;
use Exception;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    //
    function __construct(Request $request, ValidationService $validator, PostService $postService, CategoryService $categoryService, CategoryPostService $categoryPostService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->postService = $postService;
        $this->categoryService = $categoryService;
        $this->categoryPostService = $categoryPostService;
        $this->language = App::currentLocale();
        session()->start();
        session()->put('RF.subfolder', "news");
        session()->put('RF.thumbnailWidth', 355);
        session()->put('RF.thumbnailHeight', 345);
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {

        return view('admin.news.index');
    }

    /**
     * METHOD index - Ajax Get List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();
        $params['query']['post_type'] = 'news';
        $result = $this->postService->getList($params);

        return response()->json($result);
    }


    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {

        $params = array('category_parent' => 0, 'category_type' => 'category_of_news');
        $categories = CategoryService::getAllByKey(['id', 'category_name'], $params);
        $categories = $this->categoriesCheckbox($categories, array(), 0);
        return view('admin.news.add', ['categoriesCheckbox' =>  $categories]);
    }

    public function addAction()
    {
        DB::beginTransaction();
        try {
            $params = $this->request->all();
            $user = auth()->user();
            $params['created_by_user'] = $user->id;
            $params['updated_by_user'] = $user->id;
            $params['post_author'] = $user->full_name;

            $validator = $this->validator->make($params, 'add_news_fields');

            if ($validator->fails()) {
                return response()->json(Message::get(1, $this->language , $validator->errors()->all()), 400);
            }

            $add = $this->postService->insert($params);

            if ($add) {
                if (isset($params['post_categories']) && is_array($params['post_categories'])) {

                    foreach ($params['post_categories'] as $id) {
                        $data = array('category_id' => $id, 'post_id' => $add);
                        $this->categoryPostService->insert($data);
                    }
                }

                $log['action'] = __('admin.logs.add_type_with_id', ['type' => 'News', 'id' => $add]);
                $log['content'] = json_encode($params);
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

                $data['success'] = true;
                $data['message'] = __('admin.news.add_news_success');
                $data['redirect_url'] = route('news.list');
            } else {

                $data['message'] = __('admin.news.add_news_error');
            }

            DB::commit();
            return response()->json($data);
        } catch (Exception $e) {
            DB::rollBack();
            $data['message'] = __('admin.news.add_news_error');
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
                return response()->json(Message::get(26, $lang = '', []), 400);
            }
            $delete = $this->postService->deleteMany($params['ids']);
            if (!$delete) {
                return response()->json(Message::get(12, $lang = '', []), 400);
            }



            $log['action'] = trans('admin.logs.delete_type_success_with_ids', ['type' => 'news', 'ids' => implode(", ", $params['ids'])]);
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = trans('admin.news.delete_many_news_success', ['total' => $params['total']]);

        } catch (\Exception $e) {

        }
        return response()->json($data);
    }

    public function delete($id)
    {

        try {

            $delete = $this->postService->delete($id);
            if ($delete) {
                $log['action'] = trans('admin.logs.delete_type_success_with_ids', ['type' => 'news', 'ids' => $id]);
                $log['content'] = '';
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

                $data['success'] = true;
                $data['message'] = trans('admin.news.delete_news_success');
            } else {
                $data['message'] = trans('admin.news.error_exception');
            }

        } catch (Exception $e) {
            $data['message'] = trans('admin.news.error_exception');

        }

        return response()->json($data);
    }

    /**
     * METHOD edit - edit view
     *
     * @return json
     */
    public function edit($id = 0)
    {
        if(!$id) {
            abort(404);
        }

        $params = array('category_parent' => 0, 'category_type' => 'category_of_news');
        $categories = CategoryService::getAllByKey(['id', 'category_name'], $params);
        if ($id > 0) {
            $params_cat = array('post_id' => $id);
            $categories_is_check = CategoryPostService::getAllByKey(['category_id'], $params_cat, true);
            $categories_is_check = $categories_is_check->map(function ($item) {
                return $item->category_id;
            });

                $categories = $this->categoriesCheckbox($categories, $categories_is_check->toArray(), 0);
        } else {
            $categories = $this->categoriesCheckbox($categories, array(), 0);
        }

        $news = $this->postService->findByKey('id', $id);

        return view('admin.news.edit', ['news' => $news, 'categoriesCheckbox' => $categories]);

    }

    /**
     * METHOD editMany - Edit Array News with IDs
     *
     * @return json
     */

    public function editAction($id = 0)
    {
        DB::beginTransaction();
        if(!$id) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }

        try {
            $params = $this->request->all();
            $params = ArrayHelper::removeArrayNull($params);

            $update = $this->postService->update($id, $params);
            if (!$update) {
                return response()->json(Message::get(12, $lang = '', []), 400);
            }

            $this->categoryPostService->deleteManyByKey(['post_id' => $id]);
            if (isset($params['post_categories']) && is_array($params['post_categories'])) {

                foreach ($params['post_categories'] as $c_id) {
                    $data = array('category_id' => $c_id, 'post_id' => $id);
                    $this->categoryPostService->insert($data);
                }
            }


            // Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " Bài viết thành công !!!", 'success');

            //add log
            $log['action'] = __('admin.logs.update_type_with_id', ['type' => 'News', 'id' => $id]);
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = __('admin.news.update_news_success');

            DB::commit();
            return response()->json($data);
        } catch (\Exception $e) {

            $data['message'] = __('admin.news.update_news_error');
            DB::rollBack();
            return response()->json($data);
        }

    }

    /**
     * METHOD editMany - Edit Array News with IDs
     *
     * @return json
     */

    public function editManyAction()
    {
        try {
            $params = $this->request->only(['status', 'ids', 'total']);
            $params = ArrayHelper::removeArrayNull($params);
            if (!isset($params['ids'])) {
                return response()->json(Message::get(26, $lang = '', []), 400);
            }
            $update = $this->postService->updateMany($params['ids'], ['post_status' => $params['status']]);
            if (!$update) {
                return response()->json(Message::get(12, $lang = '', []), 400);
            }

            Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " Bài viết thành công !!!", 'success');

            //add log
            $log['action'] = "Cập nhập các bài viết có IDs = " . implode(", ", $params['ids']) . " viết thành công";
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " Bài viết thành công !!!";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['message'] = __('admin.news.add_news_error');
            return response()->json($data);
        }

    }


    public function categoriesCheckbox($categories = [], $categories_is_check = array(), $lvl = 0)
    {
        $html = '';
        foreach ($categories as $category) {
            $params = array('category_parent' => $category->id);
            $category->is_check = '';
            if (is_array($categories_is_check)) {
                if (in_array($category->id, $categories_is_check)) {
                    $category->is_check = 'checked';
                }
            }

            $html = $html . '<div class="form-check form-check-custom form-check-solid mb-5 ms-' . ($lvl * 2) . ' ps-' . ($lvl * 2) . '">
            <input id="post_category_'. $category->id .'" class="form-check-input" type="checkbox" name="post_categories[]" value="' . $category->id . '" ' . $category->is_check . ' >
            <label class="form-check-label" for="post_category_'. $category->id .'">' . $category->category_name . '</label>
        </div>';
            $category->child = $this->categoryService->getAllByKey(['id', 'category_name'], $params);
            if ($category->child) {
                $html = $html . self::categoriesCheckbox($category->child, $categories_is_check, $lvl + 1);
            }
        }

        return $html;
    }
}
