<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->copySampleImages();

        $catalog = [
            'Điện tử' => [['Tai nghe không dây', 490000], ['Loa Bluetooth mini', 650000], ['Chuột không dây', 250000], ['Bàn phím cơ', 890000], ['Sạc dự phòng', 390000], ['Đèn bàn LED', 320000]],
            'Thời trang' => [['Áo thun cotton', 159000], ['Áo sơ mi linen', 329000], ['Quần jeans', 450000], ['Mũ lưỡi trai', 129000], ['Túi tote', 99000], ['Giày thể thao', 690000]],
            'Gia dụng' => [['Bình giữ nhiệt', 249000], ['Hộp cơm văn phòng', 179000], ['Cốc sứ', 89000], ['Kệ để bàn', 219000], ['Khăn tắm cotton', 149000], ['Đồng hồ treo tường', 299000]],
            'Sách & Văn phòng' => [['Sổ tay bìa cứng', 79000], ['Bút bi bộ 5 cây', 39000], ['Sách học Laravel', 189000], ['Sách kỹ năng làm việc', 129000], ['Bìa hồ sơ', 29000], ['Giá đỡ sách', 99000]],
            'Thể thao' => [['Thảm yoga', 249000], ['Dây nhảy', 79000], ['Bình nước thể thao', 129000], ['Tạ tay 2 kg', 159000], ['Bóng đá', 299000], ['Túi đựng đồ tập', 219000]],
            'Làm đẹp & Sức khỏe' => [['Son dưỡng môi ẩm mượt', 120000], ['Kem chống nắng phổ rộng', 280000], ['Sữa rửa mặt tạo bọt dịu nhẹ', 165000], ['Serum Vitamin C sáng da', 350000], ['Nước tẩy trang dịu nhẹ', 145000], ['Mặt nạ đất sét thải độc', 190000], ['Bàn chải điện sóng âm', 450000], ['Máy xịt khoáng nano', 210000], ['Dầu gội bưởi ngăn rụng tóc', 185000], ['Tinh dầu tràm trà giảm mụn', 135000]],
            'Mẹ & Bé' => [['Bình sữa chống sặc PPSU', 215000], ['Máy tiệt trùng hâm sữa', 580000], ['Khăn ướt không mùi cho bé', 45000], ['Ghế ăn dặm đa năng', 490000], ['Xe đẩy em bé gấp gọn', 890000], ['Địu em bé trợ lực thoáng khí', 360000], ['Bộ đồ chơi xếp hình gỗ', 175000], ['Sữa tắm gội thảo dược cho bé', 155000], ['Chăn ủ lông tuyết cho bé', 130000], ['Gặm nướu silicon kháng khuẩn', 85000]],
            'Bách hóa & Thực phẩm' => [['Cà phê rang xay nguyên chất', 145000], ['Trà ô long thượng hạng', 125000], ['Mật ong hoa rừng tự nhiên', 220000], ['Hạt điều rang muối vỏ lụa', 165000], ['Granola yến mạch siêu hạt', 180000], ['Hạt macca sấy nứt vỏ', 195000], ['Gạo ST25 hữu cơ túi 5kg', 230000], ['Bột ca cao nguyên chất', 110000], ['Dầu ô liu Extra Virgin', 210000], ['Trái cây sấy giòn thập cẩm', 95000]],
        ];

        foreach ($catalog as $name => $products) {
            $category = Category::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => 'Các sản phẩm thuộc danh mục '.$name.'.',
            ]);

            foreach ($products as [$productName, $price]) {
                $imagePath = SampleProductImageManager::resolveImagePath($productName);

                $product = Product::firstOrCreate(['category_id' => $category->id, 'name' => $productName], [
                    'price' => $price,
                    'stock' => 20,
                    'description' => $productName.' — sản phẩm mẫu phục vụ chạy thử SmartCart.',
                    'image' => $imagePath,
                ]);

                SampleProductImageManager::syncProductImage($product, $imagePath);
            }
        }
    }

    /**
     * Sao chép các ảnh mẫu có sẵn sang disk public nếu chưa tồn tại.
     */
    protected function copySampleImages(): void
    {
        SampleProductImageManager::copySampleImages(false);
    }
}
