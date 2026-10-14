# Test cases — Cấu hình hệ thống GHN & Mail SMTP (Admin System Settings)

**Phân hệ:** Admin  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh tính năng quản trị cấu hình hệ thống: tích hợp cổng vận chuyển Giao Hàng Nhanh (Shop ID, Token API) và cấu hình máy chủ gửi thư điện tử SMTP phục vụ gửi thông báo đơn hàng tự động.

## Phạm vi (Scope)

- Trong phạm vi:
  - Trang cài đặt Giao Hàng Nhanh (`/admin/ghn_settings.php`).
  - Cấu hình Shop ID, Token kết nối GHN API và tên cửa hàng đại diện.
  - Xóa bỏ hoặc làm mới cấu hình GHN về trạng thái ban đầu.
  - Trang cài đặt Mail SMTP (`/admin/smtp_settings.php`).
  - Thiết lập thông số máy chủ gửi thư (Máy chủ SMTP, Cổng Port, Giao thức mã hóa TLS/SSL, Tài khoản xác thực, Mật khẩu ứng dụng).
  - Cấu hình thông tin người gửi: Địa chỉ email người gửi và Tên hiển thị người gửi.
  - Tính năng gửi thử nghiệm thư điện tử (Test Email) để kiểm tra kết nối máy chủ mail.
- Ngoài phạm vi:
  - Tích hợp thêm các đơn vị chuyển phát khác (ViettelPost, J&T Express, Ninja Van).
  - Cấu hình chữ ký số điện tử trên email.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Form cài đặt rộng rãi, chia các khối thông tin kết nối và kiểm tra kết nối rõ ràng |
| Mobile | Có | Form co giãn dọc hợp lý, các nút lưu cấu hình và gửi thư thử nghiệm to bản dễ bấm |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Quản trị viên đã đăng nhập thành công vào bảng điều khiển quản trị.
- [ ] Bảng cơ sở dữ liệu lưu cấu hình GHN (`ghn_shops`) và cấu hình SMTP (`smtp_settings`) đã sẵn sàng.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng trang Cài đặt GHN hiển thị đầy đủ các trường: Shop ID, Token API, Tên cửa hàng, nút Lưu cấu hình và nút Xóa cấu hình.
- [ ] Xác minh rằng khối trạng thái cấu hình GHN hiện tại hiển thị rõ ràng mã Shop ID và Token đang được áp dụng.
- [ ] Xác minh rằng trang Cài đặt SMTP hiển thị đầy đủ các trường: Máy chủ SMTP, Cổng kết nối, Giao thức bảo mật, Tên đăng nhập, Mật khẩu, Email người gửi và Tên hiển thị người gửi.
- [ ] Xác minh rằng khối "Gửi email thử nghiệm" hiển thị ô nhập địa chỉ email nhận thử kèm nút "Gửi thư kiểm tra".

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng quản trị viên có thể nhập Shop ID và Token GHN hợp lệ và lưu cấu hình thành công.
- [ ] Xác minh rằng sau khi lưu cấu hình GHN, hệ thống hiển thị thông báo thành công màu xanh lá và hiển thị thông tin cấu hình mới.
- [ ] Xác minh rằng quản trị viên có thể cập nhật các thông số máy chủ SMTP và lưu cấu hình thành công.
- [ ] Xác minh rằng sau khi lưu cấu hình SMTP, hệ thống thông báo cập nhật thành công và các thông số mới được lưu giữ đầy đủ.
- [ ] Xác minh rằng quản trị viên có thể nhập một địa chỉ email hợp lệ vào ô gửi thư thử nghiệm và bấm nút gửi thư kiểm tra thành công.
- [ ] Xác minh rằng khi bấm nút Xóa cấu hình GHN và xác nhận, thông tin kết nối GHN được xóa bỏ an toàn.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, form thiết lập hiển thị gọn gàng trong các khung card nổi bật và dễ thao tác.
- [ ] Xác minh rằng trên màn hình Mobile, các ô nhập liệu được xếp chồng dọc vừa vặn với chiều ngang màn hình điện thoại.
- [ ] Xác minh rằng trên màn hình Mobile, nút gửi email thử nghiệm không bị tràn viền hoặc che khuất thông báo kết quả.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi bấm lưu cấu hình GHN mà để trống trường Shop ID hoặc Token, hệ thống hiển thị thông báo nhắc nhở điền đầy đủ.
- [ ] Xác minh rằng khi nhập địa chỉ email nhận thư thử nghiệm không đúng định dạng email, hệ thống hiển thị cảnh báo email không hợp lệ.
- [ ] Xác minh rằng khi thông số máy chủ SMTP bị sai hoặc không kết nối được máy chủ mail, hệ thống hiển thị chi tiết thông báo lỗi kết nối gửi mail.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng sau khi cấu hình GHN thành công, tính năng tính phí ship tự động ngoài trang Thanh toán (Checkout) hoạt động chính xác.
- [ ] Xác minh rằng sau khi cấu hình SMTP thành công, hệ thống gửi email xác nhận tự động tới khách hàng khi có đơn hàng mới được tạo.
