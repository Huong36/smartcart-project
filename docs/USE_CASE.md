# Use Case SmartCart

Phạm vi bên dưới mô tả toàn bộ đề tài dự kiến. Bộ khung hiện đã triển khai đăng ký, đăng nhập/đăng xuất, tài khoản, phân quyền và tổng quan Admin. Các chức năng mua hàng/CRUD còn lại thuộc các thành viên 2–4.

```mermaid
flowchart LR
    G[Khách vãng lai]
    C[Khách hàng]
    A[Quản trị viên]
    API[API tỷ giá hoặc thời tiết]
    subgraph SmartCart
        Browse([Xem danh sách và chi tiết sản phẩm])
        Search([Lọc, tìm kiếm và phân trang])
        Register([Đăng ký tài khoản])
        Login([Đăng nhập và đăng xuất])
        Profile([Xem tài khoản])
        Cart([Thêm, sửa, xóa giỏ hàng])
        Checkout([Đặt hàng])
        History([Xem đơn hàng cá nhân])
        Dashboard([Xem tổng quan cửa hàng])
        Categories([CRUD danh mục])
        Products([CRUD sản phẩm và upload ảnh])
        Orders([Duyệt và cập nhật trạng thái đơn])
        Widget([Xem tiện ích API])
    end
    G --- Browse & Search & Register & Login & Widget
    C --- Browse & Search & Login & Profile & Cart & Checkout & History & Widget
    A --- Login & Profile & Dashboard & Categories & Products & Orders
    API --- Widget
```

Sơ đồ Mermaid là nguồn tham khảo quan hệ actor–chức năng. Khi viết báo cáo, dùng bảng dưới để dựng sơ đồ UML Use Case chính thức với actor, biên hệ thống và oval trong draw.io; flowchart trên không thể hiện đầy đủ ký pháp UML.

| Actor | Use Case chính | Điều kiện |
|---|---|---|
| Guest | Xem/lọc/tìm kiếm sản phẩm, đăng ký, đăng nhập, xem widget | Không cần đăng nhập |
| Customer | Các chức năng xem sản phẩm, tài khoản, giỏ hàng, đặt hàng, lịch sử đơn, đăng xuất | Đặt hàng/lịch sử/tài khoản yêu cầu auth; chỉ truy cập đơn của mình |
| Admin | Tổng quan, CRUD danh mục và sản phẩm, upload ảnh, quản lý trạng thái đơn | auth và role:admin |
| API ngoài | Cung cấp thông tin cho widget | Xử lý timeout/lỗi API |

Checkout bao gồm kiểm tra giỏ hàng, tồn kho, thông tin giao hàng và ghi đơn. Chưa có yêu cầu thanh toán trực tuyến; không đưa cổng thanh toán vào phạm vi bàn giao này.
