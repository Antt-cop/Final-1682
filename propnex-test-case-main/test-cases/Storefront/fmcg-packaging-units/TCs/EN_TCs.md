# Test Cases — FMCG Packaging Units & B2B Unit Selection

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify multi-tier B2B wholesale packaging specifications (Pack/Box for beverages, Pack/Cup/Box for noodles, Bag/Sack for rice), flexible unit selection for retail distributors and canteens, instant price switching, and accurate WMS stock decomposition without loose remainders.

## Scope

- In scope:
  - Display of standardized B2B wholesale packaging specifications on product details and cart pages.
  - Wholesale unit selection for Rice products (Bag 5kg vs Sack 25kg = 5 Bags).
  - Wholesale unit selection for Beverages (Pack 6 cans/bottles vs Box 4 packs = 24 cans/bottles).
  - Wholesale packaging specifications for Instant Noodles (Box = 30 packs) and Cup Noodles (Box = 24 cups).
  - Instant unit price switching upon toggling unit options on the product detail page.
  - WMS stock decomposition into Box/Sack/Pack multiples dropping loose remainders.
  - Adding items to cart under separate packaging units and managing distinct line items.
  - Accurate base stock inventory deduction when multiple packaging units of the same item are ordered.
- Out of scope:
  - Retail selling of single loose cans or unpackaged raw rice quantities.
  - Customized private-label packaging (OEM/ODM).

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Unit selector displayed as horizontal option cards with clear unit names and prices |
| Mobile | Yes | Responsive unit selector cards wrapping cleanly for fingertip touch interaction |

## Preconditions

- [x] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [x] Rice ST25 5kg (with Bag and Sack options) and Coca-Cola (with Pack and Box options) are seeded in the database.
- [x] Storable product inventory conforms to standard packaging multiples without loose remainders.

## Test Cases

### 1) UI & Element Presence

- [x] Verify that on the product detail page, the packaging box clearly displays the B2B packaging specification (e.g., "1 Bao = 5 Túi = 25 Kg" or "1 Thùng = 4 Lốc = 24 Lon (1 Lốc = 6 Lon)").
- [x] Verify that the "Select Purchase Unit" section presents all available unit options alongside their respective unit prices.
- [x] Verify that available warehouse stock is displayed in converted primary wholesale units (e.g., total Boxes or Sacks in stock) rather than loose remainders.
- [x] Verify that the product specifications table below accurately displays the warehouse dispatch unit and its base unit conversion rate.

### 2) Happy-Path Business Flow

- [x] Verify that for Rice ST25, users can toggle between "Túi" (Bag 5kg) and "Bao" (Sack 25kg = 5 Bags) units.
- [x] Verify that selecting the "Túi" unit immediately updates the displayed product price to the single bag price (190,000 ₫).
- [x] Verify that selecting the "Bao" unit immediately updates the displayed product price to the full sack price (950,000 ₫).
- [x] Verify that for Coca-Cola, users can toggle between "Lốc" (Pack of 6 cans) and "Thùng" (Box of 24 cans).
- [x] Verify that selecting the Pack unit and clicking "Add to Cart" adds the product to the shopping cart under the Pack unit and price.
- [x] Verify that subsequently selecting the Box unit for the same product and adding to cart creates two distinct line items in the cart (one Pack and one Box).
- [x] Verify that on the Cart page, each item row prominently displays its designated unit badge (Lốc, Thùng, Túi, Bao).
- [x] Verify that for Instant Bag Noodles, the system displays the standard specification of 1 Box = 30 Packs and permits Box purchasing.
- [x] Verify that for Handy Cup Noodles, the system displays the standard specification of 1 Box = 24 Cups and permits Box purchasing.

### 3) Desktop vs Mobile Responsive Layout

- [x] Verify that on Desktop viewports, unit selector option cards are evenly spaced horizontally with distinct green highlight borders when active.
- [x] Verify that on Mobile viewports, unit selector option cards wrap neatly without horizontal text clipping or overlapping.
- [x] Verify that the B2B packaging specification banner fits comfortably within mobile screen width without generating undesirable horizontal scrollbars.

### 4) Edge Cases & UI States

- [x] Verify that for products sold exclusively in one packaging unit (e.g., Noodle boxes), the unit selector displays only that single default option.
- [x] Verify that when purchasing a combination of sub-units (Pack/Bag) and master units (Box/Sack), the system deducts total inventory correctly in base units.
- [x] Verify that when total converted demand across combined units exceeds warehouse stock, an insufficient stock warning appears on each affected cart row.
- [x] Verify that if product inventory is 0, the interface displays an Out of Stock badge and prevents adding to cart across all packaging units.

### 5) Regression Checks

- [x] Verify that adding multi-unit products to the Wishlist retains and displays their wholesale packaging specifications and default pricing accurately.
- [x] Verify that proceeding to Checkout and Order Tracking preserves the selected packaging units (Túi, Bao, Lốc, Thùng) accurately in order details.
