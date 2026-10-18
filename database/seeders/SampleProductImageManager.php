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
     * Tự động phát hiện thay đổi nội dung để đồng bộ và dọn dẹp ảnh mẫu cũ khác đuôi nếu có.
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
            $sourceSize = $file->getSize();

            $needsCopy = $force
                || ! Storage::disk('public')->exists($relativePath)
                || Storage::disk('public')->size($relativePath) !== $sourceSize
                || md5(Storage::disk('public')->get($relativePath)) !== md5($sourceContent);

            if ($needsCopy) {
                Storage::disk('public')->put($relativePath, $sourceContent);
                $copiedCount++;
            }

            // Dọn dẹp file mẫu cũ cùng slug nhưng khác extension (ví dụ .png cũ khi đã có .jpg mới)
            $baseName = pathinfo($filename, PATHINFO_FILENAME);
            $currentExt = strtolower($file->getExtension());

            foreach (static::SUPPORTED_EXTENSIONS as $otherExt) {
                if ($otherExt === $currentExt) {
                    continue;
                }

                $stalePath = 'products/'.$baseName.'.'.$otherExt;
                if (Storage::disk('public')->exists($stalePath)) {
                    Storage::disk('public')->delete($stalePath);
                }
            }
        }

        // Đảm bảo public/storage/products nhận đủ ảnh khi public/storage là thư mục vật lý riêng (chưa link symlink trên Windows)
        $publicStoragePath = public_path('storage');
        $storageAppPublicPath = storage_path('app/public');
        if (File::isDirectory($publicStoragePath) && realpath($publicStoragePath) !== realpath($storageAppPublicPath)) {
            $publicProductsDir = public_path('storage/products');
            File::ensureDirectoryExists($publicProductsDir);

            foreach (File::files($sourceDir) as $file) {
                $targetFile = $publicProductsDir.DIRECTORY_SEPARATOR.$file->getFilename();
                if (! File::exists($targetFile) || File::size($targetFile) !== $file->getSize()) {
                    File::copy($file->getRealPath(), $targetFile);
                }

                $baseName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $currentExt = strtolower($file->getExtension());
                foreach (static::SUPPORTED_EXTENSIONS as $otherExt) {
                    if ($otherExt === $currentExt) {
                        continue;
                    }
                    $staleTarget = $publicProductsDir.DIRECTORY_SEPARATOR.$baseName.'.'.$otherExt;
                    if (File::exists($staleTarget)) {
                        File::delete($staleTarget);
                    }
                }
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
        $fileMissingInStorage = ! $isMissing && ! Storage::disk('public')->exists($currentImage);
        $isObsoleteSampleImage = ! $isMissing && $currentImage !== $resolvedImagePath && Str::is("products/{$baseName}.*", $currentImage);

        if ($isMissing || $fileMissingInStorage || $isObsoleteSampleImage) {
            $product->update(['image' => $resolvedImagePath]);

            return true;
        }

        return false;
    }
}
