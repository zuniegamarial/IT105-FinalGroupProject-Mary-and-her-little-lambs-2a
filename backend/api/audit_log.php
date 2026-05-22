<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Create table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $sql = "SELECT * FROM audit_log ORDER BY timestamp DESC LIMIT 100";
        $stmt = $pdo->query($sql);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($logs);
        exit();
    }
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            echo json_encode(["status" => "error", "message" => "No data received"]);
            exit();
        }
        
        $action = $data['action'] ?? 'UNKNOWN';
        $details = $data['details'] ?? '';
        $user_id = $data['user_id'] ?? null;
        
        $sql = "INSERT INTO audit_log (user_id, action, details) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$user_id, $action, $details])) {
            echo json_encode(["status" => "success", "message" => "Log saved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save log"]);
        }
        exit();
    }
    
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>