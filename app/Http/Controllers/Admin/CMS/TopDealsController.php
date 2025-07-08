<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\ValidationService;
use App\Services\TopDealService;
use Illuminate\Http\Request;

class TopDealsController extends Controller
{
    protected $request;
    protected $topDealService;
    protected $validator;

    function __construct(Request $request, ValidationService $validator, TopDealService $topDealService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->topDealService = $topDealService;
        session()->start();
        session()->put('RF.subfolder', "top-deals");
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {
        return view('admin.top_deals.index');
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->topDealService->getList($params);

        return response()->json($result);
    }

    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {

        return view('admin.top_deals.add');
    }

    public function addAction()
    {
        $params = $this->request->only('title', 'image', 'description', 'label', 'location', 'start_time', 'end_time', 'regular_price', 'sale_price', 'link', 'position', 'language', 'deal_group');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'add_top_deals_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        $params['start_time'] = strtotime($params['start_time']);
        $params['end_time'] = strtotime($params['end_time']);

        $add = TopDealService::add($params);
        if ($add) {
            //add log
            $log['action'] = "Thêm mới 1 top deals có id = " . $add;
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Thêm mới top deals thành công !!!";
        } else {
            $data['message'] = "Lỗi khi thêm mới top deals !";
        }

        return response()->json($data);
    }

    public function delete($id)
    {
        $detail = $this->topDealService->detail($id);
        $delete = $this->topDealService->delete($id);
        if($delete) {
            UploadImage::deleteFile($detail->image);
            //add log
            $log['action'] = "Xóa top deals thành công có ID = " . $id;
            $log['content'] = json_encode($detail);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);
            Message::alertFlash('Bạn đã xóa top deals thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa top deals không thành công', 'danger');
        }

        return redirect("banner");
    }

    public function detail($id)
    {
        $topDeal = $this->topDealService->detail($id);

        return view('admin.top_deals.detail', ['topDeal' => $topDeal]);
    }

    public function edit($id)
    {
        $topDeal = $this->topDealService->detail($id);

        return view('admin.top_deals.edit', ['topDeal' => $topDeal]);
    }

    public function editAction($id)
    {
        $params = $this->request->only('title', 'image', 'description', 'label', 'location', 'start_time', 'end_time', 'regular_price', 'sale_price', 'link', 'position', 'language', 'deal_group');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_top_deals_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        $params['start_time'] = strtotime($params['start_time']);
        $params['end_time'] = strtotime($params['end_time']);

        $edit = $this->topDealService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập top deals thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập top deals thành công !!!";
        return response()->json($data);
    }

}
