# Test cases — Danh mục sản phẩm & Bộ lọc sỉ (Wholesale Shop Catalog)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh trang danh mục sản phẩm sỉ, cơ chế lọc theo ngành hàng, tìm kiếm từ khóa không dấu/có dấu, sắp xếp theo giá và phân trang hiển thị sản phẩm.

## Phạm vi (Scope)

- Trong phạm vi:
  - Bố cục danh mục sản phẩm (`/public/shop.php`).
  - Bộ lọc danh mục ngành hàng ở thanh bên (Sidebar).
  - Thanh tìm kiếm sản phẩm theo tên / từ khóa (hỗ trợ tiếng Việt có dấu và không dấu).
  - Tùy chọn sắp xếp (Mới nhất, Giá tăng dần, Giá giảm dần, Bán chạy nhất).
  - Lưới hiển thị danh sách sản phẩm kèm quy cách đóng gói sỉ B2B.
  - Phân trang hoặc tải thêm khi danh sách có nhiều trang.
  - Trạng thái rỗng khi không tìm thấy sản phẩm nào khớp với bộ lọc.
- Ngoài phạm vi:
  - So sánh chi tiết thông số giữa hai sản phẩm trên cùng một bảng.
  - Bộ lọc thuộc tính nâng cao theo nhà sản xuất nước ngoài.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Cột bộ lọc danh mục cố định bên trái, lưới sản phẩm 3 cột bên phải |
| Mobile | Có | Bộ lọc dạng nút bấm mở ngăn kéo (Drawer/Modal), lưới sản phẩm 1-2 cột |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Cơ sở dữ liệu có sẵn nhiều mặt hàng thuộc ít nhất 3 danh mục khác nhau.
- [ ] Các sản phẩm đang ở trạng thái hoạt động và còn hàng khả dụng.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng tiêu đề trang danh mục, thanh điều hướng phân cấp (Breadcrumb) và số lượng sản phẩm tìm thấy hiển thị rõ ràng.
- [ ] Xác minh rằng danh sách các danh mục ở thanh bên hiển thị tên danh mục kèm số đếm sản phẩm tương ứng.
- [ ] Xác minh rằng ô tìm kiếm từ khóa và danh sách tùy chọn sắp xếp hiển thị đầy đủ ở đầu danh sách sản phẩm.
- [ ] Xác minh rằng mỗi thẻ sản phẩm hiển thị ảnh đại diện sắc nét, tên sản phẩm, nhãn quy cách đóng gói sỉ và đơn giá sỉ.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng khi người dùng nhấp chọn một danh mục cụ thể (ví dụ: Nước giải khát), danh sách chỉ hiển thị các sản phẩm thuộc danh mục đó.
- [ ] Xác minh rằng khi nhập từ khóa tìm kiếm (ví dụ: "coca" hoặc "mì") và nhấn Enter, danh sách lọc ra đúng các sản phẩm liên quan.
- [ ] Xác minh rằng chức năng tìm kiếm hỗ trợ tìm kiếm không dấu (ví dụ: gõ "gao" vẫn tìm thấy "Gạo ST25").
- [ ] Xác minh rằng khi chọn sắp xếp "Giá tăng dần", các sản phẩm được sắp xếp từ mức giá sỉ thấp nhất đến cao nhất.
- [ ] Xác minh rằng khi chọn sắp xếp "Giá giảm dần", các sản phẩm được sắp xếp từ mức giá sỉ cao nhất đến thấp nhất.
- [ ] Xác minh rằng khi nhấp vào nút chuyển trang tại thanh phân trang, danh sách tải trang tiếp theo chính xác.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, thanh bên bộ lọc nằm cố định bên trái và lưới sản phẩm hiển thị rộng rãi bên phải.
- [ ] Xác minh rằng trên màn hình Mobile, bộ lọc tự động ẩn vào nút mở bộ lọc tiện lợi giúp tiết kiệm không gian màn hình.
- [ ] Xác minh rằng trên màn hình Mobile, lưới sản phẩm hiển thị dạng thẻ dọc cân đối và người dùng có thể cuộn lướt nhẹ nhàng.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi tìm kiếm từ khóa không có trong hệ thống (ví dụ: "xyz999"), giao diện hiển thị thông báo không tìm thấy sản phẩm phù hợp.
- [ ] Xác minh rằng thông báo không tìm thấy có đính kèm nút "Xem tất cả sản phẩm" để người dùng dễ dàng quay lại danh mục đầy đủ.
- [ ] Xác minh rằng các sản phẩm hết hàng hoặc tạm khóa không hiển thị trên danh mục bán hàng công khai.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng bấm nút "Thêm vào giỏ" nhanh tại mỗi thẻ sản phẩm trong danh mục cập nhật đúng số đếm giỏ hàng ở thanh tiêu đề.
- [ ] Xác minh rằng bấm biểu tượng trái tim tại mỗi thẻ sản phẩm thêm chính xác sản phẩm vào danh sách yêu thích.
