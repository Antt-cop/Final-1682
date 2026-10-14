# Test Cases — Wholesale Shop Catalog & Filtering

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify wholesale product catalog layout, category sidebar filtering, accent-insensitive keyword search, sorting controls, and pagination mechanisms.

## Scope

- In scope:
  - Wholesale catalog page layout (`/public/shop.php`).
  - Category filtering sidebar.
  - Keyword search input field (supporting both Vietnamese accented and unaccented terms).
  - Sorting criteria (Newest, Price: Low to High, Price: High to Low, Best Selling).
  - Product card grid displaying B2B packaging specifications.
  - Pagination navigation controls.
  - Empty state when no products match applied filters.
- Out of scope:
  - Side-by-side comparative specification matrix between two products.
  - Advanced international manufacturer origin filters.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Fixed left filter sidebar, 3-column product grid on the right |
| Mobile | Yes | Drawer/Modal filter toggle button, 1-to-2 column responsive cards |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Multiple products across at least 3 distinct categories exist in the database.
- [ ] Sample products are active and in stock.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the shop catalog title, breadcrumb trail, and total products found count are clearly visible.
- [ ] Verify that the category list in the sidebar shows category names with product counters.
- [ ] Verify that the search keyword input and sorting dropdown are positioned prominently above the product grid.
- [ ] Verify that each product card clearly displays a sharp thumbnail, product name, B2B wholesale packaging badge, and wholesale price.

### 2) Happy-Path Business Flow

- [ ] Verify that selecting a specific category (e.g., Beverages) filters the product grid to show only items belonging to that category.
- [ ] Verify that entering a search keyword (e.g., "coca" or "mì") and pressing Enter filters the list to matching products.
- [ ] Verify that keyword search supports unaccented queries (e.g., searching "gao" returns "Gạo ST25").
- [ ] Verify that choosing "Price: Low to High" sorts the product grid in ascending order of wholesale prices.
- [ ] Verify that choosing "Price: High to Low" sorts the product grid in descending order of wholesale prices.
- [ ] Verify that clicking a page number on the pagination bar navigates to the corresponding page smoothly.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, the filter sidebar is permanently pinned on the left and the product grid displays 3 columns.
- [ ] Verify that on Mobile viewports, filters collapse into an intuitive toggle button to conserve screen real estate.
- [ ] Verify that on Mobile viewports, the product grid presents touch-friendly vertical cards with smooth scrolling.

### 4) Edge Cases & UI States

- [ ] Verify that searching for a non-existent keyword (e.g., "xyz999") displays a friendly no matching products found notice.
- [ ] Verify that the empty search state includes a "View All Products" button to quickly reset filters.
- [ ] Verify that out-of-stock or disabled products are omitted from the public catalog.

### 5) Regression Checks

- [ ] Verify that clicking the quick "Add to Cart" button on any product card increments the header cart badge counter correctly.
- [ ] Verify that clicking the heart icon on any product card properly saves the item into the customer wishlist.
