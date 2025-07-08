<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthPermissionService
{

    //CI instance
    private $CI;
    private $request;
    private $admin = 'no';
    private $is_root = 'no';
    private $requireRoot = 'no';
    //current user's permissions
    private $authobj;

    private $MyProfileController = array(
        // 'MyProfileController' => 'Quản lý toàn quyền(Tài Khoản => Thông tin tài khoản)',
        'MyProfileController/profile' => 'permission.profile.action.view',
        'MyProfileController/updateProfile' => 'permission.profile.action.edit',
        // 'MyProfileController/changePassword' => 'Thay đổi mật khẩu',
        // 'MyProfileController/changePasswordAction' => 'Action thay đổi mật khẩu',
    );

    private $UsersController = array(
        // 'UsersController' => 'Quản lý toàn quyền(Tài Khoản => Tài Khoản Hệ Thống)',
        'UsersController/index' => 'permission.user.action.view',
        // 'UsersController/ajaxGetList' => 'Action Danh sách hệ thống',
        'UsersController/add' => 'permission.user.action.add',
        // 'UsersController/addAction' => 'Action xử lý tạo mới 1 tài khoản hệ thống',
        'UsersController/edit' => 'permission.user.action.edit',
        // 'UsersController/editAction' => 'Action xử lý sửa thông tin 1 tài khoản hệ thống',
        // 'UsersController/editManyAction' => 'Action xử lý sửa nhiều thông tin tài khoản hệ thống',
        'UsersController/delete' => 'permission.user.action.delete',
        // 'UsersController/deleteMany' => 'Xóa nhiều thông tin tài khoản hệ thống',
    );

    private $AgentsController = array(
        // 'AgentsController' => 'Quản lý toàn quyền(Tài Khoản => Tài Khoản Đại Lý)',
        'AgentsController/index' => 'permission.agent.action.view',
        // 'AgentsController/ajaxGetList' => 'Action Danh sách đại lý',
        'AgentsController/add' => 'permission.user.action.add',
        // 'AgentsController/addAction' => 'Action xử lý tạo mới 1 tài khoản đại lý',
        'AgentsController/edit' => 'permission.user.action.edit',
        // 'AgentsController/editAction' => 'Action xử lý sửa thông tin 1 tài khoản đại lý',
        // 'AgentsController/editManyAction' => 'Action xử lý sửa nhiều thông tin tài khoản đại lý',
        'AgentsController/delete' => 'permission.user.action.delete',
        // 'AgentsController/deleteMany' => 'Xóa nhiều thông tin tài khoản đại lý',
    );

    private $GuestsController = array(
        // 'GuestsController' => 'Quản lý toàn quyền(Tài Khoản => Tài Khoản Khách Hàng)',
        'GuestsController/index' => 'permission.guest.action.view',
        // 'GuestsController/ajaxGetList' => 'Action Danh sách khách hàng',
        'GuestsController/add' => 'permission.user.action.add',
        // 'GuestsController/addAction' => 'Action xử lý tạo mới 1 tài khoản khách hàng',
        'GuestsController/edit' => 'permission.user.action.edit',
        // 'GuestsController/editAction' => 'Action xử lý sửa thông tin 1 tài khoản khách hàng',
        // 'GuestsController/editManyAction' => 'Action xử lý sửa nhiều thông tin tài khoản khách hàng',
        'GuestsController/delete' => 'permission.user.action.delete',
        // 'GuestsController/deleteMany' => 'Xóa nhiều thông tin tài khoản khách hàng',
    );

    private $UserGroupController = array(
        // 'UserGroupController' => 'Quản lý toàn quyền(Tài Khoản => Quản lý phân quyền)',
        'UserGroupController/index' => 'permission.group.action.view',
        // 'UserGroupController/ajaxGetList' => 'Action Danh sách phân quyền',
        'UserGroupController/add' => 'permission.group.action.add',
        // 'UserGroupController/addAction' => 'Action xử lý tạo mới 1 phân quyền',
        'UserGroupController/edit' => 'permission.group.action.edit',
        // 'UserGroupController/editAction' => 'Action xử lý sửa thông tin 1 phân quyền',
        // 'UserGroupController/editManyAction' => 'Action xử lý sửa nhiều thông tin phân quyền',
        'UserGroupController/delete' => 'permission.group.action.delete',
        // 'UserGroupController/deleteMany' => 'Xóa nhiều thông tin phân quyền',
    );

    private $LogsUserController = array(
        // 'LogsUserController' => 'Quản lý toàn quyền(Tài Khoản => Logs User)',
        'LogsUserController/index' => 'permission.logs.action.view',
        // 'LogsUserController/ajaxGetList' => 'Action Danh sách Logs User',
    );

    // Group quyền tin tức
    private $NewsController = array(
        // 'NewsController' => 'Quản lý toàn quyền (Tin tức)',
        'NewsController/index' => 'permission.news.action.view',
        'NewsController/add' => 'permission.news.action.add',
        'NewsController/edit' => 'permission.news.action.edit',
        // 'NewsController/save' => 'Action Lưu dữ liệu',
        'NewsController/delete' => 'permission.news.action.delete',
    );

    // Group quyền dịch vụ
    private $ServiceController = array(
        // 'ServiceController' => 'Quản lý toàn quyền (Dịch vụ)',
        'ServiceController/index' => 'permission.service.action.view',
        'ServiceController/add' => 'permission.service.action.add',
        'ServiceController/edit' => 'permission.service.action.edit',
        // 'ServiceController/save' => 'Action Lưu dữ liệu',
        'ServiceController/delete' => 'permission.service.action.delete',
    );

    // Group quyền dự án
    private $ProjectController = array(
        // 'ProjectController' => 'Quản lý toàn quyền (dự án)',
        'ProjectController/index' => 'permission.project.action.view',
        'ProjectController/add' => 'permission.service.action.add',
        // 'ProjectController/addAction' => 'Action Tạo mới dự án',
        'ProjectController/edit' => 'permission.service.action.edit',
        // 'ProjectController/editAction' => 'Action xử lý sửa thông tin 1 dự án',
        // 'ProjectController/editManyAction' => 'Action xử lý sửa nhiều dự án',
        'ProjectController/delete' => 'permission.service.action.delete',
        // 'ProjectController/deleteMany' => 'Xóa nhiều dự án',
    );

    // Group quyền đối tác
    private $PartnerController = array(
        // 'PartnerController' => 'Quản lý toàn quyền (Tin đối tác)',
        'PartnerController/index' => 'permission.partner.action.view',
        'PartnerController/add' => 'permission.partner.action.add',
        'PartnerController/edit' => 'permission.partner.action.edit',
        // 'PartnerController/save' => 'Action Lưu dữ liệu',
        'PartnerController/delete' => 'permission.partner.action.delete',
    );

    // Group quyền tuyển dụng
    private $RecruitmentController = array(
        // 'RecruitmentController' => 'Quản lý toàn quyền (Tin tuyển dụng)',
        'RecruitmentController/index' => 'permission.recruitment.action.view',
        'RecruitmentController/add' => 'permission.recruitment.action.add',
        'RecruitmentController/edit' => 'permission.recruitment.action.edit',
        // 'RecruitmentController/save' => 'Action Lưu dữ liệu',
        'RecruitmentController/delete' => 'permission.recruitment.action.delete',
    );


    // Group quyền khách sạn
    private $HostController = array(
        // 'HostController' => 'Quản lý toàn quyền (Khách sạn)',
        'HostController/index' => 'permission.host.action.view',
        'HostController/add' => 'permission.host.action.add',
        // 'HostController/addAction' => 'Action Tạo mới khách sạn',
        'HostController/edit' => 'permission.host.action.edit',
        // 'HostController/editAction' => 'Action xử lý sửa thông tin 1 khách sạn',
        // 'HostController/editManyAction' => 'Action xử lý sửa nhiều khách sạn',
        'HostController/delete' => 'permission.host.action.delete',
        // 'HostController/deleteMany' => 'Xóa nhiều khách sạn',
    );

    // Group quyền phòng
    private $RoomController = array(
        // 'RoomController' => 'Quản lý toàn quyền (Phòng)',
        'RoomController/index' => 'permission.room.action.view',
        'RoomController/add' => 'permission.room.action.add',
        // 'RoomController/addAction' => 'Action Tạo mới phòng',
        'RoomController/edit' => 'permission.room.action.edit',
        // 'RoomController/editAction' => 'Action xử lý sửa thông tin 1 phòng',
        // 'RoomController/editManyAction' => 'Action xử lý sửa nhiều phòng',
        'RoomController/delete' => 'permission.room.action.delete',
        // 'RoomController/deleteMany' => 'Xóa nhiều phòng',
    );

    // Group quyền bình luận
    private $CommentController = array(
        // 'CommentController' => 'Quản lý toàn quyền (Bình luận)',
        'CommentController/index' => 'permission.comment.action.view',
        'CommentController/add' => 'permission.comment.action.add',
        // 'CommentController/addAction' => 'Action Tạo mới bình luận',
        'CommentController/edit' => 'permission.comment.action.edit',
        // 'CommentController/editAction' => 'Action xử lý sửa thông tin 1 bình luận',
        // 'CommentController/editManyAction' => 'Action xử lý sửa nhiều bình luận',
        'CommentController/delete' => 'permission.comment.action.delete',
        // 'CommentController/deleteMany' => 'Xóa nhiều bình luận',
    );

    // Group quyền đánh giá
    private $ReviewController = array(
        // 'ReviewController' => 'Quản lý toàn quyền (Đánh giá)',
        'ReviewController/index' => 'permission.review.action.view',
        'ReviewController/add' => 'permission.review.action.add',
        // 'ReviewController/addAction' => 'Action Tạo mới đánh giá',
        'ReviewController/edit' => 'permission.review.action.edit',
        // 'ReviewController/editAction' => 'Action xử lý sửa thông tin 1 đánh giá',
        // 'ReviewController/editManyAction' => 'Action xử lý sửa nhiều đánh giá',
        'ReviewController/delete' => 'permission.review.action.delete',
        // 'ReviewController/deleteMany' => 'Xóa nhiều đánh giá',
    );

    // Group banner
    private $BannersController = array(
        // 'BannersController' => 'Quản lý toàn quyền (Banner)',
        'BannersController/index' => 'permission.banner.action.view',
        'BannersController/add' => 'permission.banner.action.add',
        // 'BannersController/addAction' => 'Action Tạo mới banner',
        'BannersController/edit' => 'permission.banner.action.edit',
        // 'BannersController/editAction' => 'Action xử lý sửa thông tin 1 banner',
        // 'BannersController/editManyAction' => 'Action xử lý sửa nhiều banner',
        'BannersController/delete' => 'permission.banner.action.delete',
        // 'BannersController/deleteMany' => 'Xóa nhiều banner',
    );

    // Group brand
    private $BrandsController = array(
        // 'BrandsController' => 'Quản lý toàn quyền (Brand)',
        'BrandsController/index' => 'permission.brand.action.view',
        'BrandsController/add' => 'permission.brand.action.add',
        // 'BrandsController/addAction' => 'Action Tạo mới brand',
        'BrandsController/edit' => 'permission.brand.action.edit',
        // 'BrandsController/editAction' => 'Action xử lý sửa thông tin 1 brand',
        // 'BrandsController/editManyAction' => 'Action xử lý sửa nhiều brand',
        'BrandsController/delete' => 'permission.brand.action.delete',
        // 'BrandsController/deleteMany' => 'Xóa nhiều brand',
    );

    // Group products
    private $ProductController = array(
        // 'ProductController' => 'Quản lý toàn quyền (Products)',
        'ProductController/index' => 'permission.product.action.view',
        'ProductController/add' => 'permission.product.action.add',
        // 'ProductController/addAction' => 'Action Tạo mới product',
        'ProductController/edit' => 'permission.product.action.edit',
        // 'ProductController/editAction' => 'Action xử lý sửa thông tin 1 product',
        // 'ProductController/editManyAction' => 'Action xử lý sửa nhiều product',
        'ProductController/delete' => 'permission.product.action.delete',
        // 'ProductController/deleteMany' => 'Xóa nhiều product',
    );

    // // Group quyền top deal
    // private $TopDealsController = array(
    //     'TopDealsController' => 'permission.brand.action.edit',
    //     'TopDealsController/index' => 'Danh sách top deals',
    //     'TopDealsController/add' => 'Tạo mới top deals',
    //     'TopDealsController/addAction' => 'Action Tạo mới top deals',
    //     'TopDealsController/detail' => 'Chi tiết 1 top deals',
    //     'TopDealsController/edit' => 'Sửa thông tin 1 top deals',
    //     'TopDealsController/editAction' => 'Action xử lý sửa thông tin 1 top deals',
    //     'TopDealsController/editManyAction' => 'Action xử lý sửa nhiều top deals',
    //     'TopDealsController/delete' => 'Xóa 1 top deals',
    //     'TopDealsController/deleteMany' => 'Xóa nhiều top deals',
    // );

    // Group quyền top deal
    private $SubcribeEmailsController = array(
        // 'SubcribeEmailsController' => 'Quản lý toàn quyền (Subcribe Email)',
        'SubcribeEmailsController/index' => 'permission.subcribe_email.action.view',
        'SubcribeEmailsController/edit' => 'permission.subcribe_email.action.edit',
        // 'SubcribeEmailsController/editAction' => 'Action xử lý sửa thông tin 1 subcribe email',
        'SubcribeEmailsController/delete' => 'permission.subcribe_email.action.delete',
    );

    // Group thư viện
    private $GalleryController = array(
        'GalleryController/index' => 'permission.media.action.view',
        'GalleryController/add' => 'permission.media.action.add',
        'GalleryController/edit' => 'permission.media.action.edit',
        'GalleryController/delete' => 'permission.media.action.delete',
        // 'GalleryController/index' => 'Quản lý thư viện',
    );

    // Group thư viện
    private $MultiLanguageController = array(
        'MultiLanguageController' => 'permission.language.action.view',
        // 'MultiLanguageController/index' => 'Quản lý đa ngôn ngữ',
    );
    private $MenusController = array(
        'MenusController' => 'permission.menu.action.view',
        // 'MenusController/index' => 'Quản lý Menu',
    );

    private $CustomCssController = array(
        'CustomCssController' => 'permission.custom_css.action.edit',
        // 'CustomCssController/index' => 'Tùy biến Css',
        // 'CustomCssController/editAction' => 'Action Tùy biến Css',
    );

    private $ThemeOptionsController = array(
        'ThemeOptionsController' => 'permission.theme.action.view',
        // 'ThemeOptionsController/index' => 'Tùy biến',
        // 'ThemeOptionsController/editAction' => 'Action Tùy biến',
    );
    /*
     * construct
     */

    public function __construct(Request $request, $requireRoot = false)
    {
        $this->CI = new \stdClass();

        $this->request = $request;
        $this->requireRoot = $requireRoot;

        //current permission
        $this->authobj = json_decode(Auth::user()->permission, true);
        $this->authobj = is_array($this->authobj) ? $this->authobj : array();
    }

    function check()
    {
        //is root
        if (Auth::user()->is_root == 1) {

            return true;
        }
        //only root
        if ($this->requireRoot && Auth::user()->is_root != 1) {
            return false;
        }
        //lấy controller - function hiện tại qua router
        $currentAction = Route::currentRouteAction();
        list($controllers, $method) = explode('@', $currentAction);
        // $controller now is "App\Http\Controllers\FooBarController"
        $controller = preg_replace('/.*\\\/', '', $controllers);
        if ($controller === 'WelcomeController') {
            return true;
        }
        //tên controller/function
        $function = $controller . '/' . $method;
        //full access controller
        if (in_array($controller, $this->authobj)) {
            return true;
        }
        //can access
        if (in_array($function, $this->authobj)) { //|| in_array($method, $this->ignores)) {
            return true;
        }
        //no permission
        return false;
    }

    /*
     * get list controller
     */

    function listController()
    {
        $this->controllers = array(
            'MyProfileController' => $this->MyProfileController,
            'UsersController' => $this->UsersController,
            'AgentsController' => $this->AgentsController,
            'GuestsController' => $this->GuestsController,
            'UserGroupController' => $this->UserGroupController,
            'LogsUserController' => $this->LogsUserController,
            'NewsController' => $this->NewsController,
            'ServiceController' => $this->ServiceController,
            'ProjectController' => $this->ProjectController,
            'PartnerController' => $this->PartnerController,
            'RecruitmentController' => $this->PartnerController,
            'HostController' => $this->HostController,
            'RoomController' => $this->RoomController,
            'CommentController' => $this->CommentController,
            'ReviewController' => $this->ReviewController,
            'BannersController' => $this->BannersController,
            'BrandsController' => $this->BrandsController,
            'ProductController' => $this->ProductController,
            // 'TopDealsController' => $this->TopDealsController,
            'SubcribeEmailsController' => $this->SubcribeEmailsController,
            'MenusController' => $this->MenusController,
            'GalleryController' => $this->GalleryController,
            'MultiLanguageController' => $this->MultiLanguageController,
            'ThemeOptionsController' => $this->ThemeOptionsController,
            'CustomCssController' => $this->CustomCssController,
        );
        return $this->controllers;
    }

    /*
     * get list controller
     */

    function listControllerWithTitle()
    {
        $this->controllers = array(
            'MyProfileController' => [
                'title' => __('permission.profile.title'),
                'controller' => $this->MyProfileController,
            ],
            'UsersController' => [
                'title' => __('permission.user.title'),
                'controller' => $this->UsersController,
            ],
            'AgentsController' => [
                'title' => __('permission.agent.title'),
                'controller' => $this->AgentsController,
            ],
            'GuestsController' => [
                'title' => __('permission.guest.title'),
                'controller' => $this->GuestsController,
            ],
            'UserGroupController' => [
                'title' => __('permission.group.title'),
                'controller' => $this->UserGroupController,
            ],
            'LogsUserController' => [
                'title' => __('permission.logs.title'),
                'controller' => $this->LogsUserController,
            ],
            'NewsController' => [
                'title' => __('permission.news.title'),
                'controller' => $this->NewsController,
            ],

            'ServiceController' => [
                'title' => __('permission.service.title'),
                'controller' => $this->ServiceController,
            ],

            'ProjectController' => [
                'title' => __('permission.project.title'),
                'controller' => $this->ProjectController,
            ],

            'PartnerController' => [
                'title' => __('permission.partner.title'),
                'controller' => $this->PartnerController,
            ],

            'RecruitmentController' => [
                'title' => __('permission.recruiment.title'),
                'controller' => $this->RecruitmentController,
            ],

            'HostController' => [
                'title' => __('permission.host.title'),
                'controller' => $this->HostController,
            ],

            'RoomController' => [
                'title' => __('permission.room.title'),
                'controller' => $this->RoomController,
            ],

            'CommentController' => [
                'title' => __('permission.comment.title'),
                'controller' => $this->CommentController,
            ],

            'ReviewController' => [
                'title' => __('permission.review.title'),
                'controller' => $this->ReviewController,
            ],

            'BannersController' => [
                'title' => __('permission.banner.title'),
                'controller' => $this->BannersController,
            ],

            'BrandsController' => [
                'title' => __('permission.brand.title'),
                'controller' => $this->BrandsController,
            ],

            'ProductController' => [
                'title' => __('permission.product.title'),
                'controller' => $this->ProductController,
            ],

            'SubcribeEmailsController' => [
                'title' => __('permission.subcribe_email.title'),
                'controller' => $this->SubcribeEmailsController,
            ],

            'MenusController' => [
                'title' => __('permission.menu.title'),
                'controller' => $this->MenusController,
            ],

            'GalleryController' => [
                'title' => __('permission.media.title'),
                'controller' => $this->GalleryController,
            ],

            'MultiLanguageController' => [
                'title' => __('permission.language.title'),
                'controller' => $this->MultiLanguageController,
            ],

            'ThemeOptionsController' => [
                'title' => __('permission.theme.title'),
                'controller' => $this->ThemeOptionsController,
            ],

            'CustomCssController' => [
                'title' => __('permission.custom_css.title'),
                'controller' => $this->CustomCssController,
            ],
        );
        return $this->controllers;
    }

    /*
     * return admin
     */

    public function isAdmin()
    {
        if ($this->admin == 'yes') {
            return true;
        } else {
            return false;
        }
    }

    /*
     * check function show in header
     */

    function checkHeader($controller)
    {
        if (Auth::user()->is_root == 1) {
            return true;
        }
        foreach ($this->authobj as $permission) {
            if (strpos($controller, $permission) === 0) {
                return true;
            }
        }
        return false;
    }

    public function isHeader($controller)
    {
        if (Auth::user()->is_root == 1) {
            return true;
        }
        if (in_array($controller, $this->authobj)) {
            return true;
        } else {
            return false;
        }
    }

    public function getListPermission($encode = true)
    {
        $permissions = [];
        foreach ($this->listController() as $key => $value) {
            //neu co 1 chuc nang dc lua chon
            if ($this->request->input($key, '')) {
                $permission = $this->request->input($key, '');
                //neu la toan quyen
                if ($permission[0] == $key) {
                    $permissions[] = $key;
                } else {
                    foreach ($permission as $x) {
                        $permissions[] = $x;
                    }
                }
            }
        }

        return $encode ? json_encode($permissions) : $permissions;
    }

    public function getListPermissionString($permissions = [])
    {
        $permString = [];
        foreach ($permissions as $key => $perm) {
            $arr = explode('/', $perm);
            $permString[$arr[0] ?? 'Other'][] = $arr[1] ?? "";
        }


        $permArray = [];
        $listController = $this->listControllerWithTitle();
        foreach ($permString as $key => $value) {
            $title = $listController[$key]["title"];
            $actions = [];
            if ($value) {
                foreach ($value as $action) {
                    if ($action != "") {
                        if ($listController[$key]["controller"]["$key/$action"]) {
                            $actions[] = __($listController[$key]["controller"]["$key/$action"]);
                        }
                    } else {
                        if ($listController[$key]["controller"]["$key"]) {
                            $actions[] = __($listController[$key]["controller"]["$key"]);
                        }
                    }
                }
            }
            $permArray[] = implode(", ", $actions) . " " . $title;
        }

        return $permArray;
    }
}
