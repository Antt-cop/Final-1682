# Test Cases — Admin Products Management & Packaging Configuration

**Module:** Admin  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify administrator product management operations: product creation, wholesale pricing updates, multi-tier B2B packaging configuration (Box/Pack/Sack/Bag, conversion rate), and catalog availability controls.

## Scope

- In scope:
  - Admin products list data table (`/admin/products.php`).
  - Metric summary cards (Total Products, Low Stock, Out of Stock).
  - Search filtering by keyword, category, and inventory levels.
  - Add / Edit product modal dialog.
  - Configuring B2B wholesale packaging parameters (Master dispatch unit, sub-packaging unit, conversion rate, pack size, minimum order unit).
  - Uploading product image assets and image preview.
  - Toggling active / disabled status.
  - Deleting products from the catalog.
- Out of scope:
  - Bulk spreadsheet product catalog imports (Excel/CSV batch processing).
  - Automated barcode / shipping label generation.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Full-column data table layout, horizontal search and filter toolbar |
| Mobile | Yes | Horizontally scrollable data table, vertically scrollable modal form |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Administrator user is authenticated into the admin portal (`/admin/`).
- [ ] At least one approved product category exists in the database.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the Manage Products header, "Add Product" button, and summary metric cards are clearly displayed.
- [ ] Verify that the products data table displays all expected columns: ID, Thumbnail, Name & Category, Wholesale Price, Stock, Storage Type, Status, and Actions.
- [ ] Verify that filter controls including keyword search, category selector, and stock level dropdown are visible.
- [ ] Verify that the Add/Edit Product modal dialog renders input fields for basic product metadata and the B2B packaging configuration panel.

### 2) Happy-Path Business Flow

- [ ] Verify that clicking "Add Product" opens the modal dialog with clean empty inputs ready for data entry.
- [ ] Verify that the administrator can enter product name, choose category, input wholesale price, and configure packaging attributes successfully.
- [ ] Verify that the administrator can upload a product thumbnail image and observe a clear preview image.
- [ ] Verify that clicking Save persists the product, displays a success confirmation message, and displays the new item in the list.
- [ ] Verify that clicking Edit on any product row populates the modal with the product's existing values accurately.
- [ ] Verify that the administrator can update the price or packaging configuration and save changes successfully.
- [ ] Verify that clicking the status toggle button immediately switches the product between Active and Inactive.
- [ ] Verify that clicking Delete and confirming in the dialog removes the product from the catalog.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, the data table expands to full width with convenient inline action buttons.
- [ ] Verify that on Mobile viewports, the data table allows smooth horizontal swiping and the Add Product button remains prominently pinned at top.
- [ ] Verify that on Mobile viewports, the product creation form scales comfortably to viewport width facilitating touch input.

### 4) Edge Cases & UI States

- [ ] Verify that attempting to save with required fields left blank (name, category, price) presents appropriate validation hints.
- [ ] Verify that entering a negative or zero price value is rejected with an invalid price warning.
- [ ] Verify that searching for a non-existent keyword displays an empty search results message in the table.

### 5) Regression Checks

- [ ] Verify that newly created products or updated packaging specifications immediately reflect on public customer storefront pages.
- [ ] Verify that products transitioned to inactive status are immediately hidden from customer homepage and shop listings.
