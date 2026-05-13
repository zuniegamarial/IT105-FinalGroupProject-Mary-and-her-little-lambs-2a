<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("
        SELECT 
            p.product_id as id,
            p.name,
            COALESCE(s.name, 'Unknown') as supplier,
            p.current_stock as stock,
            p.price,
            'General' as category   
        FROM PRODUCTS p
        LEFT JOIN SUPPLIERS s ON p.supplier_id = s.supplier_id
        ORDER BY p.product_id DESC
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>