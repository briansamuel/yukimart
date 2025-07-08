<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\ValidationService;
use App\Services\PostService;
use App\Services\CategoryService;
use App\Services\CategoryPostService;
use App\Services\LogsUserService;
use Illuminate\Http\Request;
use App\Helpers\Message;
use App\Helpers\ArrayHelper;
class ServiceController extends Controller
{
    protected $request;
    protected $validator;
    protected $postService;

    function __construct(Request $request, ValidationService $validator, PostService $postService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->postService = $postService;
    }
    
    
    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        
        return view('admin.service.index');
    }

    /**
     * METHOD index - Ajax Get List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();
        $params['query']['post_type'] = 'service';
        $result = $this->postService->getList($params);

        return response()->json($result);
    }


    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */
    
    public function add($id = 0)
    {
        session()->start();
        session()->put('RF.subfolder', "dich-vu");
        $params = array('category_parent' => 0, 'category_type' => 'category_of_service');
        $categories = CategoryService::getAllByKey(['id', 'category_name'], $params);
        if($id > 0) {
            $params_cat = array('post_id' => $id);
            
            $categories_is_check = CategoryPostService::getAllByKey(['category_id'], $params_cat, true);
            $categories_is_check = $categories_is_check->map(function ($item) {
                return $item->category_id;
            });
            
            $categories = $this->categoriesCheckbox($categories,$categories_is_check->toArray(), 0);
        } else {
            $categories = $this->categoriesCheckbox($categories,array(), 0);
        }
        if($id > 0) {

            $news = $this->postService->findByKey('id', $id);
            
            return view('admin.service.edit', ['news' => $news, 'categoriesCheckbox' => $categories ]);

        } else {
            
            return view('admin.service.add', [ 'categoriesCheckbox' =>  $categories]);
        }
        
    }

    public function save() {
        $params = $this->request->all();
        $user = auth()->user();
        $params['created_by_user'] = $user->id;
        $params['updated_by_user'] = $user->id;
        $params['post_author'] = $user->full_name;
        
        $validator = $this->validator->make($params, 'add_news_fields');

        if ($validator->fails()) {
            return redirect()->back()->withErrors(['error' => $validator->errors()->all()]);     
        }
       
        if(isset($params['id']) && $params['id'] > 0) {
            $edit = $this->postService->update($params['id'],$params);

            if ($edit) {

                CategoryPostService::deleteManyByKey(array('post_id' => $params['id']));
                
                if(isset($params['post_categories']) && is_array($params['post_categories'])) {
                    
                    foreach($params['post_categories'] as $category_id) {
                        $data = array('category_id' => $category_id, 'post_id' => $params['id']);
                        CategoryPostService::insert($data);
                    }
                }
                
                Message::alertFlash('Bạn đã cập nhật bài viết thành công', 'success');

                $log['action'] = "Cập nhật bài viết có ID = ".$params['id']." viết thành công";
                $log['content'] = json_encode($params);
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);
                
            } else {
                
                Message::alertFlash('Đã xảy ra lỗi khi cập nhật bài viết, vui lòng liên hệ quản trị viên!', 'danger');
            
            }
        } else {
            $add = $this->postService->insert($params);

            if ($add) {
                if(isset($params['post_categories']) && is_array($params['post_categories'])) {
                    
                    foreach($params['post_categories'] as $id) {
                        $data = array('category_id' => $id, 'post_id' => $add);
                        CategoryPostService::insert($data);
                    }
                }
               
                Message::alertFlash('Bạn đã thêm bài viết mới thành công', 'success');
                $log['action'] = "Thêm bài viết có ID = ".$add." viết thành công";
                $log['content'] = json_encode($params);
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

            } else {
                
                Message::alertFlash('Đã xảy ra lỗi khi tạo bài viết mới, vui lòng liên hệ quản trị viên!', 'danger');
            
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
        $delete = $this->postService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }
        
        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " Bài viết thành công !!!", 'success');

        $log['action'] = "Xóa các bài viết có IDs = ".implode(", ", $params['ids'])." viết thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " Bài viết thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $delete = $this->postService->delete($id);
        if($delete) {
            Message::alertFlash('Bạn đã xóa bài viết thành công', 'success');

            $log['action'] = "Xóa bài viết có ID = ".$id." thành công";
            $log['content'] = '';
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);
        } else {
            Message::alertFlash('Bạn đã xóa bài viết thất bại', 'danger');
        }

        return redirect("news");
    }

    public function editManyAction()
    {
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
        $log['action'] = "Cập nhập các bài viết có IDs = ".implode(", ", $params['ids'])." viết thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " Bài viết thành công !!!";
        return response()->json($data);
    }


    public function categoriesCheckbox($categories = [], $categories_is_check = array(), $lvl = 0)
    {
        $html = '';
        foreach($categories as $category) {
            $params = array('category_parent' => $category->id);
            $category->is_check = '';
            if(is_array($categories_is_check)) {
                if(in_array($category->id, $categories_is_check)) {
                    $category->is_check = 'checked';
                }
            }
            
            $html = $html. '<label class="kt-checkbox kt-checkbox--tick kt-checkbox--brand ml-'.($lvl*2).'">
            <input type="checkbox" name="post_categories[]" value="'.$category->id.'" '.$category->is_check.' > '.$category->category_name .'
            <span></span>
        </label>';
            $category->child = CategoryService::getAllByKey(['id', 'category_name'], $params);
            if($category->child) {
                $html = $html.self::categoriesCheckbox($category->child, $categories_is_check, $lvl+1);
            }
            
        }
       
        return $html;
    }
}
