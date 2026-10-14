# Test cases — Quy cách đóng gói FMCG & Chọn đơn vị sỉ B2B (FMCG Packaging Units & Unit Selection)

**Phân hệ:** Storefront  
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)  
**Mục tiêu:** Xác minh hệ thống quy cách đóng gói sỉ B2B đa cấp (Lốc/Thùng cho đồ uống, Gói/Ly/Thùng cho mì, Túi/Bao cho gạo), cơ chế cho phép khách hàng tự do chọn đơn vị mua sỉ phù hợp với quy mô đại lý/căn tin, chuyển đổi giá tức thì và quản trị tồn kho kho vận WMS chính xác.

## Phạm vi (Scope)

- Trong phạm vi:
  - Hiển thị quy chuẩn đóng gói sỉ B2B trên trang chi tiết sản phẩm và giỏ hàng.
  - Lựa chọn đơn vị mua sỉ đối với sản phẩm Gạo (chọn mua theo Túi 5kg hoặc Bao 25kg = 5 Túi).
  - Lựa chọn đơn vị mua sỉ đối với Nước giải khát (chọn mua theo Lốc 6 lon/chai hoặc Thùng 4 lốc = 24 lon/chai).
  - Quy cách đóng gói sỉ Mì gói (1 Thùng = 30 Gói) và Mì ly (1 Thùng = 24 Ly).
  - Cơ chế tự động đổi giá hiển thị theo đơn vị mua được chọn trên giao diện chi tiết sản phẩm.
  - Phân rã tồn kho kho vận WMS theo Thùng/Bao/Lốc và làm tròn loại bỏ số dư lẻ theo quy chuẩn sỉ B2B.
  - Thêm sản phẩm vào giỏ hàng theo từng đơn vị mua độc lập và hiển thị thành các dòng riêng biệt trong giỏ hàng.
  - Khấu trừ tồn kho gốc chính xác khi cùng một sản phẩm được đặt mua ở nhiều đơn vị khác nhau.
- Ngoài phạm vi:
  - Bán lẻ từng lon nước giải khát hoặc từng kg gạo rời xé lẻ khỏi bao gói nguyên seal.
  - Đóng gói theo yêu cầu tem nhãn riêng của khách hàng (OEM/ODM).

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800).

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Bộ chọn đơn vị dạng các nút thẻ ngang, hiển thị rõ tên đơn vị và đơn giá từng loại |
| Mobile | Có | Bộ chọn đơn vị co giãn linh hoạt, dễ bấm chọn bằng ngón tay trên màn hình cảm ứng |

## Tiền điều kiện (Preconditions)

