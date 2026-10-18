# SmartCart

Website bán hàng và giỏ hàng tương tác cho bài tập lớn môn Mã Nguồn Mở, nhóm 4 thành viên. Laravel 13 + MySQL + Eloquent + Blade + Bootstrap 5.

Nhánh `integration/admin-cart` đã ghép module `feat/client-orders`: có CSDL, Auth/role, CRUD Admin, giỏ hàng Fetch, live search và widget thời tiết; bổ sung danh sách sản phẩm phân trang/lọc/sắp xếp, chi tiết sản phẩm, checkout COD, lịch sử đơn của khách và quản lý trạng thái đơn cho Admin. Báo cáo và nghiệm thu cuối cần được nhóm hoàn thiện trước khi nộp.


## Bản demo đóng gói ngày 18/10/2026

Bản demo đầy đủ nằm trên **`integration/admin-cart`**, có tag **`demo-2026-10-18`** để cả nhóm lấy cùng một phiên bản. Nhánh `main` chưa được cập nhật bản nghiệm thu cuối. Bản demo gồm các module mua hàng, ảnh sản phẩm thật, giao diện cam nhạt và tìm sản phẩm bằng ảnh OpenCLIP. Hướng dẫn bên dưới chạy được từ mã nguồn hoặc ZIP; ZIP không chứa `.env`, vendor, database, ảnh truy vấn, token hoặc trọng số mô hình.

Kiểm tra gần nhất trên bản mã `afe7a0b`: **88/88 test, 676 assertions trên SQLite**, Composer validate và kiểm tra phụ thuộc Python đạt. Bộ 79 test MySQL đã đạt ở bản nghiệp vụ trước đó; chưa chạy lại toàn bộ 88 test bằng MySQL cho bản demo này. Thành viên xác nhận ngày 18/10 rằng **máy thứ hai chưa thử**, đang chờ thực hiện theo [hướng dẫn cài đặt](docs/INSTALL_ACCEPTANCE.md).

Ảnh chụp màn hình người dùng cung cấp đã tìm được đúng loại tai nghe, bàn phím và hạt điều. Đây là các ví dụ chức năng, không phải bộ đánh giá độ chính xác hoặc độ đa dạng có tính thống kê. Catalog hiện vẫn có 60 sản phẩm và 8 danh mục. Báo cáo Word và việc điền thông tin bìa còn cần hoàn thiện trước khi nộp.

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
git clone --branch integration/admin-cart https://github.com/Huong36/smartcart-project.git
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
| docs/USE_CASE.md | Actor, chức năng và điều kiện truy cập |

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

Trưởng nhóm kiểm tra rồi merge. Xem [phân công chi tiết](docs/TEAM.md). Nội dung báo cáo và ảnh demo đã cập nhật theo bản tích hợp; chỉ nộp bản Word/PDF sau khi kiểm tra bố cục và hoàn tất nghiệm thu còn mở.

## Tài liệu báo cáo và nghiệm thu

- [Nguồn báo cáo Word](docs/report/Bao_Cao_SmartCart.docx): đã cập nhật nội dung và ảnh demo, đang chờ kiểm tra bố cục. Thông tin lớp/giảng viên/MSSV và tên thành viên 2–4 để trống để nhóm điền. Báo cáo nghiệm thu Markdown ghi riêng kết quả đã đạt và các mục còn chờ.
- [ERD](docs/ERD.md) và [UML Use Case](docs/USE_CASE.md): có ảnh PNG để chèn báo cáo và nguồn draw.io chỉnh sửa trong docs/diagrams.
- [Checklist kiểm thử tích hợp](docs/TEST_CHECKLIST.md): trạng thái đã qua nền tảng tách biệt với ca chưa chạy. Cập nhật kết quả và bằng chứng theo commit sau khi ghép module.

Báo cáo cuối cần bổ sung ảnh demo thực tế, kết quả nghiệm thu toàn hệ thống và thông tin nhóm trước khi nộp.

## Tài liệu nền tảng

