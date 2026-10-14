# Test Cases — Customer Wishlist

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify customer wishlist functionality, ensuring strict authentication gating, redirection handling, and integration with B2B wholesale packaging specifications.

## Scope

- In scope:
  - Access control verification for the wishlist page (Authentication required).
  - Redirection to the Login page with callback parameter and automatic return upon successful login.
  - Wishlist item listing (Product image, title, wholesale packaging spec, wholesale unit price, warehouse inventory level).
  - Adding products to the wishlist from the product detail page.
  - Removing individual items or clearing the entire wishlist.
  - Direct addition of items to the shopping cart from the wishlist.
  - Empty state presentation when no items are saved.
- Out of scope:
  - External social media wishlist sharing.
  - Organization into custom multi-tier wishlist folders.

## Viewport Matrix

Execute tests across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800) viewports.

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Full horizontal table displaying all packaging, price, and action columns |
| Mobile | Yes | Smooth horizontal table scrolling with touch-optimized action buttons |

## Preconditions

- [ ] Web server is actively running and accessible.
- [ ] Database contains active wholesale FMCG products.
- [ ] Valid registered customer account is available for authentication.

## Test Cases

### 1) Access Control & Authentication Guard

- [ ] Verify that when an unauthenticated guest clicks the Wishlist navigation link, access is restricted and the browser is redirected to the Login page.
- [ ] Verify that on the Login page, an alert banner informs the user that logging in is required to view their wishlist.
- [ ] Verify that after submitting valid credentials, the system automatically redirects the user back to the Wishlist page rather than the home page.

### 2) UI & Display Elements

- [ ] Verify that page title, breadcrumb navigation, and navigation links render clearly and link accurately.
- [ ] Verify that the top header utility bar displays the Wishlist link with a red heart icon consistently.
- [ ] Verify that within the My Account dashboard, the Wishlist service box links directly to the Wishlist page.
- [ ] Verify that when the wishlist contains no items, the system presents an outlined heart icon, an empty state message, and an action button to explore catalog items.
- [ ] Verify that the wishlist table displays all standard columns: Images, Product & Packaging Spec, Wholesale Unit Price, Stock, Order, and Remove.
- [ ] Verify that each product row clearly displays the B2B packaging specification (e.g., packs per box, cans per pack, bags per sack) and the date added.
- [ ] Verify that the warehouse inventory column accurately displays decomposed stock units (e.g., In Stock with remaining full boxes or sacks).

### 3) Happy Path Flow

- [ ] Verify that on the product detail page, clicking the "Add to wishlist" button saves the product and toggles the button state to saved.
- [ ] Verify that the newly added product appears immediately in the wishlist table with the correct thumbnail, title, and wholesale pricing.
- [ ] Verify that users can click the "Add to cart" button directly in the wishlist row to launch the order modal or place the item into the shopping cart.
- [ ] Verify that clicking the red trash can icon on a product row and confirming the prompt successfully removes the item from the wishlist.
- [ ] Verify that clicking the "Clear all" button and confirming removes all saved products and transitions the view into the empty state.

### 4) Desktop vs Mobile Viewport

- [ ] Verify that on Desktop viewports, the table expands across full width with balanced spacing for action buttons and packaging badges.
- [ ] Verify that on Mobile viewports, the table scrolls horizontally without layout distortion and buttons maintain sufficient touch-target dimensions.

### 5) Edge Cases & States

- [ ] Verify that adding an already favorited product does not create duplicate entries in the wishlist table.
- [ ] Verify that for out-of-stock items, the inventory badge indicates out of stock and the order button is disabled to prevent invalid checkout.
- [ ] Verify that choosing Cancel on the deletion confirmation dialog leaves the wishlist product unchanged.

### 6) Regression

- [ ] Verify that shared header and footer components render properly without CSS misalignment on the wishlist view.
- [ ] Verify that adding or removing wishlist items does not alter existing quantities or line items inside the active shopping cart.
