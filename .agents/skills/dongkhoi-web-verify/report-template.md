# Mẫu Báo cáo Kiểm thử — Dong Khoi Store (Test Report Template)

Đường dẫn lưu tệp: `test-cases/{Storefront|Admin}/{feature-name}/Reports/report-{hhmm-dd-mm-yyyy}.md`  
Ảnh bằng chứng lỗi (evidence): `Reports/imgs/report-{hhmm-dd-mm-yyyy}/issue-{n}-{slug}-{desktop|mobile}.png`  
Nhúng trong report: `./imgs/report-{hhmm-dd-mm-yyyy}/...` (thư mục ảnh cùng tên stem với tệp `report-*.md`).

---

```markdown
# Báo cáo kết quả kiểm thử — {Tên tính năng / Mã chức năng}

**Ngày thực thi:** {dd/mm/yyyy}  
**Phân hệ kiểm thử:** {Storefront / Admin}  
**Nền tảng kiểm thử:** Web ứng dụng Dong Khoi Store (PHP/MySQL)  
**Tài liệu tham chiếu:** [EN_TCs.md](../TCs/EN_TCs.md) | [VN_TCs.md](../TCs/VN_TCs.md)  
**Môi trường:** `http://localhost/dongkhoistore` (Laragon Local)  
**Người thực hiện:** QA / Browser Verification Agent  

---

## 1. Tổng quan kết quả kiểm thử (Executive Summary)

- **Đánh giá chung:** {Đạt / Chưa đạt / Đạt có điều kiện}
- **Tóm tắt:** Đã thực hiện kiểm tra toàn diện {Tổng số} kịch bản kiểm thử trên cả hai giao diện **Desktop** (1440×900) và **Mobile** (390×844). Kết quả ghi nhận {Số lượng Pass} case Đạt ({Tỉ lệ %}), {Số lượng Fail} case Lỗi và {Số lượng Blocked/Skipped} case chưa thực hiện được. Tất cả các case Pass đã được cập nhật đánh dấu `- [x]` trực tiếp trong cả hai tệp [VN_TCs.md](../TCs/VN_TCs.md) và [EN_TCs.md](../TCs/EN_TCs.md).

| Chỉ số | Số lượng | Tỉ lệ (%) |
|---|---|---|
| **Tổng số Test Cases** | {Total} | 100% |
| **Passed (Đạt)** | {Pass} | {% Pass} |
| **Failed (Lỗi)** | {Fail} | {% Fail} |
| **Blocked / Skipped** | {Blocked} | {% Blocked} |

---

## 2. Danh sách các vấn đề phát hiện (Defects / Issues)

<!-- Nếu không phát hiện lỗi, ghi: "Không phát hiện lỗi trong phiên kiểm thử. Tất cả các trường hợp đều hoạt động chính xác theo yêu cầu thiết kế." -->

### Issue 1: {Tên ngắn gọn của lỗi}

- **Tóm tắt lỗi (Summary):** {Mô tả hiện tượng lỗi}
- **Các bước tái hiện (Steps to Reproduce):**
  1. {Bước 1: ví dụ Mở trang chủ http://localhost/dongkhoistore}
  2. {Bước 2: ví dụ Bấm vào giỏ hàng và chuyển đến trang thanh toán}
  3. {Bước 3: ví dụ Chọn tỉnh thành nhưng phí vận chuyển GHN không được tính toán}
- **Thực tế (Actual):** {Hiện tượng thực tế đang xảy ra}
- **Kỳ vọng (Expected):** {Kết quả mong đợi theo tài liệu nghiệp vụ}
- **Bằng chứng (Evidence):**
  - ![Issue 1 — Desktop](./imgs/report-{hhmm-dd-mm-yyyy}/issue-1-{slug}-desktop.png)
  - ![Issue 1 — Mobile](./imgs/report-{hhmm-dd-mm-yyyy}/issue-1-{slug}-mobile.png)

---

## 3. Chi tiết kết quả kiểm thử theo từng Nhóm chức năng

### 1) {Tên nhóm chức năng 1}
- [x] Passed - {X}/{Y} cases. {Ghi chú kết quả}

### 2) {Tên nhóm chức năng 2}
- [ ] Failed - {X}/{Y} cases. Phát hiện lỗi tại {mô tả ngắn}, xem chi tiết tại Issue 1.

---

## 4. Đề xuất & Kết luận (Recommendations)

- {Đề xuất hướng khắc phục hoặc các điểm cần lưu ý trước khi release/demo}
```
