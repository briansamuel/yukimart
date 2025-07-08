<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    protected $request;
    protected $brandService;
    protected $validator;

    function __construct(Request $request, ValidationService $validator, BrandService $brandService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->brandService = $brandService;
        session()->start();
        session()->put('RF.subfolder', "brand");
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        return view('admin.brands.index');
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->brandService->getList($params);

        return response()->json($result);
    }

    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {

        return view('admin.brands.add');
    }

    public function addAction()
    {
        $params = $this->request->only('image', 'title', 'description', 'link', 'language', 'position', 'active', 'type', 'type_value', 'caption', 'caption_position', 'banner_type', 'order');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'add_brand_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        $add = BrandService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Thêm mới 1 brand có id = " . $add;
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Thêm mới brand thành công !!!";
        } else {
            $data['message'] = "Lỗi khi thêm mới brand !";
        }

        return response()->json($data);
    }

    public function delete($id)
    {
        $detail = $this->brandService->detail($id);
        $delete = $this->brandService->delete($id);
        if($delete) {
            //add log
            $log['action'] = "Xóa brand thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);
            Message::alertFlash('Bạn đã xóa brand thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa brand không thành công', 'danger');
        }

        return redirect("brand");
    }

    public function edit($id)
    {
        $brandInfo = $this->brandService->detail($id);

        return view('admin.brands.edit', compact('brandInfo'));
    }

    public function editAction($id)
    {
        $params = $this->request->only('image', 'title', 'description', 'link', 'language', 'position', 'active', 'type', 'type_value', 'caption', 'caption_position', 'banner_type', 'order');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_brand_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        $edit = $this->brandService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập brand thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập brand thành công !!!";
        return response()->json($data);
    }

}
