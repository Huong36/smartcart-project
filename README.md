# SmartCart

Bộ khung website bán hàng cho bài tập lớn môn Mã Nguồn Mở, nhóm 4 thành viên. Laravel 13 + MySQL + Eloquent + Blade + Bootstrap 5.

Nhánh `feat/client-orders` phát triển từ `integration/admin-cart`: có CSDL, Auth/role, CRUD Admin, giỏ hàng Fetch, live search và widget thời tiết; bổ sung danh sách sản phẩm phân trang/lọc/sắp xếp, chi tiết sản phẩm, checkout COD, lịch sử đơn của khách và quản lý trạng thái đơn cho Admin. Báo cáo và nghiệm thu cuối cần được nhóm hoàn thiện trước khi nộp.

## Client Module

- Trang chủ: 8 sản phẩm/trang, tìm tên, lọc danh mục, giá tăng/giảm; giữ bộ lọc khi chuyển trang.
- Chi tiết sản phẩm: `/products/{id}`, ảnh, mô tả, tồn kho và thêm giỏ bằng Fetch.
- Thanh toán: `/checkout`, yêu cầu đăng nhập, nhập địa chỉ/điện thoại/ghi chú. COD và miễn phí giao hàng.
- Lịch sử và chi tiết đơn: `/orders`, `/orders/{id}`; khách chỉ xem đơn của chính mình.
- Admin: `/admin/orders`, xem/lọc tất cả đơn và chuyển Chờ xác nhận → Đang giao → Hoàn thành.
- Hủy đơn: khách được hủy đơn của mình khi còn Chờ xác nhận; Admin được hủy đơn Chờ xác nhận hoặc Đang giao. Không hủy đơn Hoàn thành. Mỗi lần hủy khóa đơn và các sản phẩm trong transaction, hoàn đúng số lượng theo chi tiết đơn. Gửi lại yêu cầu hủy không hoàn kho thêm; lỗi giữa chừng sẽ rollback cả trạng thái và tồn kho.
- Checkout tính tiền bằng số nguyên theo phần trăm đơn vị tiền từ giá DB; khóa tồn kho và ghi đơn/items trong transaction. Token duy nhất chống gửi lặp. Giá/tên được lưu tại thời điểm mua.

Sau khi lấy code, chạy `php artisan migrate` để thêm cột `orders.checkout_token`; không cần xóa database hoặc chạy lại seed. Nếu cài mới, làm theo hướng dẫn bên dưới.

Kiểm tra riêng module: `php artisan test --compact tests/Feature/ClientOrdersTest.php`. Bộ test bao gồm phân trang/bộ lọc, phân quyền, validation, snapshot giá, tồn kho, gửi lặp và rollback khi lưu lỗi. SQLite không kiểm chứng khóa cạnh tranh MySQL; nhóm cần thử thêm hai giao dịch đồng thời trên MySQL trước khi triển khai công khai.

## Cài đặt trên máy thành viên

Yêu cầu PHP **8.3 trở lên**, Composer 2, MySQL 8, Git. Bật MySQL trong Laragon và dùng Terminal của Laragon để PHP/Composer có trong PATH. Nếu Composer báo thiếu ZIP, bật `extension=zip` trong php.ini của PHP đang dùng; kiểm tra bằng `php --ini` và `php -m`.

```bash
git clone https://github.com/Huong36/smartcart-project.git
cd smartcart-project
composer install
```

Tạo database trống qua HeidiSQL hoặc MySQL:

```sql
CREATE DATABASE smartcart_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Trong PowerShell, sao chép cấu hình:

```powershell
Copy-Item .env.example .env
```

Linux/macOS dùng `cp .env.example .env`. Sửa DB_USERNAME/DB_PASSWORD trong `.env` theo MySQL của máy bạn. Mặc định của dự án:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartcart_db
DB_USERNAME=root
DB_PASSWORD=
```

Sau đó:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

Mở http://127.0.0.1:8000. Với server Artisan, chỉ cần MySQL hoạt động; không bắt buộc bật Apache. Nếu dùng Apache/Laragon virtual host, document root phải trỏ vào `public/`, không trỏ vào thư mục gốc Laravel.

CSS/JS Bootstrap đã lưu cục bộ. Các trang hiện tại dùng CSS trực tiếp trong `public/css/app.css`, không dùng `@vite`, nên không cần npm để chạy bộ khung.

## Tài khoản chạy thử

Chỉ được tạo ở APP_ENV=local hoặc testing, phục vụ demo trên máy cá nhân:

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Admin | admin@gmail.com | 12345678 |
| Customer | user@gmail.com | 12345678 |

