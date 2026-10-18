# Cài đặt và xác nhận trên máy thành viên

Ngày chuẩn bị: 18/10/2026. Bản mã demo cần kiểm tra: tag `demo-2026-10-18` (mã chức năng `afe7a0b`) trên nhánh `integration/admin-cart`. Nhánh `main` chưa phải bản nghiệm thu đầy đủ.

## Kết quả đã kiểm tra

Đã clone trực tiếp từ GitHub vào thư mục sạch trên máy trưởng nhóm, cài 115 gói từ `composer.lock`, không sao chép vendor hoặc cấu hình cũ. `composer check-platform-reqs`, tạo khóa, migration, seeder và storage link đều chạy thành công. Chạy seeder lần hai vẫn có 2 tài khoản demo, 8 danh mục và 60 sản phẩm. Bộ 79 test đạt với 636 assertions trên SQLite và MySQL 8.4.3 riêng cho test.

Đây là kiểm tra cài sạch trên cùng máy, chưa thay thế xác nhận trên máy vật lý khác. Trưởng nhóm đã nhờ thành viên chạy các bước dưới; kết quả máy thứ hai đang chờ.

## 1 Chuẩn bị môi trường

Dùng Terminal của Laragon; bật MySQL. Kiểm tra:

```powershell
php -v
composer --version
git --version
php --ini
php -m
```

PHP phải từ 8.3 và đáp ứng `composer.lock`; môi trường đã kiểm tra là PHP 8.3.33, Laravel 13.35.0, MySQL 8.4.3. Cần PDO MySQL để chạy ứng dụng, PDO SQLite để chạy bộ test mặc định; GD phục vụ test tạo ảnh. Nếu Composer không giải nén được, bật ZIP trong đúng php.ini mà `php --ini` báo rồi mở lại Terminal. Không dùng `composer update` để chữa lỗi cài đặt vì sẽ đổi phiên bản đã kiểm thử.

## 2 Tải đúng bản đầy đủ

Chọn một thư mục mới chưa có dự án:

```powershell
git clone --branch integration/admin-cart https://github.com/Huong36/smartcart-project.git smartcart-check
cd smartcart-check
git rev-parse --short HEAD
git merge-base --is-ancestor afe7a0b HEAD
composer install --no-interaction --prefer-dist
composer check-platform-reqs
Copy-Item .env.example .env
```

Lệnh kiểm tra tổ tiên phải có exit code 0; commit mới hơn có thể chứa cập nhật tài liệu. Nếu không đạt, dừng và báo lại mã commit, không thử trên nhánh thiếu module.

## 3 Tạo database riêng

Trong HeidiSQL/MySQL, tạo database trống mới, ví dụ:

```sql
CREATE DATABASE smartcart_member_check CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sửa `.env`: `DB_CONNECTION=mysql`, `DB_DATABASE=smartcart_member_check`, DB_HOST/PORT/USERNAME/PASSWORD đúng máy; `APP_ENV=local`, `APP_URL=http://127.0.0.1:8000`. Không gửi `.env` hoặc mật khẩu cho nhóm.

```powershell
php artisan key:generate --no-interaction
php artisan migrate --seed --no-interaction
php artisan storage:link --no-interaction
php artisan db:seed --no-interaction
php artisan test --compact
php artisan serve --host=127.0.0.1 --port=8000 --no-interaction
```

Storage link phải trỏ vào storage/app/public của bản clone này. Nếu Windows từ chối quyền tạo link, gửi nguyên thông báo lỗi để nhóm xác định cách xử lý. Không chép ảnh từ máy trưởng nhóm để che lỗi setup.

Bộ test mặc định dùng SQLite in-memory; không chạy RefreshDatabase hoặc migrate:fresh trên database đang demo. Không cần npm/build vì ứng dụng dùng CSS/JS cục bộ trong public.

## 3b Khởi động tìm sản phẩm bằng ảnh

Cần Python 3.12. Ở terminal thứ hai, từ gốc dự án:

```powershell
py -3.12 -m venv .venv-visual-search
.\.venv-visual-search\Scripts\python.exe -m pip install torch==2.14.1 torchvision==0.29.1 --index-url https://download.pytorch.org/whl/cpu
.\.venv-visual-search\Scripts\python.exe -m pip install -r app/Services/visual-search-requirements.txt
.\.venv-visual-search\Scripts\python.exe app/Services/visual_search.py
```

