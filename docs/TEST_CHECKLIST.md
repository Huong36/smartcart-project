# Checklist kiểm thử tích hợp SmartCart

Người phụ trách tổng hợp: thành viên 1. Trạng thái `Chưa chạy` là ca kiểm thử bàn giao cho giai đoạn tích hợp, không phải kết quả đã đạt.

## Chuẩn bị

- Clone commit cần kiểm tra vào thư mục mới; ghi mã commit, ngày, máy, PHP/MySQL và người kiểm tra.
- Chấp nhận lời mời cộng tác; từng thành viên dùng tài khoản Git riêng.
- Tạo database thử riêng, cấu hình `.env`, chạy `composer install`, `key:generate`, `migrate --seed`, `storage:link`.
- Chạy `php artisan test`. Bản đầy đủ ae4649a có 79 test, 636 assertions đạt trên SQLite và MySQL. Bản nghiệm thu 316a2ac chỉ bổ sung sửa layout mobile Admin. Bộ test không thay thế nghiệm thu UI.
- Dữ liệu chức năng: 8 danh mục, 60 sản phẩm; 2 Customer riêng biệt để thử phân quyền đơn; sản phẩm có tồn kho 0, 1 và 20; ảnh hợp lệ/sai định dạng; đơn ở các trạng thái.
- Không chạy `migrate:fresh` hoặc RefreshDatabase trên database có dữ liệu cần giữ. MySQL test tự động dùng riêng `smartcart_test`.

## Ca kiểm thử

