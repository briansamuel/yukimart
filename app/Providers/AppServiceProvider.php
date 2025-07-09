<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SettingService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Harimayco\Menu\Facades\Menu;
use App\Helpers\ArrayHelper;
use App\Repositories\Setting\SettingRepositoryInterface;
use Carbon\Carbon;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //

        foreach (glob(app_path() . '/Helpers/*.php') as $file) {
            require_once($file);
        }

        $this->app->singleton(
            \App\Repositories\User\UserRepositoryInterface::class,
            \App\Repositories\User\UserRepository::class
        );
        $this->app->singleton(
            \App\Repositories\Auth\AuthRepositoryInterface::class,
            \App\Repositories\Auth\AuthRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Setting\SettingRepositoryInterface::class,
            \App\Repositories\Setting\SettingRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Product\ProductRepositoryInterface::class,
            \App\Repositories\Product\ProductRepository::class
        );


        $this->app->singleton(
            \App\Repositories\Page\PageRepositoryInterface::class,
            \App\Repositories\Page\PageRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Post\PostRepositoryInterface::class,
            \App\Repositories\Post\PostRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Role\RoleRepositoryInterface::class,
            \App\Repositories\Role\RoleRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Contact\ContactRepositoryInterface::class,
            \App\Repositories\Contact\ContactRepository::class
        );

        $this->app->singleton(
            \App\Repositories\CategoryPost\CategoryPostRepositoryInterface::class,
            \App\Repositories\CategoryPost\CategoryPostRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Category\CategoryRepositoryInterface::class,
            \App\Repositories\Category\CategoryRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Project\ProjectRepositoryInterface::class,
            \App\Repositories\Project\ProjectRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Task\TaskRepositoryInterface::class,
            \App\Repositories\Task\TaskRepository::class
        );

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        View::share('arrayLang', ArrayHelper::arrayLang());

        View::share('setting', SettingService::class);
        // View::share( 'menus', Menu::getByName('Main Menu'));
        // View::share( 'menus_footer', Menu::getByName('Footer Menu'));

        // Register FontAwesome helper as a global function
        // if (!function_exists('faIcon')) {
        //     function faIcon($name, $size = '', $class = '', $color = '') {
        //         return \App\Helpers\FontAwesomeHelper::render($name, $size, $class, $color);
        //     }
        // }
    }
}
