# Test cases — Danh sách yêu thích (Wishlist)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh chức năng quản lý danh sách sản phẩm yêu thích của khách hàng, đảm bảo yêu cầu bảo mật chỉ người dùng đăng nhập mới truy cập được và đồng bộ dữ liệu chuẩn quy cách đóng gói sỉ B2B.

## Phạm vi (Scope)

- Trong phạm vi:
  - Kiểm tra phân quyền truy cập trang danh sách yêu thích (Yêu cầu đăng nhập).
  - Điều hướng chuyển tiếp sang trang đăng nhập và tự động quay lại sau khi đăng nhập thành công.
  - Hiển thị danh sách sản phẩm yêu thích (Hình ảnh, tên, quy cách đóng gói sỉ, đơn giá sỉ, tồn kho kho vận).
  - Thao tác thêm sản phẩm vào danh sách yêu thích từ trang chi tiết sản phẩm.
  - Thao tác xóa từng sản phẩm hoặc xóa toàn bộ danh sách yêu thích.
  - Thao tác thêm nhanh sản phẩm từ danh sách yêu thích vào giỏ hàng.
  - Giao diện trạng thái trống khi chưa có sản phẩm nào.
- Ngoài phạm vi:
  - Chia sẻ danh sách yêu thích qua mạng xã hội ngoài hệ thống.
  - Phân loại danh sách yêu thích thành nhiều thư mục con.

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bảng danh sách sản phẩm dạng bảng ngang đầy đủ các cột thông tin, quy cách đóng gói và thao tác |
| Mobile | Có | Bảng cuộn ngang mượt mà, các nút bấm thao tác tối ưu kích thước cảm ứng |

## Tiền điều kiện (Preconditions)

- [ ] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống.
- [ ] Cơ sở dữ liệu có sẵn các sản phẩm phân phối sỉ đang ở trạng thái kinh doanh.
- [ ] Có tài khoản khách hàng đã đăng ký hợp lệ trong hệ thống.

## Kịch bản kiểm thử (Test Cases)

### 1) Phân quyền & Điều hướng bảo mật (Access Control & Authentication Guard)

- [ ] Xác minh rằng khi người dùng chưa đăng nhập bấm vào liên kết Danh sách yêu thích trên thanh điều hướng, hệ thống không cho phép xem và tự động chuyển hướng đến trang Đăng nhập.
- [ ] Xác minh rằng tại trang Đăng nhập, hệ thống hiển thị thông báo nhắc nhở màu vàng yêu cầu người dùng đăng nhập để xem danh sách yêu thích.
- [ ] Xác minh rằng sau khi người dùng nhập thông tin và đăng nhập thành công, hệ thống tự động đưa người dùng quay trở lại đúng trang Danh sách yêu thích thay vì trang chủ.

### 2) Giao diện & Thành phần hiển thị (UI & Elements)

- [ ] Xác minh rằng tiêu đề trang, thanh điều hướng và đường dẫn phân cấp (Breadcrumb) hiển thị rõ ràng và điều hướng chính xác.
- [ ] Xác minh rằng trên thanh tiện ích đầu trang, biểu tượng trái tim cùng liên kết Danh sách yêu thích hiển thị đồng bộ.
- [ ] Xác minh rằng trong trang quản lý tài khoản cá nhân, ô dịch vụ Danh sách yêu thích có liên kết trực tiếp đến trang danh sách yêu thích.
- [ ] Xác minh rằng khi danh sách yêu thích trống, hệ thống hiển thị biểu tượng trái tim viền đỏ, thông báo trạng thái trống và nút kêu gọi khám phá sản phẩm.
- [ ] Xác minh rằng bảng danh sách yêu thích hiển thị đầy đủ các cột: Hình ảnh, Sản phẩm & Quy cách, Đơn giá sỉ, Tồn kho, Đặt hàng và Xóa.
- [ ] Xác minh rằng mỗi dòng sản phẩm hiển thị đúng quy cách đóng gói sỉ B2B (ví dụ: số lốc hoặc số chai trong thùng, số túi trong bao) và thời gian lưu sản phẩm.
- [ ] Xác minh rằng trạng thái tồn kho kho vận hiển thị chính xác số lượng quy đổi (ví dụ: Còn hàng kèm số thùng hoặc bao khả dụng trong kho).

### 3) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng tại trang chi tiết sản phẩm, khi người dùng bấm nút "Thêm vào yêu thích", hệ thống lưu sản phẩm thành công và chuyển trạng thái nút sang đã yêu thích.
- [ ] Xác minh rằng sản phẩm vừa thêm xuất hiện ngay lập tức trong bảng danh sách yêu thích với thông tin hình ảnh, tên và giá sỉ chính xác.
- [ ] Xác minh rằng người dùng có thể bấm nút "Thêm vào giỏ" trực tiếp từ danh sách yêu thích để mở cửa sổ đặt hàng hoặc đưa sản phẩm vào giỏ hàng.
- [ ] Xác minh rằng khi người dùng bấm biểu tượng thùng rác màu đỏ tại một dòng sản phẩm và xác nhận trong hộp thoại, sản phẩm đó bị xóa khỏi danh sách yêu thích.
- [ ] Xác minh rằng khi người dùng bấm nút "Xóa tất cả" và xác nhận, toàn bộ sản phẩm trong danh sách bị xóa và giao diện chuyển về trạng thái trống.

### 4) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên màn hình Desktop, bảng dữ liệu mở rộng đầy đủ các cột, các nút bấm thao tác xếp ngang cân đối và dễ nhìn.
- [ ] Xác minh rằng trên màn hình Mobile, bảng danh sách có thể cuộn ngang nhẹ nhàng, hình ảnh thu nhỏ vừa vặn và nút bấm không bị tràn khung hình.

### 5) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng sản phẩm đã có trong danh sách yêu thích nếu được bấm thêm lại sẽ không bị trùng lặp dòng trong bảng dữ liệu.
- [ ] Xác minh rằng đối với sản phẩm đã hết hàng trong kho, cột tồn kho hiển thị nhãn hết hàng và nút đặt hàng chuyển sang trạng thái tạm khóa để tránh đặt nhầm.
- [ ] Xác minh rằng nếu người dùng hủy bỏ (chọn Cancel) trên hộp thoại xác nhận khi bấm xóa, sản phẩm vẫn được giữ nguyên trong danh sách.

### 6) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng thanh Header và Footer dùng chung vẫn hiển thị đầy đủ và không bị vỡ giao diện trên trang danh sách yêu thích.
- [ ] Xác minh rằng việc thêm hoặc xóa sản phẩm khỏi danh sách yêu thích không làm ảnh hưởng đến số lượng và nội dung hàng hóa trong giỏ hàng.
