# Kịch bản thuyết trình và demo SmartCart

Thời lượng đề xuất: 12 phút trình bày/demo và 3 phút hỏi đáp. Điều chỉnh theo thời gian giảng viên cho phép. Trình diễn bằng dữ liệu mẫu trên máy đã chạy thử; không thực hiện giao dịch thật.

## Chuẩn bị trước buổi báo cáo

- Dùng nhánh integration/admin-cart có tag demo-2026-10-18; sau khi được gộp mới đổi sang main.
- Cài và kiểm tra từ hôm trước theo INSTALL_ACCEPTANCE.md; mở MySQL và server trước giờ báo cáo. Không cài thư viện hoặc migrate:fresh ngay trong buổi thuyết trình.
- Có một cửa sổ trình duyệt thường cho Customer và một cửa sổ riêng tư cho Admin để hai session không đổi đăng nhập của nhau. Hai tab cùng hồ sơ thường chia sẻ session.
- Tạo danh mục và sản phẩm demo dễ tìm, ví dụ “Demo nghiệm thu”, giá 100.000 đồng, tồn kho 10, có ảnh. Chuẩn bị tệp PNG/JPEG hợp lệ để upload.
- Chuẩn bị ERD, Use Case, GitHub lịch sử commit và kết quả test. Ảnh dự phòng nằm ở docs/demo; không trình bày ảnh dự phòng như thao tác trực tiếp.
- Tất cả thành viên hiểu phần code của mình. Điền lớp, giảng viên và MSSV trong báo cáo trước khi nộp.

## Phân vai và lời dẫn

| Thời gian | Người | Nội dung và thao tác |
|---|---|---|
| 0:00–1:30 | Thành viên 1 | Mục tiêu, công nghệ, hai role và phạm vi COD; giới thiệu ERD 5 bảng |
| 1:30–3:30 | Thành viên 2 | CRUD danh mục/sản phẩm, upload, validation và ràng buộc xóa |
| 3:30–5:30 | Thành viên 4 | Live search, giỏ Fetch, số lượng/tổng tiền và widget ngoài |
| 5:30–9:00 | Thành viên 3 | Lọc/phân trang, chi tiết, checkout, lịch sử đơn và Admin xử lý |
| 9:00–10:30 | Thành viên 3, số 2 hỗ trợ | Đơn thứ hai để hủy; đối chiếu tồn kho và doanh thu |
| 10:30–12:00 | Thành viên 1 | Phân quyền, kết quả test, Git, hạn chế và công việc còn mở |

Lời mở đầu gợi ý: “Nhóm em xây dựng SmartCart, một website bán hàng phục vụ học tập bằng Laravel và MySQL. Khách có thể tìm sản phẩm, quản lý giỏ, đặt hàng COD và theo dõi đơn; Admin quản lý danh mục, sản phẩm và xử lý đơn. Nhóm tập trung vào tính nhất quán của giá, tồn kho và quyền truy cập.”

Số 1 giải thích quan hệ: categories–products là 1–n; users–orders là 1–n; orders–order_items là 1–n. Order item giữ giá và tên lúc mua để thay đổi sản phẩm không sửa lịch sử.

Số 2: “Validation chạy ở server. Giá phải dương, stock được bằng 0 để biểu diễn hết hàng. Danh mục đang có sản phẩm và sản phẩm có lịch sử mua không được xóa.” Demo một lỗi nhập dữ liệu, sửa lại hợp lệ; không cố xóa dữ liệu đã dùng trong đơn.

Số 4: “Các thao tác giỏ dùng Fetch để cập nhật badge và tổng tiền mà không tải lại trang. Server kiểm tra tồn kho, giao diện dùng kết quả server trả về.” Gõ từ khóa, xóa xuống một ký tự để đóng gợi ý; thử tăng/giảm số lượng. API thời tiết chỉ là tiện ích, không quyết định khả năng đặt hàng.

Số 3: “Checkout lấy giá từ database, tính tổng ở server, khóa tồn kho và ghi đơn/chi tiết/trừ kho trong cùng transaction. Token chống gửi lặp. Khách chỉ xem và hủy đơn của chính mình theo trạng thái được phép.”

## Luồng demo có số liệu cố định

Dùng sản phẩm demo giá 100.000 đồng, tồn kho ban đầu 10. Chụp/ghi lại tồn kho trước khi đặt để đối chiếu. Nếu đã tập dượt trước, đưa dữ liệu demo về đúng số liệu bằng form Admin trước buổi báo cáo, không chạy seeder reset toàn bộ.

| Bước | Thao tác | Kết quả cần chỉ cho giảng viên |
|---|---|---|
| 1 | Customer tìm sản phẩm, mở chi tiết, thêm 2 đơn vị | Giỏ 2, tổng 200.000 đồng; stock chưa trừ chỉ vì thêm giỏ |
| 2 | Checkout bằng thông tin giao hàng giả | Đơn A pending, giá 200.000; giỏ rỗng; stock 8 |
| 3 | Admin mở A, chuyển processing → completed | Customer thấy Hoàn thành; doanh thu tăng 200.000; stock vẫn 8 |
| 4 | Customer đặt đơn B gồm 1 đơn vị | B pending, 100.000; stock 7 |
| 5 | Admin chuyển B sang processing rồi hủy | B cancelled, stock trở lại 8; doanh thu vẫn chỉ 200.000 |
| 6 | Customer xem lịch sử | Thấy đúng A hoàn thành và B đã hủy; không được hủy A |
| 7 | Customer truy cập /admin | 403; các route Admin không chỉ bị ẩn trên menu |

