-- Step 1: Create and use database
CREATE DATABASE IF NOT EXISTS inventory_sales_system;
USE inventory_sales_system;

-- =====================================================
-- Step 2: Create tables (order matters due to FKs)
-- =====================================================

-- 2.1 SUPPLIERS (no dependencies)
CREATE TABLE SUPPLIERS (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT
);

-- 2.2 CUSTOMERS (no dependencies)
CREATE TABLE CUSTOMERS (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    registration_date DATE DEFAULT (CURRENT_DATE)
);

-- 2.3 PRODUCTS (depends on SUPPLIERS)
CREATE TABLE PRODUCTS (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(20),
    unit_price DECIMAL(10,2) NOT NULL,
    current_stock INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    supplier_id INT,
    FOREIGN KEY (supplier_id) REFERENCES SUPPLIERS(supplier_id) ON DELETE SET NULL
);

-- 2.4 ORDERS (depends on CUSTOMERS)
-- NOTE: payment_id removed (circular reference fixed)
CREATE TABLE ORDERS (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES CUSTOMERS(customer_id) ON DELETE SET NULL
);

-- 2.5 PAYMENT (depends on ORDERS)
-- payment_id removed from ORDERS, only FK here
CREATE TABLE PAYMENT (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    method VARCHAR(20) NOT NULL,
    cash_amount DECIMAL(10,2),
    change_due DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'completed',
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES ORDERS(order_id) ON DELETE CASCADE
);

-- 2.6 ORDER_ITEMS (depends on ORDERS and PRODUCTS)
CREATE TABLE ORDER_ITEMS (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price_at_sale DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES ORDERS(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCTS(product_id) ON DELETE RESTRICT
);

-- 2.7 INVENTORY_LOG (depends on PRODUCTS)
CREATE TABLE INVENTORY_LOG (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    change_type VARCHAR(20) NOT NULL,
    quantity_changed INT NOT NULL,
    old_stock INT,
    new_stock INT,
    reason VARCHAR(100),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES PRODUCTS(product_id) ON DELETE CASCADE
);