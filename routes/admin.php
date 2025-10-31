<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

use App\Http\Controllers\Admin\CMS\MyProfileController;
use App\Http\Controllers\Admin\CMS\PageController;
use App\Http\Controllers\Admin\CMS\NewsController;
use App\Http\Controllers\Admin\CMS\SubcribeEmailsController;
use App\Http\Controllers\Admin\CMS\ProjectController;
use App\Http\Controllers\Admin\CMS\PartnerController;
use App\Http\Controllers\Admin\CMS\RecruitmentController;
use App\Http\Controllers\Admin\CMS\VideoController;
use App\Http\Controllers\Admin\CMS\ImageController;
use App\Http\Controllers\Admin\CMS\BrandsController;
use App\Http\Controllers\Admin\CMS\CommentController;
use App\Http\Controllers\Admin\CMS\CategoryController;
use App\Http\Controllers\Admin\CMS\ProductController; // Still used for legacy file manager route
use App\Http\Controllers\Admin\CMS\RoleController;
use App\Http\Controllers\Admin\CMS\PermissionController;
use App\Http\Controllers\Admin\CMS\LogsUserController;
use App\Http\Controllers\Admin\CMS\ThemeOptionsController;
use App\Http\Controllers\Admin\CMS\CustomCssController;
use App\Http\Controllers\Admin\CMS\TemplateController;
use App\Http\Controllers\Admin\CMS\ContactController;
use App\Http\Controllers\Admin\CMS\SettingController;
use App\Http\Controllers\Admin\CMS\GalleryController;
use App\Http\Controllers\Admin\CMS\AuditLogController;
use App\Http\Controllers\Admin\CMS\ReportsController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\Shopee\ShopeeOAuthController;
use App\Http\Controllers\Admin\Shopee\ShopeeProductController;
use App\Http\Controllers\Admin\Shopee\ShopeeSyncController;


if (!defined('FM_USE_ACCESS_KEYS')) {
    define('FM_USE_ACCESS_KEYS', true); // TRUE or FALSE
}


if (!defined('FM_DEBUG_ERROR_MESSAGE')) {
    define('FM_DEBUG_ERROR_MESSAGE', false); // TRUE or FALSE
}


// Denied Permission Page
Route::get('denied-permission', function () {
    return view('admin.pages.permission_denied');
});
/*
// COMMENTED OUT - Old tenant auth routes (moved to web.php with subdomain routing)
// Tenant Auth Routes (with subdomain middleware)
Route::middleware(['subdomain.tenant'])->group(function () {
    // Tenant login routes (renamed to avoid conflict with platform)
    Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('tenant.admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('tenant.admin.login.action');
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('tenant.admin.logout');
    Route::get('/admin/auth/check', [AuthController::class, 'checkAuth'])->name('admin.auth.check');
    // Admin dashboard (protected)
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin/dashboard', [AuthController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin', [AuthController::class, 'dashboard'])->name('admin.home');
    });
});
*/

