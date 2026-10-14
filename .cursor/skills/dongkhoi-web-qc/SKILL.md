---
name: dongkhoi-web-qc
description: >-
  Generates high-level Vietnamese and English UI/UX manual test checklists for
  Dong Khoi Store (CanteenB2B) Web application across Storefront and Admin
  modules on Desktop and Mobile viewports. Use when the user asks to create web
  test cases, TCs, VN_TCs, EN_TCs, review an e-commerce feature, or test web
  flows from user descriptions, mockups, or Figma links.
---

# Dong Khoi Store Web QC

Kỹ năng tự động tạo danh sách kiểm thử (test checklist) giao diện và trải nghiệm người dùng (**UI/UX**) cho hệ thống web **Dong Khoi Store (CanteenB2B)** trên cả hai môi trường **Desktop và Mobile**.

Mặc định: Luôn tạo song song cả hai tệp `TCs/VN_TCs.md` (Tiếng Việt) và `TCs/EN_TCs.md` (Tiếng Anh, đối ứng 1:1).

---

## 1. Cấu trúc đường dẫn đầu ra (Output Paths)

Gốc: `test-cases/{Phân hệ}/{Tên-tính-năng-hoặc-ID}/TCs/`
Trong đó `{Phân hệ}` là một trong hai thư mục:
- `Storefront/` (Dành cho giao diện khách hàng mua sắm)
- `Admin/` (Dành cho giao diện quản trị viên)

| Tệp đầu ra | Ngôn ngữ | Mục đích |
|---|---|---|
| `TCs/VN_TCs.md` | Tiếng Việt | Bảng kiểm thử chức năng & giao diện chính |
| `TCs/EN_TCs.md` | Tiếng Anh | Bảng kiểm thử đối ứng 1:1 với bản Tiếng Việt |

*Ví dụ:* `test-cases/Storefront/checkout-ghn-momo/TCs/VN_TCs.md` hoặc `test-cases/Admin/order-processing/TCs/VN_TCs.md`.

---

## 2. Quy trình thực hiện (Workflow)

```
QC Progress:
- [ ] 1. Đọc ngữ cảnh tính năng / yêu cầu (Storefront hoặc Admin)
- [ ] 2. Đối chiếu màn hình và luồng thao tác (Tra cứu screen-map.md)
- [ ] 3. Áp dụng kỹ thuật thiết kế Test Case (EP, BVA, State transition,...)
- [ ] 4. Viết TCs/VN_TCs.md
- [ ] 5. Viết TCs/EN_TCs.md (đối ứng 1:1 với bản VN)
```

### Bước 1: Tiếp nhận ngữ cảnh tính năng
Thu thập các thông tin:
1. Phân hệ cần kiểm thử: Storefront hay Admin.
2. Mục tiêu tính năng, dữ liệu đầu vào, luồng thao tác mong đợi (Happy path).
3. Các trường hợp ngoại lệ (hết hàng, chưa chọn địa chỉ, lỗi thanh toán).
4. Thiết kế đính kèm (Figma URL, ảnh chụp mockup màn hình, hoặc mô tả luồng).

### Bước 2: Đối chiếu bản đồ màn hình (Screen Map)
Tham khảo tệp [screen-map.md](screen-map.md) để:
- Xác định đúng route (ví dụ `/public/checkout.php`, `/admin/orders.php`).
- Xác định cách hiển thị trên Desktop (≥ 1024px) vs Mobile (< 1024px).
- Bao quát các trạng thái UI: Trống (Empty), Đang tải (Loading), Thành công, Lỗi (Validation Error).

### Bước 3: Áp dụng kỹ thuật thiết kế kiểm thử (Test Design Techniques)
- **Equivalence Partitioning (EP):** Phân chia lớp dữ liệu hợp lệ và không hợp lệ (ví dụ: giỏ hàng có hàng vs giỏ hàng rỗng; đã đăng nhập vs khách vãng lai).
- **Boundary Value Analysis (BVA):** Giá trị biên đối với số lượng đặt hàng (tối thiểu 1 sản phẩm, tối đa bằng tồn kho kho hàng, vượt quá tồn kho).
- **Decision Table:** Kết hợp phương thức thanh toán (COD, MoMo QR) và trạng thái đơn hàng.
- **State Transition:** Chu trình trạng thái đơn hàng (Pending → Confirmed → Shipping → Delivered / Cancelled).

### Bước 4 & 5: Viết bộ test case song ngữ
Sử dụng mẫu chuẩn tại [tc-template.md](tc-template.md).

---

## 3. Quy chuẩn ngôn ngữ kiểm thử (UI/UX Assertions)

Kiểm thử hướng tới **người kiểm thử thủ công (manual tester) hoặc browser agent** thao tác trên trình duyệt thật.

### Quy định BẮT BUỘC:
- **Tiền tố Tiếng Việt:** `- [ ] Xác minh rằng …`
- **Tiền tố Tiếng Anh:** `- [ ] Verify that …`
- **Trạng thái khởi tạo:** Luôn để trống checkbox: `- [ ]`. Không tự ý đánh dấu `- [x]`.
- Tập trung vào quan sát trực quan: Tiêu đề, nút bấm, thông báo toast, danh sách thẻ, bảng biểu, modal.

### Những điều TUYỆT ĐỐI KHÔNG làm trong Test Case:
- Không đưa tên file code (`.php`, `.js`), tên class CSS (`.btn-primary`), tên hàm hoặc câu lệnh SQL vào nội dung test case.
- Không kiểm tra thông số chi tiết mã màu hex hoặc pixel (trừ khi có quy chuẩn design hệ thống).
- Không tự bịa ra đường link URL không thể điều hướng từ giao diện người dùng. Đối với trường hợp kiểm thử trang 404, hướng dẫn mở origin + chuỗi ngẫu nhiên ~10 ký tự.

---

## 4. Ma trận hiển thị Desktop vs Mobile

Hệ thống web Dong Khoi Store chạy responsive trên cả hai giao diện:
- **Desktop (≥ 1024px):** Menu ngang, lọc sidebar, bảng dữ liệu mở rộng.
- **Mobile (< 1024px):** Menu Hamburger, bộ lọc drawer trượt, giỏ hàng dạng thẻ dọc.

Chỉ bổ sung test case riêng rẽ giữa Desktop và Mobile khi hai giao diện có hành vi tương tác khác biệt.

---

## 5. Tài liệu liên quan

- Bản đồ màn hình hệ thống: [screen-map.md](screen-map.md)
- Mẫu tệp test case chuẩn: [tc-template.md](tc-template.md)
- Kỹ năng xác thực tự động trên trình duyệt: [dongkhoi-web-verify](../dongkhoi-web-verify/SKILL.md)
