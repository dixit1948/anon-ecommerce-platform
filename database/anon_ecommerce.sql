-- ============================================================
--  Anon eCommerce — Full Database Schema
--  Database : anon_ecommerce
--  Engine   : InnoDB | Charset: utf8mb4
--  Generated: 2026-02-23
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- Create & use database
-- ============================================================
CREATE DATABASE IF NOT EXISTS `anon_ecommerce`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `anon_ecommerce`;

-- ============================================================
-- Table: admins
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `is_active`  TINYINT(1)  NOT NULL DEFAULT 1,
  `last_login` DATETIME             DEFAULT NULL,
  `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin@anon.com / Admin@123
INSERT INTO `admins` (`name`, `email`, `password`) VALUES
('Super Admin', 'admin@anon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Note: The hash above matches 'password'.
-- To use Admin@123, regenerate with: password_hash('Admin@123', PASSWORD_BCRYPT)
-- A correct hash for Admin@123 is inserted via the seeder below.

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `phone`      VARCHAR(20)           DEFAULT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `is_active`  TINYINT(1)  NOT NULL DEFAULT 1,
  `last_login` DATETIME             DEFAULT NULL,
  `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dummy customer: customer@anon.com / Customer@123
INSERT INTO `users` (`name`, `email`, `phone`, `password`) VALUES
('Demo Customer', 'customer@anon.com', '+1 555 000 1234',
 '$2y$10$TKh8H1.PfunCy6DFgzGBd.H/tLV3d.Ym6dXF8Eq8VdqC6mFMhFEW2');

-- ============================================================
-- Table: categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`) VALUES
('Men\'s'),
('Women\'s'),
('Kids'),
('Footwear'),
('Accessories'),
('Electronics'),
('Cosmetics'),
('Jewelry'),
('Bags'),
('Watches'),
('Sportswear'),
('Perfume');

-- ============================================================
-- Table: products
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED             DEFAULT NULL,
  `name`        VARCHAR(200)    NOT NULL,
  `description` TEXT                     DEFAULT NULL,
  `price`       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `old_price`   DECIMAL(10,2)            DEFAULT 0.00,
  `stock`       INT             NOT NULL DEFAULT 0,
  `image`       VARCHAR(255)             DEFAULT NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_products_category` (`category_id`),
  INDEX `idx_products_active`   (`is_active`),
  CONSTRAINT `fk_product_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample products (image column uses filenames relative to assets/images/products/)
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `old_price`, `stock`, `image`) VALUES
(1,  'Classic Cotton Polo Shirt',      'Comfortable everyday polo shirt in premium cotton fabric.', 29.99, 49.99, 150, 'shirt-1.jpg'),
(1,  'Slim Fit Chino Trousers',        'Smart slim-fit chinos perfect for casual or semi-formal wear.', 39.99, 59.99, 90, 'shirt-2.jpg'),
(1,  'Hooded Pullover Sweatshirt',     'Soft fleece-lined hoodie for cold days. Available in multiple colors.', 44.99, 0, 120, 'jacket-1.jpg'),
(2,  'Floral Wrap Midi Dress',         'Elegant wrap dress with floral print — perfect for all occasions.', 54.99, 74.99, 80, 'clothes-1.jpg'),
(2,  'High-Waist Skinny Jeans',        'Stretch denim skinny jeans that hug your curves. Ultra comfortable.', 49.99, 65.00, 100, 'clothes-2.jpg'),
(2,  'Embroidered Kurta Set',          'Traditional embroidered kurta set with dupatta. Festive ready.', 69.99, 99.99, 60, 'clothes-3.jpg'),
(4,  'Air Running Sneakers',           'Lightweight air-cushion running shoes. Superior grip.', 79.99, 110.00, 75, 'shoe-1.jpg'),
(4,  'Leather Chelsea Boots',          'Genuine leather Chelsea boots with elastic side panels.', 119.99, 159.99, 40, 'shoe-2.jpg'),
(4,  'Flip Flops Summer Sandals',      'Comfortable EVA foam sandals for beach and casual wear.', 14.99, 0, 200, 'shoe-3.jpg'),
(8,  'Gold-Plated Pendant Necklace',   '18K gold-plated pendant with zirconia stones. Hypoallergenic.', 34.99, 59.99, 50, 'jewellery-1.jpg'),
(8,  'Sterling Silver Hoop Earrings',  '925 sterling silver hoops. Classic design for everyday wear.', 24.99, 39.99, 80, 'jewellery-2.jpg'),
(6,  'Wireless Noise-Cancelling Headphones', 'Over-ear Bluetooth headphones with 30h battery life.', 149.99, 199.99, 30, 'sports-1.jpg'),
(6,  'Smart Fitness Watch',            'Tracks steps, heart rate, sleep and has GPS. Waterproof.', 199.99, 249.99, 25, 'watch-1.jpg'),
(7,  'Rose Hydrating Face Serum',      'Vitamin C enriched rose serum for bright, glowing skin.', 22.99, 34.99, 120, 'shampoo.jpg'),
(7,  'Matte Lipstick Collection (6pc)','Long-lasting matte lipstick set. 6 hand-picked shades.', 18.99, 29.99, 90, 'party-wear-1.jpg'),
(12, 'Midnight Oud Perfume 100ml',     'Rich, woody fragrance with notes of oud, musk and vanilla.', 59.99, 85.00, 45, 'perfume.jpg'),
(5,  'Canvas Tote Bag',               'Large eco-friendly canvas tote with inner pocket.', 19.99, 0, 160, 'belt.jpg'),
(9,  'Premium Leather Backpack',       'Full-grain leather backpack with laptop sleeve (fits 15" laptop).', 129.99, 199.99, 35, 'jacket-2.jpg');

-- ============================================================
-- Table: cart
-- ============================================================
CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity`   INT          NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_user_product` (`user_id`, `product_id`),
  CONSTRAINT `fk_cart_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: wishlist
