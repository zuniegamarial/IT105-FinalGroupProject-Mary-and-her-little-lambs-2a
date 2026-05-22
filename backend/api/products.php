<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("
        SELECT 
            p.product_id as id,
            p.name,
            p.price,
            p.current_stock as stock,
            COALESCE(p.size, 'General') as category,
            COALESCE(s.name, 'Unknown') as supplier,
            COALESCE(p.order_status, 'available') as order_status
        FROM PRODUCTS p
        LEFT JOIN SUPPLIERS s ON p.supplier_id = s.supplier_id
        ORDER BY p.product_id DESC
        LIMIT 2000
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>