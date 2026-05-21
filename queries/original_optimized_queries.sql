-- =====================================================
-- ORIGINAL SLOW QUERIES vs OPTIMIZED VERSIONS
-- Database: inventory_sales_db
-- Author: Mary Franxine Nicol (SQL Developer)
-- =====================================================

-- =====================================================
-- QUERY 1: Find products that need reordering (low stock)
-- =====================================================

-- ORIGINAL (SLOW) - No index on current_stock, scans all 100,000 rows
SELECT product_id, name, current_stock, reorder_level
FROM products 
WHERE current_stock < reorder_level;

-- OPTIMIZED - With index on current_stock
-- Run this once: CREATE INDEX idx_current_stock ON products(current_stock);
SELECT product_id, name, current_stock, reorder_level
FROM products 
WHERE current_stock < reorder_level;


-- =====================================================
-- QUERY 2: Find expensive products (price > $200)
-- =====================================================

-- ORIGINAL (SLOW) - No index on price, full table scan
SELECT product_id, name, price, size, current_stock
FROM products 
WHERE price > 200;

-- OPTIMIZED - With index on price
-- Run this once: CREATE INDEX idx_price ON products(price);
SELECT product_id, name, price, size, current_stock
FROM products 
WHERE price > 200;


-- =====================================================
-- QUERY 3: Search products by name (text search)
-- =====================================================

-- ORIGINAL (SLOW) - LIKE with leading wildcard (%) cannot use index
-- Example: find products with "boost" anywhere in name
SELECT product_id, name, price
FROM products 
WHERE name LIKE '%boost%';

-- OPTIMIZED - Using FULLTEXT search (much faster for text)
-- Run this once: CREATE FULLTEXT INDEX idx_name ON products(name);
SELECT product_id, name, price
FROM products 
WHERE MATCH(name) AGAINST('boost' IN NATURAL LANGUAGE MODE);


-- =====================================================
-- QUERY 4: Products with supplier information (JOIN query)
-- =====================================================

-- ORIGINAL (SLOW) - Subquery runs for EVERY row (100,000 subqueries!)
SELECT p.product_id, p.name, p.price,
    (SELECT name FROM suppliers WHERE supplier_id = p.supplier_id) as supplier_name
FROM products p
WHERE p.supplier_id IS NOT NULL;

-- OPTIMIZED - Using INNER JOIN (single pass, much faster)
-- Run this once: CREATE INDEX idx_supplier_id ON products(supplier_id);
SELECT p.product_id, p.name, p.price, s.name as supplier_name
FROM products p
INNER JOIN suppliers s ON p.supplier_id = s.supplier_id;


-- =====================================================
-- QUERY 5: Products with total quantity sold (from order_items)
-- =====================================================

-- ORIGINAL (SLOW) - Correlated subquery runs for each product
SELECT p.product_id, p.name,
    (SELECT SUM(quantity) FROM order_items oi WHERE oi.product_id = p.product_id) as total_quantity_sold
FROM products p;

-- OPTIMIZED - Using LEFT JOIN with GROUP BY (single pass)
-- Run this once: CREATE INDEX idx_product_id ON order_items(product_id);
SELECT p.product_id, p.name, COALESCE(SUM(oi.quantity), 0) as total_quantity_sold
FROM products p
LEFT JOIN order_items oi ON p.product_id = oi.product_id
GROUP BY p.product_id, p.name;


-- =====================================================
-- CREATE ALL INDEXES (Run this section ONCE to optimize)
-- =====================================================

-- Products table indexes
CREATE INDEX idx_current_stock ON products(current_stock);
CREATE INDEX idx_price ON products(price);
CREATE INDEX idx_supplier_id ON products(supplier_id);
CREATE FULLTEXT INDEX idx_name ON products(name);

-- Order_items table index
CREATE INDEX idx_product_id ON order_items(product_id);

-- Verify indexes were created
SHOW INDEX FROM products;
SHOW INDEX FROM order_items;


-- =====================================================
-- HOW TO TEST PERFORMANCE DIFFERENCES
-- =====================================================

/*
TESTING INSTRUCTIONS:

1. Enable profiling:
   SET profiling = 1;

2. Run the ORIGINAL (slow) query
3. Run the OPTIMIZED version
4. Repeat for all 5 queries

5. View execution times:
   SHOW PROFILES;

6. Disable profiling when done:
   SET profiling = 0;

EXPECTED RESULTS (approximate):
- Query 1: Slow ~0.3-0.5 sec → Optimized ~0.01-0.03 sec
- Query 2: Slow ~0.3-0.5 sec → Optimized ~0.01-0.03 sec  
- Query 3: Slow ~0.5-1.0 sec → Optimized ~0.02-0.05 sec
- Query 4: Slow ~5-10 sec (subquery) → Optimized ~0.05 sec
- Query 5: Slow ~5-10 sec (subquery) → Optimized ~0.05 sec
*/