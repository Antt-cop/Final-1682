# Test cases — Quản lý tồn kho WMS (Admin Inventory & WMS Management)

**Phân hệ:** Admin  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh tính năng quản trị kho vận WMS: theo dõi số lượng tồn kho theo đơn vị đóng gói sỉ (Thùng/Bao), cảnh báo mức tồn kho tối thiểu, nhập kho trực tiếp và duy trì tính toàn vẹn tồn kho không có số dư lẻ.

## Phạm vi (Scope)

- Trong phạm vi:
  - Bảng quản lý tồn kho các sản phẩm lưu trữ (`/admin/inventory.php`).
  - Thẻ tóm tắt mức độ tồn kho (Tổng mặt hàng lưu kho, Cảnh báo sắp hết hàng, Hết hàng).
  - Bộ lọc sản phẩm theo tình trạng tồn kho (Tất cả, Sắp hết hàng, Hết hàng) và danh mục ngành hàng.
  - Hiển thị phân rã số lượng tồn kho theo quy cách sỉ (số Thùng/Bao khả dụng) kèm tổng số đơn vị cơ sở.
  - Thao tác cập nhật nhanh số lượng nhập kho theo đơn vị Thùng/Bao lớn hoặc theo đơn vị cơ sở.
  - Cập nhật định mức tồn kho an toàn tối thiểu (Min stock level).
  - Nhãn cảnh báo trực quan tương ứng với trạng thái kho (Còn nhiều hàng, Sắp hết hàng, Hết hàng).
- Ngoài phạm vi:
  - Quản lý sơ đồ vị trí kệ hàng 3D trong kho vật lý.
  - Tích hợp cổng cân điện tử hoặc máy quét RFID tại cửa kho xuất nhập.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bảng kiểm kê trải rộng, form cập nhật tồn kho tích hợp trực tiếp trên từng dòng |
| Mobile | Có | Bảng kiểm kê cuộn ngang mượt mà, các nút nhập kho bấm dễ dàng trên màn hình cảm ứng |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [ ] Quản trị viên đã đăng nhập vào hệ thống quản trị.
- [ ] Trong cơ sở dữ liệu có các sản phẩm lưu trữ có cài đặt định mức tồn kho tối thiểu.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng tiêu đề trang Quản lý tồn kho và các thẻ số liệu cảnh báo (Tổng mặt hàng, Sắp hết hàng, Hết hàng) hiển thị rõ nét.
- [ ] Xác minh rằng bảng tồn kho hiển thị đầy đủ các cột: Mã, Tên sản phẩm & Quy cách sỉ, Tồn kho quy đổi (Thùng/Bao), Tổng tồn kho cơ sở, Mức tối thiểu, Trạng thái cảnh báo và Cập nhật kho.
- [ ] Xác minh rằng các nhãn cảnh báo (Còn hàng an toàn màu xanh, Sắp hết hàng màu vàng, Hết hàng màu đỏ) hiển thị chính xác theo số lượng.
- [ ] Xác minh rằng thanh lọc hỗ trợ lọc nhanh các sản phẩm sắp hết hàng chỉ với một cú nhấp chuột.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng khi nhập số Thùng hoặc số Bao mới vào ô cập nhật và bấm Lưu, hệ thống tự động quy đổi chính xác sang số đơn vị cơ sở theo tỷ lệ đóng gói.
- [ ] Xác minh rằng sau khi lưu thành công, hệ thống hiển thị thông báo xác nhận cập nhật tồn kho thành công kèm số lượng cơ sở mới.
- [ ] Xác minh rằng số lượng tồn kho mới được phản ánh ngay lập tức trên bảng dữ liệu mà không bị sai lệch số liệu.
- [ ] Xác minh rằng quản trị viên có thể điều chỉnh mức tồn kho an toàn tối thiểu và lưu thay đổi thành công.
- [ ] Xác minh rằng khi lọc theo trạng thái "Sắp hết hàng", bảng chỉ hiển thị đúng các mặt hàng có tồn kho nhỏ hơn hoặc bằng mức tồn kho tối thiểu.
- [ ] Xác minh rằng khi lọc theo trạng thái "Hết hàng", bảng chỉ hiển thị đúng các mặt hàng có tồn kho bằng 0.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, bảng dữ liệu mở rộng tối ưu không gian hiển thị, ô nhập liệu và nút cập nhật thẳng hàng ngay ngắn.
- [ ] Xác minh rằng trên màn hình Mobile, bảng kiểm kê cho phép vuốt cuộn ngang mượt mà và ô nhập số lượng không bị che khuất bởi bàn phím ảo.
- [ ] Xác minh rằng trên màn hình Mobile, các thẻ số liệu thống kê ở đầu trang xếp gọn gàng theo lưới 2 cột.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi quản trị viên nhập số lượng tồn kho là số âm, hệ thống từ chối lưu và đưa ra cảnh báo số lượng không hợp lệ.
- [ ] Xác minh rằng các sản phẩm thuộc nhóm hàng tươi sống không lưu kho (non-storable) tự động được loại trừ khỏi danh sách kiểm kê kho vận WMS.
- [ ] Xác minh rằng khi nhập số lượng tồn kho vượt định mức an toàn, trạng thái của mặt hàng tự động chuyển từ "Sắp hết hàng" sang "Còn hàng an toàn".

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng số lượng tồn kho vừa cập nhật trong trang Quản lý kho được đồng bộ chính xác với số lượng hiển thị trên trang Chi tiết sản phẩm ngoài Storefront.
- [ ] Xác minh rằng khi sản phẩm được nâng tồn kho từ 0 lên có hàng, nhãn "Hết hàng" ngoài Storefront lập tức biến mất và cho phép khách đặt mua lại bình thường.
