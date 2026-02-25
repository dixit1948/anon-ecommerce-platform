<div align="center">

# 🛍️ Anon — Full-Stack E-Commerce Platform

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![XAMPP](https://img.shields.io/badge/XAMPP-Local%20Dev-FB7A24?style=for-the-badge&logo=apache&logoColor=white)](https://apachefriends.org)
[![Razorpay](https://img.shields.io/badge/Razorpay-Payment%20Gateway-02042B?style=for-the-badge&logo=razorpay&logoColor=white)](https://razorpay.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0-blue?style=for-the-badge)](CHANGELOG.md)

**A production-ready, feature-rich e-commerce web application built with pure PHP & MySQL.**  
Complete with admin panel, Razorpay payment gateway, coupon system, and a beautiful responsive UI.

[✨ Features](#-features) · [🚀 Installation](#-installation) · [📸 Screenshots](#-screenshots) · [🔧 Tech Stack](#-tech-stack) · [📄 Changelog](CHANGELOG.md)

</div>

---

## ✨ Features

### 🛒 Customer Side
- 🏠 **Dynamic Homepage** — Banner slider, category strips, new arrivals, trending, top-rated, deal of the day with countdown timer, blog section
- 🔍 **Product Browsing** — Filter by category, sort by price/name, search
- 🛍️ **Product Details** — Image gallery, description, related products, add to cart/wishlist
- 🛒 **Shopping Cart** — Quantity management, real-time totals
- ❤️ **Wishlist** — Save products for later
- 💳 **Checkout** — Address form, coupon codes, Razorpay online payment + COD
- 📦 **Order Tracking** — Order history, status tracking (Pending → Processing → Shipped → Delivered)
- 👤 **User Profile** — Edit personal details, change password
- 📋 **Invoice Download** — Order invoice page

### 🔧 Admin Panel
- 📊 **Dashboard** — Revenue charts, recent orders, top-selling products
- 📦 **Product Management** — Add/edit/delete products with image upload
- 🗂️ **Category Management** — CRUD categories
- 📋 **Order Management** — View & update order statuses
- 👥 **Customer Management** — View all registered users
- 🎟️ **Coupon Management** — Create percentage/flat discount coupons
- 📈 **Sales Reports** — Date-range revenue & order reports

### 💰 Payment
- ✅ **Razorpay Integration** — Test & live mode ready
- ✅ **Cash on Delivery (COD)**
- ✅ **Signature Verification** — Secure payment validation

---

## 📸 Screenshots

> _Add screenshot images in a `/screenshots` folder and update the paths below_

| Homepage | Product Page |
|----------|--------------|
| ![Homepage](screenshots/homepage.png) | ![Product](screenshots/product.png) |

| Admin Dashboard | Checkout |
|-----------------|----------|
| ![Admin](screenshots/admin.png) | ![Checkout](screenshots/checkout.png) |

---

## 🔧 Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.x (Vanilla — no framework) |
| **Database** | MySQL 8 / MariaDB |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **Icons** | Ionicons v5 |
| **Payment** | Razorpay API |
| **Server** | Apache (XAMPP) |
| **Auth** | PHP Sessions + bcrypt password hashing |
| **Security** | CSRF tokens, input sanitization, prepared statements |

---

## 🚀 Installation

### Prerequisites
- [XAMPP](https://apachefriends.org) (Apache + MySQL + PHP 8+)
- Git

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/anon-ecommerce-website.git

# 2. Move to XAMPP's htdocs folder
# Windows: C:\xampp\htdocs\anon-ecommerce-website
# Mac/Linux: /opt/lampp/htdocs/anon-ecommerce-website
```

**3. Import the database**
- Open `http://localhost/phpmyadmin`
- Create a new database: `anon_ecommerce`
- Import: `database/anon_ecommerce.sql`

**4. Configure the database connection**
- Open `config/db.php`
- Update credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // your MySQL password
define('DB_NAME', 'anon_ecommerce');
define('SITE_URL', 'http://localhost/anon-ecommerce-website');
```

**5. Configure Razorpay (optional)**
```php
define('RAZORPAY_KEY_ID',     'rzp_test_XXXXXXXXXX');
define('RAZORPAY_KEY_SECRET', 'XXXXXXXXXXXXXXXXXX');
```

**6. Start Apache & MySQL in XAMPP, then visit:**
```
http://localhost/anon-ecommerce-website
```

### Default Admin Login
| Field | Value |
|-------|-------|
| URL | `http://localhost/anon-ecommerce-website/admin` |
| Username | `admin@anon.com` |
| Password | `admin123` |

---

## 📁 Project Structure

```
anon-ecommerce-website/
├── admin/              # Admin panel pages
├── assets/
│   ├── css/            # Stylesheets (style.css, extra.css)
│   ├── js/             # JavaScript files
│   └── images/         # Static images & icons
├── config/
│   └── db.php          # Database connection & constants
├── database/
│   └── anon_ecommerce.sql  # Full DB schema + seed data
├── includes/
│   ├── auth.php        # Helper functions, authentication
│   ├── header.php      # Site header/navbar
│   └── footer.php      # Site footer
├── uploads/
│   └── products/       # Admin-uploaded product images
├── user/               # User account pages
├── index.php           # Homepage
├── products.php        # Product listing
├── product.php         # Single product detail
└── README.md
```

---

## 🔐 Security Features

- ✅ CSRF token protection on all forms
- ✅ Prepared statements (SQL injection prevention)
- ✅ `htmlspecialchars` / `strip_tags` input sanitization
- ✅ `password_hash` (bcrypt) for user passwords
- ✅ Admin-only route protection
- ✅ Razorpay `HMAC-SHA256` payment signature verification

---

## 🤝 Contributing

Contributions, issues and feature requests are welcome!  
Feel free to check the [issues page](https://github.com/YOUR_USERNAME/anon-ecommerce-website/issues).

1. Fork the project
2. Create your feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Your Name**  
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Connect-0A66C2?style=flat-square&logo=linkedin)](https://linkedin.com/in/YOUR_PROFILE)
[![GitHub](https://img.shields.io/badge/GitHub-Follow-181717?style=flat-square&logo=github)](https://github.com/YOUR_USERNAME)

---

<div align="center">
  <sub>⭐ If you found this project useful, please give it a star! It helps others discover it.</sub>
</div>