/*
// COMMENTED OUT - Legacy login routes (moved to domain-specific routing)
// Legacy login routes (for main domain)
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'loginAction'])->name('login.action');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/reset_password', [LoginController::class, 'resetPassword'])->name('resetPassword');
Route::get('/active-user', [LoginController::class, 'activeUser'])->name('activeUser');
Route::get('/active-agent', [LoginController::class, 'activeAgent'])->name('activeAgent');
Route::get('/kich-hoat-tai-khoan', [LoginController::class, 'activeGuest'])->name('activeGuest');
*/
// Wrap admin routes in domain constraint to avoid conflicts with tenant routes
Route::domain('yukimart.local')->group(function () {
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {


        // TODO: Create DashboardController
    /*
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/revenue-data', [DashboardController::class, 'getRevenueData'])->name('dashboard.revenue-data');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/revenue-chart', [DashboardController::class, 'getRevenueChart'])->name('dashboard.revenue-chart');
    Route::get('/dashboard/top-products', [\App\Http\Controllers\Api\V1\DashboardController::class, 'getTopProducts'])->name('dashboard.top-products');
    Route::get('/dashboard/top-products-data', [DashboardController::class, 'getTopProductsData'])->name('dashboard.top-products-data');
    */
    
   

    // my profile
    Route::get('/my-profile', [MyProfileController::class, 'profile'])->name('profile');
    Route::post('/my-profile', [MyProfileController::class, 'updateProfile'])->name('profile.update');

    // change password
    Route::get('/change-password', [MyProfileController::class, 'changePassword'])->name('profile.changePassword');
    Route::put('/change-password', [MyProfileController::class, 'changePasswordAction'])->name('profile.changePasswordAction');

    Route::namespace('CMS')->group(function () {
        // Page
        Route::get('/page', [PageController::class, 'index'])->name('page.list');
        Route::get('/page/add', [PageController::class, 'add'])->name('page.add');
        Route::post('/page/add', [PageController::class, 'addAction'])->name('page.add.action');
        Route::get('/page/edit/{page_id}', [PageController::class, 'edit'])->name('page.edit');
        Route::post('/page/edit/{page_id}', [PageController::class, 'editAction'])->name('page.edit.action');
        Route::post('/page/edit', [PageController::class, 'editManyAction'])->name('page.edit.many.action');
        Route::get('/page/delete/{page_id}', [PageController::class, 'delete'])->name('page.delete');
        Route::post('/page/delete', [PageController::class, 'deletemany'])->name('page.delete.many');
        Route::post('/page', [PageController::class, 'ajaxGetList'])->name('page.ajax.getList');

        // News
        Route::get('/news', [NewsController::class, 'index'])->name('news.list');
        Route::get('/news/add', [NewsController::class, 'add'])->name('news.add');
        Route::post('/news/add', [NewsController::class, 'addAction'])->name('news.add.action');
        Route::get('/news/edit/{news_id}', [NewsController::class, 'edit'])->name('news.edit');
        Route::post('/news/edit/{news_id}', [NewsController::class, 'editAction'])->name('news.edit.action');
        Route::post('/news/edit', [NewsController::class, 'editManyAction'])->name('news.edit.many.action');
        Route::get('/news/delete/{news_id}', [NewsController::class, 'delete'])->name('news.delete');
        Route::post('/news/delete', [NewsController::class, 'deletemany'])->name('news.delete.many');
        Route::post('/news', [NewsController::class, 'ajaxGetList'])->name('news.ajax.getList');

        // subcribe Emails
        Route::get('/subcribe-emails', [SubcribeEmailsController::class, 'index'])->name('subcribe_email.list');
        Route::get('/subcribe-emails/edit/{id}', [SubcribeEmailsController::class, 'edit'])->name('subcribe_email.edit');
        Route::post('/subcribe-emails/edit/{id}', [SubcribeEmailsController::class, 'editAction'])->name('subcribe_email.edit.action');
        Route::get('/subcribe-emails/delete/{id}', [SubcribeEmailsController::class, 'delete'])->name('subcribe_email.delete');
        Route::get('/subcribe-emails/ajax/get-list', [SubcribeEmailsController::class, 'ajaxGetList'])->name('subcribe_email.ajax.getList');

        // Project
        Route::get('/project', [ProjectController::class, 'index'])->name('project.list');
        Route::get('/project/add', [ProjectController::class, 'add'])->name('project.add');
        Route::post('/project/add', [ProjectController::class, 'addAction'])->name('project.add.action');
        Route::get('/project/edit/{project_id}', [ProjectController::class, 'edit'])->name('project.edit');
        Route::post('/project/edit/{edit_id}', [ProjectController::class, 'editAction'])->name('project.edit.action');
        Route::post('/project/edit', [ProjectController::class, 'editManyAction'])->name('project.edit.many.action');
        Route::get('/project/delete/{comment_id}', [ProjectController::class, 'delete'])->name('project.delete');
        Route::post('/project/delete', [ProjectController::class, 'deletemany'])->name('project.delete.many');
        Route::post('/project', [ProjectController::class, 'ajaxGetList'])->name('project.ajax.getList');

        // Partner
        Route::get('/partner', [PartnerController::class, 'index'])->name('partner.list');
        Route::get('/partner/add', [PartnerController::class, 'add'])->name('partner.add');
        Route::get('/partner/edit/{news_id}', [PartnerController::class, 'add'])->name('partner.edit');
        Route::post('/partner/edit', [PartnerController::class, 'editManyAction'])->name('partner.edit.many.action');
        Route::get('/partner/delete/{news_id}', [PartnerController::class, 'delete'])->name('partner.delete');
        Route::post('/partner/delete', [PartnerController::class, 'deletemany'])->name('partner.delete.many');
        Route::post('/partner/save', [PartnerController::class, 'save'])->name('partner.save');
        Route::get('/partner/ajax/get-list', [PartnerController::class, 'ajaxGetList'])->name('partner.ajax.getList');

        // Recruitment
        Route::get('/recruitment', [RecruitmentController::class, 'index'])->name('recruitment.list');
        Route::get('/recruitment/add', [RecruitmentController::class, 'add'])->name('recruitment.add');
        Route::get('/recruitment/edit/{news_id}', [RecruitmentController::class, 'add'])->name('recruitment.edit');
        Route::post('/recruitment/edit', [RecruitmentController::class, 'editManyAction'])->name('recruitment.edit.many.action');
        Route::get('/recruitment/delete/{news_id}', [RecruitmentController::class, 'delete'])->name('recruitment.delete');
        Route::post('/recruitment/delete', [RecruitmentController::class, 'deletemany'])->name('recruitment.delete.many');
        Route::post('/recruitment/save', [RecruitmentController::class, 'save'])->name('recruitment.save');
        Route::get('/recruitment/ajax/get-list', [RecruitmentController::class, 'ajaxGetList'])->name('recruitment.ajax.getList');

        // Video Gallery
        Route::get('/video', [VideoController::class, 'index'])->name('video.list');
        Route::get('/video/add', [VideoController::class, 'add'])->name('video.add');
        Route::get('/video/edit/{news_id}', [VideoController::class, 'add'])->name('video.edit');
        Route::post('/video/edit', [VideoController::class, 'editManyAction'])->name('video.edit.many.action');
        Route::get('/video/delete/{news_id}', [VideoController::class, 'delete'])->name('video.delete');
        Route::post('/video/delete', [VideoController::class, 'deletemany'])->name('video.delete.many');
        Route::post('/video/save', [VideoController::class, 'save'])->name('video.save');
        Route::get('/video/ajax/get-list', [VideoController::class, 'ajaxGetList'])->name('video.ajax.getList');

        // Album Image
        Route::get('/album', [ImageController::class, 'index'])->name('album.list');
        Route::get('/album/add', [ImageController::class, 'add'])->name('album.add');
        Route::get('/album/edit/{id}', [ImageController::class, 'add'])->name('album.edit');
        Route::post('/album/edit', [ImageController::class, 'editManyAction'])->name('album.edit.many.action');
        Route::get('/album/delete/{id}', [ImageController::class, 'delete'])->name('album.delete');
        Route::post('/album/delete', [ImageController::class, 'deletemany'])->name('album.delete.many');
        Route::post('/album/save', [ImageController::class, 'save'])->name('album.save');
        Route::get('/album/ajax/get-list', [ImageController::class, 'ajaxGetList'])->name('album.ajax.getList');

        // Brand
        Route::get('/brand', [BrandsController::class, 'index'])->name('brand.list');
        Route::get('/brand/add', [BrandsController::class, 'add'])->name('brand.add');
        Route::post('/brand/add', [BrandsController::class, 'addAction'])->name('brand.add.action');
        Route::get('/brand/edit/{edit_id}', [BrandsController::class, 'edit'])->name('brand.edit');
        Route::post('/brand/edit/{edit_id}', [BrandsController::class, 'editAction'])->name('brand.edit.action');
        Route::post('/brand/edit', [BrandsController::class, 'editManyAction'])->name('brand.edit.many.action');
        Route::get('/brand/delete/{comment_id}', [BrandsController::class, 'delete'])->name('brand.delete');
        Route::post('/brand/delete', [BrandsController::class, 'deletemany'])->name('brand.delete.many');
        Route::get('/brand/ajax/get-list', [BrandsController::class, 'ajaxGetList'])->name('brand.ajax.getList');

        // Products - MOVED TO tenant.php

        // Inventory Management - MOVED TO tenant.php










        // Warehouses Management - MOVED TO tenant.php

        // Notifications Management - MOVED TO tenant.php

        // Notification Settings
        Route::prefix('notification-settings')->name('notification-settings.')->group(function () {
            Route::get('/', [NotificationSettingController::class, 'index'])->name('index');
            Route::post('/update', [NotificationSettingController::class, 'update'])->name('update');
            Route::post('/reset', [NotificationSettingController::class, 'reset'])->name('reset');
            Route::get('/api/settings', [NotificationSettingController::class, 'getSettings'])->name('api.settings');
            Route::post('/test', [NotificationSettingController::class, 'test'])->name('test');
        });

        // Inventory Import/Export - MOVED TO tenant.php

        // Reports and Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('/sales', [ReportsController::class, 'getSalesReport'])->name('sales');
            Route::get('/inventory', [ReportsController::class, 'getInventoryReport'])->name('inventory');
            Route::get('/product-performance', [ReportsController::class, 'getProductPerformanceReport'])->name('product-performance');
            Route::get('/customer-analytics', [ReportsController::class, 'getCustomerAnalytics'])->name('customer-analytics');
            Route::get('/financial-summary', [ReportsController::class, 'getFinancialSummary'])->name('financial-summary');
            Route::get('/dashboard-analytics', [ReportsController::class, 'getDashboardAnalytics'])->name('dashboard-analytics');
            Route::post('/export', [ReportsController::class, 'exportReport'])->name('export');
            Route::get('/filters', [ReportsController::class, 'getFilters'])->name('filters');
        });

        // Product Categories - MOVED TO tenant.php

        // Backup and Restore
        Route::prefix('backup')->name('backup.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::post('/manual', [BackupController::class, 'createManual'])->name('manual');
            Route::get('/progress/{id}', [BackupController::class, 'getProgress'])->name('progress');
            Route::get('/download/{id}', [BackupController::class, 'download'])->name('download');
            Route::delete('/{id}', [BackupController::class, 'delete'])->name('delete');
            Route::post('/restore/{id}', [BackupController::class, 'restore'])->name('restore');
            Route::post('/schedule', [BackupController::class, 'createSchedule'])->name('schedule.create');
            Route::patch('/schedule/{id}/toggle', [BackupController::class, 'toggleSchedule'])->name('schedule.toggle');
            Route::delete('/schedule/{id}', [BackupController::class, 'deleteSchedule'])->name('schedule.delete');
            Route::get('/stats', [BackupController::class, 'getStats'])->name('stats');
        });

        // User Settings (Legacy - removed to avoid conflicts)

        // Language switching routes
        Route::get('/change-language/{locale}', function ($locale) {
            if (in_array($locale, config('app.supported_locales', ['vi', 'en']))) {
                // Set session locale
                session(['locale' => $locale]);

                // Save to user settings if authenticated
                if (Auth::check()) {
                    \App\Models\UserSetting::updateOrCreate(
                        ['user_id' => Auth::id(), 'key' => 'language'],
                        ['value' => $locale, 'type' => 'string']
                    );
                }

                // Set application locale
                App::setLocale($locale);
            }

            return redirect()->back();
        })->name('change-language');

        // Audit Logs
        Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/data', [AuditLogController::class, 'getData'])->name('data');
            Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
            Route::get('/statistics/summary', [AuditLogController::class, 'getStatistics'])->name('statistics');
            Route::post('/export', [AuditLogController::class, 'export'])->name('export');
            Route::post('/cleanup', [AuditLogController::class, 'cleanup'])->name('cleanup');
            Route::get('/filters/list', [AuditLogController::class, 'getFilters'])->name('filters');
        });

        // Customers - MOVED TO tenant.php









        // Custom File Manager Routes - MOVED TO tenant.php

        // Legacy file manager route (for backward compatibility)
        Route::get('/file-manager', [ProductController::class, 'fileManager'])->name('file.manager');

        // Comment
        Route::get('/comment', [CommentController::class, 'index'])->name('comment.list');
        Route::get('/comment/add', [CommentController::class, 'add'])->name('comment.add');
        Route::post('/comment/add', [CommentController::class, 'addAction'])->name('comment.add.action');
        Route::get('/comment/edit/{comment_id}', [CommentController::class, 'edit'])->name('comment.edit');
        Route::post('/comment/edit/{comment_id}', [CommentController::class, 'editAction'])->name('comment.edit.action');
        Route::post('/comment/edit', [CommentController::class, 'editManyAction'])->name('comment.edit.many.action');
        Route::get('/comment/delete/{comment_id}', [CommentController::class, 'delete'])->name('comment.delete');
        Route::post('/comment/delete', [CommentController::class, 'deletemany'])->name('comment.delete.many');
        Route::get('/comment/ajax/get-list', [CommentController::class, 'ajaxGetList'])->name('comment.ajax.getList');

        // Category
        Route::get('/category', [CategoryController::class, 'index'])->name('category.list');
        // Route::post('/category/save', [CategoryController::class, 'save'])->name('category.save');
        Route::post('/category/add', [CategoryController::class, 'addAction'])->name('category.add');
        Route::get('/category/edit/{category_id}', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/category/edit', [CategoryController::class, 'editManyAction'])->name('category.edit.many.action');
        Route::post('/category/edit/{category_id}', [CategoryController::class, 'editAction'])->name('category.edit.action');
        Route::get('/category/delete/{category_id}', [CategoryController::class, 'delete'])->name('category.delete');
        Route::post('/category/delete', [CategoryController::class, 'deleteMany'])->name('category.deleteMany');
        Route::post('/category', [CategoryController::class, 'ajaxGetList'])->name('category.ajax.getList');

        // Users Management - MOVED TO tenant.php

        // // Agent
        // Route::get('/agent', [AgentsController::class, 'index'])->name('agent.list');
        // Route::get('/agent/add', [AgentsController::class, 'add'])->name('agent.add');
        // Route::post('/agent/add', [AgentsController::class, 'addAction'])->name('agent.add.action');
        // Route::get('/agent/detail/{agent_id}', [AgentsController::class, 'detail'])->name('agent.detail');
        // Route::get('/agent/edit/{agent_id}', [AgentsController::class, 'edit'])->name('agent.edit');
        // Route::post('/agent/edit', [AgentsController::class, 'editManyAction'])->name('agent.edit.many.action');
        // Route::post('/agent/edit/{agent_id}', [AgentsController::class, 'editAction'])->name('agent.edit.action');
        // Route::get('/agent/delete', [AgentsController::class, 'deleteMany'])->name('agent.delete.many');
        // Route::get('/agent/delete/{agent_id}', [AgentsController::class, 'delete'])->name('agent.delete');
        // // Route::post('/agent/delete', [AgentsController::class, 'delete'])->name('agent.delete');
        // Route::get('/agent/ajax/get-list', [AgentsController::class, 'ajaxGetList'])->name('agent.ajax.getList');

        //User
        // Route::get('/guest', [GuestsController::class, 'index'])->name('guest.list');
        // Route::get('/guest/add', [GuestsController::class, 'add'])->name('guest.add');
        // Route::post('/guest/add', [GuestsController::class, 'addAction'])->name('guest.add.action');
        // Route::get('/guest/detail/{guest_id}', [GuestsController::class, 'detail'])->name('guest.detail');
        // Route::get('/guest/edit/{guest_id}', [GuestsController::class, 'edit'])->name('guest.edit');
        // Route::post('/guest/edit', [GuestsController::class, 'editManyAction'])->name('guest.edit.many.action');
        // Route::post('/guest/edit/{guest_id}', [GuestsController::class, 'editAction'])->name('guest.edit.action');
        // Route::get('/guest/delete', [GuestsController::class, 'deleteMany'])->name('guest.delete.many');
        // Route::get('/guest/delete/{guest_id}', [GuestsController::class, 'delete'])->name('guest.delete');
        // Route::post('/guest/delete', [GuestsController::class, 'delete'])->name('guest.delete');
        // Route::get('/guest/ajax/get-list', [GuestsController::class, 'ajaxGetList'])->name('guest.ajax.getList');

        // Roles Management
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index')
                ->middleware('permission:settings.roles.read');
            Route::get('/create', [RoleController::class, 'create'])->name('create')
                ->middleware('permission:settings.roles.create');
            Route::post('/', [RoleController::class, 'store'])->name('store')
                ->middleware('permission:settings.roles.create');
            Route::get('/{id}', [RoleController::class, 'show'])->name('show')
                ->middleware('permission:settings.roles.read');
            Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit')
                ->middleware('permission:settings.roles.update');
            Route::put('/{id}', [RoleController::class, 'update'])->name('update')
                ->middleware('permission:settings.roles.update');
            Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy')
                ->middleware('permission:settings.roles.delete');
            Route::post('/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->name('toggle-status')
                ->middleware('permission:settings.roles.update');
            Route::get('/{id}/permissions', [RoleController::class, 'getPermissions'])->name('permissions')
                ->middleware('permission:settings.roles.read');
            Route::post('/bulk-delete', [RoleController::class, 'bulkDelete'])->name('bulk-delete')
                ->middleware('permission:settings.roles.delete');
        });

        // Permissions Management
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/data', [PermissionController::class, 'getData'])->name('data');
            Route::get('/create', [PermissionController::class, 'create'])->name('create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [PermissionController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/by-module', [PermissionController::class, 'getByModule'])->name('by-module');
            Route::post('/bulk-delete', [PermissionController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/generate-for-module', [PermissionController::class, 'generateForModule'])->name('generate-for-module');
        });

        // Logs User
        Route::get('/logs-user', [LogsUserController::class, 'index'])->name('logs_user.list');
        Route::get('/logs-user/detail/{id}', [LogsUserController::class, 'detail'])->name('logs_user.detail');
        Route::get('/logs-user/ajax/get-list', [LogsUserController::class, 'ajaxGetList'])->name('logs_user.ajax.getList');

        // Menu
        Route::get('/menus', [LogsUserController::class, 'index'])->name('menu.index');

        // Custom Css
        Route::get('/theme-option', [ThemeOptionsController::class, 'option'])->name('theme_option.index');
        Route::post('/theme-option', [ThemeOptionsController::class, 'optionAction'])->name('theme_option.action');

        // Custom Css
        Route::get('/custom-css', [CustomCssController::class, 'index'])->name('custom_css.index');
        Route::post('/custom-css', [CustomCssController::class, 'editAction'])->name('custom_css.editAction');

        // Custom Template
        Route::get('/template', [TemplateController::class, 'index'])->name('template.index');
        Route::post('/template', [TemplateController::class, 'editAction'])->name('template.editAction');
        // Contacts
        Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
        Route::get('/contact/ajax/get-list', [ContactController::class, 'ajaxGetList'])->name('contact.ajax.getList');
        Route::get('/contact/edit/{id}', [ContactController::class, 'edit'])->name('contact.edit');
        Route::post('/contact/edit', [ContactController::class, 'editManyAction'])->name('contact.edit.many.action');
        Route::post('/contact/edit/{id}', [ContactController::class, 'editAction'])->name('contact.edit.action');
        Route::get('/contact/delete', [ContactController::class, 'deleteMany'])->name('contact.delete.many');
        Route::get('/contact/delete/{id}', [ContactController::class, 'delete'])->name('contact.delete');
        // Route::post('/contact/delete', [ContactController::class, 'delete'])->name('contact.delete');
        Route::get('/contact/ajax/get-list', [ContactController::class, 'ajaxGetList'])->name('contact.ajax.getList');
        Route::post('/contact/{id}/reply', [ContactController::class, 'replyAction'])->name('contact.reply.action');

        // Setting
        Route::get('/settings-general', [SettingController::class, 'general'])->name('setting.general');
        Route::post('/settings-general', [SettingController::class, 'generalAction'])->name('setting.general.action');
        Route::post('/settings-login-social', [SettingController::class, 'loginSocialAction'])->name('setting.login_social.action');
        Route::get('/settings-email', [SettingController::class, 'email'])->name('setting.email');
        Route::get('/settings-social-login', [SettingController::class, 'loginSocial'])->name('setting.login_social');
        Route::get('/settings-notification', [SettingController::class, 'notification'])->name('setting.notification');
        Route::post('/settings-notification', [SettingController::class, 'notificationAction'])->name('setting.notification.action');

        // Gallery
        Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

        // User Settings routes
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserSettingController::class, 'index'])->name('index');
            Route::post('/update', [\App\Http\Controllers\Admin\UserSettingController::class, 'update'])->name('update');
            Route::get('/export', [\App\Http\Controllers\Admin\UserSettingController::class, 'export'])->name('export');
            Route::post('/import', [\App\Http\Controllers\Admin\UserSettingController::class, 'import'])->name('import');
            Route::post('/reset', [\App\Http\Controllers\Admin\UserSettingController::class, 'resetToDefault'])->name('reset');
            Route::post('/clear-cache', [\App\Http\Controllers\Admin\UserSettingController::class, 'clearCache'])->name('clear-cache');
            Route::get('/{key}', [\App\Http\Controllers\Admin\UserSettingController::class, 'getSetting'])->name('get');
            Route::post('/{key}', [\App\Http\Controllers\Admin\UserSettingController::class, 'setSetting'])->name('set');
            Route::post('/theme/update', [\App\Http\Controllers\Admin\UserSettingController::class, 'updateTheme'])->name('theme.update');
            Route::post('/language/update', [\App\Http\Controllers\Admin\UserSettingController::class, 'updateLanguage'])->name('language.update');
        });





        // Shopee Integration Routes
        Route::prefix('shopee')->name('shopee.')->group(function () {
            // OAuth Routes
            Route::get('/connect', [ShopeeOAuthController::class, 'connect'])->name('connect');
            Route::get('/callback', [ShopeeOAuthController::class, 'callback'])->name('callback');
            Route::post('/refresh', [ShopeeOAuthController::class, 'refresh'])->name('refresh');
            Route::post('/revoke', [ShopeeOAuthController::class, 'revoke'])->name('revoke');
            Route::get('/status', [ShopeeOAuthController::class, 'status'])->name('status');
            Route::get('/dashboard', [ShopeeOAuthController::class, 'dashboard'])->name('dashboard');
            Route::post('/check-expiring-tokens', [ShopeeOAuthController::class, 'checkExpiringTokens'])->name('check-expiring-tokens');

            // Product Management Routes
            Route::prefix('products')->name('products.')->group(function () {
                Route::post('/search-by-sku', [ShopeeProductController::class, 'searchBySku'])->name('search-by-sku');
                Route::post('/link', [ShopeeProductController::class, 'linkProduct'])->name('link');
                Route::post('/create', [ShopeeProductController::class, 'createProduct'])->name('create');
                Route::post('/sync-inventory', [ShopeeProductController::class, 'syncInventory'])->name('sync-inventory');
                Route::post('/bulk-sync-inventory', [ShopeeProductController::class, 'bulkSyncInventory'])->name('bulk-sync-inventory');
                Route::get('/links', [ShopeeProductController::class, 'getLinks'])->name('links');
                Route::post('/unlink', [ShopeeProductController::class, 'unlinkProduct'])->name('unlink');
            });

            // Order Sync Routes
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::post('/sync', [ShopeeSyncController::class, 'syncOrders'])->name('sync');
                Route::get('/sync-status', [ShopeeSyncController::class, 'getSyncStatus'])->name('sync-status');
                Route::post('/sync-single', [ShopeeSyncController::class, 'syncSingleOrder'])->name('sync-single');
                Route::get('/detail', [ShopeeSyncController::class, 'getOrderDetail'])->name('detail');
                Route::get('/marketplace-orders', [ShopeeSyncController::class, 'getMarketplaceOrders'])->name('marketplace-orders');
                Route::get('/sync-logs', [ShopeeSyncController::class, 'getSyncLogs'])->name('sync-logs');
            });

            // Test and Utility Routes
            Route::post('/test-connection', [ShopeeSyncController::class, 'testConnection'])->name('test-connection');
        });
    });

    // Global Filter API Routes - MOVED TO tenant.php

    // Route::namespace('General')->group(function () {
    //     Route::post('/upload-image', [UpLoadImageController::class, 'uploadImage'])->name('uploadImage');
    //     Route::post('/destroy-image', [UpLoadImageController::class, 'imageDestroy'])->name('imageDestroy');
    //     Route::get('/language', [MultiLanguageController::class, 'index'])->name('language.index');
    // });
    });
}); // End of domain constraint group

// Public Shopee OAuth Routes (outside auth middleware and domain constraint)
Route::prefix('shopee')->name('shopee.')->group(function () {
    Route::get('/connect', [ShopeeOAuthController::class, 'connect'])->name('public.connect');
    Route::get('/callback', [ShopeeOAuthController::class, 'callback'])->name('public.callback');
});
