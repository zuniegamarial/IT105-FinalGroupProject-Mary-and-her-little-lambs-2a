<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>