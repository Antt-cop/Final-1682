# Test cases — Xác thực & Quản lý tài khoản (Authentication & Account Management)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh luồng đăng ký tài khoản đại lý sỉ B2B, đăng nhập hệ thống, bảo vệ chuyển hướng đăng nhập và cập nhật hồ sơ cá nhân của khách hàng.

## Phạm vi (Scope)

- Trong phạm vi:
  - Form đăng ký tài khoản khách hàng mới (`/public/register.php`).
  - Form đăng nhập hệ thống (`/public/login.php`).
  - Cơ chế tự động chuyển tiếp về trang trước đó sau khi đăng nhập (ví dụ: quay lại giỏ hàng hoặc danh sách yêu thích).
  - Đăng xuất tài khoản an toàn và xóa bỏ phiên làm việc.
  - Trang tổng quan tài khoản cá nhân (`/public/my-account.php`).
  - Chỉnh sửa thông tin liên hệ, số điện thoại và địa chỉ giao hàng (`/public/my-account-edit.php`).
- Ngoài phạm vi:
  - Đăng nhập một chạm bằng tài khoản mạng xã hội bên thứ ba (Google OAuth, Facebook Login).
  - Xác thực sinh trắc học vân tay / FaceID.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Form đăng nhập/đăng ký căn giữa trang, thẻ hồ sơ tài khoản hiển thị dạng lưới các ô chức năng |
| Mobile | Có | Form điền thông tin tối ưu bàn phím ảo di động, các ô chức năng xếp chồng dọc |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Cơ sở dữ liệu sẵn sàng tiếp nhận đăng ký tài khoản mới và lưu thông tin người dùng.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng form Đăng nhập hiển thị đầy đủ các trường: Địa chỉ Email, Mật khẩu, nút Đăng nhập và liên kết chuyển sang trang Đăng ký.
- [ ] Xác minh rằng form Đăng ký hiển thị đầy đủ các trường: Tên người dùng, Email, Số điện thoại, Mật khẩu, Xác nhận mật khẩu và nút Đăng ký.
- [ ] Xác minh rằng trang Tài khoản cá nhân hiển thị thẻ chào mừng kèm Họ tên, Email, Số điện thoại và Địa chỉ giao hàng mặc định.
- [ ] Xác minh rằng các ô liên kết dịch vụ (Lịch sử đơn hàng, Danh sách yêu thích, Chỉnh sửa hồ sơ) hiển thị rõ ràng trên trang tài khoản.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng người dùng có thể điền thông tin hợp lệ vào form Đăng ký và tạo tài khoản thành công.
- [ ] Xác minh rằng sau khi đăng ký thành công, hệ thống thông báo và cho phép người dùng đăng nhập ngay lập tức.
- [ ] Xác minh rằng khi nhập đúng Email và Mật khẩu đã đăng ký, người dùng đăng nhập thành công vào hệ thống.
- [ ] Xác minh rằng nếu người dùng bị chuyển hướng sang trang đăng nhập từ một trang yêu cầu xác thực (như Wishlist), sau khi đăng nhập thành công hệ thống tự động quay lại đúng trang đó.
- [ ] Xác minh rằng người dùng có thể chỉnh sửa số điện thoại, địa chỉ giao hàng và lưu cập nhật thành công.
- [ ] Xác minh rằng khi bấm nút Đăng xuất, hệ thống hủy phiên đăng nhập an toàn và chuyển người dùng về trạng thái khách vãng lai.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, khung đăng nhập/đăng ký có độ rộng vừa vặn ở giữa trang, không bị kéo giãn quá mức.
- [ ] Xác minh rằng trên màn hình Mobile, các ô nhập liệu tự động kích hoạt bàn phím phù hợp (bàn phím số cho số điện thoại, bàn phím email cho email).
- [ ] Xác minh rằng trên màn hình Mobile, menu tài khoản ở thanh điều hướng trượt mở mượt mà và hiển thị đúng tên người dùng đã đăng nhập.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi nhập sai mật khẩu hoặc email chưa đăng ký, hệ thống hiển thị thông báo lỗi rõ ràng và không cho phép đăng nhập.
- [ ] Xác minh rằng khi đăng ký với email đã tồn tại trong cơ sở dữ liệu, hệ thống hiển thị cảnh báo email đã được sử dụng.
- [ ] Xác minh rằng khi bỏ trống các trường thông tin bắt buộc, hệ thống hiển thị nhắc nhở người dùng điền đầy đủ.
- [ ] Xác minh rằng khi nhập mật khẩu xác nhận không trùng khớp với mật khẩu mới, hệ thống hiển thị cảnh báo không khớp.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng sau khi đăng nhập, tên người dùng và các liên kết tương ứng trên thanh Header cập nhật tức thì từ nút Đăng nhập sang thông tin Tài khoản.
- [ ] Xác minh rằng hàng hóa đã thêm vào giỏ hàng trước khi đăng nhập vẫn được giữ nguyên đầy đủ sau khi đăng nhập thành công.
