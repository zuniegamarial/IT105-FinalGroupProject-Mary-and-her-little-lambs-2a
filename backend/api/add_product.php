<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data['name'] ?? '');
$price = floatval($data['price'] ?? 0);
$stock = intval($data['stock'] ?? 0);
$supplier = trim($data['supplier'] ?? '');
$category = trim($data['category'] ?? '');

if (empty($name) || $price <= 0) {
    echo json_encode(["success" => false, "message" => "Product name and valid price are required"]);
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
    
    // Insert product
    $sql = "INSERT INTO PRODUCTS (name, price, current_stock, size, supplier_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $price, $stock, $category, $supplier_id]);
    
    $newId = $pdo->lastInsertId();
    
    echo json_encode([
        "success" => true, 
        "message" => "Product added successfully",
        "id" => $newId
    ]);
    
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>