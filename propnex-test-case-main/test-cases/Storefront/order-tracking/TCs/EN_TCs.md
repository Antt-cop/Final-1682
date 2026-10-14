# Test Cases — Order Tracking & GHN Shipment Milestones

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify order lookup functionality, real-time Giao Hàng Nhanh (GHN) tracking timeline updates, and recipient fulfillment details for B2B wholesale buyers.

## Scope

- In scope:
  - Order tracking page interface (`/public/tracking.php`).
  - Recent order history listing for authenticated buyers.
  - Tracking code search input and submission button.
  - Real-time shipping timeline milestone displays (Ready to pick, In transit, Delivering, Delivered).
  - Shipping fulfillment details: Recipient name, address, GHN shipping fee, COD collection amount.
  - Order itemization breakdown with packaging specifications and line totals.
- Out of scope:
  - Mid-transit recipient address re-routing requests.
  - Parcel damage claims submitted directly through logistics APIs.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Two-column layout: Recent order list on the left, Timeline details on the right |
| Mobile | Yes | Vertically stacked layout with clean timeline nodes optimized for vertical scrolling |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] At least 1 existing order with a valid GHN tracking code exists in the database.
- [ ] GHN API integration credentials are valid and responsive.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the order tracking page heading, tracking input field, and lookup button are prominently rendered.
- [ ] Verify that the user's recent orders table displays Order ID, Date, Order Status, and Total Amount clearly.
- [ ] Verify that the shipment milestone timeline visually illustrates delivery phases with dedicated status icons.
- [ ] Verify that the recipient panel clearly displays recipient name, phone number, destination address, and payment method.

### 2) Happy-Path Business Flow

- [ ] Verify that selecting an order from the recent orders list instantly loads and reveals its delivery journey details.
- [ ] Verify that submitting a valid GHN tracking code in the input field queries and presents real-time tracking information from GHN.
- [ ] Verify that when an order is "Delivering", the current timestamp and transit depot location are clearly presented.
- [ ] Verify that when an order is successfully completed, the final delivered milestone is marked with a green checkmark.
- [ ] Verify that the underlying items table accurately details ordered products, packaging units, and the grand order amount.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, the order summary and timeline are arranged in two balanced columns for convenient inspection.
- [ ] Verify that on Mobile viewports, the delivery timeline stacks vertically with compact spacing avoiding overlapping text.
- [ ] Verify that on Mobile viewports, the historical order list supports effortless touch scrolling and item selection.

### 4) Edge Cases & UI States

- [ ] Verify that entering an invalid or non-existent tracking code displays an order not found alert.
- [ ] Verify that clicking the search button with an empty input displays a validation prompt requesting an order code.
- [ ] Verify that for newly created orders pending carrier pickup, the system clearly shows a "Awaiting pickup from seller" status.

### 5) Regression Checks

- [ ] Verify that Order ID and pricing details displayed on the tracking page match the sales invoice records identically.
- [ ] Verify that navigation links returning to the catalog or homepage from the tracking screen operate reliably.
