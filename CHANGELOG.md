# Changelog

All notable changes to **Anon E-Commerce** will be documented in this file.  
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) · Versioning follows [Semantic Versioning](https://semver.org/).

---

## [1.0.0] — 2026-02-25

### 🎉 Initial Release

#### Added
- Full e-commerce homepage with banner slider, category strip, New Arrivals / Trending / Top Rated showcase panels
- Deal of the Day section with live countdown timer and stock progress bar
- New Products grid with discount badges and star ratings
- CTA banner + Our Services section (side-by-side)
- Blog section with hover animations
- Product listing page with category filter, sort, and search
- Single product detail page with related products
- Shopping cart with quantity update and real-time totals
- Wishlist system with toggle (add/remove)
- Checkout page with address form, coupon code input, order summary
- Razorpay payment gateway integration (test mode)
- Cash on Delivery (COD) payment option
- Razorpay HMAC-SHA256 signature verification
- User authentication (register, login, logout) with bcrypt password hashing
- User profile page (edit details, change password)
- Order history page with status badges
- Order success / confirmation page
- Invoice detail page
- Full admin panel:
  - Dashboard with revenue stats and recent orders
  - Product management (add, edit, delete, image upload)
  - Category management (CRUD)
  - Order management with status updates
  - Customer management
  - Coupon management (percentage & flat discount)
  - Sales reports with date filtering
- `getProductImageUrl()` helper for consistent image path resolution
- CSRF token protection on all POST forms
- SQL injection prevention with prepared statements
- Admin-only route security
- Responsive design (mobile-friendly)
- `uploads/products/` directory for admin-uploaded images

#### Technical
- Pure PHP 8.x — no framework dependency
- MySQL/MariaDB with normalized schema
- Vanilla CSS with CSS custom properties (design tokens)
- Ionicons v5 for icons
- JavaScript Fetch API for AJAX cart/wishlist actions

---

## [Unreleased]

### Planned
- Product image gallery (multiple images per product)
- Product reviews and ratings system
- Email notifications (order confirmation, status updates)
- Google / social login
- Advanced search with filters
- Wishlist sharing
- Product comparison feature
- PWA (Progressive Web App) support
