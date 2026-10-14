# Dong Khoi Store — QA & Verification Framework

Hệ thống khung làm việc (QA & Verification framework) hỗ trợ AI Agent và Manual Tester kiểm thử tự động / bán tự động cho ứng dụng thương mại điện tử phân phối sỉ thực phẩm **Dong Khoi Store (CanteenB2B)** trên nền tảng Web (**Desktop và Mobile**).

---

## 📁 Cấu trúc thư mục (Repository Structure)

| Thư mục / Tệp | Mục đích |
|---|---|
| `.cursor/skills/` | Kỹ năng QA tuỳ biến cấu hình cho Cursor Agent |
| `.agents/skills/` | Kỹ năng QA tiêu chuẩn cho AI Agents (Antigravity, Claude Code, Codex,...) |
| `.cursor/rules/` | Quy tắc hành vi kiểm thử (ví dụ: `dongkhoi-ui-navigation.mdc`) |
| `test-cases/` | Kho lưu trữ kịch bản kiểm thử, tài liệu ngữ cảnh và báo cáo kiểm thử |
| `├── Storefront/` | Kịch bản kiểm thử phân hệ Khách hàng mua sắm (Giao diện Client) |
| `└── Admin/` | Kịch bản kiểm thử phân hệ Quản trị viên (Bảng điều khiển & Vận hành) |
| `.playwright-mcp/` | Cấu hình cho Playwright MCP Server |
| `.env.example` | Mẫu biến môi trường kiểm thử (Local Laragon / Staging) |

---

## 🛠️ Danh sách AI Skills hỗ trợ

