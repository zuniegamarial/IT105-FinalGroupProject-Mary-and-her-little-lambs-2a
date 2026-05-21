<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$dbname = 'inventory_sales_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit();
}

try {
    $stmt = $pdo->query("
        SELECT 
            p.product_id as id,
            p.name,
            p.price,
            p.current_stock as stock,
            COALESCE(p.size, 'General') as category,
            COALESCE(s.name, 'Unknown') as supplier
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