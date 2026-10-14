# Test cases — Quản lý danh mục & Khách hàng sỉ (Categories & Customers Management)

**Phân hệ:** Admin  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh tính năng quản trị danh mục ngành hàng (Thêm, Sửa, Xóa danh mục kèm bảo toàn ràng buộc sản phẩm) và quản trị hồ sơ khách sỉ/căn tin B2B (Xem thông tin đại lý, đổi trạng thái, kích hoạt tài khoản).

## Phạm vi (Scope)

- Trong phạm vi:
  - Bảng danh mục ngành hàng (`/admin/categories.php`).
  - Thao tác thêm mới, chỉnh sửa tên và mô tả danh mục.
  - Xóa danh mục và kiểm tra tính toàn vẹn (Chặn xóa danh mục đang có sản phẩm liên kết).
  - Bảng danh sách khách hàng sỉ (`/admin/customers.php`).
  - Xem thông tin chi tiết khách hàng: Tên đăng nhập, Email, Tên đại lý/Căn tin, Số điện thoại, Địa chỉ giao hàng.
  - Thao tác thêm khách hàng mới hoặc chỉnh sửa thông tin đại lý.
  - Bật/tắt trạng thái tài khoản khách hàng (Hoạt động / Tạm khóa).
- Ngoài phạm vi:
  - Phân hạng thành viên đại lý đa cấp (Gold/Platinum) tự động theo chiết khấu doanh số.
  - Tích hợp cổng tra cứu mã số thuế doanh nghiệp trực tiếp từ Tổng cục Thuế.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bảng dữ liệu mở rộng các cột, nút hành động xếp ngang cân đối |
| Mobile | Có | Hỗ trợ cuộn ngang bảng dữ liệu, cửa sổ modal thêm/sửa tự động co giãn |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Quản trị viên đã đăng nhập vào hệ thống quản trị.
- [ ] Trong cơ sở dữ liệu có sẵn ít nhất một danh mục và tài khoản khách hàng mẫu.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng tiêu đề trang Quản lý danh mục, nút "Thêm danh mục" và bảng danh mục hiển thị rõ ràng.
- [ ] Xác minh rằng tiêu đề trang Quản lý khách hàng, nút "Thêm khách hàng" và bảng danh sách khách sỉ hiển thị đầy đủ các cột thông tin.
- [ ] Xác minh rằng cửa sổ modal Thêm danh mục hiển thị đầy đủ ô Tên danh mục và Mô tả.
- [ ] Xác minh rằng cửa sổ modal Khách hàng hiển thị đầy đủ ô Họ tên, Email, Tên cơ quan/Căn tin, Số điện thoại, Địa chỉ và Trạng thái.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng quản trị viên có thể nhập tên danh mục mới và tạo thành công danh mục.
- [ ] Xác minh rằng danh mục mới tạo xuất hiện ngay lập tức trong bảng danh mục và hiển thị ngoài trang chọn ngành hàng của khách hàng.
- [ ] Xác minh rằng quản trị viên có thể chỉnh sửa tên hoặc mô tả của danh mục hiện có và lưu thay đổi thành công.
- [ ] Xác minh rằng quản trị viên có thể thêm mới một tài khoản khách sỉ B2B với đầy đủ thông tin đại lý và mật khẩu.
- [ ] Xác minh rằng quản trị viên có thể chỉnh sửa số điện thoại, địa chỉ hoặc tên cơ quan của khách hàng và lưu thành công.
- [ ] Xác minh rằng quản trị viên có thể nhấp chuyển đổi trạng thái khách hàng giữa "Hoạt động" và "Tạm khóa".

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, bảng danh mục và bảng khách hàng hiển thị thoáng đãng với các nút thao tác dễ bấm.
- [ ] Xác minh rằng trên màn hình Mobile, bảng dữ liệu cho phép vuốt ngang mượt mà mà không làm méo vỡ nội dung các cột.
- [ ] Xác minh rằng trên màn hình Mobile, các modal biểu mẫu hiển thị vừa vặn trong màn hình cảm ứng và cho phép cuộn dọc êm ái.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi xóa một danh mục đang có sản phẩm liên kết, hệ thống chặn hành động và hiển thị cảnh báo không thể xóa kèm số lượng sản phẩm liên quan.
- [ ] Xác minh rằng khi tạo danh mục để trống tên danh mục, hệ thống đưa ra thông báo nhắc nhở điền tên danh mục.
- [ ] Xác minh rằng khi tạo khách hàng với email hoặc tên đăng nhập đã trùng lặp trong hệ thống, hệ thống hiển thị cảnh báo đã tồn tại.
- [ ] Xác minh rằng khi tài khoản khách hàng bị chuyển sang trạng thái "Tạm khóa", tài khoản đó không thể đăng nhập vào Storefront.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng việc đổi tên danh mục trong trang quản trị tự động cập nhật ngay lập tức tại thanh menu danh mục ngoài trang chủ Storefront.
- [ ] Xác minh rằng các sản phẩm thuộc danh mục được cập nhật vẫn giữ nguyên mối liên kết và không bị mất danh mục.