- [Laravel 13](https://laravel.com/docs/13.x)
- [Laravel Authentication](https://laravel.com/docs/13.x/authentication)
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/)

Bootstrap 5.3.3 được phân phối theo MIT, xem public/vendor/bootstrap/LICENSE.

## Nghiệm thu và chuẩn bị báo cáo ngày 18/10/2026

Bản nghiệp vụ 316a2ac đã ghép cả bốn phần, sửa header Admin trên mobile. Chưa gộp main khi nghiệm thu còn mở.

- [Hướng dẫn cài và xác nhận máy thứ hai](docs/INSTALL_ACCEPTANCE.md).
- [Kịch bản thuyết trình cho bốn thành viên](docs/PRESENTATION_SCRIPT.md).
- [Báo cáo nghiệm thu có ảnh demo](docs/report/Bao_Cao_Nghiem_Thu.md).
- [Checklist và kết quả thực tế](docs/TEST_CHECKLIST.md).

Đã cài sạch cùng máy từ GitHub, Composer lock và database mới; bộ 79 test đạt trên SQLite/MySQL, 636 assertions. Browser đã thử mua hàng, hoàn thành/hủy đơn và các viewport 375/768/1440 px. Máy thứ hai đang chờ thành viên gửi kết quả. Bản Word được cập nhật nội dung, chưa kiểm tra bố cục bằng renderer vì thiếu LibreOffice; cần kiểm tra trước khi nộp.


## Tìm sản phẩm bằng ảnh

Trang `/search/image` nhận ảnh JPG/PNG/WebP tối đa 2 MB, tối đa 6000 × 6000 pixel. Khách có thể chọn ảnh hoặc mở camera, xem ảnh trước khi tìm và thêm kết quả vào giỏ. Camera trực tiếp cần HTTPS hoặc localhost và quyền camera; thiết bị cảm ứng có thể dùng trình chụp ảnh của hệ điều hành khi trình duyệt hỗ trợ `capture`. Không bật camera tự động khi mở trang.

Laravel kiểm tra file và gửi ảnh trong bộ nhớ tới dịch vụ Python **trên máy cục bộ**. Ảnh truy vấn không được lưu vào tài khoản hoặc storage. Dịch vụ chỉ lắng nghe `127.0.0.1:8020`, yêu cầu token nội bộ tạo trong `storage/app/private/visual-search/token`. Không đưa token, cache vector, mô hình hoặc môi trường Python lên Git.

### Chạy trên máy thành viên

Đã thử bằng Python **3.12**, OpenCLIP 3.3.0, PyTorch 2.14.1 CPU và torchvision 0.29.1 CPU trên Windows. Không cần GPU hoặc API trả phí. Ngoài bước cài thư viện và tải trọng số lần đầu, tìm kiếm ảnh chạy cục bộ. Máy mới cần dung lượng cho môi trường Python và khoảng 600 MB trọng số mô hình; lần khởi động đầu cần Internet.

Tại thư mục gốc dự án, tạo môi trường riêng trong PowerShell:

```powershell
py -3.12 -m venv .venv-visual-search
.\.venv-visual-search\Scripts\python.exe -m pip install torch==2.14.1 torchvision==0.29.1 --index-url https://download.pytorch.org/whl/cpu
.\.venv-visual-search\Scripts\python.exe -m pip install -r app/Services/visual-search-requirements.txt
.\.venv-visual-search\Scripts\python.exe app/Services/visual_search.py
```

Giữ terminal dịch vụ Python mở. Terminal thứ hai chạy Laravel như hướng dẫn cài đặt ở trên. Không cần cấu hình API key. Mặc định URL dịch vụ là `VISUAL_SEARCH_URL=http://127.0.0.1:8020`; nếu đổi cổng Python bằng `--port`, phải đổi biến này tương ứng. Nếu chạy dịch vụ cho một bản cài khác, dùng `--root DUONG_DAN_THU_MUC_LARAVEL` để cùng đọc đúng storage và token của bản đó. Token của hai bản cài khác nhau không dùng chung.

Trọng số lưu ở `~/.cache/smartcart-clip`; có thể đổi bằng biến môi trường Python `VISUAL_SEARCH_MODEL_CACHE`. Dùng đường dẫn ngắn trên Windows. Lần chạy tiếp theo nạp checkpoint đã lưu, không tải lại. Cache vector lưu trong private storage của từng bản cài.

### Cách tìm và cập nhật dữ liệu

Mô hình mã hóa ảnh thành vector, chuẩn hóa L2 rồi tính tích vô hướng giữa các vector (tương đương cosine similarity). Ảnh catalog được băm SHA-256: ảnh mới hoặc thay đổi nội dung sẽ được tính lại ở lượt tìm kế tiếp; sản phẩm đã xóa được loại khỏi tập tìm. Giá, tồn kho và đường dẫn sản phẩm lấy từ dữ liệu Laravel, không lấy từ mô hình. Lần tìm đầu xây chỉ mục nên lâu hơn những lượt sau.

Kết quả tối đa 8 sản phẩm. Ngưỡng ban đầu `VISUAL_SEARCH_MIN_SIMILARITY=0.45`; ngoài ngưỡng tuyệt đối, chỉ giữ kết quả cách điểm cao nhất không quá 0.12 để giảm gợi ý lạc nhóm. Đây là tham số thử nghiệm, **chưa được hiệu chỉnh trên bộ ảnh chụp độc lập** và không bảo đảm loại được mọi ảnh ngoài catalog. Điểm cosine không phải xác suất đúng; giao diện không hiển thị phần trăm độ tin cậy. Không có kết quả đủ gần thì hiển thị thông báo, không thay bằng sản phẩm ngẫu nhiên. Nếu dịch vụ tắt/lỗi, giao diện báo chưa sẵn sàng.

Hiện catalog vẫn có 60 sản phẩm. Kiểm tra ban đầu gồm ảnh catalog được xoay/đổi sáng và một ảnh chụp màn hình không liên quan; các kết quả đó chỉ kiểm tra luồng và khả năng chịu biến đổi đơn giản, không chứng minh độ chính xác trên ảnh camera thực tế. Nhóm cần bổ sung ảnh chụp riêng, ảnh ngoài catalog và nhiều mẫu cùng nhóm trước nghiệm thu tính năng.

### Nguồn và giấy phép

- Thư viện: [OpenCLIP](https://github.com/mlfoundations/open_clip), phiên bản 3.3.0, [giấy phép MIT](https://github.com/mlfoundations/open_clip/blob/main/LICENSE).
- Trọng số: [LAION CLIP ViT-B/32 laion2B-s34B-b79K](https://huggingface.co/laion/CLIP-ViT-B-32-laion2B-s34B-b79K), model card khai báo MIT. Khóa revision `1a25a446712ba5ee05982a381eed697ef9b435cf` để dữ liệu vector không bị trộn giữa các checkpoint.
- Nhóm không huấn luyện mô hình từ đầu. Phần tích hợp của SmartCart gồm kiểm tra ảnh, dịch vụ cục bộ, quản lý chỉ mục/cache, xếp hạng/lọc kết quả, giao diện camera/upload, kết nối giỏ và kiểm thử.
- Nguồn và quyền sử dụng ảnh sản phẩm cần được thành viên chuẩn bị catalog bổ sung riêng; giấy phép mô hình không cấp quyền đối với các ảnh sản phẩm đó.
