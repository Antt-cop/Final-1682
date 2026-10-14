# Test Cases — Home & AI Visual Search

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify home page layout, featured categories, best-selling product showcases, and intelligent product recognition via Gemini Vision AI.

## Scope

- In scope:
  - Hero slider banner and B2B wholesale marketplace welcoming messaging.
  - Featured product category showcases (Beverages, Instant Noodles, Clean Rice, Canned Goods).
  - Best-selling, new arrivals, and wholesale promotion product grid sections.
  - Camera trigger button for visual search on the search bar and navigation bar.
  - Image uploading from computer/phone or dragging-and-dropping image files into the dropzone.
  - AI vision analysis and product metadata identification.
  - Displaying AI matching product recommendations and direct navigation / add-to-cart actions.
- Out of scope:
  - Voice product search.
  - Real-time video barcode/QR camera scanning.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Full-width hero banner, 4-column product grid, centered AI search modal dialog |
| Mobile | Yes | Aspect-ratio responsive banner, 2-column product grid, touch-optimized modal dialog |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Catalog categories and sample products with clear imagery exist in the database.
- [ ] Gemini Vision API configuration is active for image recognition processing.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the homepage promotional banner displays hero imagery, headline text, and wholesale call-to-action buttons.
- [ ] Verify that featured categories display category cover images and titles clearly.
- [ ] Verify that product cards display product thumbnails, names, B2B wholesale packaging specifications, and wholesale prices.
- [ ] Verify that the camera icon for visual search is clearly visible within the top search bar.
- [ ] Verify that clicking the camera icon opens the centered AI Visual Search modal dialog with clear upload guidelines.

### 2) Happy-Path Business Flow

- [ ] Verify that homepage slider banners automatically transition smoothly or cycle properly when clicking left/right navigation arrows.
- [ ] Verify that clicking on a featured category card redirects to the corresponding product category listing.
- [ ] Verify that clicking on any product card navigates to the detailed product page.
- [ ] Verify that inside the visual search modal, users can successfully select and upload a product image from their device.
- [ ] Verify that while the image is being transmitted and analyzed by AI, the modal displays an active scanning progress animation.
- [ ] Verify that upon completion of AI analysis, the modal presents the identified product name and a list of matching catalog items in stock.
- [ ] Verify that users can click on an item in the AI results to view product details or add it directly to the shopping cart.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, the product grid presents 4 columns evenly with smooth hover zoom effects on cards.
- [ ] Verify that on Mobile viewports, the product grid arranges into 2 balanced columns without broken layout elements.
- [ ] Verify that on Mobile viewports, the AI visual search modal allows taking photos directly using the device camera or picking from photo library.

### 4) Edge Cases & UI States

- [ ] Verify that uploading an unsupported file format (such as text documents or archive files) displays an invalid file format warning.
- [ ] Verify that uploading an image unrelated to retail FMCG products displays a friendly no matching products found notice.
- [ ] Verify that clicking the close button or clicking outside the AI modal area immediately dismisses the modal without altering page state.

### 5) Regression Checks

- [ ] Verify that global navigation header and footer remain pinned and functional when scrolling across the homepage.
- [ ] Verify that performing an AI visual search does not affect existing items in the shopping cart or wishlist.
