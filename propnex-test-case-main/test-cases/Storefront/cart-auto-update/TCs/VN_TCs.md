# Test cases — Cập nhật giỏ hàng tự động & Chiết khấu sỉ B2B (Cart Real-Time Auto-Update)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh tính năng tự động cập nhật giỏ hàng theo thời gian thực khi thay đổi số lượng, phản hồi tức thì của các nút tăng giảm, tự động áp dụng các bậc chiết khấu sỉ B2B và cập nhật bảng tổng kết đơn hàng mà không cần tải lại trang.

## Phạm vi (Scope)

- Trong phạm vi:
  - Thao tác nút tăng (+) và giảm (-) số lượng sản phẩm trên từng dòng hàng.
  - Nhập trực tiếp số lượng từ bàn phím với cơ chế xử lý độ trễ chống dồn yêu cầu (debounce).
  - Trạng thái thông báo phản hồi tự động lưu trên giao diện.
  - Tự động áp dụng các mốc chiết khấu số lượng lớn B2B (từ 20 đơn vị giảm 5%, từ 50 đơn vị giảm 10%, từ 100 đơn vị giảm 15%).
  - Tự động tính toán lại Thành tiền từng món, Tiền hàng, Tổng chiết khấu sỉ và Tổng cộng thanh toán trong khung Tóm tắt đơn hàng.
  - Cập nhật cảnh báo tồn kho kho vận theo thời gian thực (Đủ hàng vs Không đủ hàng khả dụng).
  - Đồng bộ số lượng hiển thị trên biểu tượng giỏ hàng và danh sách giỏ hàng thu nhỏ ở thanh tiêu đề (Header mini-cart).
  - Thao tác xóa sản phẩm và xử lý trạng thái giỏ hàng rỗng.
- Ngoài phạm vi:
  - Tích hợp mã giảm giá khuyến mãi bên thứ ba (Coupon/Voucher ngoài hệ thống).
  - Tùy chọn đóng gói quà tặng theo yêu cầu.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bảng giỏ hàng dàn trải đầy đủ các cột thông tin, quy cách đóng gói, bộ nút số lượng căn giữa |
| Mobile | Có | Bảng giỏ hàng cho phép cuộn ngang mượt mà, nút bấm tăng giảm tối ưu kích thước cảm ứng |

## Tiền điều kiện (Preconditions)

- [x] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [x] Trong cơ sở dữ liệu có sẵn ít nhất 2 sản phẩm phân phối sỉ còn tồn kho lớn (≥ 200 đơn vị khả dụng).
- [x] Giỏ hàng đã có ít nhất 1 sản phẩm trước khi bắt đầu thực hiện kiểm thử.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [x] Xác minh rằng bảng giỏ hàng hiển thị đầy đủ các cột: Sản phẩm & Quy cách đóng gói, Đơn giá, Số lượng, Chiết khấu sỉ, Thành tiền và Thao tác xóa.
- [x] Xác minh rằng mỗi ô số lượng có đầy đủ nút giảm (-), ô nhập số lượng và nút tăng (+) xếp liền nhau rõ ràng.
- [x] Xác minh rằng phía dưới tên mỗi sản phẩm hiển thị nhãn đơn vị mua, quy cách đóng gói sỉ B2B và dòng trạng thái tồn kho khả dụng.
- [x] Xác minh rằng khung Tóm tắt đơn hàng hiển thị đầy đủ ba mục: Tiền hàng, Chiết khấu sỉ và Tổng cộng thanh toán.
- [x] Xác minh rằng khung chính sách giá sỉ hiển thị rõ ràng thông tin 3 mốc chiết khấu (từ 20 đơn vị giảm 5%, từ 50 đơn vị giảm 10%, từ 100 đơn vị giảm 15%).
- [x] Xác minh rằng nút "Tiến hành thanh toán" hiển thị nổi bật và sẵn sàng thao tác ở cuối trang giỏ hàng.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [x] Xác minh rằng khi bấm nút tăng (+), giá trị ô số lượng tăng thêm 1 đơn vị và hệ thống tự động kích hoạt cập nhật giỏ hàng mà không cần bấm nút cập nhật thủ công.
- [x] Xác minh rằng khi bấm nút giảm (-), giá trị ô số lượng giảm đi 1 đơn vị và hệ thống tự động kích hoạt cập nhật giỏ hàng mà không cần bấm nút cập nhật thủ công.
- [x] Xác minh rằng trong lúc hệ thống đang gửi dữ liệu lưu, thông báo trạng thái hiển thị biểu tượng xoay cùng nội dung thông báo đang tự động lưu.
- [x] Xác minh rằng sau khi lưu thành công, thông báo chuyển sang màu xanh lá với nội dung xác nhận đã tự động cập nhật giỏ hàng.
- [x] Xác minh rằng khi người dùng gõ số lượng mới trực tiếp vào ô nhập liệu, hệ thống tự động lưu lại sau khi dừng gõ phím.
- [x] Xác minh rằng khi số lượng đạt từ 20 đến 49 đơn vị, ô chiết khấu của dòng sản phẩm tự động hiển thị mức giảm 5% kèm số tiền được trừ tương ứng.
- [x] Xác minh rằng khi số lượng đạt từ 50 đến 99 đơn vị, ô chiết khấu của dòng sản phẩm tự động hiển thị mức giảm 10% kèm số tiền được trừ tương ứng.
- [x] Xác minh rằng khi số lượng đạt từ 100 đơn vị trở lên, ô chiết khấu của dòng sản phẩm tự động hiển thị mức giảm 15% kèm số tiền được trừ tương ứng.
- [x] Xác minh rằng thành tiền của dòng sản phẩm tự động tính đúng bằng Đơn giá nhân với Số lượng trừ đi Tiền chiết khấu.
- [x] Xác minh rằng các số liệu Tiền hàng, Chiết khấu sỉ và Tổng cộng thanh toán trong khung Tóm tắt đơn hàng tự động tính toán lại chính xác khớp với bảng sản phẩm.
- [x] Xác minh rằng số lượng hiển thị trên biểu tượng giỏ hàng ở thanh tiêu đề đầu trang tự động cập nhật theo số lượng hàng mới.

