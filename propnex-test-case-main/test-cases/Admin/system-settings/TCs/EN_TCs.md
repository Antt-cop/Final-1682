# Test Cases — Admin System Settings (GHN Logistics & SMTP Mail)

**Module:** Admin  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify system integration configurations: Giao Hàng Nhanh (GHN) carrier credentials (Shop ID, API Token) and SMTP email server parameters for automated order notification dispatch.

## Scope

- In scope:
  - GHN Carrier Settings panel (`/admin/ghn_settings.php`).
  - Configuring Shop ID, Token API, and Store Representative Name.
  - Resetting or clearing GHN configuration.
  - SMTP Mail Settings panel (`/admin/smtp_settings.php`).
  - Configuring mail server parameters (SMTP Host, Port, Encryption TLS/SSL, Username, Password).
  - Configuring sender details: Sender From Address and From Name.
  - Test Email utility to verify outbound mail connectivity.
- Out of scope:
  - Multi-carrier rate comparison engine (integrating alternative carriers like ViettelPost or Ninja Van).
  - Cryptographic digital email signature verification.

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Structured settings card forms with clear credential and diagnostic sections |
| Mobile | Yes | Vertically responsive forms with touch-friendly action buttons |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Administrator user is authenticated into the admin portal.
- [ ] Database tables for GHN credentials (`ghn_shops`) and SMTP parameters (`smtp_settings`) are accessible.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the GHN Settings page displays Shop ID, API Token, Store Name, Save Configuration button, and Clear Configuration button.
- [ ] Verify that the current active GHN configuration card clearly illustrates the active Shop ID and masked Token.
- [ ] Verify that the SMTP Settings page displays SMTP Host, Port, Protocol, Username, Password, Sender Address, and Sender Name inputs.
- [ ] Verify that the "Send Test Email" section provides an email input field and a "Send Test Email" button.

### 2) Happy-Path Business Flow

- [ ] Verify that the administrator can enter valid GHN Shop ID and Token credentials and save the configuration successfully.
- [ ] Verify that upon saving GHN settings, the system presents a green confirmation alert and displays the newly saved values.
- [ ] Verify that the administrator can update SMTP mail server parameters and save changes successfully.
- [ ] Verify that upon saving SMTP settings, the system confirms successful updates and persists the parameters.
- [ ] Verify that the administrator can input a recipient email address into the diagnostic form and trigger a test email successfully.
- [ ] Verify that clicking the Clear GHN Configuration button and confirming resets the active GHN settings safely.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, configuration forms arrange cleanly inside bordered cards with ample spacing.
- [ ] Verify that on Mobile viewports, form fields stack vertically matching the mobile device width.
- [ ] Verify that on Mobile viewports, test email submission controls remain clearly accessible without text truncation.

### 4) Edge Cases & UI States

- [ ] Verify that submitting the GHN form with empty Shop ID or Token triggers a prompt requiring both values.
- [ ] Verify that submitting a malformed email address in the test email field displays an invalid email format warning.
- [ ] Verify that entering incorrect SMTP credentials displays meaningful diagnostic error output detailing the connection failure.

### 5) Regression Checks

- [ ] Verify that following successful GHN configuration, real-time shipping fee calculation on the public Checkout page functions seamlessly.
- [ ] Verify that following successful SMTP configuration, automated order confirmation emails are dispatched upon order creation.
