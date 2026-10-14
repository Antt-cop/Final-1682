# Test cases — Trang chủ & Tìm kiếm bằng hình ảnh AI (Home & AI Visual Search)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh giao diện trang chủ, các khối sản phẩm nổi bật/bán chạy và tính năng nhận diện sản phẩm thông minh bằng hình ảnh sử dụng Gemini Vision AI.

## Phạm vi (Scope)

- Trong phạm vi:
  - Banner Slider chính và thông điệp chào mừng khách sỉ B2B.
  - Khối danh mục ngành hàng nổi bật (Nước giải khát, Mì ăn liền, Gạo sạch, Thực phẩm đóng hộp).
  - Khối sản phẩm bán chạy, sản phẩm mới và sản phẩm khuyến mãi sỉ.
  - Nút bấm mở cửa sổ tìm kiếm bằng hình ảnh trên thanh tiện ích hoặc thanh tìm kiếm.
  - Tải lên hình ảnh sản phẩm từ máy tính/điện thoại hoặc kéo thả ảnh vào vùng tải lên.
  - Phân tích hình ảnh bằng trí tuệ nhân tạo (AI Vision) và nhận diện tên mặt hàng.
  - Hiển thị danh sách sản phẩm gợi ý khớp với hình ảnh và thao tác xem chi tiết / thêm vào giỏ.
- Ngoài phạm vi:
  - Tìm kiếm sản phẩm bằng giọng nói (Voice Search).
  - Quét mã vạch trực tiếp từ camera thời gian thực (Barcode Scanner).

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Banner toàn chiều rộng, lưới sản phẩm 4 cột, cửa sổ AI dạng modal trung tâm |
| Mobile | Có | Banner co giãn tỷ lệ, lưới sản phẩm 2 cột, modal AI tối ưu vừa màn hình cảm ứng |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Cơ sở dữ liệu có sẵn danh mục và sản phẩm mẫu có hình ảnh rõ ràng.
- [ ] Khóa API Gemini Vision đã được cấu hình trong hệ thống để xử lý nhận diện ảnh.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng banner quảng bá trên trang chủ hiển thị đầy đủ hình ảnh, tiêu đề và nút kêu gọi hành động mua hàng sỉ.
- [ ] Xác minh rằng danh mục ngành hàng nổi bật hiển thị đầy đủ hình ảnh đại diện và tên danh mục.
- [ ] Xác minh rằng các thẻ sản phẩm hiển thị đầy đủ ảnh sản phẩm, tên, quy cách đóng gói sỉ B2B và đơn giá sỉ.
- [ ] Xác minh rằng biểu tượng máy ảnh phục vụ tìm kiếm hình ảnh hiển thị rõ ràng tại thanh tìm kiếm đầu trang.
- [ ] Xác minh rằng khi bấm biểu tượng máy ảnh, cửa sổ tìm kiếm bằng hình ảnh hiển thị ở giữa màn hình với hướng dẫn tải ảnh rõ ràng.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng các slide trên banner chính tự động chuyển tiếp mượt mà hoặc chuyển đổi chính xác khi bấm nút điều hướng trái/phải.
- [ ] Xác minh rằng khi nhấp vào một danh mục nổi bật, hệ thống điều hướng đến danh mục sản phẩm tương ứng.
- [ ] Xác minh rằng khi nhấp vào thẻ sản phẩm bất kỳ, hệ thống chuyển hướng đến trang chi tiết của sản phẩm đó.
- [ ] Xác minh rằng tại cửa sổ tìm kiếm hình ảnh, người dùng có thể chọn tải lên tệp ảnh chụp sản phẩm từ thiết bị thành công.
- [ ] Xác minh rằng trong quá trình hệ thống gửi ảnh và phân tích AI, giao diện hiển thị hiệu ứng đang xử lý trực quan.
- [ ] Xác minh rằng sau khi phân tích hoàn tất, hệ thống hiển thị tên sản phẩm nhận diện được và danh sách các sản phẩm phù hợp có trong kho hàng.
- [ ] Xác minh rằng người dùng có thể bấm vào sản phẩm trong danh sách kết quả AI để xem chi tiết hoặc thêm trực tiếp vào giỏ hàng.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, lưới sản phẩm hiển thị đều đặn 4 cột và hiệu ứng rê chuột phóng to thẻ sản phẩm mượt mà.
- [ ] Xác minh rằng trên màn hình Mobile, lưới sản phẩm tự động thu về 2 cột, hình ảnh và chữ hiển thị cân đối không bị vỡ giao diện.
- [ ] Xác minh rằng trên màn hình Mobile, cửa sổ tìm kiếm bằng hình ảnh cho phép chụp ảnh trực tiếp từ camera của điện thoại hoặc chọn từ thư viện ảnh.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi người dùng tải lên tệp không phải định dạng hình ảnh (như tệp văn bản hoặc tệp nén), hệ thống hiển thị cảnh báo định dạng không hợp lệ.
- [ ] Xác minh rằng khi tải lên hình ảnh sản phẩm không liên quan đến hàng hóa cửa hàng phân phối, hệ thống hiển thị thông báo không tìm thấy sản phẩm phù hợp.
- [ ] Xác minh rằng khi bấm nút đóng hoặc nhấp chuột ra ngoài vùng cửa sổ tìm kiếm AI, cửa sổ modal đóng lại ngay lập tức và trang chủ giữ nguyên trạng thái.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng thanh điều hướng đầu trang và chân trang luôn hiển thị cố định và các liên kết hoạt động chính xác khi cuộn trang chủ.
- [ ] Xác minh rằng thao tác tìm kiếm bằng hình ảnh không làm ảnh hưởng đến số đếm giỏ hàng và danh sách yêu thích hiện có của người dùng.
