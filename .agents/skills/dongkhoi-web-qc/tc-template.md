# Test Case Templates — Dong Khoi Store (CanteenB2B)

Tài liệu mẫu test case song ngữ (Tiếng Việt & Tiếng Anh).  
Viết cả hai tệp `TCs/VN_TCs.md` và `TCs/EN_TCs.md` đồng nhất về cấu trúc và số lượng case.

Quy ước đường dẫn lưu tệp:
- Storefront: `test-cases/Storefront/{feature-or-ticket-id}/TCs/`
- Admin: `test-cases/Admin/{feature-or-ticket-id}/TCs/`

Tiền tố kiểm thử bắt buộc:
- **Tiếng Việt:** `- [ ] Xác minh rằng …`
- **Tiếng Anh:** `- [ ] Verify that …`

---

## 1. Mẫu Tiếng Việt — `TCs/VN_TCs.md`

```markdown
# Test cases — {Mã tính năng / ID} {Tên tính năng kiểm thử}

**Phân hệ:** {Storefront / Admin}
**Nền tảng:** Web ứng dụng Dong Khoi Store (PHP/MySQL)
**Viewport:** Desktop (≥ 1024px) và Mobile (< 1024px)
**Mục tiêu:** {Tóm tắt mục tiêu kiểm thử trong 1-2 câu hướng người dùng}

## Phạm vi (Scope)

- Trong phạm vi (In scope): {Màn hình / Luồng nghiệp vụ / Giao diện kiểm thử}
- Ngoài phạm vi (Out of scope): {Các chức năng không liên quan hoặc để phase sau}

## Ma trận Viewport

Kiểm thử trên cả **Desktop** (1440×900 / 1280×800) và **Mobile** (390×844 / 360×800) trừ các trường hợp chỉ định riêng cho một loại thiết bị.

| Viewport | Bắt buộc | Ghi chú giao diện |
|---|---|---|
| Desktop | Có | Giao diện thanh điều hướng ngang, bảng dữ liệu đầy đủ |
| Mobile | Có | Menu Hamburger, bố cục dạng thẻ (card), thanh cuộn cảm ứng |

## Tiền điều kiện (Preconditions)

- [ ] Web server Laragon đang chạy và truy cập được `http://localhost/dongkhoistore`.
- [ ] Dữ liệu sản phẩm và danh mục đã có trong cơ sở dữ liệu (`mock_data.sql`).
- [ ] Trạng thái người dùng: {Khách vãng lai / Đã đăng nhập tài khoản khách hàng / Tài khoản Admin}.
- [ ] Thiết lập địa chỉ nhận hàng và phương thức thanh toán thử nghiệm.

## Kịch bản kiểm thử (Test Cases)

### 1) Kiểm tra Giao diện & Thành phần (UI & Elements)

- [ ] Xác minh rằng {tiêu đề / khối nội dung / nút bấm} hiển thị rõ ràng và đúng vị trí thiết kế.
- [ ] Xác minh rằng hình ảnh sản phẩm tải đầy đủ và có ảnh fallback khi ảnh gốc bị lỗi.

### 2) Luồng nghiệp vụ chính (Happy Path Flow)

- [ ] Xác minh rằng người dùng có thể thực hiện thành công: {Bước 1 → Bước 2 → Kết quả hoàn tất}.
- [ ] Xác minh rằng sau khi hoàn tất, hệ thống hiển thị thông báo thành công và chuyển hướng đúng trang.

### 3) Hiển thị trên Desktop vs Mobile

- [ ] Xác minh rằng trên Desktop, {menu điều hướng / bộ lọc / giỏ hàng} hiển thị đầy đủ ở dạng cột.
- [ ] Xác minh rằng trên Mobile, {menu rút gọn / bộ lọc drawer / giỏ hàng xếp dọc} hiển thị tối ưu cảm ứng.

### 4) Xử lý ngoại lệ & Trạng thái dữ liệu (Edge Cases & States)

- [ ] Xác minh rằng khi không có dữ liệu ({giỏ hàng rỗng / tìm kiếm không thấy SP}), giao diện hiển thị thông báo trạng thái trống rõ ràng.
- [ ] Xác minh rằng khi bỏ trống trường bắt buộc, hệ thống hiển thị cảnh báo lỗi tương ứng.
- [ ] Xác minh rằng khi nhập số lượng vượt quá tồn kho, hệ thống cảnh báo và giới hạn số lượng tối đa.

### 5) Kiểm thử hồi quy (Regression)

