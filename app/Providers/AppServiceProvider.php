<?php

namespace App\Providers;

use App\Contracts\FileStorageInterface;
use App\Http\Controllers\ProductController;
use App\Contracts\AuthInterface;
use App\Contracts\OrderInterface;
use App\Services\AuthService;
use App\Services\FileStorageService;
use App\Contracts\ProductCategoryInterface;
use App\Contracts\ProductImageInterface;
use App\Services\ProductCategoryService;
use App\Contracts\ProductInterface;
use App\Services\ProductImageService;
use App\Services\ProductService;
use App\Contracts\ProductTypeInterface;
use App\Services\ProductTypeService;
use App\Contracts\RolesInterface;
use App\Services\RolesService;
use App\Contracts\UserInterface;
use App\Contracts\UserLocationInterface;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\UserLocationService;
use App\Services\UserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ?Laravel docs: The singleton method binds a class or interface into the container that should only be resolved one time.
        $this->app->singleton(FileStorageInterface::class, function () {
            return new FileStorageService(config('filesystems.default', 's3'));
        });

        $this->app->bind(AuthInterface::class, AuthService::class);
        $this->app->bind(RolesInterface::class, RolesService::class);
        $this->app->bind(UserInterface::class, UserService::class);
        $this->app->bind(OrderInterface::class, OrderService::class);
        $this->app->bind(UserLocationInterface::class, UserLocationService::class);

        $this->app->bind(ProductTypeInterface::class, function (Application $app) {
            return new ProductTypeService($app->make(FileStorageInterface::class));
        });

        $this->app->bind(ProductCategoryInterface::class, function (Application $app) {
            return new ProductCategoryService($app->make(FileStorageInterface::class));
        });

        $this->app->bind(ProductImageInterface::class, function (Application $app) {
            return new ProductImageService($app->make(FileStorageInterface::class));
        });

        $this->app->when(ProductController::class)
            ->needs(ProductInterface::class)
            ->give(function (Application $app) {
                return new ProductService(
                    $app->make(ProductTypeService::class),
                    $app->make(FileStorageInterface::class),
                    $app->make(ProductCategoryService::class),
                    $app->make(ProductImageService::class)
                );
            });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
