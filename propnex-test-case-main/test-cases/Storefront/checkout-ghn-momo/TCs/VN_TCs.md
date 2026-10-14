# Test cases — Checkout & Thanh toán (GHN & MoMo)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh toàn bộ luồng thêm vào giỏ, chọn địa chỉ tính phí giao hàng GHN và thanh toán đơn hàng bằng COD / MoMo.

## Phạm vi (Scope)

- Trong phạm vi:
  - Thêm sản phẩm vào giỏ và xem giỏ hàng (`/public/cart.php`).
  - Điền thông tin giao hàng tại trang Checkout (`/public/checkout.php`).
  - Chọn địa chỉ Tỉnh/Huyện/Xã và tính phí ship GHN tự động.
  - Đặt hàng với COD và đặt hàng với MoMo (`/public/momo_simulator.php`).
  - Hiển thị trang kết quả đơn hàng và kiểm tra tra cứu tại `/public/tracking.php`.
- Ngoài phạm vi:
  - Thanh toán bằng thẻ tín dụng quốc tế (Stripe/PayPal).
  - Tích hợp tài khoản đối tác doanh nghiệp lớn.

## Ma trận Viewport

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bảng giỏ hàng dạng bảng ngang, layout Checkout chia 2 cột thông tin & tóm tắt |
| Mobile | Có | Giỏ hàng dạng danh sách thẻ, layout Checkout xếp chồng dọc dễ thao tác cảm ứng |

## Tiền điều kiện (Preconditions)

- [x] Web server chạy tại `http://localhost/dongkhoistore`.
- [x] Có ít nhất 1 sản phẩm còn tồn kho > 5 đơn vị trong cơ sở dữ liệu.
- [x] Cấu hình API GHN và MoMo Sandbox đã kích hoạt.

## Test cases

### 1) Giỏ hàng (Cart)

- [x] Xác minh rằng bấm nút "Thêm vào giỏ hàng" tại trang chi tiết sản phẩm hiển thị thông báo thành công.
- [x] Xác minh rằng số lượng sản phẩm trên icon giỏ hàng ở Header tăng tương ứng sau khi thêm.
- [x] Xác minh rằng tại trang giỏ hàng (`/public/cart.php`), danh sách sản phẩm, đơn giá, số lượng và tổng tiền hiển thị chính xác.
- [x] Xác minh rằng người dùng có thể tăng/giảm số lượng hoặc xóa một mặt hàng khỏi giỏ.
- [x] Xác minh rằng khi giỏ hàng trống, hệ thống hiển thị thông báo giỏ hàng rỗng và nút quay lại cửa hàng.

### 2) Form thông tin giao hàng & Tính phí ship GHN

- [x] Xác minh rằng form Checkout hiển thị đầy đủ các trường: Họ tên, Số điện thoại, Email, Tỉnh/Thành phố, Quận/Huyện, Phường/Xã, Địa chỉ cụ thể.
- [x] Xác minh rằng khi chưa chọn Tỉnh/Thành phố, các dropdown Quận/Huyện và Phường/Xã ở trạng thái vô hiệu hóa hoặc trống.
- [x] Xác minh rằng sau khi chọn đầy đủ Tỉnh/Thành phố, Quận/Huyện và Phường/Xã, hệ thống tự động gọi API GHN để hiển thị phí vận chuyển.
- [x] Xác minh rằng tổng tiền thanh toán được cập nhật bằng Tiền hàng + Phí ship GHN.
- [x] Xác minh rằng nếu bỏ trống các trường bắt buộc và bấm Đặt hàng, hệ thống hiển thị cảnh báo lỗi hợp lệ.

### 3) Thanh toán COD (Tiền mặt khi nhận hàng)

- [x] Xác minh rằng khi chọn phương thức thanh toán "COD" và bấm "Đặt hàng", hệ thống tạo đơn hàng thành công và chuyển hướng đến trang cảm ơn / mã đơn hàng.
- [x] Xác minh rằng mã vận đơn GHN được sinh và liên kết với đơn hàng mới tạo.

### 4) Thanh toán MoMo Sandbox

- [x] Xác minh rằng khi chọn phương thức "Ví MoMo" và bấm "Đặt hàng", hệ thống chuyển hướng sang cổng/trình giả lập MoMo (`/public/momo_simulator.php`).
- [x] Xác minh rằng thông tin mã đơn hàng, số tiền và mã đối tác trên trang MoMo hiển thị chính xác.
- [x] Xác minh rằng sau khi bấm "Xác nhận thanh toán thành công" trên trang giả lập MoMo, hệ thống chuyển hướng về `/public/momo_return.php` và cập nhật trạng thái đơn hàng là Đã thanh toán.

### 5) Tra cứu đơn hàng (Tracking)

- [x] Xác minh rằng nhập mã đơn hàng vừa tạo vào trang Tra cứu đơn hàng (`/public/tracking.php`) hiển thị đúng thông tin trạng thái đơn và lịch sử giao hàng.

### 6) Quy cách đóng gói B2B & Quản lý kho WMS (Packaging & Inventory)

- [x] Xác minh nước giải khát (Coca-Cola, Pepsi) hiển thị quy cách `1 Thùng = 4 Lốc = 24 Lon (1 Lốc = 6 Lon)` với bộ chọn đơn vị mua (Lốc vs Thùng).
- [x] Xác minh mì gói (Hảo Hảo, Omachi) bán theo `Thùng (30 Gói)` và hiển thị đúng quy cách `1 Thùng = 30 Gói`.
- [x] Xác minh mì ly (Handy Hảo Hảo) bán theo `Thùng (24 Ly)` và hiển thị đúng quy cách `1 Thùng = 24 Ly`.
- [x] Xác minh khi chọn đơn vị mua Lốc hoặc Thùng, giá bán tự động cập nhật tương ứng theo từng quy cách.
- [x] Xác minh hệ thống kho WMS tự động phân rã số lượng tồn kho thành Thùng và Lốc, bỏ các đơn vị lẻ dư thừa ra ngoài ("bỏ số dư ra").
- [x] Xác minh khi đặt hàng và thanh toán, tồn kho trong cơ sở dữ liệu được trừ chính xác theo số đơn vị cơ sở (`Số lượng * Tỷ lệ quy đổi`).
- [x] Xác minh trang quản trị kho (`/admin/inventory.php`) hiển thị cột quy cách B2B và cho phép điều chỉnh tồn kho trực tiếp theo Thùng.

