-- Lost & Found Management System - Database Schema
-- Run this file to set up the database

CREATE DATABASE IF NOT EXISTS lost_found_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lost_found_db;

-- Branches Table
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    contact VARCHAR(50),
    email VARCHAR(100),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin','branch_manager','staff','guest') NOT NULL DEFAULT 'staff',
    status ENUM('active','inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Item Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-box',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Found Items
CREATE TABLE IF NOT EXISTS found_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    logged_by INT NOT NULL,
    category_id INT NULL,
    item_name VARCHAR(150) NOT NULL,
    description TEXT,
    color VARCHAR(50),
    brand VARCHAR(100),
    found_date DATE NOT NULL,
    found_location VARCHAR(255),
    photo VARCHAR(255) NULL,
    status ENUM('unclaimed','matched','claimed','disposed') DEFAULT 'unclaimed',
    storage_location VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (logged_by) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Lost Item Reports
CREATE TABLE IF NOT EXISTS lost_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    logged_by INT NOT NULL,
    category_id INT NULL,
    reporter_name VARCHAR(100) NOT NULL,
    reporter_contact VARCHAR(100) NOT NULL,
    reporter_email VARCHAR(100),
    item_name VARCHAR(150) NOT NULL,
    description TEXT,
    color VARCHAR(50),
    brand VARCHAR(100),
    lost_date DATE NOT NULL,
    lost_location VARCHAR(255),
    photo VARCHAR(255) NULL,
    status ENUM('open','matched','closed') DEFAULT 'open',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (logged_by) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Item Matches
CREATE TABLE IF NOT EXISTS item_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    found_item_id INT NOT NULL,
    lost_report_id INT NOT NULL,
    matched_by INT NOT NULL,
    match_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','confirmed','rejected') DEFAULT 'pending',
    notes TEXT,
    match_score TINYINT UNSIGNED DEFAULT 0,
    auto_matched TINYINT(1) DEFAULT 0,
    FOREIGN KEY (found_item_id) REFERENCES found_items(id),
    FOREIGN KEY (lost_report_id) REFERENCES lost_reports(id),
    FOREIGN KEY (matched_by) REFERENCES users(id)
);

-- Claim Records
CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    found_item_id INT NOT NULL,
    lost_report_id INT NULL,
    claimed_by_name VARCHAR(100) NOT NULL,
    claimed_by_contact VARCHAR(100) NOT NULL,
    claimed_by_email VARCHAR(100),
    id_presented VARCHAR(100),
    processed_by INT NOT NULL,
    claim_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (found_item_id) REFERENCES found_items(id),
    FOREIGN KEY (lost_report_id) REFERENCES lost_reports(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Data
INSERT INTO branches (name, address, contact, email) VALUES
('Main Branch', '123 Main St, City Center', '+1-555-0001', 'main@lostandfound.com'),
('North Branch', '456 North Ave, North District', '+1-555-0002', 'north@lostandfound.com'),
('South Branch', '789 South Rd, South District', '+1-555-0003', 'south@lostandfound.com');

INSERT INTO categories (name, icon) VALUES
('Electronics', 'fa-laptop'),
('Jewelry & Accessories', 'fa-gem'),
('Clothing', 'fa-tshirt'),
('Bags & Wallets', 'fa-shopping-bag'),
('Keys', 'fa-key'),
('Documents & Cards', 'fa-id-card'),
('Toys & Games', 'fa-gamepad'),
('Sports Equipment', 'fa-futbol'),
('Books & Stationery', 'fa-book'),
('Other', 'fa-box');

-- Default Super Admin (password: Admin@1234)
INSERT INTO users (branch_id, full_name, email, password, role) VALUES
(NULL, 'Super Administrator', 'superadmin@lostandfound.com', '$2y$12$d3tBX9GoUaXrgmTwg875/ubr1XsE/D1rmjOEoqC.BRfNy2.yV/bQm', 'superadmin');

-- Branch Managers (password: Manager@1234)
INSERT INTO users (branch_id, full_name, email, password, role) VALUES
(1, 'Main Branch Manager', 'main.manager@lostandfound.com', '$2y$12$.LuZTyQkq.kY9cWrhUwJ/uoHkWYYfmgZ9oJ8nKVdpcbpRQmMCzxyq', 'branch_manager'),
(2, 'North Branch Manager', 'north.manager@lostandfound.com', '$2y$12$.LuZTyQkq.kY9cWrhUwJ/uoHkWYYfmgZ9oJ8nKVdpcbpRQmMCzxyq', 'branch_manager'),
(3, 'South Branch Manager', 'south.manager@lostandfound.com', '$2y$12$.LuZTyQkq.kY9cWrhUwJ/uoHkWYYfmgZ9oJ8nKVdpcbpRQmMCzxyq', 'branch_manager');

-- Staff (password: Staff@1234)
INSERT INTO users (branch_id, full_name, email, password, role) VALUES
(1, 'Main Staff 1', 'main.staff@lostandfound.com', '$2y$12$8cNWK/X1EEYuVB5UKSF3Ee1VvWukB9I7x8J.Yx3WRLM2RiQZU62Yq', 'staff'),
(2, 'North Staff 1', 'north.staff@lostandfound.com', '$2y$12$8cNWK/X1EEYuVB5UKSF3Ee1VvWukB9I7x8J.Yx3WRLM2RiQZU62Yq', 'staff');
-- Guest account (password: Guest@1234)
INSERT INTO users (branch_id, full_name, email, password, role) VALUES
(NULL, 'Guest User', 'guest@lostandfound.com', '$2y$12$rGvBxfQL2w.AcjNX6oBL4ut6gwTl2UVDfq1PPyD446T2B1pzDXmcu', 'guest');