- [x] Máy chủ web đang hoạt động bình thường và truy cập được hệ thống tại `http://localhost/dongkhoistore`.
- [x] Cơ sở dữ liệu có sẵn sản phẩm Gạo ST25 5kg (có đơn vị Túi và Bao) và Nước ngọt Coca-Cola (có đơn vị Lốc và Thùng).
- [x] Tồn kho của các sản phẩm lưu kho tuân thủ bội số chuẩn của quy cách đóng gói và không chứa số dư lẻ.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [x] Xác minh rằng tại trang chi tiết sản phẩm, khung thông tin hiển thị rõ quy cách đóng gói B2B (ví dụ: "1 Bao = 5 Túi = 25 Kg" hoặc "1 Thùng = 4 Lốc = 24 Lon (1 Lốc = 6 Lon)").
- [x] Xác minh rằng khu vực "Chọn đơn vị mua" hiển thị đầy đủ các tùy chọn đơn vị khả dụng kèm đơn giá tương ứng của từng đơn vị.
- [x] Xác minh rằng dòng thông tin tồn kho kho vận hiển thị số lượng quy đổi theo đơn vị lớn nhất (ví dụ: số Thùng hoặc số Bao còn trong kho) thay vì hiển thị số dư lẻ.
- [x] Xác minh rằng bảng thông số kỹ thuật bên dưới hiển thị đúng thông tin Đơn vị xuất kho kèm số lượng quy đổi sang đơn vị cơ sở.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [x] Xác minh rằng đối với Gạo ST25, người dùng có thể nhấp chọn giữa đơn vị "Túi" (5kg) và "Bao" (25kg = 5 Túi).
- [x] Xác minh rằng khi người dùng chọn đơn vị "Túi", đơn giá hiển thị trên trang cập nhật ngay lập tức sang giá của một túi (190.000 ₫).
- [x] Xác minh rằng khi người dùng chọn đơn vị "Bao", đơn giá hiển thị trên trang cập nhật ngay lập tức sang giá của một bao (950.000 ₫).
- [x] Xác minh rằng đối với Nước ngọt Coca-Cola, người dùng có thể nhấp chọn giữa đơn vị "Lốc" (6 Lon) và "Thùng" (24 Lon).
- [x] Xác minh rằng khi người dùng chọn mua theo Lốc và bấm "Thêm vào giỏ", sản phẩm được đưa vào giỏ với đơn vị Lốc và giá tiền tương ứng.
- [x] Xác minh rằng khi người dùng tiếp tục chọn mua theo Thùng của cùng sản phẩm đó và thêm vào giỏ, giỏ hàng ghi nhận thành 2 dòng mặt hàng riêng biệt (1 dòng Lốc và 1 dòng Thùng).
- [x] Xác minh rằng trên trang Giỏ hàng, mỗi dòng sản phẩm hiển thị huy hiệu phân biệt rõ ràng tên đơn vị (Lốc, Thùng, Túi, Bao).
- [x] Xác minh rằng đối với Mì gói, hệ thống hiển thị quy cách đóng gói chuẩn 1 Thùng = 30 Gói và cho phép đặt mua theo Thùng.
- [x] Xác minh rằng đối với Mì ly Handy, hệ thống hiển thị quy cách đóng gói chuẩn 1 Thùng = 24 Ly và cho phép đặt mua theo Thùng.

### 3) Hiển thị trên Desktop vs Mobile

- [x] Xác minh rằng trên màn hình Desktop, các thẻ nút chọn đơn vị mua được xếp ngang đều đặn, đường viền xanh lá nổi bật khi được kích hoạt.
- [x] Xác minh rằng trên màn hình Mobile, các nút chọn đơn vị mua tự động xuống hàng gọn gàng, nội dung tên đơn vị và giá tiền không bị tràn viền.
- [x] Xác minh rằng thông báo quy cách đóng gói sỉ hiển thị vừa vặn trong khung màn hình điện thoại mà không làm phát sinh thanh cuộn ngang ngoài ý muốn.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [x] Xác minh rằng đối với sản phẩm chỉ bán theo 1 đơn vị duy nhất (ví dụ Thùng mì), bộ chọn đơn vị chỉ hiển thị một tùy chọn mặc định duy nhất.
- [x] Xác minh rằng khi khách hàng đặt mua kết hợp cả đơn vị nhỏ (Lốc/Túi) và đơn vị lớn (Thùng/Bao), hệ thống tính tổng trừ tồn kho kho vận chính xác theo đơn vị cơ sở.
- [x] Xác minh rằng khi tổng số lượng quy đổi của các đơn vị kết hợp vượt quá tồn kho thực tế của sản phẩm, hệ thống hiển thị cảnh báo không đủ hàng tương ứng trên từng dòng.
- [x] Xác minh rằng nếu một sản phẩm có tồn kho bằng 0, giao diện hiển thị nhãn Hết hàng và không cho phép thực hiện thêm vào giỏ đối với mọi đơn vị đóng gói.

### 5) Kiểm thử hồi quy (Regression)

- [x] Xác minh rằng các sản phẩm có nhiều đơn vị đóng gói khi thêm vào Danh sách yêu thích hiển thị đúng quy cách đóng gói sỉ và đơn giá mặc định.
- [x] Xác minh rằng khi chuyển sang trang Thanh toán và Theo dõi đơn hàng, đơn vị mua đã chọn (Túi, Bao, Lốc, Thùng) vẫn được thể hiện chính xác trong chi tiết đơn hàng.
