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
    YEAR(s.sale_date) AS year,
    MONTH(s.sale_date) AS month,
    SUM(si.quantity * si.unit_price) AS total_revenue,
    COUNT(DISTINCT s.id) AS number_of_orders
FROM sales s
JOIN sale_items si ON s.id = si.sale_id
GROUP BY YEAR(s.sale_date), MONTH(s.sale_date)
ORDER BY year DESC, month DESC;

-- -----------------------------------------------------
-- QUERY 2: Top 10 Best-Selling Products (by quantity)
-- Identifies which products sell the most volume
-- -----------------------------------------------------
SELECT 
    p.id AS product_id,
    p.name AS product_name,
    SUM(si.quantity) AS total_quantity_sold,
    SUM(si.quantity * si.unit_price) AS total_revenue_generated
FROM products p
JOIN sale_items si ON p.id = si.product_id
GROUP BY p.id, p.name
ORDER BY total_quantity_sold DESC
LIMIT 10;

-- -----------------------------------------------------
-- QUERY 3: Average Order Value Per Customer
-- Segments customers by spending behavior
-- -----------------------------------------------------
SELECT 
    c.id AS customer_id,
    c.name AS customer_name,
    COUNT(s.id) AS number_of_orders,
    AVG(s.total_amount) AS average_order_value,
    SUM(s.total_amount) AS lifetime_spent
FROM customers c
LEFT JOIN sales s ON c.id = s.customer_id
GROUP BY c.id, c.name
HAVING number_of_orders > 0
ORDER BY average_order_value DESC
LIMIT 20;

-- -----------------------------------------------------
-- QUERY 4: Monthly Revenue Trend with Year-over-Year Comparison
-- Uses conditional aggregation to compare same month across years
-- -----------------------------------------------------
SELECT 
    MONTH(s.sale_date) AS month_num,
    SUM(CASE WHEN YEAR(s.sale_date) = YEAR(CURDATE()) THEN si.quantity * si.unit_price ELSE 0 END) AS current_year_revenue,
    SUM(CASE WHEN YEAR(s.sale_date) = YEAR(CURDATE()) - 1 THEN si.quantity * si.unit_price ELSE 0 END) AS previous_year_revenue,
    ROUND(
        (SUM(CASE WHEN YEAR(s.sale_date) = YEAR(CURDATE()) THEN si.quantity * si.unit_price ELSE 0 END) -
         SUM(CASE WHEN YEAR(s.sale_date) = YEAR(CURDATE()) - 1 THEN si.quantity * si.unit_price ELSE 0 END)) /
        NULLIF(SUM(CASE WHEN YEAR(s.sale_date) = YEAR(CURDATE()) - 1 THEN si.quantity * si.unit_price ELSE 0 END), 0) * 100, 2
    ) AS percent_change
FROM sales s
JOIN sale_items si ON s.id = si.sale_id
WHERE YEAR(s.sale_date) >= YEAR(CURDATE()) - 1
GROUP BY MONTH(s.sale_date)
ORDER BY month_num;

-- -----------------------------------------------------
-- QUERY 5: Customers with Most Purchases (frequency)
-- Identifies loyal, high-frequency customers for VIP programs
-- -----------------------------------------------------
SELECT 
    c.id AS customer_id,
    c.name AS customer_name,
    COUNT(s.id) AS total_orders,
    SUM(s.total_amount) AS total_spent,
    AVG(s.total_amount) AS avg_order_value
FROM customers c
JOIN sales s ON c.id = s.customer_id
GROUP BY c.id, c.name
HAVING total_orders > 1
ORDER BY total_orders DESC
LIMIT 10;