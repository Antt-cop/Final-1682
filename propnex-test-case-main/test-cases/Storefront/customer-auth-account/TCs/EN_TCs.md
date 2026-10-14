# Test Cases — Customer Authentication & Account Management

**Module:** Storefront  
**Platform:** Dong Khoi Store Web Application (PHP/MySQL)  
**Viewport:** Desktop (≥ 1024px) and Mobile (< 1024px)  
**Goal:** Verify B2B buyer account registration, secure authentication, return redirection flow, and customer profile management.

## Scope

- In scope:
  - New customer registration form (`/public/register.php`).
  - Customer login form (`/public/login.php`).
  - Target redirect preservation mechanism following authentication (e.g., returning to Wishlist or Cart).
  - Secure session destruction on logout.
  - My Account overview dashboard (`/public/my-account.php`).
  - Updating contact phone, shipping destination, and account details (`/public/my-account-edit.php`).
- Out of scope:
  - Social media single sign-on (Google OAuth, Facebook Login).
  - Biometric device authentication (TouchID/FaceID).

## Viewport Matrix

Run every case across both **Desktop** (1440×900 / 1280×800) and **Mobile** (390×844 / 360×800).

| Viewport | Required | UI Notes |
|---|---|---|
| Desktop | Yes | Centered login/registration cards, account service tile grid layout |
| Mobile | Yes | Virtual-keyboard friendly inputs, vertically stacked service cards |

## Preconditions

- [ ] Web server is running and accessible at `http://localhost/dongkhoistore`.
- [ ] Database is ready to accept new user registrations and store customer credentials.

## Test Cases

### 1) UI & Element Presence

- [ ] Verify that the Login form displays Email Address, Password, Login button, and a link to the Registration page.
- [ ] Verify that the Registration form displays Username, Email, Phone Number, Password, Confirm Password, and a Register button.
- [ ] Verify that the My Account dashboard renders a welcome banner with Username, Email, Phone, and default Shipping Address.
- [ ] Verify that account service shortcut tiles (Order History, Wishlist, Edit Profile) are clearly visible.

### 2) Happy-Path Business Flow

- [ ] Verify that users can fill valid information into the registration form and create a new buyer account successfully.
- [ ] Verify that upon successful registration, the system prompts the user and allows immediate sign-in.
- [ ] Verify that entering valid registered Email and Password credentials authenticates the user successfully.
- [ ] Verify that if redirected to login from an authentication-guarded page (such as Wishlist), signing in returns the user to that target page.
- [ ] Verify that users can edit their contact telephone, shipping address, and save profile changes successfully.
- [ ] Verify that clicking Logout destroys the authenticated session securely and returns the user to guest status.

### 3) Desktop vs Mobile Responsive Layout

- [ ] Verify that on Desktop viewports, authentication card forms are neatly centered without excessive stretching.
- [ ] Verify that on Mobile viewports, form fields invoke appropriate virtual keyboards (numeric for phone, email keyboard for email).
- [ ] Verify that on Mobile viewports, the account menu in the mobile drawer reveals smoothly showing the active user name.

### 4) Edge Cases & UI States

- [ ] Verify that entering an incorrect password or unregistered email address displays a clear error alert preventing sign-in.
- [ ] Verify that registering with an email address already present in the database displays an email already registered warning.
- [ ] Verify that submitting the form with required inputs left blank displays clear validation error messages.
- [ ] Verify that entering non-matching passwords in the password and confirmation fields displays a mismatch warning.

### 5) Regression Checks

- [ ] Verify that upon logging in, header navigation controls dynamically toggle from Login/Register to Account/Logout actions.
- [ ] Verify that products accumulated in the shopping cart prior to sign-in remain intact after logging in.