| Kỹ năng (Skill) | Phạm vi mục tiêu | Sản phẩm đầu ra chính |
|---|---|---|
| [`dongkhoi-web-qc`](file:///.agents/skills/dongkhoi-web-qc/SKILL.md) | Phân hệ Storefront & Admin | Sinh danh sách test checklist song ngữ UI/UX: `VN_TCs.md` & `EN_TCs.md` |
| [`dongkhoi-web-verify`](file:///.agents/skills/dongkhoi-web-verify/SKILL.md) | Trình duyệt Web (Desktop & Mobile) | Thực thi kiểm thử trên browser, cập nhật checkbox `- [x]`, chụp ảnh bằng chứng lỗi khoanh vùng đỏ và xuất báo cáo `report-{hhmm-dd-mm-yyyy}.md` |

---

## 📂 Tổ chức thư mục kết quả kiểm thử (Test Output Hierarchy)

Các tài liệu kiểm thử được tổ chức theo từng phân hệ và mã chức năng:

```
test-cases/{Storefront|Admin}/{feature-name}/
├── TCs/
│   ├── VN_TCs.md          # Checklist kiểm thử tiếng Việt (được cập nhật '- [x]' khi Pass)
│   └── EN_TCs.md          # Checklist kiểm thử tiếng Anh (đối ứng 1:1 với bản VN)
├── Docs/                  # Tài liệu ngữ cảnh, phân tích luồng nghiệp vụ & rủi ro (context.md)
└── Reports/
    ├── report-{hhmm-dd-mm-yyyy}.md    # Báo cáo tổng kết phiên kiểm thử
    └── imgs/report-{hhmm-dd-mm-yyyy}/ # Ảnh chụp bằng chứng lỗi được nhúng vào báo cáo
```

### 📋 Danh mục toàn bộ Test Case trong hệ thống (13 Modules)

#### 🛍️ Phân hệ Khách hàng (Storefront)
| # | Mã tính năng / Thư mục | Tên chức năng | Bản Tiếng Việt | Bản Tiếng Anh |
|---|---|---|---|---|
| 1 | `cart-auto-update` | Cập nhật giỏ hàng tự động & Chiết khấu sỉ | [VN_TCs.md](test-cases/Storefront/cart-auto-update/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/cart-auto-update/TCs/EN_TCs.md) |
| 2 | `fmcg-packaging-units` | Quy cách đóng gói FMCG & Chọn đơn vị sỉ | [VN_TCs.md](test-cases/Storefront/fmcg-packaging-units/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/fmcg-packaging-units/TCs/EN_TCs.md) |
| 3 | `wishlist` | Danh sách yêu thích & Guard đăng nhập | [VN_TCs.md](test-cases/Storefront/wishlist/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/wishlist/TCs/EN_TCs.md) |
| 4 | `checkout-ghn-momo` | Thanh toán GHN & MoMo Sandbox | [VN_TCs.md](test-cases/Storefront/checkout-ghn-momo/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/checkout-ghn-momo/TCs/EN_TCs.md) |
| 5 | `home-ai-search` | Trang chủ & Tìm kiếm bằng hình ảnh AI | [VN_TCs.md](test-cases/Storefront/home-ai-search/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/home-ai-search/TCs/EN_TCs.md) |
| 6 | `shop-catalog` | Danh mục sản phẩm & Bộ lọc sỉ | [VN_TCs.md](test-cases/Storefront/shop-catalog/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/shop-catalog/TCs/EN_TCs.md) |
| 7 | `order-tracking` | Tra cứu đơn hàng & Vận đơn GHN | [VN_TCs.md](test-cases/Storefront/order-tracking/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/order-tracking/TCs/EN_TCs.md) |
| 8 | `customer-auth-account` | Xác thực & Quản lý tài khoản | [VN_TCs.md](test-cases/Storefront/customer-auth-account/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Storefront/customer-auth-account/TCs/EN_TCs.md) |

#### 🛡️ Phân hệ Quản trị (Admin)
| # | Mã tính năng / Thư mục | Tên chức năng | Bản Tiếng Việt | Bản Tiếng Anh |
|---|---|---|---|---|
| 9 | `orders-management` | Quản lý & xử lý đơn hàng | [VN_TCs.md](test-cases/Admin/orders-management/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Admin/orders-management/TCs/EN_TCs.md) |
| 10 | `products-management` | Quản lý sản phẩm B2B | [VN_TCs.md](test-cases/Admin/products-management/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Admin/products-management/TCs/EN_TCs.md) |
| 11 | `inventory-wms` | Quản lý tồn kho WMS | [VN_TCs.md](test-cases/Admin/inventory-wms/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Admin/inventory-wms/TCs/EN_TCs.md) |
| 12 | `categories-customers` | Quản lý danh mục & Khách hàng sỉ | [VN_TCs.md](test-cases/Admin/categories-customers/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Admin/categories-customers/TCs/EN_TCs.md) |
| 13 | `system-settings` | Cấu hình GHN & Mail SMTP | [VN_TCs.md](test-cases/Admin/system-settings/TCs/VN_TCs.md) | [EN_TCs.md](test-cases/Admin/system-settings/TCs/EN_TCs.md) |


---

## 🌐 Môi trường kiểm thử & Cấu hình (Environment Setup)

### 1. Môi trường ứng dụng
- **Local URL (Laragon):** `http://localhost/dongkhoistore`
- **Admin URL:** `http://localhost/dongkhoistore/admin`
- **Cơ sở dữ liệu:** MySQL database `dongkhoistore`

### 2. Tiêu chuẩn Viewport Responsive
- **Desktop Viewport:** Kích thước khuyến nghị `1440 × 900` hoặc `1280 × 800` (≥ 1024px).
- **Mobile Viewport:** Kích thước khuyến nghị `390 × 844` hoặc `360 × 800` (< 1024px).

---

## 🔌 Cấu hình MCP Server (Model Context Protocol)

### Playwright MCP Server
Được sử dụng bởi skill `dongkhoi-web-verify` để khởi động trình duyệt tương tác, chụp ảnh lỗi và xác thực trực quan:
- Cài đặt plugin Playwright trong Cursor hoặc cấu hình Playwright MCP trong `mcp_config.json`.
- Sử dụng các công cụ: `browser_navigate`, `browser_click`, `browser_fill_form`, `browser_take_screenshot`, `browser_resize`.

### Figma MCP Server (Tùy chọn)
Được sử dụng bởi `dongkhoi-web-qc` khi cần đọc giao diện mockup trực tiếp từ Figma link để đối chiếu component và bố cục trước khi sinh test cases.
