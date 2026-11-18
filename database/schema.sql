-- Database schema for the Café Management System.
-- Import with:  mysql -u <user> -p <database> < database/schema.sql
-- (or via phpMyAdmin → Import).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,             -- bcrypt hash
    role       ENUM('admin', 'user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS venues (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    daily_cost DECIMAL(10, 2) NOT NULL DEFAULT 0   -- fixed cost per day the venue is open
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    price       DECIMAL(10, 2) NOT NULL,           -- selling price
    entry_price DECIMAL(10, 2) NOT NULL DEFAULT 0  -- purchase cost
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shifts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    venue_id   INT NULL,
    date       DATE NOT NULL,
    smena      TINYINT NOT NULL,                   -- shift number: 1 = first, 2 = second
    start_time TIME NULL,
    end_time   TIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id),
    FOREIGN KEY (venue_id) REFERENCES venues(id),
    INDEX idx_shifts_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shiftproducts (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    shift_id      INT NOT NULL,
    product_id    INT NOT NULL,
    products_sold INT NOT NULL,
    entry_price   DECIMAL(10, 2) NOT NULL,         -- cost at time of sale
    total         DECIMAL(10, 2) NOT NULL,         -- price × quantity
    true_total    DECIMAL(10, 2) NOT NULL,         -- profit: total − cost
    FOREIGN KEY (shift_id)   REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
