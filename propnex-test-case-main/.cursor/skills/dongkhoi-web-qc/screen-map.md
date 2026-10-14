# Dong Khoi Store (CanteenB2B) Screen Map

Tài liệu bản đồ màn hình phục vụ việc sinh test cases (QC) và kiểm thử giao diện (Verification).
Sử dụng các tên màn hình và hành động hướng người dùng (user-visible).

Môi trường kiểm thử mặc định:
- **Local Laragon:** `http://localhost/dongkhoistore`
- **Breakpoints:**
  - **Desktop:** `≥ 1024px` (khuyến nghị `1440 × 900` hoặc `1280 × 800`)
  - **Mobile:** `< 1024px` (khuyến nghị `390 × 844` hoặc `360 × 800`)

---

## 1. Phân hệ Khách hàng (Storefront)

| Tên màn hình | Đường dẫn / Route | Mô tả & Thành phần chính |
|---|---|---|
| Trang chủ (Home) | `/` hoặc `/index.php` | Banner Slider B2B, Danh mục nổi bật, Sản phẩm bán chạy / mới nhất, Modal tìm kiếm bằng hình ảnh (Gemini AI), Header (Tìm kiếm, Giỏ hàng mini, Tài khoản), Footer |
| Danh mục sản phẩm (Shop) | `/public/shop.php` | Bộ lọc danh mục, lọc theo khoảng giá, thanh tìm kiếm từ khóa, sắp xếp (Giá tăng/giảm, Mới nhất, Bán chạy), lưới sản phẩm, phân trang |
| Chi tiết sản phẩm (Product Detail) | `/public/shop-detail.php?id={productId}` | Thư viện nhiều hình ảnh (Main + Sub images), Tên sản phẩm, Giá bán sỉ/lẻ, Tình trạng tồn kho, Bộ chọn số lượng, Nút "Thêm vào giỏ hàng", Đánh giá/bình luận, Sản phẩm liên quan |
| Giỏ hàng (Cart) | `/public/cart.php` | Danh sách sản phẩm trong giỏ, cập nhật số lượng, xóa sản phẩm, tính tổng tiền tạm tính, nút chuyển đến thanh toán |
| Thanh toán & Đặt hàng (Checkout) | `/public/checkout.php` | Form thông tin người nhận, Bộ chọn địa chỉ 3 cấp (Tỉnh/Thành -> Quận/Huyện -> Phường/Xã kết nối GHN), Tính phí vận chuyển tự động, Phương thức thanh toán (COD / Ví MoMo), Ghi chú đơn hàng |
| Cổng thanh toán MoMo (Simulator & Return) | `/public/momo_simulator.php`, `/public/momo_return.php` | Trang giả lập giao diện quét mã QR / ATM MoMo Sandbox, Trang nhận kết quả thanh toán redirect và thông báo trạng thái đơn hàng |
| Tra cứu vận đơn (Order Tracking) | `/public/tracking.php` | Form nhập mã đơn / mã vận đơn, dòng thời gian lộ trình giao hàng GHN, thông tin người nhận và người giao |
| Đăng nhập / Đăng ký (Auth) | `/public/login.php`, `/public/register.php` | Form đăng nhập email/mật khẩu, Form đăng ký tài khoản B2B (tên, email, số điện thoại, mật khẩu, địa chỉ) |
| Tài khoản cá nhân (My Account) | `/public/my-account.php`, `/public/my-account-edit.php` | Thông tin hồ sơ khách hàng, chỉnh sửa thông tin cá nhân, danh sách lịch sử đơn hàng đã đặt và trạng thái |
| Giới thiệu & Liên hệ | `/public/about.php`, `/public/contact-us.php` | Trang giới thiệu cửa hàng sỉ thực phẩm, form gửi phản hồi/liên hệ |
| Hộp thư / Tin nhắn (Messages) | `/public/messages.php` | Danh sách tin nhắn trao đổi giữa khách hàng và ban quản trị |
| Tìm kiếm bằng hình ảnh AI | Modal kích hoạt từ Header / Home | Modal upload hình ảnh sản phẩm, gọi API Gemini Vision AI để phân tích và gợi ý sản phẩm phù hợp |

---

## 2. Phân hệ Quản trị (Admin Panel)

Đường dẫn gốc: `http://localhost/dongkhoistore/admin/`

