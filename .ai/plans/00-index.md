# 📋 Pinora — Multi-Vendor Jewellery Platform
## Implementation Plan Index

> Review each step file individually and approve before execution.
> Steps are ordered by dependency — do NOT skip steps.

---

## ✅ Decisions Reference

| Topic | Decision |
|---|---|
| Stack | Laravel 13 + Filament 5 + Blade frontend |
| User system | Unified `users` table, multiple roles via Spatie |
| Vendor onboarding | Self-register → Admin approves |
| Commission | Flat % (no categories, no subscription) |
| Metal rates | Manual daily update by Admin |
| Admin power | Can do everything a vendor can |
| Login | Email/Password + Google/Social + Guest (account first) |
| Email conflict | Unified — same email can be vendor + customer |
| Customer visibility | Vendor-centric — vendors see full customer details |
| Reviews | Admin approval required |
| Tax | GST (CGST + SGST / IGST) |
| Fulfillment | Vendor ships directly |
| Frontend | Custom Laravel + Blade |

---

## 📁 Step Files

### 🗄️ Phase 1 — Database Foundation

| Step | File | Description |
|------|------|-------------|
| 01 | `01-migrate-users-extend.md` | Extend users table with customer fields |
| 02 | `02-migrate-vendors.md` | Vendors & vendor documents tables |
| 03 | `03-migrate-categories.md` | Product categories table |
| 04 | `04-migrate-metal-rates.md` | Metal rates table (gold, silver, platinum) |
| 05 | `05-migrate-products.md` | Products & product variants tables |
| 06 | `06-migrate-product-images.md` | Product images table |
| 07 | `07-migrate-addresses.md` | Customer addresses table |
| 08 | `08-migrate-wishlists.md` | Wishlists table |
| 09 | `09-migrate-orders.md` | Orders & order items tables |
| 10 | `10-migrate-commissions-payouts.md` | Commissions & payouts tables |
| 11 | `11-migrate-reviews.md` | Product reviews table |

### 🧩 Phase 2 — Models & Relationships

| Step | File | Description |
|------|------|-------------|
| 12 | `12-model-user.md` | Update User model |
| 13 | `13-model-vendor.md` | Vendor & VendorDocument models |
| 14 | `14-model-category.md` | Category model |
| 15 | `15-model-metal-rate.md` | MetalRate model |
| 16 | `16-model-product.md` | Product & ProductVariant models |
| 17 | `17-model-product-image.md` | ProductImage model |
| 18 | `18-model-address.md` | Address model |
| 19 | `19-model-wishlist.md` | Wishlist model |
| 20 | `20-model-order.md` | Order & OrderItem models |
| 21 | `21-model-commission-payout.md` | Commission & Payout models |
| 22 | `22-model-review.md` | Review model |

### 🌱 Phase 3 — Seeders & Roles

| Step | File | Description |
|------|------|-------------|
| 23 | `23-seeder-roles-permissions.md` | Seed default roles & permissions |

### 🏢 Phase 4 — Admin Panel (Filament)

| Step | File | Description |
|------|------|-------------|
| 24 | `24-admin-vendor-management.md` | Vendor listing, approval, editing |
| 25 | `25-admin-category-management.md` | Product categories CRUD |
| 26 | `26-admin-metal-rates.md` | Daily metal rate management |
| 27 | `27-admin-product-management.md` | Products CRUD (as any vendor) |
| 28 | `28-admin-customer-management.md` | Customer listing, tagging, impersonation |
| 29 | `29-admin-order-management.md` | Order listing, status management |
| 30 | `30-admin-review-management.md` | Review approval / rejection |
| 31 | `31-admin-commission-payouts.md` | Commission tracking, payout management |
| 32 | `32-admin-dashboard-widgets.md` | Dashboard stats widgets |

### 🏪 Phase 5 — Vendor Panel (Filament)

| Step | File | Description |
|------|------|-------------|
| 33 | `33-vendor-panel-setup.md` | New Filament vendor panel at `/vendor` |
| 34 | `34-vendor-product-management.md` | Vendor: own products CRUD |
| 35 | `35-vendor-order-management.md` | Vendor: manage incoming orders |
| 36 | `36-vendor-customer-view.md` | Vendor: view customer details |
| 37 | `37-vendor-payout-reports.md` | Vendor: commission & payout reports |
| 38 | `38-vendor-dashboard-widgets.md` | Vendor dashboard stats |

### 🔐 Phase 6 — Authentication

| Step | File | Description |
|------|------|-------------|
| 39 | `39-auth-customer-registration.md` | Customer register (guest→account flow) |
| 40 | `40-auth-social-login.md` | Google OAuth login |
| 41 | `41-auth-vendor-registration.md` | Vendor self-registration form |

### 🛍️ Phase 7 — Customer Storefront (Blade)

| Step | File | Description |
|------|------|-------------|
| 42 | `42-frontend-layout.md` | Master layout, navbar, footer |
| 43 | `43-frontend-homepage.md` | Homepage (hero, featured, categories) |
| 44 | `44-frontend-product-listing.md` | Shop page with filters & search |
| 45 | `45-frontend-product-detail.md` | Product detail page with pricing |
| 46 | `46-frontend-cart.md` | Shopping cart |
| 47 | `47-frontend-checkout.md` | Checkout with GST calculation |
| 48 | `48-frontend-order-confirmation.md` | Order placed confirmation page |
| 49 | `49-frontend-customer-account.md` | My Account (orders, wishlist, addresses) |
| 50 | `50-frontend-vendor-storefront.md` | Individual vendor store page |

### 💰 Phase 8 — Business Logic Services

| Step | File | Description |
|------|------|-------------|
| 51 | `51-service-pricing.md` | PricingService (metal rate × weight + charges + GST) |
| 52 | `52-service-commission.md` | CommissionService (calculate on order) |
| 53 | `53-service-gst.md` | GstService (CGST/SGST vs IGST logic) |
| 54 | `54-service-order.md` | OrderService (place order, inventory) |
| 55 | `55-service-payment.md` | PaymentService (abstract gateway) |
| 56 | `56-sub-category-menu.md` | Dedicated subcategory navigation menu |
| 57 | `57-admin-hidden-navigation.md` | Admin navigation visibility & hidden menus |
| 58 | `58-temporary-vendor-freeze.md` | Temporary vendor freeze & reversion instructions |

---

## 🔢 Total Steps: 58

Start with `01-migrate-users-extend.md` and work through sequentially.
