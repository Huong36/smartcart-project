# Use Case SmartCart

Các module đã được tích hợp trên integration/admin-cart: Auth/role, CRUD, tìm kiếm/phân trang, giỏ, checkout COD và quản lý đơn. Mức độ nghiệm thu từng chức năng xem TEST_CHECKLIST.md.

## Sơ đồ UML khách vãng lai và khách hàng

![UML khách hàng](diagrams/smartcart-usecase-client.png)

## Sơ đồ UML quản trị viên

![UML quản trị viên](diagrams/smartcart-usecase-admin.png)

Các sơ đồ dùng actor bên ngoài biên hệ thống, use case hình oval và đường liên kết không hướng. Sơ đồ mô tả phạm vi chức năng, không xác nhận chức năng đã lập trình. Các tiền điều kiện auth/role được ghi trong bảng dưới.

Nguồn chỉnh sửa: [khách hàng](diagrams/smartcart-usecase-client.drawio), [quản trị viên](diagrams/smartcart-usecase-admin.drawio). Mở bằng app.diagrams.net hoặc draw.io Desktop. Bản PNG dùng để chèn báo cáo.

| Actor | Use Case chính | Điều kiện |
|---|---|---|
| Guest | Xem/lọc/tìm kiếm sản phẩm, đăng ký, đăng nhập, xem widget | Không cần đăng nhập |
| Customer | Các chức năng xem sản phẩm, tài khoản, giỏ hàng, đặt hàng, lịch sử đơn, đăng xuất | Đặt hàng/lịch sử/tài khoản yêu cầu auth; chỉ truy cập đơn của mình |
| Admin | Tổng quan, CRUD danh mục và sản phẩm, upload ảnh, quản lý trạng thái đơn | auth và role:admin |
| API ngoài | Cung cấp thông tin cho widget | Xử lý timeout/lỗi API |

Checkout bao gồm kiểm tra giỏ hàng, tồn kho, thông tin giao hàng và ghi đơn. Chưa có yêu cầu thanh toán trực tuyến; không đưa cổng thanh toán vào phạm vi bàn giao này.