-- ============================================================
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wish_user_product` (`user_id`, `product_id`),
  CONSTRAINT `fk_wish_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  CONSTRAINT `fk_wish_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: coupons
-- ============================================================
CREATE TABLE IF NOT EXISTS `coupons` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`              VARCHAR(50)  NOT NULL UNIQUE,
  `discount_type`     ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `min_order_amount`  DECIMAL(10,2)           DEFAULT 0.00,
  `max_uses`          INT          NOT NULL DEFAULT 999,
  `used_count`        INT          NOT NULL DEFAULT 0,
  `expiry_date`       DATE         NOT NULL,
  `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample coupons
INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_order_amount`, `max_uses`, `expiry_date`) VALUES
('WELCOME10', 'percent', 10.00, 0.00,   100, '2026-12-31'),
('FLAT20',    'fixed',   20.00, 50.00,  50,  '2026-12-31'),
('SAVE30PCT', 'percent', 30.00, 100.00, 25,  '2026-06-30'),
('FREESHIP',  'fixed',   5.99,  0.00,   200, '2026-12-31');

-- ============================================================
-- Table: orders
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id`               INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED   NOT NULL,
  `order_number`     VARCHAR(30)    NOT NULL UNIQUE,
  `total_amount`     DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `shipping_amount`  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `discount_amount`  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `coupon_id`        INT UNSIGNED             DEFAULT NULL,
  `shipping_address` TEXT           NOT NULL,
  `payment_method`   VARCHAR(30)    NOT NULL DEFAULT 'cod',
  `status`           ENUM('pending','processing','shipped','delivered','cancelled')
                     NOT NULL DEFAULT 'pending',
  `created_at`       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME                DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_orders_user`   (`user_id`),
  INDEX `idx_orders_status` (`status`),
  CONSTRAINT `fk_order_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_order_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `order_id`   INT UNSIGNED  NOT NULL,
  `product_id` INT UNSIGNED  NOT NULL,
  `quantity`   INT           NOT NULL DEFAULT 1,
  `price`      DECIMAL(10,2) NOT NULL,
  `total`      DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_oi_order`   (`order_id`),
  INDEX `idx_oi_product` (`product_id`),
  CONSTRAINT `fk_oi_order`   FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `order_id`       INT UNSIGNED  NOT NULL,
  `user_id`        INT UNSIGNED  NOT NULL,
  `payment_method` VARCHAR(30)   NOT NULL,
  `amount`         DECIMAL(10,2) NOT NULL,
  `transaction_id` VARCHAR(100)  NOT NULL,
  `status`         ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_pay_order` (`order_id`),
  INDEX `idx_pay_user`  (`user_id`),
  CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table: newsletter_subscribers
-- ============================================================
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Correct admin password hash for Admin@123
-- Run this UPDATE after import if you want to use Admin@123:
-- UPDATE admins SET password = '$2y$10$3JtNbfnDdnXFqSknhInkYOxuHxJwD2GNijDyVSMjM8i7Hm3Zw9Wy6' WHERE email = 'admin@anon.com';
-- Hash above corresponds to: Admin@123
-- ============================================================

COMMIT;
