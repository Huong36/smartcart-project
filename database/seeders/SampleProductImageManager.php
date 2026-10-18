<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SampleProductImageManager
{
    /**
     * Danh sách đuôi file ảnh được hỗ trợ theo thứ tự ưu tiên.
     *
     * @var list<string>
     */
    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Thư mục chứa ảnh mẫu được theo dõi trong Git.
     */
    public static function sourceDirectory(): string
    {
        return database_path('seeders/images/products');
    }

    /**
     * Sao chép toàn bộ ảnh mẫu từ thư mục seed sang disk public của Laravel.
     * Chỉ thêm file chưa tồn tại; việc ghi đè phải được yêu cầu qua force.
     *
     * @param  bool  $force  Bắt buộc ghi đè toàn bộ kể cả khi nội dung chưa đổi.
     * @return int Số file đã được sao chép hoặc cập nhật.
     */
    public static function copySampleImages(bool $force = false): int
    {
        $sourceDir = static::sourceDirectory();
        if (! File::isDirectory($sourceDir)) {
            Log::warning('Thư mục ảnh mẫu seeder không tồn tại: '.$sourceDir);

            return 0;
        }

        Storage::disk('public')->makeDirectory('products');
        $copiedCount = 0;

        foreach (File::files($sourceDir) as $file) {
            $filename = $file->getFilename();
            $relativePath = 'products/'.$filename;
            $sourcePath = $file->getRealPath();
            $sourceContent = File::get($sourcePath);

            $needsCopy = $force || ! Storage::disk('public')->exists($relativePath);

            if ($needsCopy) {
                Storage::disk('public')->put($relativePath, $sourceContent);
                $copiedCount++;
            }
        }

        return $copiedCount;
    }

    /**
     * Tìm đường dẫn ảnh tương đối trên disk public cho một sản phẩm theo tên/slug.
     * Ưu tiên ảnh trong storage disk public, sau đó kiểm tra trong source directory.
     */
    public static function resolveImagePath(string $productName): ?string
    {
        $baseName = Str::slug($productName);

        // 1. Kiểm tra ảnh đã tồn tại trên storage disk public
        foreach (static::SUPPORTED_EXTENSIONS as $ext) {
            $candidate = 'products/'.$baseName.'.'.$ext;
            if (Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        // 2. Nếu chưa có trên storage disk, kiểm tra trong thư mục nguồn seeder
        $sourceDir = static::sourceDirectory();
        if (File::isDirectory($sourceDir)) {
            foreach (static::SUPPORTED_EXTENSIONS as $ext) {
                $filename = $baseName.'.'.$ext;
                $sourcePath = $sourceDir.DIRECTORY_SEPARATOR.$filename;

                if (File::exists($sourcePath)) {
                    $relativePath = 'products/'.$filename;
                    Storage::disk('public')->put($relativePath, File::get($sourcePath));

                    return $relativePath;
                }
            }
        }

        Log::warning('Không tìm thấy ảnh mẫu cho sản phẩm "'.$productName.'" trong seeders/images hoặc storage.');

        return null;
    }

    /**
     * Đồng bộ hoặc cập nhật đường dẫn ảnh cho sản phẩm khi cần thiết mà không làm thay đổi các trường dữ liệu khác.
     *
     * @param  Product  $product  Sản phẩm cần kiểm tra.
     * @param  string|null  $resolvedImagePath  Đường dẫn ảnh mẫu hợp lệ đã được resolve.
     * @param  bool  $force  Cưỡng chế cập nhật (dành cho DemoResetSeeder).
     * @return bool True nếu có cập nhật ảnh, False nếu giữ nguyên.
     */
    public static function syncProductImage(Product $product, ?string $resolvedImagePath, bool $force = false): bool
    {
        if ($resolvedImagePath === null) {
            return false;
        }

        $currentImage = $product->image;

        if ($force) {
            if ($currentImage !== $resolvedImagePath) {
                $product->update(['image' => $resolvedImagePath]);

                return true;
            }

            return false;
        }

        $baseName = Str::slug($product->name);

        $isMissing = ($currentImage === null || $currentImage === '');
        $isObsoleteSampleImage = ! $isMissing && $currentImage !== $resolvedImagePath && Str::is("products/{$baseName}.*", $currentImage);

        if ($isMissing || $isObsoleteSampleImage) {
            $product->update(['image' => $resolvedImagePath]);

            return true;
        }

        return false;
    }
}