| ID | Phụ trách | Thao tác và dữ liệu | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| SET-01 | 1 | Clone trên máy thành viên, làm theo README | Cài được bằng composer.lock, mở trang chủ; không thiếu file/khóa ứng dụng | Cài sạch cùng máy đạt; máy thứ hai chờ xác nhận |
| SET-02 | 1 | migrate --seed rồi db:seed lần nữa | 2 tài khoản demo, 8 danh mục, 60 sản phẩm; không trùng, không đổi mật khẩu hiện có | Đạt: seed hai lần; 2 user, 8 danh mục, 60 sản phẩm; test không reset dữ liệu |
| AUTH-01 | 1 | Đăng ký dữ liệu hợp lệ, gửi thêm role=admin | Tài khoản được hash mật khẩu, email chuẩn hóa, role luôn customer | Đạt test tự động |
| AUTH-02 | 1 | Email trùng/không hợp lệ, mật khẩu ngắn/xác nhận sai, phone sai | Lỗi validation; không tạo user mới; không giữ mật khẩu trong form | Đạt test tự động |
| AUTH-03 | 1 | Đăng nhập Admin và Customer | Chuyển /admin hoặc /account; session được tái tạo | Đạt test và browser 18/10 |
| AUTH-04 | 1 | Sai mật khẩu 5 lần rồi gửi lần thứ 6 | Không đăng nhập, tạm chặn theo email và IP | Đạt test tự động |
| AUTH-05 | 1 | Guest vào /admin; Customer vào /admin | Guest chuyển đến login; Customer nhận 403 | Đạt test tự động; UI trước tích hợp đã kiểm tra |
| AUTH-06 | 1 | Logout bằng POST; truy cập lại account | Session đăng nhập bị hủy, account yêu cầu login; GET logout không được phép | Đạt test và browser 18/10 |
| SEC-01 | 1 | Gửi POST login/logout/checkout/cart thiếu token CSRF bằng trình duyệt/HTTP thực | Không thay đổi dữ liệu; trả 419 khi thiếu token hợp lệ và không có tín hiệu origin được chấp nhận | Đạt ca HTTP thiếu CSRF: POST login/cart-add/checkout đều 419; logout chưa chạy ca âm |
| CAT-01 | 2 | Thêm, xem, sửa, xóa danh mục trống | Lưu đúng, slug unique, có thông báo kết quả | Đạt test tự động; chưa chạy lại toàn bộ CRUD bằng UI |
| CAT-02 | 2 | Xóa danh mục còn sản phẩm | Bị chặn; sản phẩm và lịch sử đơn giữ nguyên | Đạt test/khóa ngoại; chưa chạy lại UI |
| PRO-01 | 2 | CRUD sản phẩm với danh mục hợp lệ | Liên kết đúng danh mục, dữ liệu hiển thị chính xác | Đạt test tự động; chưa chạy lại toàn bộ CRUD bằng UI |
| PRO-02 | 2 | Giá <= 0, tồn kho âm, category_id không có, tên trống | Validation chặn; dữ liệu cũ giữ nguyên | Đạt test validation hiện có; xem bộ AdminProductTest |
| PRO-03 | 2 | Upload jpeg/png/webp và tệp giả ảnh/quá dung lượng | Chỉ nhận ảnh được xác minh trong giới hạn; lưu public disk, ảnh hiển thị sau clone/setup | Đạt ca upload/validation có trong test; các định dạng và lỗi UI cần kiểm tra tiếp |
| PRO-04 | 2 | Sửa/thay ảnh rồi xóa sản phẩm chưa có đơn | Không mất ảnh đang dùng; xử lý ảnh cũ theo quy tắc đã thống nhất | Có test tự động; UI thay ảnh/xóa chưa nghiệm thu lại |
| PRO-05 | 2 | Xóa sản phẩm đã nằm trong order_items | Bị chặn với thông báo rõ ràng | Đạt test/khóa ngoại; chưa chạy lại UI |
| LIST-01 | 3 | Lọc danh mục, tìm từ khóa rồi sang trang 2 | 8 sản phẩm/trang; giữ category/query; kết quả chính xác | Đạt test 8 sản phẩm/trang, giữ filter/sort; browser q=0 đạt |
| LIST-02 | 3 | Không có kết quả, category/product không tồn tại | Có trạng thái rỗng hoặc 404; không lỗi 500 | Đạt test rỗng/404; browser q=0 không kết quả đạt |
| CART-01 | 4 | Thêm, tăng/giảm, xóa bằng Fetch | Không reload; badge, số lượng, tổng tiền đồng bộ với server | Đạt test; UI thêm/tăng số lượng/tổng tiền đạt; UI xóa chưa chạy lại |
| CART-02 | 4 | quantity 0/âm/chữ/vượt tồn kho, product_id giả | Chặn và trả JSON phù hợp; giỏ cũ còn nguyên | Có test hiện có; cần bổ sung ma trận dữ liệu âm đầy đủ trước nghiệm thu cuối |
| CART-03 | 4 | Giỏ có sản phẩm vừa bị xóa hoặc hết hàng | Hiển thị lỗi rõ ràng; không đặt sản phẩm không hợp lệ | Đạt checkout từ chối sản phẩm mất/hết kho trong test; UI chưa chạy đủ |
| ORD-01 | 3 | Checkout giỏ hợp lệ, có địa chỉ/phone | Tạo orders + items trong transaction, snapshot giá/tên, trừ kho, xóa giỏ sau thành công | Đạt test và browser COD mobile; SQL xác nhận giá/kho/đơn |
| ORD-02 | 3 | Sửa total_amount/price ở request trình duyệt | Server tính từ giá DB; không tin tổng/giá do khách gửi | Đạt test bỏ qua giá/tổng/user/status client giả |
| ORD-03 | 3 | Giỏ rỗng, thiếu thông tin, lỗi DB giữa checkout | Không tạo đơn dở dang; rollback kho/items, không mất giỏ khi thất bại | Đạt test validation/rollback; browser checkout hợp lệ đạt |
| ORD-04 | 3 | Hai khách đồng thời mua sản phẩm còn 1 | Chỉ một yêu cầu thành công; không âm kho | Đạt ca hai tiến trình MySQL trong review 4e065fa; chưa thay đổi CheckoutController ở ae4649a |
| ORD-05 | 3 | Customer B đổi ID sang đơn của Customer A | Nhận 403/404; không lộ thông tin đơn hoặc sửa đơn khác | Đạt test ownership và role |
| ORD-06 | 3 | Admin chuyển pending → processing → completed; thử bước ngược | Chỉ cho phép chuyển trạng thái hợp lệ; doanh thu chỉ tính completed | Đạt test và browser; SQL doanh thu completed = 190000 |
| ORD-07 | 3 | Hủy đơn rồi hủy lại; thử hủy completed | Theo quy tắc đã thống nhất: không hoàn kho hai lần, không hủy trạng thái bị cấm | Đạt test và browser Admin hủy processing; hai ca cạnh tranh MySQL đạt ở ae4649a |
| SRCH-01 | 4 | Gõ nhanh nhiều từ khóa, xóa query | Debounce/giới hạn kết quả; response cũ không đè kết quả mới; xóa query đóng gợi ý | Đạt browser mobile gợi ý và xóa xuống 1 ký tự; race response cũ đã kiểm tra JS cô lập ở 9ea4496 |
| API-01 | 4 | API ngoài thành công, timeout và lỗi | Hiển thị dữ liệu hợp lệ hoặc thông báo lỗi; trang vẫn dùng được; khóa bí mật ở server | Browser nhận thời tiết thành công; lỗi/timeout chưa nghiệm thu bằng browser |
| SEC-02 | 1–4 | Nhập <script> vào tên/description/note | Blade escape hoặc sanitize; không chạy script trong trang quản trị/khách | Đạt các ca escape có trong test; chưa chạy toàn bộ ma trận nhập liệu bằng browser |
| UI-01 | 1–4 | Màn hình khoảng 375 px, 768 px và desktop; menu/form/table | Không tràn ngang; menu hoạt động; nút dễ bấm, lỗi gắn với đúng trường | 375/768/1440 px đã chạy các màn hình trong biên bản; chưa phải mọi trang/điện thoại thật |
| DEMO-01 | 1–4 | Customer đăng ký → mua → xem đơn; Admin xử lý → Customer xem lại | Luồng hoàn chỉnh, trạng thái/giá/kho nhất quán, không lỗi console/500 | Đạt browser khách mua → Admin hoàn thành/hủy → khách xem lại; SQL khớp |

