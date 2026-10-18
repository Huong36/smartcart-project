<?php

namespace App\Providers;

use Database\Seeders\SampleProductImageManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $this->autoBootstrapSampleData();
    }

    /**
     * Tự động khởi tạo dữ liệu mẫu và đồng bộ ảnh nếu dự án vừa tải về trên máy mới
     * mà người dùng chưa kịp chạy migrate/seed/storage:link.
     */
    private function autoBootstrapSampleData(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        try {
            // 1. Tự động đồng bộ ảnh mẫu và tạo storage link nếu máy mới chưa có
            $sampleIndicator = public_path('storage/products/tai-nghe-khong-day.jpg');
            if (! File::exists($sampleIndicator)) {
                if (! File::exists(public_path('storage'))) {
                    Artisan::call('storage:link');
                }
                SampleProductImageManager::copySampleImages();
            }

            // 2. Tự động migrate và seed nếu database chưa có bảng products hoặc chưa có dữ liệu
            if (! Schema::hasTable('products') || DB::table('products')->count() === 0) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Throwable) {
            // Không làm gián đoạn luồng chính nếu môi trường thiếu quyền
        }
    }
}
