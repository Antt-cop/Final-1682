# Test Cases — Admin Inventory & WMS Management

**Module:** Admin  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify warehouse management (WMS) inventory controls: tracking stock in wholesale master packaging multiples (Boxes/Sacks), minimum stock threshold alerts, inline stock restocks, and zero loose remainder integrity.

## Scope

- In scope:
  - Storable products inventory ledger table (`/admin/inventory.php`).
  - Stock condition summary metric cards (Total Storable Items, Low Stock Warnings, Out of Stock Alerts).
  - Filtering by inventory status (All, Low Stock, Out of Stock) and category.
  - Displaying decomposed inventory in wholesale units (available Boxes/Sacks) alongside base inventory totals.
  - Inline stock restock updates via master Box/Sack units or base units.
  - Setting safe minimum inventory threshold levels.
  - Visual status badges (In Stock, Low Stock Warning, Out of Stock).
- Out of scope:
  - 3D physical warehouse rack slotting and bin location management.
  - Warehouse RFID gate scanners or automated scale integration.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Wide inventory ledger with inline quick-update restock inputs on each row |
| Mobile | Yes | Smooth horizontal table swiping with touch-friendly restock controls |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Administrator user is authenticated into the admin portal.
- [ ] Storable products with configured minimum stock alert thresholds exist in the database.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the Inventory Management heading and alert metric cards (Total Items, Low Stock, Out of Stock) are clearly rendered.
- [ ] Verify that the inventory table displays all required columns: ID, Product & Packaging Spec, Converted Stock (Boxes/Sacks), Base Stock, Min Level, Alert Status, and Quick Restock.
- [ ] Verify that status warning badges (Safe Green, Warning Yellow, Out-of-Stock Red) reflect current inventory levels accurately.
- [ ] Verify that the filter toolbar allows instant one-click filtering for low stock products.

### 2) Happy-Path Business Flow

- [ ] Verify that entering a new Box or Sack quantity into the inline input and saving accurately computes base inventory based on conversion ratios.
- [ ] Verify that upon successful save, the system presents an inventory updated confirmation message citing the new base unit count.
- [ ] Verify that the updated inventory level immediately reflects in the table without discrepancy.
- [ ] Verify that the administrator can modify the safe minimum stock alert threshold and save changes successfully.
- [ ] Verify that filtering by "Low Stock" isolates products whose inventory is at or below the minimum threshold.
- [ ] Verify that filtering by "Out of Stock" isolates products whose inventory count is exactly 0.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, the ledger table maximizes screen width with aligned input controls and save buttons.
- [ ] Verify that on Mobile viewports, the inventory table supports smooth horizontal panning without virtual keyboard interference.
- [ ] Verify that on Mobile viewports, top metric overview cards arrange cleanly into a 2-column responsive grid.

### 4) Edge Cases & UI States

- [ ] Verify that submitting a negative stock value is rejected with an invalid stock quantity warning.
- [ ] Verify that fresh/perishable non-storable products are automatically excluded from the WMS inventory ledger.
- [ ] Verify that restocking an item above its threshold automatically transitions its badge from "Low Stock" to "Safe Stock".

### 5) Regression Checks

- [ ] Verify that restocked quantities in the admin portal synchronize identically with stock levels shown on Storefront product detail pages.
- [ ] Verify that when an item's stock increases from 0 to positive, the Storefront "Out of Stock" badge immediately clears allowing purchases.