Đăng ký mới luôn tạo Customer. Admin đăng nhập được chuyển đến `/admin`, Customer đến `/account`; Customer truy cập `/admin` nhận 403. Seeder tạo sẵn 8 danh mục và 60 sản phẩm, tự động sao chép 60 ảnh mẫu từ kho lưu trữ Git vào disk public (`storage/app/public/products`). Chạy lại `php artisan db:seed` chỉ bổ sung dữ liệu chưa có, không tạo trùng, không ghi đè giá, tồn kho hoặc ảnh đã chỉnh sửa và không reset mật khẩu tài khoản đã có. Khi có nhu cầu đặt lại dữ liệu demo mẫu về ban đầu, chạy riêng lệnh có chủ đích: `php artisan db:seed --class=DemoResetSeeder`.

Không dùng tài khoản demo ở môi trường công khai. `.env`, APP_KEY, vendor, log, database local và ảnh upload không được commit. Khi triển khai thật, dùng APP_ENV=production, APP_DEBUG=false, HTTPS và thông tin DB/tài khoản riêng.

## Cấu trúc để tiếp tục phát triển

| Đường dẫn | Nội dung |
|---|---|
| app/Models | User, Category, Product, Order, OrderItem |
| database/migrations | Migration nền Laravel và 5 migration SmartCart |
| database/seeders | Tài khoản demo và catalog |
| app/Http/Controllers/AuthController.php | Đăng ký, đăng nhập, đăng xuất |
| app/Http/Middleware/CheckRole.php | Middleware role:admin |
| resources/views/layouts/app.blade.php | Layout Bootstrap chung, meta CSRF, vùng styles/scripts |
| routes/web.php | Route public, guest, auth và admin |
| docs/TEAM.md | Phân công, branch, giao diện tích hợp, quy tắc nghiệp vụ |
| docs/ERD.md | Schema và sơ đồ ERD |
| docs/USE_CASE.md | Actor và chức năng dự kiến để dựng UML |

Danh mục còn sản phẩm và sản phẩm đã được đặt hàng bị chặn xóa bằng khóa ngoại. Order item lưu `product_name` và `price` tại thời điểm mua. Thống nhất với nhóm trước khi sửa cấu trúc dữ liệu. Xem chi tiết trong [quy ước nhóm](docs/TEAM.md).

## Kiểm tra

```bash
php artisan test
php vendor/bin/pint --test
```

Mặc định kiểm thử dùng SQLite in-memory, tách biệt database MySQL chạy ứng dụng. Các test kiểm tra đăng ký/validation, chống tự nâng role, đăng nhập đúng/sai và giới hạn lần thử, đăng xuất, phân quyền và quan hệ/khóa ngoại, seeder chạy lại.

Để kiểm tra MySQL, tạo database **smartcart_test** riêng và sao chép phpunit.xml thành phpunit.mysql.local.xml. Đổi DB_CONNECTION thành mysql, DB_DATABASE thành smartcart_test, thêm DB_HOST/PORT/USERNAME/PASSWORD phù hợp với `force="true"`. Sau đó chạy:

```bash
php artisan test --configuration phpunit.mysql.local.xml
```

Test RefreshDatabase sẽ làm mới database test; luôn kiểm tra đúng tên smartcart_test trước khi chạy. File cấu hình MySQL local đã được gitignore.

## Quy trình nhóm

Chấp nhận lời mời collaborator, clone bộ khung, tự tạo branch và mở Pull Request vào main. Mỗi người dùng tài khoản Git của mình để có lịch sử đóng góp rõ ràng.

- Thành viên 2: `feat/admin-products`
- Thành viên 3: `feat/client-orders`
- Thành viên 4: `feat/cart-search`

Trưởng nhóm kiểm tra rồi merge. Xem [phân công chi tiết](docs/TEAM.md). Báo cáo Word/PDF cuối cùng sẽ được viết khi các module hoàn thiện và có ảnh demo thực tế.

## Tài liệu báo cáo và nghiệm thu

- [Khung báo cáo Word](docs/report/Bao_Cao_SmartCart.docx): yêu cầu, thiết kế, các sơ đồ, chức năng, phân công và kiểm thử nền tảng. Thông tin lớp/giảng viên/MSSV và tên thành viên 2–4 để trống để nhóm điền. Các phần chưa triển khai được ghi là dự kiến.
- [ERD](docs/ERD.md) và [UML Use Case](docs/USE_CASE.md): có ảnh PNG để chèn báo cáo và nguồn draw.io chỉnh sửa trong docs/diagrams.
- [Checklist kiểm thử tích hợp](docs/TEST_CHECKLIST.md): trạng thái đã qua nền tảng tách biệt với ca chưa chạy. Cập nhật kết quả và bằng chứng theo commit sau khi ghép module.

Báo cáo cuối cần bổ sung ảnh demo thực tế, kết quả nghiệm thu toàn hệ thống và thông tin nhóm trước khi nộp.

## Tài liệu nền tảng

- [Laravel 13](https://laravel.com/docs/13.x)
- [Laravel Authentication](https://laravel.com/docs/13.x/authentication)
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/)

Bootstrap 5.3.3 được phân phối theo MIT, xem public/vendor/bootstrap/LICENSE.
