# Phân công và quy ước phối hợp

Bộ khung do thành viên 1 chuẩn bị gồm Laravel 13, MySQL, model và migration, seeder, Auth, middleware phân quyền, layout Bootstrap, trang tài khoản và tổng quan Admin.

| Thành viên | GitHub | Branch gợi ý | Công việc tiếp theo |
|---|---|---|---|
| 1 — Trần Xuân Hướng | Huong36 | feat/setup-auth | Nền tảng, tích hợp, ERD, Use Case, báo cáo |
| 2 | dle1102 | feat/admin-products | CRUD danh mục/sản phẩm, upload ảnh, Form Request, hoàn thiện dữ liệu mẫu |
| 3 | nguyenxuanphuong | feat/client-orders | Trang chủ, chi tiết, bộ lọc, tìm kiếm/phân trang, checkout, lịch sử đơn và quản lý đơn Admin |
| 4 | nongvanminh | feat/cart-search | Cart Session + Fetch API, live search, SweetAlert2, widget API bên ngoài |

Phần đơn hàng Admin thuộc thành viên 3 theo bảng phân công. Thành viên 2 hỗ trợ nếu nhóm thống nhất. Không chỉnh lại những migration đã đưa lên main; thêm migration mới khi cần thay đổi schema.

## Tạo nhánh và gửi Pull Request

Mỗi người clone từ main, tự tạo nhánh của mình (thay tên nhánh cho đúng thành viên):

```bash
git clone https://github.com/Huong36/smartcart-project.git
cd smartcart-project
git switch -c feat/admin-products
```

Sau khi hoàn thành một phần:

```bash
git add app resources routes database tests
git commit -m "feat: implement category CRUD"
git push -u origin feat/admin-products
```

Mở Pull Request vào main, ghi chức năng đã làm và cách kiểm tra. Trưởng nhóm kiểm tra rồi merge. Mỗi người commit bằng tài khoản của chính mình. Lấy code mới trước khi tiếp tục:

```bash
git fetch origin
git merge origin/main
```

## Điểm tích hợp đã có

- Layout chung: `resources/views/layouts/app.blade.php`; có `@stack('styles')`, `@stack('scripts')` và meta `csrf-token`.
- Route admin: thêm trong group `prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])` của `routes/web.php`.
- Customer được lưu với role `customer`; guest là người chưa đăng nhập, không phải một role trong DB.
- Model: `Category::products()`, `Product::category()`, `Product::orderItems()`, `User::orders()`, `Order::user()`, `Order::items()`, `OrderItem::order()`, `OrderItem::product()`.
- Giữ ảnh sản phẩm là đường dẫn tương đối trên public disk, ví dụ `products/abc.webp`. `Product::image_url` trả URL hiển thị; `null` dùng ảnh placeholder. Chạy `php artisan storage:link` để phục vụ ảnh upload.
- Tất cả form có `@csrf`. Fetch gửi header `X-CSRF-TOKEN` lấy từ meta và `Accept: application/json` để nhận validation JSON. Không dùng GET cho thao tác thay đổi dữ liệu.
- Bootstrap 5.3.3 được lưu trong `public/vendor/bootstrap`, không cần mạng hay npm để mở giao diện. Pagination mặc định dùng Bootstrap 5.

## Quy tắc nghiệp vụ cần giữ khi triển khai

- Danh mục còn sản phẩm không được xóa. Sản phẩm đã có trong đơn hàng không được xóa; thông báo rõ lý do cho Admin. Dùng `exists()` kiểm tra trước và xử lý lỗi khóa ngoại.
- Giá dùng `decimal(12,2)`, tiền VND, không dùng float để tính tổng thanh toán. Tồn kho có thể bằng 0; giá > 0, số lượng mua là số nguyên >= 1.
- `order_items.price` và `product_name` là giá/tên tại thời điểm mua; không lấy giá sản phẩm hiện tại để hiển thị đơn cũ.
- Status thống nhất: `pending` → `processing` → `completed`; `cancelled` là đã hủy. Nhãn tiếng Việt nằm ở `Order::STATUS_LABELS`.
- Checkout đọc giá từ DB, kiểm tra tồn kho, dùng transaction và khóa hàng (`lockForUpdate()`), trừ tồn kho và tạo order/items trong cùng transaction. Không tin tổng tiền do trình duyệt gửi.
- Lịch sử/chi tiết đơn khách phải truy vấn qua `$request->user()->orders()` để ngăn xem đơn người khác. Các thao tác Admin nằm trong group bảo vệ.
- Trang danh sách dùng `with('category')`, `paginate(8)->withQueryString()`. Live search giới hạn số kết quả, tránh trả toàn bộ DB.
- Seeder đã có 5 danh mục và 30 sản phẩm; chạy lại không tạo trùng. Thành viên 2 tiếp tục hoàn thiện ảnh thật và dữ liệu trong `CatalogSeeder`.

## Trạng thái bàn giao

- [x] Nền tảng, CSDL, Auth/role, dữ liệu mẫu, giao diện nền, tổng quan Admin.
- [x] Hướng dẫn setup, quy ước nhóm, ERD/UML dạng ảnh và draw.io, khung báo cáo Word và checklist kiểm thử tích hợp.
- [ ] CRUD danh mục/sản phẩm, upload ảnh và validation tương ứng.
- [ ] Chi tiết sản phẩm, search/filter/phân trang hoàn chỉnh.
- [ ] Cart, checkout, lịch sử/duyệt đơn, live search và API ngoài.
- [ ] Báo cáo Word/PDF cuối cùng và chạy thử trên máy các thành viên.
