<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Điện tử' => [['Tai nghe không dây', 490000], ['Loa Bluetooth mini', 650000], ['Chuột không dây', 250000], ['Bàn phím cơ', 890000], ['Sạc dự phòng', 390000], ['Đèn bàn LED', 320000]],
            'Thời trang' => [['Áo thun cotton', 159000], ['Áo sơ mi linen', 329000], ['Quần jeans', 450000], ['Mũ lưỡi trai', 129000], ['Túi tote', 99000], ['Giày thể thao', 690000]],
            'Gia dụng' => [['Bình giữ nhiệt', 249000], ['Hộp cơm văn phòng', 179000], ['Cốc sứ', 89000], ['Kệ để bàn', 219000], ['Khăn tắm cotton', 149000], ['Đồng hồ treo tường', 299000]],
            'Sách & Văn phòng' => [['Sổ tay bìa cứng', 79000], ['Bút bi bộ 5 cây', 39000], ['Sách học Laravel', 189000], ['Sách kỹ năng làm việc', 129000], ['Bìa hồ sơ', 29000], ['Giá đỡ sách', 99000]],
            'Thể thao' => [['Thảm yoga', 249000], ['Dây nhảy', 79000], ['Bình nước thể thao', 129000], ['Tạ tay 2 kg', 159000], ['Bóng đá', 299000], ['Túi đựng đồ tập', 219000]],
        ];

        foreach ($catalog as $name => $products) {
            $category = Category::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => 'Các sản phẩm thuộc danh mục '.$name.'.',
            ]);

            foreach ($products as [$productName, $price]) {
                Product::firstOrCreate(['category_id' => $category->id, 'name' => $productName], [
                    'price' => $price,
                    'stock' => 20,
                    'description' => $productName.' — sản phẩm mẫu phục vụ chạy thử SmartCart.',
                    'image' => null,
                ]);
            }
        }
    }
}
