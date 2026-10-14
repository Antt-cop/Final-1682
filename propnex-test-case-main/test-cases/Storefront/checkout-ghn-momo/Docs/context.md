# Ngữ cảnh kiểm thử — Luồng Đặt hàng, Tính phí GHN & Thanh toán MoMo

## 1. Mô tả luồng nghiệp vụ (Business Flow)
Khách hàng mua hàng sỉ trên Dong Khoi Store:
1. Duyệt sản phẩm tại trang chủ (`/index.php`) hoặc danh mục (`/public/shop.php`).
2. Xem chi tiết sản phẩm (`/public/shop-detail.php`), chọn số lượng và bấm "Thêm vào giỏ hàng".
3. Vào trang giỏ hàng (`/public/cart.php`), kiểm tra danh sách mặt hàng và tổng tiền, nhấn "Tiến hành thanh toán".
4. Tại trang Checkout (`/public/checkout.php`):
   - Nhập họ tên, số điện thoại, địa chỉ nhận hàng.
   - Chọn Tỉnh/Thành phố, Quận/Huyện, Phường/Xã: Hệ thống tự động gọi API Giao Hàng Nhanh (GHN) để tính phí vận chuyển theo khối lượng hàng.
   - Chọn phương thức thanh toán: COD (Thanh toán khi nhận hàng) hoặc MoMo (Cổng thanh toán MoMo Sandbox).
5. Xử lý đặt hàng:
   - Nếu COD: Tạo đơn hàng thành công, hiển thị trang hoàn tất và mã vận đơn.
   - Nếu MoMo: Chuyển hướng sang cổng thanh toán MoMo (`/public/momo_simulator.php`), sau khi thanh toán thành công điều hướng về `/public/momo_return.php`.
6. Tra cứu đơn hàng tại `/public/tracking.php` theo mã đơn.

## 2. Tiền điều kiện dữ liệu (Data Preconditions)
- Đã có sản phẩm còn tồn kho trong cơ sở dữ liệu.
- Cấu hình GHN Shop ID & Token hợp lệ trong hệ thống.
- Cấu hình MoMo Partner Code sandbox sẵn sàng.