- [ ] Xác minh rằng các thành phần dùng chung (Header, Footer, Mini-cart) vẫn hoạt động ổn định trên toàn bộ luồng.
```

---

## 2. Mẫu Tiếng Anh — `TCs/EN_TCs.md`

```markdown
# Test Cases — {Feature / Ticket ID} {Feature Name}

**Module:** {Storefront / Admin}
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)
**Goal:** {1-2 concise sentences explaining user-facing testing objective}

## Scope

- In scope: {Screen / User Flow / Verified features}
- Out of scope: {Excluded features or separate phase items}

## Viewport Matrix

Run every case across both **Desktop** (1440×900) and **Mobile** (390×844) unless specifically marked for one viewport.

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Horizontal navigation, full table layouts |
| Mobile | Yes | Hamburger menu, card layouts, touch-friendly elements |

## Preconditions

- [ ] Laragon web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Product catalog and categories are seeded in the database.
- [ ] User authentication state: {Guest / Logged-in Customer / Administrator}.
- [ ] Shipping address and test payment methods configured.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that {title / section / CTA button} is displayed clearly in the expected location.
- [ ] Verify that product images load properly with fallback image for missing assets.

### 2) Happy-Path Business Flow

- [ ] Verify that the user can successfully complete: {Step 1 → Step 2 → Expected result}.
- [ ] Verify that upon completion, a success confirmation is displayed and redirected appropriately.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop, {navigation bar / filter sidebar / cart table} is displayed in full multi-column layout.
- [ ] Verify that on Mobile, {hamburger menu / filter drawer / card layout} is responsive and touch-friendly.

### 4) Edge Cases & UI States

- [ ] Verify that when no data is found ({empty cart / search no results}), a clear empty state message is shown.
- [ ] Verify that required input validations display meaningful error hints when submitted empty.
- [ ] Verify that ordering a quantity exceeding available stock triggers a warning and caps the quantity.

### 5) Regression Checks

- [ ] Verify that shared components (Header, Footer, Mini-cart) function consistently without side effects.
```

---

## 3. Bảng ví dụ cách viết câu chuẩn (Language Examples)

| Phân hệ / Tính năng | Tiếng Việt (VN_TCs.md) | Tiếng Anh (EN_TCs.md) |
|---|---|---|
| Giỏ hàng (Cart) | Xác minh rằng bấm nút "Thêm vào giỏ hàng" hiển thị thông báo thành công và tăng số đếm trên icon giỏ hàng. | Verify that clicking "Add to Cart" displays a success notification and increments the cart icon badge. |
| Thanh toán (Checkout) | Xác minh rằng chọn Tỉnh/Thành, Quận/Huyện, Phường/Xã sẽ tự động kích hoạt tính phí vận chuyển GHN. | Verify that selecting Province, District, and Ward automatically triggers GHN shipping fee calculation. |
| Thanh toán MoMo | Xác minh rằng chọn phương thức MoMo và nhấn "Đặt hàng" sẽ chuyển hướng thành công đến trang thanh toán MoMo. | Verify that selecting MoMo payment and clicking "Place Order" redirects to the MoMo payment gateway. |
| Tìm kiếm ảnh AI | Xác minh rằng tải ảnh đồ uống lên modal tìm kiếm bằng hình ảnh sẽ hiển thị danh sách sản phẩm gợi ý liên quan. | Verify that uploading a beverage image to the visual search modal displays relevant matching products. |
| Quản lý đơn hàng (Admin) | Xác minh rằng admin có thể lọc đơn hàng theo trạng thái "Chờ xử lý" và bấm cập nhật sang "Đang giao". | Verify that the admin can filter orders by "Pending" status and update them to "Shipping". |
| Quản lý kho (Admin) | Xác minh rằng sản phẩm có tồn kho bằng 0 hiển thị nhãn "Hết hàng" và không cho phép khách đặt thêm. | Verify that products with 0 stock display an "Out of stock" badge and prevent customers from purchasing. |

---

## 4. Các lỗi cần tránh (Anti-patterns)

- **Không** đưa tên file mã nguồn, tên class PHP/CSS, tên hàm hay số dòng code vào checkbox test case.
- **Không** viết kiểm tra chi tiết mã màu hex hay pixel chính xác (trừ khi có spec design bắt buộc).
- **Không** đánh dấu sẵn `- [x]` khi vừa tạo test case — luôn để `- [ ]` cho người kiểm thử thực hiện.
- **Không** tạo URL không có đường dẫn từ UI (trừ URL 404 chuỗi ngẫu nhiên ~10 ký tự).
