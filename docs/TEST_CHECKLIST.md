# Checklist kiểm thử tích hợp SmartCart

Người phụ trách tổng hợp: thành viên 1. Trạng thái `Chưa chạy` là ca kiểm thử bàn giao cho giai đoạn tích hợp, không phải kết quả đã đạt.

## Chuẩn bị

- Clone commit cần kiểm tra vào thư mục mới; ghi mã commit, ngày, máy, PHP/MySQL và người kiểm tra.
- Chấp nhận lời mời cộng tác; từng thành viên dùng tài khoản Git riêng.
- Tạo database thử riêng, cấu hình `.env`, chạy `composer install`, `key:generate`, `migrate --seed`, `storage:link`.
- Chạy `php artisan test`. Bộ khung ngày 13/10/2026 đã có 16 test qua trên SQLite và MySQL; chỉ chứng minh phạm vi nền tảng hiện có.
- Dữ liệu chức năng: 5 danh mục, 30 sản phẩm; 2 Customer riêng biệt để thử phân quyền đơn; sản phẩm có tồn kho 0, 1 và 20; ảnh hợp lệ/sai định dạng; đơn ở các trạng thái.
- Không chạy `migrate:fresh` hoặc RefreshDatabase trên database có dữ liệu cần giữ. MySQL test tự động dùng riêng `smartcart_test`.

## Ca kiểm thử

