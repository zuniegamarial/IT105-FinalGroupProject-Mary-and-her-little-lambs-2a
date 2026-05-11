-- =====================================================
-- BI QUERIES – Inventory & Sales System
-- Phase 3 (Week 12)
-- SQL Developer: Mary Franxine Nicol
-- =====================================================

USE inventory_sales_db;

-- -----------------------------------------------------
-- QUERY 1: Total Sales Per Month (Revenue Trend)
-- Shows monthly revenue, useful for identifying peak seasons
-- -----------------------------------------------------
SELECT 
    YEAR(o.order_date) AS year,
    MONTH(o.order_date) AS month,
    SUM(oi.subtotal) AS total_revenue,
    COUNT(DISTINCT o.order_id) AS number_of_orders
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
GROUP BY YEAR(o.order_date), MONTH(o.order_date)
ORDER BY year DESC, month DESC;

-- -----------------------------------------------------
-- QUERY 2: Top 10 Best-Selling Products (by quantity)
-- Identifies which products sell the most volume
-- -----------------------------------------------------
SELECT 
    p.product_id,
    p.name AS product_name,
    SUM(oi.quantity) AS total_quantity_sold,
    SUM(oi.subtotal) AS total_revenue_generated
FROM products p
JOIN order_items oi ON p.product_id = oi.product_id
GROUP BY p.product_id, p.name
ORDER BY total_quantity_sold DESC
LIMIT 10;

-- -----------------------------------------------------
-- QUERY 3: Average Order Value Per Customer
-- Segments customers by spending behavior
-- -----------------------------------------------------
SELECT 
    c.customer_id,
    c.name AS customer_name,
    COUNT(o.order_id) AS number_of_orders,
    AVG(o.total_amount) AS average_order_value,
    SUM(o.total_amount) AS lifetime_spent
FROM customers c
LEFT JOIN orders o ON c.customer_id = o.customer_id
GROUP BY c.customer_id, c.name
HAVING number_of_orders > 0
ORDER BY average_order_value DESC
LIMIT 20;

-- -----------------------------------------------------
-- QUERY 4: Monthly Revenue Trend with Year-over-Year Comparison
-- Uses conditional aggregation to compare same month across years
-- -----------------------------------------------------
SELECT 
    MONTH(o.order_date) AS month_num,
    SUM(CASE WHEN YEAR(o.order_date) = YEAR(CURDATE()) THEN oi.subtotal ELSE 0 END) AS current_year_revenue,
    SUM(CASE WHEN YEAR(o.order_date) = YEAR(CURDATE()) - 1 THEN oi.subtotal ELSE 0 END) AS previous_year_revenue,
    ROUND(
        (SUM(CASE WHEN YEAR(o.order_date) = YEAR(CURDATE()) THEN oi.subtotal ELSE 0 END) -
         SUM(CASE WHEN YEAR(o.order_date) = YEAR(CURDATE()) - 1 THEN oi.subtotal ELSE 0 END)) /
        NULLIF(SUM(CASE WHEN YEAR(o.order_date) = YEAR(CURDATE()) - 1 THEN oi.subtotal ELSE 0 END), 0) * 100, 2
    ) AS percent_change
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
WHERE YEAR(o.order_date) >= YEAR(CURDATE()) - 1
GROUP BY MONTH(o.order_date)
ORDER BY month_num;

-- -----------------------------------------------------
-- QUERY 5: Customers with Most Purchases (frequency)
-- Identifies loyal, high-frequency customers for VIP programs
-- -----------------------------------------------------
SELECT 
    c.customer_id,
    c.name AS customer_name,
    COUNT(o.order_id) AS total_orders,
    SUM(o.total_amount) AS total_spent,
    AVG(o.total_amount) AS avg_order_value
FROM customers c
JOIN orders o ON c.customer_id = o.customer_id
GROUP BY c.customer_id, c.name
HAVING total_orders > 1
ORDER BY total_orders DESC
LIMIT 10;