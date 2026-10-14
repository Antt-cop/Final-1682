# Test Cases — Cart Real-Time Auto-Update & Bulk Pricing Tiers

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify real-time cart quantity auto-updates, instant responsiveness of quantity adjustment buttons, automated B2B bulk pricing tier calculations, and dynamic order summary synchronization without page refreshes.

## Scope

- In scope:
  - Quantity increment (+) and decrement (-) button interactions per cart line item.
  - Direct keyboard input editing with debounce optimization to prevent request flooding.
  - User-facing visual feedback and auto-save indicator status.
  - Automatic B2B volume discount tier application (20+ units: 5% off, 50+ units: 10% off, 100+ units: 15% off).
  - Dynamic recalculation of line totals, Sub Total, Bulk Discounts, and Grand Total within the Order Summary.
  - Real-time stock sufficiency checks per line item (In Stock vs Insufficient Stock).
  - Synchronization of the header cart badge counter and mini-cart dropdown list.
  - Line item deletion and empty cart state handling.
- Out of scope:
  - Third-party promotional voucher/coupon redemption.
  - Custom gift packaging options.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Full-width table layout with centered quantity controls, packaging badges, and summary panel |
| Mobile | Yes | Horizontally scrollable cart table with touch-friendly adjustment buttons |

## Preconditions

- [x] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [x] At least 2 wholesale products with abundant stock (≥ 200 units available) exist in the database.
- [x] At least 1 item is added to the shopping cart prior to test execution.

## Test Cases

### 1) UI & Element Presence

- [x] Verify that the cart table displays all required columns: Product & Packaging Spec, Unit Price, Quantity, Bulk Discount, Total, and Remove.
- [x] Verify that each quantity control includes an inline decrement (-) button, number input field, and increment (+) button placed together cleanly.
- [x] Verify that each product row displays its wholesale unit badge, B2B packaging specification, and real-time available stock status below the title.
- [x] Verify that the Order Summary panel displays three distinct rows: Sub Total, Bulk Discounts, and Grand Total.
- [x] Verify that the bulk pricing policy banner clearly states the 3 discount tiers (20+ units: save 5%, 50+ units: save 10%, 100+ units: save 15%).
- [x] Verify that the "Proceed to Checkout" call-to-action button is prominently visible and accessible at the bottom of the cart page.

### 2) Happy-Path Business Flow

- [x] Verify that clicking the increment (+) button increases the quantity by 1 and automatically triggers cart recalculation without requiring a manual update button click.
- [x] Verify that clicking the decrement (-) button decreases the quantity by 1 and automatically triggers cart recalculation without requiring a manual update button click.
- [x] Verify that while the auto-update request is in flight, the status indicator displays an animated spinner icon and auto-saving text.
- [x] Verify that upon successful save, the status indicator displays a green confirmation message confirming automatic cart update.
- [x] Verify that when typing a new quantity directly into the input field, the system automatically saves the update shortly after typing stops.
- [x] Verify that when an item quantity reaches 20 to 49 units, the discount column displays a 5% discount badge along with the calculated deduction amount.
- [x] Verify that when an item quantity reaches 50 to 99 units, the discount column displays a 10% discount badge along with the calculated deduction amount.
- [x] Verify that when an item quantity reaches 100 units or more, the discount column displays a 15% discount badge along with the calculated deduction amount.
- [x] Verify that each line item total is accurately calculated as Unit Price multiplied by Quantity minus the Volume Discount amount.
- [x] Verify that Sub Total, Bulk Discounts, and Grand Total values in the Order Summary panel automatically refresh to match the table item totals.
- [x] Verify that the cart count badge on the header navigation bar automatically synchronizes with the updated total cart item count.

### 3) Desktop vs Mobile Responsive Layout

- [x] Verify that on Desktop viewports, the cart table layout expands naturally with balanced column widths and centered quantity steppers.
- [x] Verify that on Mobile viewports, the cart table scrolls smoothly horizontally with touch targets of at least 36px for easy fingertip tapping.
- [x] Verify that on Mobile viewports, the Order Summary panel and the Proceed to Checkout button stack vertically without UI truncation or overlapping.

### 4) Edge Cases & UI States

- [x] Verify that when the quantity is at 1, clicking the decrement (-) button does not reduce the value to 0 or negative numbers.
- [x] Verify that when the quantity input is cleared or set to a number less than 1 and loses focus, the system automatically restores the value to 1 and saves.
- [x] Verify that entering a quantity greater than available warehouse inventory displays a red insufficient stock warning with available quantity.
- [x] Verify that clicking the remove button (red cross icon) removes the item from the cart and immediately recalculates the cart totals.
- [x] Verify that removing all items from the cart transitions the view to an empty cart notice with a link back to the product catalog.

### 5) Regression Checks

- [x] Verify that after auto-updating quantities in the cart, clicking "Proceed to Checkout" loads the checkout page with matching item quantities and totals.
- [x] Verify that opening the header Mini-cart dropdown displays synchronized item lists, units, and grand total matching the main cart view.
- [x] Verify that shared Header and Footer components remain fully intact and interactive following automated cart update cycles.