| ID | Phụ trách | Thao tác và dữ liệu | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| SET-01 | 1 | Clone trên máy thành viên, làm theo README | Cài được bằng composer.lock, mở trang chủ; không thiếu file/khóa ứng dụng | Chưa chạy trên máy khác |
| SET-02 | 1 | migrate --seed rồi db:seed lần nữa | 2 tài khoản demo, 5 danh mục, 30 sản phẩm; không trùng, không đổi mật khẩu hiện có | Đã qua test nền tảng |
| AUTH-01 | 1 | Đăng ký dữ liệu hợp lệ, gửi thêm role=admin | Tài khoản được hash mật khẩu, email chuẩn hóa, role luôn customer | Đã qua test nền tảng |
| AUTH-02 | 1 | Email trùng/không hợp lệ, mật khẩu ngắn/xác nhận sai, phone sai | Lỗi validation; không tạo user mới; không giữ mật khẩu trong form | Đã qua test nền tảng |
| AUTH-03 | 1 | Đăng nhập Admin và Customer | Chuyển /admin hoặc /account; session được tái tạo | Đã qua test nền tảng và trình duyệt |
| AUTH-04 | 1 | Sai mật khẩu 5 lần rồi gửi lần thứ 6 | Không đăng nhập, tạm chặn theo email và IP | Đã qua test nền tảng |
| AUTH-05 | 1 | Guest vào /admin; Customer vào /admin | Guest chuyển đến login; Customer nhận 403 | Đã qua test nền tảng và trình duyệt |
| AUTH-06 | 1 | Logout bằng POST; truy cập lại account | Session đăng nhập bị hủy, account yêu cầu login; GET logout không được phép | Đã qua test nền tảng và trình duyệt |
| SEC-01 | 1 | Gửi POST login/logout/checkout/cart thiếu token CSRF bằng trình duyệt/HTTP thực | Không thay đổi dữ liệu; trả 419 khi thiếu token hợp lệ và không có tín hiệu origin được chấp nhận | Chưa chạy ca âm |
| CAT-01 | 2 | Thêm, xem, sửa, xóa danh mục trống | Lưu đúng, slug unique, có thông báo kết quả | Chưa chạy |
| CAT-02 | 2 | Xóa danh mục còn sản phẩm | Bị chặn; sản phẩm và lịch sử đơn giữ nguyên | Khóa ngoại đã qua test; UI chưa chạy |
| PRO-01 | 2 | CRUD sản phẩm với danh mục hợp lệ | Liên kết đúng danh mục, dữ liệu hiển thị chính xác | Chưa chạy |
| PRO-02 | 2 | Giá <= 0, tồn kho âm, category_id không có, tên trống | Validation chặn; dữ liệu cũ giữ nguyên | Chưa chạy |
| PRO-03 | 2 | Upload jpeg/png/webp và tệp giả ảnh/quá dung lượng | Chỉ nhận ảnh được xác minh trong giới hạn; lưu public disk, ảnh hiển thị sau clone/setup | Chưa chạy |
| PRO-04 | 2 | Sửa/thay ảnh rồi xóa sản phẩm chưa có đơn | Không mất ảnh đang dùng; xử lý ảnh cũ theo quy tắc đã thống nhất | Chưa chạy |
| PRO-05 | 2 | Xóa sản phẩm đã nằm trong order_items | Bị chặn với thông báo rõ ràng | Khóa ngoại đã qua test; UI chưa chạy |
| LIST-01 | 3 | Lọc danh mục, tìm từ khóa rồi sang trang 2 | 8 sản phẩm/trang; giữ category/query; kết quả chính xác | Chưa chạy |
| LIST-02 | 3 | Không có kết quả, category/product không tồn tại | Có trạng thái rỗng hoặc 404; không lỗi 500 | Chưa chạy |
| CART-01 | 4 | Thêm, tăng/giảm, xóa bằng Fetch | Không reload; badge, số lượng, tổng tiền đồng bộ với server | Chưa chạy |
| CART-02 | 4 | quantity 0/âm/chữ/vượt tồn kho, product_id giả | Chặn và trả JSON phù hợp; giỏ cũ còn nguyên | Chưa chạy |
| CART-03 | 4 | Giỏ có sản phẩm vừa bị xóa hoặc hết hàng | Hiển thị lỗi rõ ràng; không đặt sản phẩm không hợp lệ | Chưa chạy |
| ORD-01 | 3 | Checkout giỏ hợp lệ, có địa chỉ/phone | Tạo orders + items trong transaction, snapshot giá/tên, trừ kho, xóa giỏ sau thành công | Chưa chạy |
| ORD-02 | 3 | Sửa total_amount/price ở request trình duyệt | Server tính từ giá DB; không tin tổng/giá do khách gửi | Chưa chạy |
| ORD-03 | 3 | Giỏ rỗng, thiếu thông tin, lỗi DB giữa checkout | Không tạo đơn dở dang; rollback kho/items, không mất giỏ khi thất bại | Chưa chạy |
| ORD-04 | 3 | Hai khách đồng thời mua sản phẩm còn 1 | Chỉ một yêu cầu thành công; không âm kho | Chưa chạy |
| ORD-05 | 3 | Customer B đổi ID sang đơn của Customer A | Nhận 403/404; không lộ thông tin đơn hoặc sửa đơn khác | Chưa chạy |
| ORD-06 | 3 | Admin chuyển pending → processing → completed; thử bước ngược | Chỉ cho phép chuyển trạng thái hợp lệ; doanh thu chỉ tính completed | Chưa chạy |
| ORD-07 | 3 | Hủy đơn rồi hủy lại; thử hủy completed | Theo quy tắc đã thống nhất: không hoàn kho hai lần, không hủy trạng thái bị cấm | Chưa chạy |
| SRCH-01 | 4 | Gõ nhanh nhiều từ khóa, xóa query | Debounce/giới hạn kết quả; response cũ không đè kết quả mới; xóa query đóng gợi ý | Chưa chạy |
| API-01 | 4 | API ngoài thành công, timeout và lỗi | Hiển thị dữ liệu hợp lệ hoặc thông báo lỗi; trang vẫn dùng được; khóa bí mật ở server | Chưa chạy |
| SEC-02 | 1–4 | Nhập <script> vào tên/description/note | Blade escape hoặc sanitize; không chạy script trong trang quản trị/khách | Chưa chạy |
| UI-01 | 1–4 | Màn hình khoảng 375 px, 768 px và desktop; menu/form/table | Không tràn ngang; menu hoạt động; nút dễ bấm, lỗi gắn với đúng trường | Chưa chạy toàn hệ thống |
| DEMO-01 | 1–4 | Customer đăng ký → mua → xem đơn; Admin xử lý → Customer xem lại | Luồng hoàn chỉnh, trạng thái/giá/kho nhất quán, không lỗi console/500 | Chưa chạy |

## Ghi kết quả

Mỗi lần chạy ghi `ID | commit | người kiểm tra | ngày | Pass/Fail/Blocked | kết quả thực tế | ảnh/log | issue`. Pass chỉ khi đã quan sát kết quả đúng. Các lỗi chặn demo phải được sửa và chạy lại trước khi merge.

## Quyết định cần thống nhất trước phần đơn hàng

- Đề xuất: cho hủy pending/processing, hoàn kho đúng một lần trong transaction; cấm hủy completed. Cần nhóm xác nhận quy tắc trước triển khai/test ORD-07.
- Checkout cần chống gửi lặp cùng một giao dịch (ví dụ token idempotency); bổ sung ca kiểm thử khi thiết kế cơ chế được thống nhất.
- Giỏ guest có thể lưu Session; checkout và lịch sử đơn bắt buộc đăng nhập. Nhóm quyết định việc giữ giỏ khi đăng nhập/đăng xuất vì logout hiện hủy session.