Không đọc cứng ID đơn trong lời dẫn; dùng ID vừa được tạo. Không nhấn chuyển trạng thái hai lần do sốt ruột. Nếu cần minh họa hủy lặp hoặc tranh mua đồng thời, mở kết quả test thay vì cố tạo cuộc đua bằng hai cú nhấp không kiểm soát.

## Câu hỏi thường gặp

| Câu hỏi | Ý trả lời cần nắm |
|---|---|
| Tại sao chọn Laravel? | Có routing, middleware, validation, Eloquent, migration và transaction; cả nhóm dùng chung cấu trúc |
| Có thu tiền thật không? | Chưa; COD phục vụ mô phỏng nghiệp vụ, không tích hợp VNPay/MoMo |
| Sửa giá trên trình duyệt có được không? | Checkout lấy giá DB, bỏ qua tổng/giá do client gửi |
| Hai khách cùng mua món cuối? | Transaction và khóa hàng; ca hai tiến trình MySQL đã được kiểm tra trong review |
| Gửi đơn hai lần? | checkout_token duy nhất; gửi lại không tạo đơn hoặc trừ kho lần hai |
| Hủy nhiều lần? | Khóa và đọc lại trạng thái đơn; cancelled không hoàn kho thêm |
| Vì sao Customer không xem đơn người khác? | Truy vấn thông qua quan hệ orders của user hiện tại; ID không thuộc user trả 404 |
| Đổi tên/giá sản phẩm thì đơn cũ thế nào? | order_items giữ snapshot product_name và price |
| Vì sao không cascade xóa danh mục? | Bảo vệ sản phẩm và lịch sử; báo lỗi rõ khi còn dữ liệu liên quan |
| Bao nhiêu test? | Bản demo mới có 88 test, 676 assertions đạt trên SQLite; bản nghiệp vụ trước có 79 test đạt trên MySQL. Không đồng nghĩa mọi ca UI đã tự động hóa |
| Cài được máy khác chưa? | Trả lời theo biên bản thực tế; hiện đã cài sạch cùng máy, máy thứ hai đang chờ xác nhận |

## Kết thúc và xử lý sự cố

Lời kết gợi ý: “Nhóm đã tích hợp các module, kiểm tra backend và luồng đặt hàng/hoàn thành/hủy trên trình duyệt. Các hạn chế hiện tại là chưa có thanh toán trực tuyến, chưa triển khai kinh doanh thật và còn các bước nghiệm thu được ghi riêng. Nhóm phân công theo module và lưu lịch sử đóng góp trên GitHub.”

Nếu mất mạng, bỏ qua widget và tiếp tục CRUD/cart/order chạy local; nếu server dừng, bật lại server trên đúng thư mục và cổng. Nếu gặp lỗi không xử lý nhanh được, chỉ rõ lỗi, mở ảnh đã chuẩn bị và mô tả kết quả đã kiểm chứng. Không che lỗi hoặc nói ảnh là demo trực tiếp.


## Đoạn demo tìm bằng ảnh khoảng 90 giây

Thành viên 1 trình bày sau phần tìm kiếm của thành viên 4; rút gọn thao tác CRUD để giữ tổng thời gian theo quy định của giảng viên.

1. Mở dịch vụ Python và Laravel trước buổi báo cáo; thử ảnh trước để xây cache.
2. Bấm nút cam “Tìm bằng ảnh”, chọn ảnh chụp tai nghe hoặc bàn phím khác ảnh catalog. Nếu dùng camera, kiểm tra quyền và thiết bị trước giờ trình bày.
3. Bấm tìm, chỉ ra sản phẩm phù hợp và thêm vào giỏ; tiếp tục luồng checkout đã có.
4. Nói rõ: “Nhóm dùng mô hình OpenCLIP đã huấn luyện sẵn, mã hóa ảnh thành vector rồi so sánh cosine với ảnh sản phẩm. Nhóm xây phần kết nối Laravel, đồng bộ chỉ mục khi ảnh thay đổi, lọc kết quả và giao diện chụp/chọn ảnh.”
5. Nếu được hỏi tại sao chỉ có 1–2 kết quả: catalog hiện 60 sản phẩm, ít mẫu cùng loại; hệ thống còn lọc theo ngưỡng và khoảng cách với điểm tốt nhất. Không ép hiện sản phẩm lạc loại và không coi số lượng kết quả là độ chính xác.

Cần nắm: điểm cosine không phải phần trăm xác suất; ngưỡng 0.45 và khoảng điểm 0.12 là tham số thử nghiệm. Không nói nhóm tự huấn luyện mô hình hoặc đã đạt một tỷ lệ nhận diện chưa đo. Ghi rõ nguồn OpenCLIP, model LAION và giấy phép trong báo cáo.

Nếu dịch vụ chưa sẵn sàng, kiểm tra terminal Python; thông báo trên giao diện không làm hỏng giỏ hàng. Có ảnh chụp dự phòng trong docs/demo, phải nói rõ đó là bằng chứng chạy trước.
