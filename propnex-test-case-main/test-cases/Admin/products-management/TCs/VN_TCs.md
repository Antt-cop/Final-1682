# Test cases — Quản lý sản phẩm B2B (Admin Products Management)

**Phân hệ:** Admin  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh tính năng quản trị danh mục sản phẩm của quản trị viên: tạo mới, cập nhật giá sỉ, thiết lập quy cách đóng gói đa cấp B2B (Thùng/Lốc/Bao/Túi, tỷ lệ quy đổi) và kiểm soát trạng thái hiển thị.

## Phạm vi (Scope)

- Trong phạm vi:
  - Bảng danh sách sản phẩm quản trị (`/admin/products.php`).
  - Thẻ đếm số liệu tổng quan (Tổng sản phẩm, Sắp hết hàng, Hết hàng).
  - Bộ lọc tìm kiếm theo tên, danh mục và tình trạng tồn kho.
  - Cửa sổ modal Thêm mới / Chỉnh sửa sản phẩm.
  - Thiết lập quy cách đóng gói sỉ B2B (Đơn vị xuất kho, Đơn vị đóng gói phụ, Tỷ lệ quy đổi, Quy mô đóng gói, Đơn vị đặt tối thiểu).
  - Tải lên ảnh sản phẩm và xem trước ảnh.
  - Bật/tắt trạng thái hoạt động (Kích hoạt / Tạm dừng).
  - Thao tác xóa sản phẩm khỏi hệ thống.
- Ngoài phạm vi:
  - Nhập dữ liệu sản phẩm hàng loạt qua tệp Excel/CSV ngoài hệ thống.
  - Tích hợp cổng in mã vạch / in tem nhãn phụ tự động.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bảng dữ liệu đầy đủ các cột thông tin, thanh công cụ tìm kiếm và lọc xếp ngang |
| Mobile | Có | Bảng dữ liệu hỗ trợ cuộn ngang linh hoạt, modal biểu mẫu cuộn dọc mượt mà |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Tài khoản quản trị viên đã đăng nhập thành công vào trang quản trị (`/admin/`).
- [ ] Đã có sẵn ít nhất một danh mục hàng hóa đã duyệt trong cơ sở dữ liệu.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng tiêu đề trang Quản lý sản phẩm, nút "Thêm sản phẩm" và các thẻ thống kê tổng quan hiển thị rõ ràng.
- [ ] Xác minh rằng bảng danh sách sản phẩm hiển thị đầy đủ các cột: Mã, Ảnh đại diện, Tên & Danh mục, Đơn giá sỉ, Tồn kho khả dụng, Loại lưu trữ, Trạng thái và Thao tác.
- [ ] Xác minh rằng bộ lọc bao gồm ô tìm kiếm từ khóa, danh sách chọn danh mục và lựa chọn mức tồn kho hiển thị đầy đủ.
- [ ] Xác minh rằng cửa sổ modal Thêm/Sửa sản phẩm hiển thị đầy đủ các trường nhập liệu thông tin cơ bản và khu vực thiết lập quy cách đóng gói B2B.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng khi bấm nút "Thêm sản phẩm", cửa sổ modal mở ra với các trường thông tin trống sẵn sàng nhập liệu.
- [ ] Xác minh rằng quản trị viên có thể nhập tên sản phẩm, chọn danh mục, điền đơn giá sỉ và cấu hình quy cách đóng gói B2B thành công.
- [ ] Xác minh rằng quản trị viên có thể chọn tải lên tệp ảnh đại diện của sản phẩm và ảnh hiển thị xem trước rõ nét.
- [ ] Xác minh rằng sau khi bấm Lưu sản phẩm, hệ thống hiển thị thông báo tạo thành công và sản phẩm mới xuất hiện ngay trong bảng danh sách.
- [ ] Xác minh rằng khi bấm nút Chỉnh sửa tại một dòng sản phẩm, dữ liệu hiện tại của sản phẩm được điền chính xác vào các ô trong modal.
- [ ] Xác minh rằng quản trị viên có thể cập nhật đơn giá hoặc quy cách đóng gói và lưu thay đổi thành công.
- [ ] Xác minh rằng khi bấm nút chuyển đổi trạng thái (Kích hoạt / Tạm dừng), trạng thái của sản phẩm lập tức thay đổi.
- [ ] Xác minh rằng khi bấm nút Xóa và xác nhận trong hộp thoại, sản phẩm bị xóa khỏi hệ thống.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, bảng dữ liệu mở rộng đầy đủ các cột và các nút thao tác xếp ngang tiện lợi.
- [ ] Xác minh rằng trên màn hình Mobile, bảng danh sách hỗ trợ vuốt cuộn ngang mượt mà và nút mở modal thêm sản phẩm nổi bật ở đầu trang.
- [ ] Xác minh rằng trên màn hình Mobile, biểu mẫu thêm/sửa sản phẩm tự động co giãn vừa chiều rộng màn hình giúp thao tác dễ dàng.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi để trống các trường bắt buộc (như tên sản phẩm, danh mục, đơn giá) và bấm lưu, hệ thống hiển thị cảnh báo lỗi hợp lệ.
- [ ] Xác minh rằng khi nhập đơn giá là số âm hoặc bằng 0, hệ thống từ chối lưu và yêu cầu nhập đơn giá hợp lệ.
- [ ] Xác minh rằng khi lọc theo từ khóa không tồn tại, bảng hiển thị thông báo không có sản phẩm nào phù hợp.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng sản phẩm mới tạo hoặc vừa cập nhật quy cách đóng gói hiển thị chính xác ngoài trang bán hàng công khai của khách hàng.
- [ ] Xác minh rằng sản phẩm bị chuyển sang trạng thái tạm dừng sẽ tự động ẩn khỏi danh mục mua sắm ngoài trang chủ và trang shop.
