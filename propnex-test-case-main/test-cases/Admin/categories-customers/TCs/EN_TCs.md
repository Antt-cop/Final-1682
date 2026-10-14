# Test Cases — Admin Categories & Wholesale Customers Management

**Module:** Admin  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify category administration (Category CRUD with product relationship integrity constraints) and B2B wholesale customer profile controls (institutional buyer details, account status toggling).

## Scope

- In scope:
  - Product categories management table (`/admin/categories.php`).
  - Adding, editing, and deleting categories with product association guards.
  - Wholesale customer account roster table (`/admin/customers.php`).
  - Viewing buyer details: Username, Email, Institution/Canteen Name, Phone Number, Shipping Destination.
  - Adding new B2B buyer accounts and updating buyer profile records.
  - Toggling buyer account status (Active / Suspended).
- Out of scope:
  - Multi-tier VIP customer loyalty points and automated discount tier upgrades.
  - Real-time tax authority business license lookup integrations.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Multi-column table layout with balanced inline action buttons |
| Mobile | Yes | Horizontally scrollable data tables, responsive modal form drawers |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Administrator user is authenticated into the admin portal.
- [ ] At least one category and one sample customer exist in the database.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the Categories Management heading, "Add Category" button, and categories table are clearly visible.
- [ ] Verify that the Customers Management heading, "Add Customer" button, and customers table display all pertinent columns.
- [ ] Verify that the Add Category modal dialog provides Category Name and Description inputs.
- [ ] Verify that the Customer modal dialog presents inputs for Full Name, Email, Institution/Canteen Name, Phone, Address, and Status.

### 2) Happy-Path Business Flow

- [ ] Verify that the administrator can enter a new category name and create the category successfully.
- [ ] Verify that the newly created category appears immediately in the table and surfaces on public customer category navigation.
- [ ] Verify that the administrator can edit an existing category name or description and save modifications successfully.
- [ ] Verify that the administrator can create a new B2B wholesale buyer account with institutional metadata and a default password.
- [ ] Verify that the administrator can modify customer contact phone, address, or institution details and save successfully.
- [ ] Verify that clicking the customer status toggle button toggles the account between "Active" and "Suspended".

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, categories and customer tables render spacious layouts with easily clickable action controls.
- [ ] Verify that on Mobile viewports, data tables support smooth horizontal panning without column deformation.
- [ ] Verify that on Mobile viewports, form modals fit touch display dimensions cleanly and scroll smoothly vertically.

### 4) Edge Cases & UI States

- [ ] Verify that attempting to delete a category actively assigned to catalog products is blocked with a descriptive dependency error.
- [ ] Verify that creating a category with an empty category name triggers a validation notice requiring a name.
- [ ] Verify that creating a customer with an email or username already registered displays a duplicate record alert.
- [ ] Verify that when a customer account is switched to "Suspended", that user is blocked from logging into the Storefront.

### 5) Regression Checks

- [ ] Verify that modifying a category name in the admin panel instantly reflects on customer header navigation dropdowns.
- [ ] Verify that catalog products assigned to the updated category preserve their category associations intact.