Lần đầu cần Internet để tải mô hình, sau đó đợi dòng `Listening on http://127.0.0.1:8020`. Giữ cả server Laravel và dịch vụ Python chạy. Tải và thử mô hình từ trước buổi báo cáo, không cài ngay khi thuyết trình. Trọng số đã lưu sẽ dùng lại khi khởi động; token tự tạo riêng cho bản cài này. Chi tiết mô hình, revision và giấy phép trong README.

Bộ test PHP hiện có 88 bài, 676 assertions đã đạt trên SQLite. Kết quả MySQL 79 bài ở mục lịch sử phía trên thuộc bản nghiệp vụ cũ, không phải kết quả của toàn bộ bản demo mới.

## 4 Kiểm tra trong trình duyệt

Mở `http://127.0.0.1:8000` trên chính máy vừa cài. 127.0.0.1 của mỗi máy là máy đó, không phải máy trưởng nhóm.

- Trang chủ có dữ liệu, ảnh hiển thị, lọc và chuyển trang được. Tìm `0` phải lọc đúng, không trả toàn bộ sản phẩm.
- Tài khoản khách `user@gmail.com`, mật khẩu demo `12345678`; Admin `admin@gmail.com`, cùng mật khẩu demo. Chỉ dùng trên database thử local.
- Khách thêm 2 đơn vị của một sản phẩm, ghi giá và tồn kho ban đầu; thay đổi số lượng phải cập nhật tổng tiền.
- Checkout với thông tin giả phục vụ demo; kiểm tra đơn được tạo, giỏ được xóa, tồn kho giảm đúng.
- Admin chuyển một đơn qua Đang giao rồi Hoàn thành; đơn hoàn thành không có nút hủy.
- Tạo đơn khác để hủy; Admin được hủy pending/processing, khách chỉ hủy đơn của mình ở pending. Tồn kho được hoàn đúng một lần.
- Khách đăng nhập lại thấy trạng thái mới. Customer vào `/admin` phải bị chặn.
- Bấm nút tìm bằng ảnh, chọn ảnh thật tai nghe/bàn phím và kiểm tra kết quả, mở sản phẩm, thêm giỏ. Thử ảnh không liên quan và tệp sai định dạng. Với camera, cần HTTPS/localhost và quyền sử dụng thiết bị; ghi riêng thiết bị đã thử.
- Thử điện thoại hoặc DevTools ở 375 px, 768 px và desktop. Bảng rộng được cuộn trong khung; trang không tràn ngang.

## 5 Gửi kết quả cho trưởng nhóm

```text
Người kiểm tra / thành viên:
Máy, hệ điều hành:
Ngày giờ:
Commit git rev-parse --short HEAD:
PHP / Composer / MySQL:
composer install và check-platform-reqs: Đạt / Lỗi
migrate --seed, seed lần hai, storage link: Đạt / Lỗi
Ảnh sản phẩm: Đạt / Lỗi
Python / OpenCLIP / mô hình khởi động: Đạt / Lỗi
Tìm bằng ảnh / thêm giỏ / ảnh không khớp: Đạt / Lỗi
Camera thật, thiết bị và trình duyệt đã thử:
Test: số bài đạt / tổng số, assertions
Luồng đặt hàng / hoàn thành / hủy / hoàn kho: Đạt / Lỗi
Màn hình đã thử:
Lỗi còn lại và ảnh chụp:
```

Chụp trang chủ có ảnh, kết quả test và chi tiết một đơn đã xử lý. Không đánh dấu máy thứ hai đạt chỉ vì đã clone được. Khi có lỗi, gửi thông báo đã che thông tin nhạy cảm và bước tái hiện; không gửi APP_KEY, cookie hay mật khẩu database.

## Lỗi cài đặt thường gặp

| Hiện tượng | Kiểm tra |
|---|---|
| Composer báo PHP không phù hợp | `where.exe php`, `php -v`; mở Terminal đúng Laragon |
| could not find driver | Bật pdo_mysql hoặc pdo_sqlite theo tác vụ |
| Access denied / Unknown database | Kiểm tra tài khoản và tên database trong .env |
| No application encryption key | Chạy key:generate trên bản clone mới |
| Ảnh lỗi 404 | Kiểm tra storage link và file do seeder sao chép |
| Cổng 8000 đang dùng | Đổi sang 8001 và đổi APP_URL tương ứng |
| Widget thời tiết lỗi mạng | Ghi riêng API; ứng dụng mua hàng vẫn phải dùng được |

## Điều kiện gộp main

Chờ biên bản máy thứ hai, kết quả các ca nghiệm thu còn mở trong TEST_CHECKLIST và kiểm tra bố cục Word. Trưởng nhóm chỉ đánh dấu đạt và gộp khi có bằng chứng; không tự coi các mục đang chờ là đã hoàn tất.
