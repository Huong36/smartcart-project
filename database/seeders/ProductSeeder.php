<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo sẵn 30 sản phẩm mẫu (20 - 30) có liên kết danh mục để cả nhóm dùng chung dữ liệu.
     */
    public function run(): void
    {
        $catalog = [
            'Điện tử' => [
                'description' => 'Các sản phẩm và thiết bị công nghệ điện tử chất lượng cao.',
                'products' => [
                    ['Tai nghe không dây', 490000, 20, 'Tai nghe không dây âm thanh trung thực, kết nối Bluetooth 5.3.'],
                    ['Loa Bluetooth mini', 650000, 20, 'Loa không dây chống nước chuẩn IPX7, âm bass mạnh mẽ.'],
                    ['Chuột không dây', 250000, 20, 'Chuột quang không dây tiện lợi cho văn phòng và học tập.'],
                    ['Bàn phím cơ', 890000, 20, 'Bàn phím cơ switch xanh độ nảy tốt, led RGB nhiều chế độ.'],
                    ['Sạc dự phòng', 390000, 20, 'Pin dự phòng dung lượng chuẩn 10.000mAh hỗ trợ sạc nhanh.'],
                    ['Đèn bàn LED', 320000, 20, 'Đèn học LED chống cận với 3 chế độ sáng tùy chỉnh.'],
                ],
            ],
            'Thời trang' => [
                'description' => 'Trang phục và phụ kiện thời trang phong cách trẻ trung.',
                'products' => [
                    ['Áo thun cotton', 159000, 20, 'Áo thun 100% cotton thoáng khí, form suông dễ phối đồ.'],
                    ['Áo sơ mi linen', 329000, 20, 'Chất vải đũi linen tự nhiên, thấm hút mồ hôi tối đa.'],
                    ['Quần jeans', 450000, 20, 'Quần bò denim form đứng thời thượng, bền đẹp theo thời gian.'],
                    ['Mũ lưỡi trai', 129000, 20, 'Mũ lưỡi trai chất liệu kaki bền chắc, phong cách năng động.'],
                    ['Túi tote', 99000, 20, 'Túi vải canvas thời trang đựng tài liệu và đồ dùng cá nhân.'],
                    ['Giày thể thao', 690000, 20, 'Giày thể thao năng động đệm êm ái, bám đường chống trơn trượt.'],
                ],
            ],
            'Gia dụng' => [
                'description' => 'Vật dụng tiện ích và đồ dùng thiết yếu cho gia đình.',
                'products' => [
                    ['Bình giữ nhiệt', 249000, 20, 'Bình giữ nhiệt inox 304 giữ nóng và lạnh trong 8 tiếng.'],
                    ['Hộp cơm văn phòng', 179000, 20, 'Hộp đựng cơm chia ngăn inox chống tràn tiện lợi.'],
                    ['Cốc sứ', 89000, 20, 'Cốc sứ tráng men cao cấp, họa tiết tinh tế.'],
                    ['Kệ để bàn', 219000, 20, 'Kệ gỗ để bàn làm việc giúp sắp xếp không gian gọn gàng.'],
                    ['Khăn tắm cotton', 149000, 20, 'Khăn bông cotton mềm mại, thấm nước cực nhanh.'],
                    ['Đồng hồ treo tường', 299000, 20, 'Đồng hồ kim trôi siêu êm, thiết kế phong cách Bắc Âu hiện đại.'],
                ],
            ],
            'Sách & Văn phòng' => [
                'description' => 'Sách chuyên môn, giáo trình và văn phòng phẩm chất lượng.',
                'products' => [
                    ['Sổ tay bìa cứng', 79000, 20, 'Sổ tay ghi chép kẻ ngang giấy vàng chống lóa mắt.'],
                    ['Bút bi bộ 5 cây', 39000, 20, 'Bộ bút bi mực xanh nét trơn êm không lem nhòe.'],
                    ['Sách học Laravel', 189000, 20, 'Cẩm nang lập trình web với framework Laravel từ cơ bản đến nâng cao.'],
                    ['Sách kỹ năng làm việc', 129000, 20, 'Kỹ năng quản lý thời gian và giải quyết vấn đề hiệu quả.'],
                    ['Bìa hồ sơ', 29000, 20, 'Bìa tài liệu lưu trữ giấy tờ khổ A4 dày dặn.'],
                    ['Giá đỡ sách', 99000, 20, 'Giá đọc sách gấp gọn hỗ trợ tư thế ngồi đọc chuẩn.'],
                ],
            ],
            'Thể thao' => [
                'description' => 'Dụng cụ thể thao và phụ kiện rèn luyện sức khỏe.',
                'products' => [
                    ['Thảm yoga', 249000, 20, 'Thảm tập yoga chất liệu TPE chống trơn trượt dày 6mm.'],
                    ['Dây nhảy', 79000, 20, 'Dây nhảy thể lực tay cầm chống trượt có thể chỉnh độ dài.'],
                    ['Bình nước thể thao', 129000, 20, 'Bình nước thể thao chịu nhiệt, nắp bật khóa an toàn.'],
                    ['Tạ tay 2 kg', 159000, 20, 'Tạ tay đúc bọc cao su êm ái, bảo vệ sàn nhà.'],
                    ['Bóng đá', 299000, 20, 'Quả bóng đá may máy tiêu chuẩn số 5 độ nảy chuẩn.'],
                    ['Túi đựng đồ tập', 219000, 20, 'Túi trống thể thao quai đeo chắc chắn, ngăn chứa rộng rãi.'],
                ],
            ],
        ];

        foreach ($catalog as $catName => $catData) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                [
                    'name' => $catName,
                    'description' => $catData['description'],
                ]
            );

            foreach ($catData['products'] as [$name, $price, $stock, $description]) {
                $imagePath = 'products/'.Str::slug($name).'.png';

                Product::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                    ],
                    [
                        'price' => $price,
                        'stock' => $stock,
                        'description' => $description,
                        'image' => $imagePath,
                    ]
                );
            }
        }
    }
}