| Tên màn hình | Đường dẫn / Route | Mô tả & Thành phần chính |
|---|---|---|
| Bảng điều khiển (Dashboard) | `/admin/dashboard.php` | Thống kê tổng doanh thu, số đơn hàng, khách hàng, biểu đồ tăng trưởng doanh số, danh sách đơn hàng cần xử lý gấp |
| Quản lý đơn hàng (Orders) | `/admin/orders.php` | Bảng danh sách đơn hàng, bộ lọc theo trạng thái (Chờ xác nhận, Đã xác nhận, Đang giao, Đã giao, Đã hủy), Xem chi tiết đơn hàng, Cập nhật trạng thái, Nút đồng bộ/đẩy đơn sang GHN |
| Hóa đơn bán hàng (Invoice) | `/admin/invoice.php?order_id={id}` | Mẫu hóa đơn thanh toán chi tiết kèm thông tin vận chuyển, danh sách mặt hàng, thuế phí và nút In hóa đơn |
| Quản lý sản phẩm (Products) | `/admin/products.php` | Danh sách sản phẩm, Tìm kiếm/lọc, Thêm mới sản phẩm (Tên, Danh mục, Giá, Tồn kho, Mô tả, Upload ảnh chính và các ảnh chi tiết), Chỉnh sửa, Xóa sản phẩm |
| Quản lý danh mục (Categories) | `/admin/categories.php` | Danh sách danh mục hàng hóa (Đồ uống, Đồ đóng hộp, Thực phẩm tươi sống, Đồ khô), Thêm mới, Sửa, Xóa |
| Quản lý tồn kho (Inventory) | `/admin/inventory.php` | Báo cáo số lượng tồn kho theo sản phẩm, cảnh báo hàng sắp hết trong kho, nhập thêm số lượng kho |
| Quản lý khách hàng (Customers) | `/admin/customers.php` | Danh sách khách hàng đã đăng ký, lịch sử mua hàng, trạng thái hoạt động tài khoản |
| Cài đặt Giao Hàng Nhanh (GHN Settings) | `/admin/ghn_settings.php` | Thiết lập Token API GHN, Shop ID, chọn địa chỉ kho lấy hàng (Tỉnh, Huyện, Xã) |
| Cấu hình Mail SMTP (SMTP Settings) | `/admin/smtp_settings.php` | Cấu hình máy chủ SMTP gửi mail thông báo đơn hàng (Host, Port, User, Pass), nút kiểm tra gửi email test |

---

## 3. Khác biệt hiển thị Desktop vs Mobile trên Web

| Khu vực / Tính năng | Desktop (≥ 1024px) | Mobile (< 1024px) |
|---|---|---|
| Header & Menu điều hướng | Thanh điều hướng ngang, menu danh mục dropdown, giỏ hàng popup | Menu Hamburger trượt ra (Off-canvas), giỏ hàng icon trên header bar |
| Bộ lọc sản phẩm (Shop) | Cột Sidebar cố định bên trái (Danh mục, Khoảng giá) | Nút mở bộ lọc dạng Modal / Drawer toàn màn hình |
| Lưới sản phẩm | 3 đến 4 cột sản phẩm | 1 đến 2 cột sản phẩm tối ưu màn hình cảm ứng |
| Bảng giỏ hàng & Thanh toán | Bảng dạng Table đầy đủ cột (Ảnh, Tên, Giá, SL, Tổng, Xóa) | Bố cục dạng thẻ (Cards) xếp dọc, nút thao tác to dễ chạm |
| Chi tiết sản phẩm | Cột ảnh bên trái, cột thông tin đặt hàng bên phải | Ảnh hiển thị trượt ngang ở trên, thông tin đặt hàng xếp bên dưới |
| Admin Panel | Thanh Sidebar cố định bên trái, nội dung tràn ngang | Nút toggle Sidebar thu gọn, bảng dữ liệu hỗ trợ vuốt ngang (horizontal scroll) |

---

## 4. Các trạng thái giao diện dùng chung cần kiểm thử (UI States)

- **Trạng thái Trống (Empty State):** Giỏ hàng trống ("Chưa có sản phẩm nào trong giỏ hàng"), Danh mục chưa có sản phẩm, Tìm kiếm không có kết quả phù hợp.
- **Trạng thái Đang tải (Loading State):** Đang tính phí ship GHN theo địa chỉ, đang phân tích ảnh bằng Gemini AI, đang xử lý thanh toán MoMo.
- **Trạng thái Lỗi / Xác thực (Validation & Error State):** Bỏ trống trường bắt buộc khi checkout (chưa chọn Tỉnh/Huyện/Xã), nhập sai thông tin đăng nhập, hết hàng trong kho.
- **Thông báo phản hồi (Feedback / Toast / Alert):** Thông báo "Đã thêm sản phẩm vào giỏ hàng thành công", modal xác nhận hủy đơn hàng.
