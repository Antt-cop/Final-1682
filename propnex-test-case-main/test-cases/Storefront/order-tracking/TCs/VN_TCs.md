# Test cases — Tra cứu đơn hàng & Vận đơn GHN (Order Tracking)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh chức năng tra cứu trạng thái đơn hàng, theo dõi tiến trình vận chuyển Giao Hàng Nhanh (GHN) thời gian thực và thông tin giao nhận hàng sỉ B2B.

## Phạm vi (Scope)

- Trong phạm vi:
  - Giao diện trang tra cứu vận đơn (`/public/tracking.php`).
  - Danh sách đơn hàng gần đây của tài khoản khách hàng.
  - Form nhập mã đơn hàng hoặc mã vận đơn GHN.
  - Hiển thị tiến trình lộ trình giao hàng thời gian thực (Dòng thời gian / Timeline các mốc: Chờ lấy hàng, Đang trung chuyển, Đang giao hàng, Giao thành công).
  - Chi tiết thông tin giao nhận: Người nhận, địa chỉ giao hàng, phí vận chuyển GHN, số tiền thu hộ COD.
  - Danh sách các mặt hàng trong đơn hàng kèm quy cách đóng gói và đơn giá.
- Ngoài phạm vi:
  - Yêu cầu đổi địa chỉ nhận hàng sau khi đơn hàng đã xuất kho GHN.
  - Khiếu nại đền bù hàng hư hỏng trực tiếp qua cổng hãng vận chuyển.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bố cục 2 cột: Cột danh sách đơn hàng bên trái, Dòng thời gian chi tiết bên phải |
| Mobile | Có | Bố cục xếp chồng dọc, dòng thời gian lộ trình co gọn dễ quan sát trên màn hình dọc |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Trong cơ sở dữ liệu có sẵn ít nhất 1 đơn hàng đã tạo có mã vận đơn GHN hợp lệ.
- [ ] Cấu hình Token API kết nối GHN hoạt động bình thường.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng tiêu đề trang Tra cứu đơn hàng, ô nhập mã vận đơn và nút tra cứu hiển thị rõ ràng.
- [ ] Xác minh rằng danh sách các đơn hàng đã đặt của người dùng hiển thị đầy đủ mã đơn, ngày đặt, trạng thái đơn và tổng tiền.
- [ ] Xác minh rằng dòng thời gian tiến trình giao hàng hiển thị trực quan các mốc trạng thái với các biểu tượng tương ứng.
- [ ] Xác minh rằng khung thông tin người nhận hiển thị rõ ràng họ tên, số điện thoại, địa chỉ nhận hàng và hình thức thanh toán.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng khi người dùng chọn một đơn hàng từ danh sách đơn hàng gần đây, chi tiết hành trình của đơn hàng đó lập tức hiển thị.
- [ ] Xác minh rằng khi nhập đúng mã vận đơn GHN vào ô tra cứu và bấm nút tìm kiếm, hệ thống truy xuất và hiển thị chính xác trạng thái giao hàng từ GHN.
- [ ] Xác minh rằng khi đơn hàng ở trạng thái "Đang giao hàng", mốc thời gian và địa điểm trung chuyển hiện tại của bưu kiện hiển thị rõ ràng.
- [ ] Xác minh rằng khi đơn hàng giao thành công, mốc thời gian giao hàng hoàn tất hiển thị dấu tích màu xanh lá.
- [ ] Xác minh rằng bảng chi tiết hàng hóa bên dưới hiển thị đầy đủ danh sách các mặt hàng, quy cách đóng gói và tổng số tiền thanh toán.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, thông tin đơn hàng và dòng thời gian phân chia 2 cột cân đối, dễ đối chiếu.
- [ ] Xác minh rằng trên màn hình Mobile, dòng thời gian vận chuyển xếp dọc tinh gọn, các mốc thời gian không bị đè chữ lên nhau.
- [ ] Xác minh rằng trên màn hình Mobile, danh sách các đơn hàng trước đó có thể cuộn và chạm chọn mượt mà.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi người dùng nhập mã đơn hàng hoặc mã vận đơn không tồn tại, hệ thống hiển thị thông báo không tìm thấy thông tin đơn hàng.
- [ ] Xác minh rằng khi để trống ô nhập liệu và bấm nút tra cứu, hệ thống nhắc nhở người dùng nhập mã đơn hàng.
- [ ] Xác minh rằng đối với đơn hàng mới tạo chưa bàn giao cho bên vận chuyển GHN, hệ thống hiển thị trạng thái "Chờ lấy hàng từ người bán" rõ ràng.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng thông tin mã đơn hàng và số tiền hiển thị trên trang tra cứu khớp hoàn toàn với thông tin trong Hóa đơn bán hàng.
- [ ] Xác minh rằng liên kết quay lại trang chủ hoặc tiếp tục mua sắm hoạt động chính xác từ trang tra cứu.
