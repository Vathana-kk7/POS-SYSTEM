<?php

namespace App\Providers;

use App\Repositories\BankRepository;
use App\Repositories\BrandRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\Interfaces\BankRepositoryInterface;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;
use App\Repositories\Interfaces\OrderPaymentMethodRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\PurchaseItemRepositoryInterface;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Repositories\Interfaces\StockmovementRepositoryInterface;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use App\Repositories\Interfaces\UniteRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderPaymentMethodRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\StockmovementRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\UniteRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            BrandRepositoryInterface::class,
            BrandRepository::class,
        );
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class,
        );
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class,
        );
        $this->app->bind(
            SupplierRepositoryInterface::class,
            SupplierRepository::class,
        );
        $this->app->bind(
            PurchaseRepositoryInterface::class,
            PurchaseRepository::class,
        );
        $this->app->bind(
            PurchaseItemRepositoryInterface::class,
            PurchaseItemRepository::class,
        );
        $this->app->bind(
            StockmovementRepositoryInterface::class,
            StockmovementRepository::class,
        );
        $this->app->bind(
            CustomerRepositoryInterface::class,
            CustomerRepository::class,
        );
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class,
        );
        $this->app->bind(
            OrderItemRepositoryInterface::class,
            OrderItemRepository::class,
        );
        $this->app->bind(
            UniteRepositoryInterface::class,
            UniteRepository::class,
        );
        $this->app->bind(
            OrderPaymentMethodRepositoryInterface::class,
            OrderPaymentMethodRepository::class,
        );
        $this->app->bind(
            BankRepositoryInterface::class,
            BankRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
