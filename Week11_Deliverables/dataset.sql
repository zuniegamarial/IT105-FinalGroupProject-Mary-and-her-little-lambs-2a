-- ============================================
-- DATASET.SQL - Inventory System
-- ============================================

CREATE TABLE SUPPLIERS (
    supplier_id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    phone TEXT,
    email TEXT,
    address TEXT
);

CREATE TABLE CUSTOMERS (
    customer_id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    phone TEXT,
    email TEXT,
    address TEXT,
    registration_date TEXT
);

CREATE TABLE PRODUCTS (
    product_id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    size TEXT,
    current_stock INTEGER DEFAULT 0,
    reorder_level INTEGER DEFAULT 10,
    supplier_id INTEGER
);

CREATE TABLE INVENTORY_LOG (
    log_id INTEGER PRIMARY KEY,
    product_id INTEGER,
    change_type TEXT NOT NULL,
    quantity_changed INTEGER NOT NULL,
    old_stock INTEGER,
    new_stock INTEGER,
    reason TEXT,
    timestamp TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE PAYMENT (
    payment_id INTEGER PRIMARY KEY,
    order_id INTEGER,
    amount_paid REAL NOT NULL,
    method TEXT NOT NULL,
    cash_amount REAL,
    change_due REAL,
    status TEXT DEFAULT 'paid',
    payment_date TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ORDERS (
    order_id INTEGER PRIMARY KEY,
    customer_id INTEGER,
    order_date TEXT DEFAULT CURRENT_TIMESTAMP,
    total_amount REAL NOT NULL,
    payment_id INTEGER
);

CREATE TABLE ORDER_ITEMS (
    order_item_id INTEGER PRIMARY KEY,
    order_id INTEGER,
    product_id INTEGER,
    quantity INTEGER NOT NULL,
    unit_price_at_sale REAL NOT NULL,
    subtotal REAL NOT NULL
);

-- ============================================
-- SAMPLE DATA
-- ============================================

INSERT INTO SUPPLIERS VALUES
(1, 'Adidas Philippines', '02-8888-1234', 'philippines@adidas.com', 'BGC, Taguig'),
(2, 'Adidas Official Store', '02-8888-5678', 'store@adidas.com.ph', 'Ayala Ave, Makati');

INSERT INTO CUSTOMERS VALUES
(101, 'Juan Dela Cruz', '09171234567', 'juan@email.com', 'Quezon City', '2024-01-15'),
(102, 'Maria Santos', '09181234568', 'maria@email.com', 'Manila', '2024-02-20');

INSERT INTO PRODUCTS VALUES
(1001, 'Adidas Ultraboost 22', 'Lightweight running shoes', 8500.00, 'US 8', 25, 5, 1),
(1002, 'Adidas Ultraboost 22', 'Lightweight running shoes', 8500.00, 'US 9', 30, 5, 1),
(1003, 'Adidas Superstar', 'Classic shell toe sneakers', 4500.00, 'US 8', 40, 8, 2),
(1004, 'Adidas Superstar', 'Classic shell toe sneakers', 4500.00, 'US 9', 35, 8, 2),
(1005, 'Adidas NMD R1', 'Modern streetwear sneaker', 7200.00, 'US 8', 15, 4, 1),
(1006, 'Adidas NMD R1', 'Modern streetwear sneaker', 7200.00, 'US 9', 18, 4, 1);

INSERT INTO PAYMENT VALUES
(5001, 2001, 8500.00, 'cash', 10000.00, 1500.00, 'paid', '2024-03-15 14:30:00'),
(5002, 2002, 9000.00, 'cash', 10000.00, 1000.00, 'paid', '2024-03-16 10:15:00'),
(5003, 2003, 7200.00, 'card', NULL, NULL, 'paid', '2024-03-17 16:45:00');

INSERT INTO ORDERS VALUES
(2001, 101, '2024-03-15 14:30:00', 8500.00, 5001),
(2002, 102, '2024-03-16 10:15:00', 9000.00, 5002),
(2003, 101, '2024-03-17 16:45:00', 7200.00, 5003);

INSERT INTO ORDER_ITEMS VALUES
(3001, 2001, 1001, 1, 8500.00, 8500.00),
(3002, 2002, 1003, 2, 4500.00, 9000.00),
(3003, 2003, 1005, 1, 7200.00, 7200.00);

INSERT INTO INVENTORY_LOG VALUES
(4001, 1001, 'RECEIVE', 25, 0, 25, 'RESTOCK', '2024-03-01 09:00:00'),
(4002, 1002, 'RECEIVE', 30, 0, 30, 'RESTOCK', '2024-03-01 09:00:00'),
(4003, 1003, 'RECEIVE', 40, 0, 40, 'RESTOCK', '2024-03-01 10:00:00'),
(4004, 1004, 'RECEIVE', 35, 0, 35, 'RESTOCK', '2024-03-01 10:00:00'),
(4005, 1005, 'RECEIVE', 15, 0, 15, 'RESTOCK', '2024-03-02 09:00:00'),
(4006, 1006, 'RECEIVE', 18, 0, 18, 'RESTOCK', '2024-03-02 09:00:00'),
(4007, 1001, 'DEDUCT', 1, 25, 24, 'SALE', '2024-03-15 14:30:00'),
(4008, 1003, 'DEDUCT', 2, 40, 38, 'SALE', '2024-03-16 10:15:00'),
(4009, 1005, 'DEDUCT', 1, 15, 14, 'SALE', '2024-03-17 16:45:00');

-- ============================================
-- END
-- ============================================