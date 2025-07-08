<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\PageService;
use App\Services\ValidationService;
use App\Services\BannerService;
use Illuminate\Http\Request;

class BannersController extends Controller
{
    protected $request;
    protected $bannerService;
    protected $validator;

    function __construct(Request $request, ValidationService $validator, BannerService $bannerService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->bannerService = $bannerService;
        session()->start();
        session()->put('RF.subfolder', "banner");
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        return view('admin.banners.index');
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->bannerService->getList($params);

        return response()->json($result);
    }

    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {
        $pages = PageService::getListSlug();

        return view('admin.banners.add', compact('pages'));
    }

    public function addAction()
    {
        $params = $this->request->only('image', 'title', 'description', 'link', 'language', 'position', 'active', 'type', 'type_value', 'caption', 'caption_position', 'banner_type');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'add_banner_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        if($params['type'] === 'page' && (!isset($params['type_value']) || $params['type_value'] === '')) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        if($params['type'] !== 'page') {
            $params['type_value'] = $params['type'];
        }
        if($params['caption'] == '0') {
            unset($params['caption_position']);
        }

        $add = BannerService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Thêm mới 1 banner có id = " . $add;
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Thêm mới banner thành công !!!";
        } else {
            $data['message'] = "Lỗi khi thêm mới banner !";
        }

        return response()->json($data);
    }

    public function delete($id)
    {
        $detail = $this->bannerService->detail($id);
        $delete = $this->bannerService->delete($id);
        if($delete) {
            UploadImage::deleteFile($detail->image);
            //add log
            $log['action'] = "Xóa banner thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);
            Message::alertFlash('Bạn đã xóa banner thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa banner không thành công', 'danger');
        }

        return redirect("banner");
    }

    public function edit($id)
    {
        $bannerInfo = $this->bannerService->detail($id);
        $pages = PageService::getListSlug();

        return view('admin.banners.edit', compact('bannerInfo', 'pages'));
    }

    public function editAction($id)
    {
        $params = $this->request->only('image', 'title', 'description', 'link', 'language', 'position', 'active', 'type', 'type_value', 'caption', 'caption_position', 'banner_type');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_banner_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        if($params['type'] !== 'page') {
            $params['type_value'] = $params['type'];
        }
        
        $edit = $this->bannerService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập banner thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập banner thành công !!!";
        return response()->json($data);
    }

}
