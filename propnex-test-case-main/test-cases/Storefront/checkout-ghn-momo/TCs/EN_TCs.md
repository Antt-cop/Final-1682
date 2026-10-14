# Test Cases — Checkout & Payment Flow (GHN & MoMo)

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify complete flow of adding products to cart, calculating GHN shipping fees based on address, and completing orders via COD or MoMo.

## Scope

- In scope:
  - Add to cart and shopping cart overview (`/public/cart.php`).
  - Shipping address form on Checkout page (`/public/checkout.php`).
  - GHN automated shipping fee calculation via Province/District/Ward selection.
  - Order placement via COD and MoMo sandbox simulator (`/public/momo_simulator.php`).
  - Order confirmation screen and verification on Order Tracking (`/public/tracking.php`).
- Out of scope:
  - International credit card processing (Stripe/PayPal).
  - Corporate wholesale contract workflows.

## Viewport Matrix

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Multi-column cart table, 2-column Checkout layout with sticky summary |
| Mobile | Yes | Stacked cart item cards, single-column touch-friendly Checkout layout |

## Preconditions

- [x] Web server is running at `http://localhost/dongkhoistore`.
- [x] At least 1 product in database with available stock > 5.
- [x] GHN API integration and MoMo Sandbox simulator configured.

## Test Cases

### 1) Shopping Cart

- [x] Verify that clicking "Add to Cart" on the product detail page displays a success notification.
- [x] Verify that the cart badge count in the Header navigation increments accurately after adding an item.
- [x] Verify that on the cart page (`/public/cart.php`), product items, unit prices, quantities, and line totals display correctly.
- [x] Verify that the customer can increase/decrease item quantities or remove an item from the cart.
- [x] Verify that when the cart is empty, an empty cart state message and a "Continue Shopping" button are displayed.

### 2) Shipping Address Form & GHN Shipping Fee Calculation

- [x] Verify that the Checkout form displays all necessary input fields: Full Name, Phone, Email, Province/City, District, Ward, Detailed Address.
- [x] Verify that before selecting a Province/City, the District and Ward dropdowns remain disabled or empty.
- [x] Verify that selecting Province/City, District, and Ward triggers the GHN API to calculate and display the accurate shipping fee.
- [x] Verify that the order grand total recalculates dynamically as Subtotal + GHN Shipping Fee.
- [x] Verify that submitting the order with required fields empty displays proper validation error messages.

### 3) Cash on Delivery (COD) Checkout

- [x] Verify that selecting "COD" payment method and clicking "Place Order" successfully creates the order and redirects to the confirmation page.
- [x] Verify that a GHN tracking code is generated and associated with the new order.

### 4) MoMo Sandbox Payment

- [x] Verify that selecting "MoMo Wallet" and clicking "Place Order" redirects to the MoMo sandbox simulator (`/public/momo_simulator.php`).
- [x] Verify that the order ID, payable amount, and partner code on the MoMo simulator match the order details.
- [x] Verify that clicking "Confirm Successful Payment" on the simulator redirects to `/public/momo_return.php` and updates order status to Paid.

### 5) Order Tracking

- [x] Verify that entering the newly created order ID on the Tracking page (`/public/tracking.php`) displays the correct order details and status timeline.

### 6) B2B Packaging Hierarchy & WMS Inventory Management

- [x] Verify beverages (Coca-Cola, Pepsi) display packaging spec `1 Thùng = 4 Lốc = 24 Lon (1 Lốc = 6 Lon)` with unit selector (Lốc vs Thùng).
- [x] Verify instant noodles (Hảo Hảo, Omachi) are sold by `Thùng (30 Gói)` with packaging spec `1 Thùng = 30 Gói`.
- [x] Verify cup noodles (Handy Hảo Hảo) are sold by `Thùng (24 Ly)` with packaging spec `1 Thùng = 24 Ly`.
- [x] Verify price dynamically updates based on selected packaging unit on Storefront product detail.
- [x] Verify WMS inventory decomposition calculates clean whole boxes and packs while dropping odd loose units ("bỏ số dư ra").
- [x] Verify order placement and checkout deduct stock in exact base units (`quantity * unit_ratio`).
- [x] Verify Admin Inventory (`/admin/inventory.php`) displays B2B packaging specifications and enables box-level stock adjustments.

