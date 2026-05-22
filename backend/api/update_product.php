<?php
require_once 'config.php';

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

if ($price <= 0) {
    echo json_encode(["success" => false, "message" => "Price must be greater than 0"]);
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