### 3) Hiển thị trên Desktop vs Mobile

- [x] Xác minh rằng trên màn hình Desktop, bảng dữ liệu hiển thị rộng rãi, khoảng cách giữa các cột hợp lý và các nút tăng giảm số lượng nằm cân đối.
- [x] Xác minh rằng trên màn hình Mobile, bảng giỏ hàng cuộn ngang mượt mà, hai nút tăng giảm có kích thước chạm tối thiểu 36px giúp bấm chính xác bằng ngón tay.
- [x] Xác minh rằng trên màn hình Mobile, khung Tóm tắt đơn hàng và nút "Tiến hành thanh toán" xếp dọc rõ ràng và không bị che khuất nội dung.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [x] Xác minh rằng khi số lượng đang ở mức 1, bấm nút giảm (-) sẽ không làm số lượng giảm xuống 0 hoặc giá trị âm.
- [x] Xác minh rằng khi người dùng xóa trắng ô số lượng hoặc nhập giá trị nhỏ hơn 1 rồi nhấp chuột ra ngoài, hệ thống tự động đặt lại số lượng tối thiểu là 1 và lưu lại.
- [x] Xác minh rằng khi người dùng nhập số lượng vượt quá lượng hàng khả dụng trong kho, dòng sản phẩm hiển thị thông báo cảnh báo không đủ hàng tồn kho màu đỏ.
- [x] Xác minh rằng khi bấm nút xóa (biểu tượng dấu nhân màu đỏ), mặt hàng đó bị xóa khỏi giỏ và tổng tiền giỏ hàng được tính lại ngay lập tức.
- [x] Xác minh rằng khi xóa toàn bộ sản phẩm trong giỏ hàng, bảng giỏ hàng chuyển sang giao diện thông báo giỏ hàng trống kèm liên kết quay lại danh mục sản phẩm.

### 5) Kiểm thử hồi quy (Regression)

- [x] Xác minh rằng sau khi cập nhật số lượng tự động trên trang giỏ hàng, bấm nút "Tiến hành thanh toán" chuyển sang trang thanh toán với đúng danh sách và tổng tiền đã cập nhật.
- [x] Xác minh rằng giỏ hàng thu nhỏ (Mini-cart) trên thanh điều hướng mở ra hiển thị đúng các mặt hàng, đơn vị mua và tổng số tiền đồng bộ với bảng giỏ hàng.
- [x] Xác minh rằng thanh Header và Footer dùng chung không bị lỗi giao diện hoặc mất tương tác sau khi chu kỳ cập nhật tự động hoàn tất.
