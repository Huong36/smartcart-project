<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo sẵn 30 sản phẩm mẫu (20 - 30) có liên kết danh mục để cả nhóm dùng chung dữ liệu.
     */
    public function run(): void
    {
        $this->copySampleImages();

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
            'Làm đẹp & Sức khỏe' => [
                'description' => 'Mỹ phẩm chính hãng, chăm sóc da và sản phẩm chăm sóc sức khỏe sắc đẹp.',
                'products' => [
                    ['Son dưỡng môi ẩm mượt', 120000, 25, 'Son dưỡng môi chiết xuất bơ hạt mỡ dưỡng ẩm sâu và chống nứt nẻ.'],
                    ['Kem chống nắng phổ rộng', 280000, 30, 'Kem chống nắng SPF50+ PA++++ kiềm dầu, nâng tông tự nhiên.'],
                    ['Sữa rửa mặt tạo bọt dịu nhẹ', 165000, 20, 'Sữa rửa mặt pH 5.5 làm sạch sâu lỗ chân lông mà không gây khô da.'],
                    ['Serum Vitamin C sáng da', 350000, 15, 'Tinh chất Vitamin C 15% mờ thâm nám và chống oxy hóa hiệu quả.'],
                    ['Nước tẩy trang dịu nhẹ', 145000, 25, 'Nước tẩy trang công nghệ Micellar sạch bụi mịn và lớp trang điểm.'],
                    ['Mặt nạ đất sét thải độc', 190000, 20, 'Mặt nạ đất sét khoáng chất hút sạch dầu thừa và bã nhờn sâu.'],
                    ['Bàn chải điện sóng âm', 450000, 18, 'Bàn chải đánh răng điện công nghệ sóng âm rung 40.000 lần/phút.'],
                    ['Máy xịt khoáng nano', 210000, 20, 'Máy phun sương nano cấp ẩm tức thì mọi lúc mọi nơi.'],
                    ['Dầu gội bưởi ngăn rụng tóc', 185000, 22, 'Dầu gội tinh dầu vỏ bưởi tự nhiên kích thích mọc tóc dày mượt.'],
                    ['Tinh dầu tràm trà giảm mụn', 135000, 30, 'Tinh dầu tràm trà nguyên chất kháng khuẩn và xẹp mụn nhanh chóng.'],
                ],
            ],
            'Mẹ & Bé' => [
                'description' => 'Sản phẩm dinh dưỡng, đồ dùng an toàn và tiện ích hàng đầu cho mẹ và bé.',
                'products' => [
                    ['Bình sữa chống sặc PPSU', 215000, 25, 'Bình sữa PPSU cao cấp núm ti siêu mềm van thông khí chống sặc.'],
                    ['Máy tiệt trùng hâm sữa', 580000, 12, 'Máy đa năng sấy khô và tiệt trùng hơi nước bảo vệ hệ tiêu hóa của bé.'],
                    ['Khăn ướt không mùi cho bé', 45000, 50, 'Gói 80 tờ khăn ướt sợi cotton dệt tinh khiết an toàn cho da sơ sinh.'],
                    ['Ghế ăn dặm đa năng', 490000, 15, 'Ghế ăn dặm điều chỉnh độ cao linh hoạt có khay ăn tháo rời vệ sinh.'],
                    ['Xe đẩy em bé gấp gọn', 890000, 10, 'Xe đẩy du lịch gấp gọn một chạm siêu nhẹ kèm mái che chống tia UV.'],
                    ['Địu em bé trợ lực thoáng khí', 360000, 16, 'Địu ngồi 4 tư thế đệm lưng thoáng khí chống gù và mỏi vai cho mẹ.'],
                    ['Bộ đồ chơi xếp hình gỗ', 175000, 25, 'Bộ khối gỗ tự nhiên sơn màu nước rèn luyện tư duy sáng tạo cho trẻ.'],
                    ['Sữa tắm gội thảo dược cho bé', 155000, 30, 'Sữa tắm gội 2 trong 1 chiết xuất cúc la mã không cay mắt.'],
                    ['Chăn ủ lông tuyết cho bé', 130000, 20, 'Chăn ủ có mũ lông tuyết mềm mịn giữ ấm tối ưu cho trẻ sơ sinh.'],
                    ['Gặm nướu silicon kháng khuẩn', 85000, 40, 'Gặm nướu silicon thực phẩm giảm ngứa nướu trong thời kỳ mọc răng.'],
                ],
            ],
            'Bách hóa & Thực phẩm' => [
                'description' => 'Nông sản tươi sạch, thực phẩm khô dinh dưỡng và đặc sản chọn lọc.',
                'products' => [
                    ['Cà phê rang xay nguyên chất', 145000, 30, 'Gói 500g cà phê Robusta & Arabica Đắk Lắk hương vị đậm đà truyền thống.'],
                    ['Trà ô long thượng hạng', 125000, 25, 'Hộp 200g trà ô long búp non Bảo Lộc hương hoa thanh khiết.'],
                    ['Mật ong hoa rừng tự nhiên', 220000, 20, 'Chai 500ml mật ong hoa rừng nguyên chất đậm đặc không pha tạp.'],
                    ['Hạt điều rang muối vỏ lụa', 165000, 25, 'Hộp 500g hạt điều Bình Phước giòn rụm béo ngậy thơm ngon.'],
                    ['Granola yến mạch siêu hạt', 180000, 20, 'Hũ 500g ngũ cốc yến mạch nướng mật ong giàu dinh dưỡng cho bữa sáng.'],
                    ['Hạt macca sấy nứt vỏ', 195000, 18, 'Hũ 500g hạt macca Tây Nguyên sấy chín tự nhiên kèm đồ khui tiện dụng.'],
                    ['Gạo ST25 hữu cơ túi 5kg', 230000, 15, 'Gạo ngon nhất thế giới dẻo thơm hạt dài đạt chuẩn hữu cơ quốc tế.'],
                    ['Bột ca cao nguyên chất', 110000, 30, 'Gói 250g bột ca cao Bến Tre không đường thơm béo pha chế đa năng.'],
                    ['Dầu ô liu Extra Virgin', 210000, 20, 'Chai 500ml dầu ô liu nguyên chất ép lạnh cao cấp cho salad và nấu ăn.'],
                    ['Trái cây sấy giòn thập cẩm', 95000, 35, 'Gói 500g mít, chuối, khoai môn sấy giòn không dầu thơm tự nhiên.'],
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

                Product::firstOrCreate(
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

    /**
     * Sao chép các ảnh mẫu có sẵn sang disk public nếu chưa tồn tại.
     */
    protected function copySampleImages(): void
    {
        $sourceDir = database_path('seeders/images/products');
        if (! File::isDirectory($sourceDir)) {
            return;
        }

        Storage::disk('public')->makeDirectory('products');
        foreach (File::files($sourceDir) as $file) {
            $relativePath = 'products/'.$file->getFilename();
            if (! Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->put($relativePath, File::get($file->getRealPath()));
            }
        }
    }
}
