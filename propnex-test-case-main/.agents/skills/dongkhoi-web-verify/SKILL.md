---
name: dongkhoi-web-verify
description: >-
  Executes browser-based verification of Dong Khoi Store (CanteenB2B) web
  features across Storefront and Admin using test cases in EN_TCs.md / VN_TCs.md.
  Verifies both Desktop and Mobile viewports, marks '- [x]' for passed test cases,
  captures defect screenshots with annotations under Reports/imgs/{report-name}/,
  and generates a professional Vietnamese test report (report-{hhmm-dd-mm-yyyy}.md).
---

# Dong Khoi Store Web Verification Workflow

Quy trình tự động hóa / bán tự động hóa xác thực (verify) giao diện và tính năng của trang web **Dong Khoi Store (CanteenB2B)** trên trình duyệt dựa trên bộ test case đã tạo trong `test-cases/{Storefront|Admin}/{feature-name}/TCs/EN_TCs.md` (hoặc `VN_TCs.md`).

---

## 1. Cấu trúc đường dẫn & Tệp đầu ra

```
test-cases/{Storefront|Admin}/{feature-name}/
├── TCs/
│   ├── VN_TCs.md                # Test checklist tiếng Việt (cập nhật '- [x]' cho case Đạt)
│   └── EN_TCs.md                # Test checklist tiếng Anh (cập nhật '- [x]' cho case Đạt)
├── Docs/                        # Tài liệu đặc tả / ngữ cảnh nghiệp vụ (nếu có)
└── Reports/
    ├── imgs/
    │   └── report-{hhmm-dd-mm-yyyy}/     # Ảnh bằng chứng lỗi của đúng file report cùng tên
    │       └── issue-{n}-{slug}-{viewport}.png
    └── report-{hhmm-dd-mm-yyyy}.md       # Báo cáo tổng kết kiểm thử (nhúng ảnh bằng chứng)
```

### Quy định cập nhật kết quả:
- **Case Passed (Đạt):** Đánh dấu `- [x]` trực tiếp vào cả 2 tệp `EN_TCs.md` và `VN_TCs.md`.
- **Case Failed (Lỗi / Chưa đạt):** **GIỮ NGUYÊN `- [ ]`** (không đánh dấu) trong cả 2 tệp TCs.
- **Thư mục báo cáo:** `test-cases/{Storefront|Admin}/{feature-name}/Reports/`.
- **Tên tệp báo cáo:** `report-{hhmm-dd-mm-yyyy}.md` (ví dụ: `report-1430-12-10-2026.md`).
- **Thư mục ảnh issue:** `Reports/imgs/{tên-file-report-không-đuôi-md}/` (ví dụ: `Reports/imgs/report-1430-12-10-2026/`).

---

## 2. Quy trình thực thi chi tiết (Step-by-Step Workflow)

```
Verification Progress:
- [ ] 1. Nạp ngữ cảnh và danh sách test case từ EN_TCs.md / VN_TCs.md
- [ ] 2. Khởi tạo môi trường kiểm thử (Desktop & Mobile) trên Laragon Local
- [ ] 3. Thực thi kiểm thử tuần tự theo từng nhóm chức năng
- [ ] 4. Đánh dấu '- [x]' các case Pass trong EN_TCs.md và VN_TCs.md
- [ ] 5. Ghi nhận lỗi và chụp ảnh bằng chứng nếu có case Failed
- [ ] 6. Tổng hợp & xuất file Reports/report-{hhmm-dd-mm-yyyy}.md
- [ ] 7. Hoàn tất phiên kiểm thử
```

### Bước 1: Nạp Test Cases & Cấu hình môi trường
1. Đọc nội dung tệp `TCs/EN_TCs.md` để lấy danh sách các assertion cần xác thực.
2. Xác định URL mục tiêu:
   - **Mặc định Storefront:** `http://localhost/dongkhoistore`
   - **Mặc định Admin:** `http://localhost/dongkhoistore/admin`
   - Hoặc URL do người dùng cung cấp trong câu lệnh.
3. Chuẩn bị tài khoản:
   - Khách vãng lai: không cần đăng nhập.
   - Khách hàng đã đăng ký: đăng nhập qua `/public/login.php`.
   - Quản trị viên: đăng nhập tại `/admin/` (hoặc thông qua session hợp lệ).

### Bước 2: Ma trận Viewport kiểm thử
Mọi tính năng web cần được kiểm tra trên cả 2 độ phân giải:
- **Desktop Viewport:** Kích thước khuyến nghị `1440 × 900` hoặc `1280 × 800`.
- **Mobile Viewport:** Kích thước khuyến nghị `390 × 844` hoặc `360 × 800`.

### Bước 3: Nguyên tắc tương tác (UI-First Navigation)
- **Tuyệt đối tuân thủ điều hướng từ giao diện:** Mở trang chủ → click menu danh mục → click sản phẩm → bấm thêm giỏ hàng → bấm thanh toán. Không tự gõ URL trực tiếp nếu trên UI có lối vào tương ứng.
- **Trường hợp kiểm thử 404:** Chỉ mở origin + chuỗi ngẫu nhiên ~10 ký tự (ví dụ `http://localhost/dongkhoistore/x9KpL2aBcQ`).

### Bước 4: Chụp bằng chứng lỗi (Defect Evidence)
Khi một case bị **Failed**:
1. Giữ nguyên `- [ ]` trong tệp TCs.
2. Chụp màn hình vùng bị lỗi trên cả Desktop và Mobile (nếu ảnh hưởng cả hai).
3. Khoanh đỏ hoặc chỉ rõ khu vực xảy ra lỗi.
4. Đặt tên tệp theo quy chuẩn:
   `Reports/imgs/report-{hhmm-dd-mm-yyyy}/issue-{n}-{slug}-{viewport}.png`

### Bước 5: Lập báo cáo kết quả kiểm thử
Tạo tệp `report-{hhmm-dd-mm-yyyy}.md` sử dụng [report-template.md](report-template.md).
- Thống kê tỷ lệ Pass / Fail / Blocked.
- Liệt kê chi tiết từng Issue kèm các bước tái hiện (Steps to Reproduce) và ảnh bằng chứng.
- Đưa ra đề xuất cải thiện hoặc danh sách các điểm cần lưu ý trước khi bàn giao.