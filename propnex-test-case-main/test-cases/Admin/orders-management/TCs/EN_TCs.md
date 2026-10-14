# Test Cases — Order Management & Invoice (Admin Orders)

**Module:** Admin  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify order list rendering, status filtering, order status transitions, GHN fulfillment push, and invoice printing.

## Scope

- In scope:
  - Orders table on `/admin/orders.php`.
  - Order status filters (All, Pending, Confirmed, Shipping, Delivered, Cancelled).
  - Order details view / modal.
  - Updating order status and pushing fulfillment orders to GHN.
  - Printable sales invoice template at `/admin/invoice.php`.
- Out of scope:
  - Year-end aggregate financial analytics (`/admin/reports.php`).

## Viewport Matrix

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Fixed admin sidebar on the left, full multi-column orders table |
| Mobile | Yes | Admin sidebar toggler, horizontal scroll enabled for data tables |

## Preconditions

- [ ] Web server is running at `http://localhost/dongkhoistore`.
- [ ] Logged in with valid Administrator credentials at `/admin/`.
- [ ] At least 3 orders existing with different statuses in the database.

## Test Cases

### 1) Orders Table Display

- [ ] Verify that the orders table displays all required columns: Order ID, Customer Name, Phone, Total Amount, Payment Method, Status, Date Created, Actions.
- [ ] Verify that status badges are color-coded appropriately (Pending: Yellow/Amber, Confirmed: Blue, Delivered: Green, Cancelled: Red).

### 2) Order Status Filters

- [ ] Verify that selecting the "Pending" status tab filters only orders currently in Pending state.
- [ ] Verify that selecting the "Delivered" status tab filters only orders that have been successfully delivered.
- [ ] Verify that count badges on the status filter tabs accurately match the number of filtered records.

### 3) Order Details & Status Transitions

- [ ] Verify that clicking "View Details" opens a modal showing purchased items, quantities, unit prices, delivery address, and GHN shipping fees.
- [ ] Verify that the administrator can change an order status from "Pending" to "Confirmed".
- [ ] Verify that a success alert is shown and the order list updates promptly without page reload issues.

### 4) GHN Fulfillment Push & Invoice Printing

- [ ] Verify that for eligible orders, clicking "Push to GHN" initiates the carrier dispatch request and stores the tracking code.
- [ ] Verify that clicking "Print Invoice" navigates to the invoice preview (`/admin/invoice.php?order_id={id}`) with print-ready styling and a Print button.
- [ ] Verify that store information, customer details, and item totals on the invoice match the order record exactly.
