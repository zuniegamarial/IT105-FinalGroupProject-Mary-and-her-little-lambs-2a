<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS");
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
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
    exit();
}

// Get input data
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? 0;
$name = $data['name'] ?? '';
$price = $data['price'] ?? 0;
$stock = $data['stock'] ?? 0;
$supplier = $data['supplier'] ?? '';
$category = $data['category'] ?? '';

if (!$id || empty($name)) {
    echo json_encode(["success" => false, "message" => "Product ID and name are required"]);
    exit();
}

try {
    // Handle supplier
    $supplier_id = null;
    if (!empty($supplier)) {
        $stmt = $pdo->prepare("SELECT supplier_id FROM SUPPLIERS WHERE name = ?");
        $stmt->execute([$supplier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $supplier_id = $row['supplier_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO SUPPLIERS (name) VALUES (?)");
            $stmt->execute([$supplier]);
            $supplier_id = $pdo->lastInsertId();
        }
    }
    
    // Update product
    $sql = "UPDATE products SET name = ?, price = ?, current_stock = ?, size = ?, supplier_id = ? WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $price, $stock, $category, $supplier_id, $id]);
    
    echo json_encode(["success" => true, "message" => "Product updated successfully"]);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>