## Ghi kết quả

Mỗi lần chạy ghi `ID | commit | người kiểm tra | ngày | Pass/Fail/Blocked | kết quả thực tế | ảnh/log | issue`. Pass chỉ khi đã quan sát kết quả đúng. Các lỗi chặn demo phải được sửa và chạy lại trước khi merge.

## Quy tắc đơn hàng đã tích hợp

- Customer hủy đơn của mình ở pending; Admin hủy pending/processing. Cấm hủy completed, hủy lặp không hoàn kho thêm. Transaction rollback cả trạng thái và tồn kho khi lỗi.
- Checkout dùng UUID checkout_token unique; gửi lặp không tạo đơn/trừ kho lần hai. Có test tuần tự và review hai tiến trình MySQL.
- Giỏ guest có thể lưu Session; checkout và lịch sử đơn bắt buộc đăng nhập. Nhóm quyết định việc giữ giỏ khi đăng nhập/đăng xuất vì logout hiện hủy session.

## Biên bản ngày 18/10/2026

Mã nghiệp vụ 316a2ac = ae4649a + sửa header mobile Admin. Kiểm tra trong bản clone sạch từ GitHub trên cùng máy trưởng nhóm; database ứng dụng và database test tách biệt. PHP 8.3.33, Laravel 13.35.0, MySQL 8.4.3.

- Composer install: 115 gói từ lock, check-platform-reqs đạt; không copy vendor/.env.
- Key, 9 migration, seed hai lần, storage link đạt; 8 danh mục, 60 sản phẩm.
- SQLite: 79/79, 636 assertions; MySQL test riêng: 79/79, 636 assertions.
- Đơn 1 gồm 2 sản phẩm × 95000, completed = 190000. Đơn 2 gồm 1 × 95000, cancelled. Stock 35 → 33 → 32 → 33. Doanh thu completed 190000.
- Viewport 375×812: checkout/chi tiết đơn, live search/menu khách, danh sách/menu Admin. Header Admin trước sửa bị tràn, sau sửa pageWidth=375. Bảng Admin cuộn ngang trong khung.
- Viewport 768×1024: bộ lọc q=0 trả rỗng đúng; pageWidth=768. Viewport 1440×900: giỏ và xử lý đơn.
- POST /login, /cart/add, /checkout không CSRF trả 419.
- Ảnh bằng chứng: docs/demo; chi tiết trong docs/report/Bao_Cao_Nghiem_Thu.md.
- Máy vật lý thứ hai: CHỜ thành viên thực hiện INSTALL_ACCEPTANCE.md và gửi biên bản. Chưa gộp main.
- Các mục chỉ đạt một phần hoặc chưa chạy giữ đúng trạng thái trong bảng, không quy đổi thành Pass toàn bộ.
