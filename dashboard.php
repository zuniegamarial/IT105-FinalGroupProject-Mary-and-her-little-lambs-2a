<!DOCTYPE html>
<html>
<head>
    <title>Stockly Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; }
        .header { background: white; border-bottom: 1px solid #e5e7eb; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; font-weight: bold; color: #1f2937; }
        .logout-btn { background: #fee2e2; color: #991b1b; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; }
        .container { max-width: 1280px; margin: 0 auto; padding: 24px; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
        .card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; }
        .card h2 { font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #1f2937; }
        input, button { border-radius: 8px; padding: 8px 12px; }
        input { width: 100%; border: 1px solid #d1d5db; margin-bottom: 12px; }
        .btn-primary { background: #2563eb; color: white; border: none; font-weight: 600; cursor: pointer; width: 100%; }
        .btn-primary:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; }
        .action-btn { padding: 4px 8px; border-radius: 4px; font-size: 12px; margin: 0 2px; border: none; cursor: pointer; }
        .sell { background: #dcfce7; color: #166534; }
        .edit { background: #dbeafe; color: #1e40af; }
        .delete { background: #fee2e2; color: #991b1b; }
        .search { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #d1d5db; border-radius: 8px; }
    </style>
</head>
<body>
    <?php
    // Database connection
    $host = 'localhost';
    $dbname = 'inventory_sales_db';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
    
    // Handle Add Product
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $supplier = $_POST['supplier'];
        $size = $_POST['category'];
        
        // Get or create supplier
        $stmt = $pdo->prepare("SELECT supplier_id FROM SUPPLIERS WHERE name = ?");
        $stmt->execute([$supplier]);
        $supplierRow = $stmt->fetch();
        if ($supplierRow) {
            $supplier_id = $supplierRow['supplier_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO SUPPLIERS (name) VALUES (?)");
            $stmt->execute([$supplier]);
            $supplier_id = $pdo->lastInsertId();
        }
        
        $stmt = $pdo->prepare("INSERT INTO PRODUCTS (name, price, current_stock, supplier_id, size) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $stock, $supplier_id, $size]);
        header('Location: dashboard.php');
        exit();
    }
    
    // Handle Delete
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM PRODUCTS WHERE product_id = ?");
        $stmt->execute([$id]);
        header('Location: dashboard.php');
        exit();
    }
    
    // Handle Sell (decrease stock)
    if (isset($_GET['sell'])) {
        $id = $_GET['sell'];
        $stmt = $pdo->prepare("UPDATE PRODUCTS SET current_stock = current_stock - 1 WHERE product_id = ? AND current_stock > 0");
        $stmt->execute([$id]);
        header('Location: dashboard.php');
        exit();
    }
    
    // Fetch all products with supplier name
    $stmt = $pdo->query("
        SELECT p.*, COALESCE(s.name, 'Unknown') as supplier_name 
        FROM PRODUCTS p 
        LEFT JOIN SUPPLIERS s ON p.supplier_id = s.supplier_id 
        ORDER BY p.product_id DESC
    ");
    $products = $stmt->fetchAll();
    ?>
    
    <div class="header">
        <h1>Stockly Admin Dashboard</h1>
        <button class="logout-btn" onclick="alert('Logged out (demo)')">Logout</button>
    </div>
    
    <div class="container">
        <div class="grid">
            <!-- Left Column - Add Product Form -->
            <div>
                <div class="card">
                    <h2>Add New Product</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="text" name="name" placeholder="Name" required>
                        <input type="text" name="category" placeholder="Category / Size">
                        <input type="number" step="0.01" name="price" placeholder="Price" required>
                        <input type="number" name="stock" placeholder="Stock" required>
                        <input type="text" name="supplier" placeholder="Supplier">
                        <button type="submit" class="btn-primary">Add Product</button>
                    </form>
                </div>
                
                <div class="card">
                    <h2>Audit Log</h2>
                    <div style="font-size: 13px; color: #6b7280;">
                        <?php echo date('d/m/Y, H:i:s'); ?> - LOGIN: Admin logged in<br>
                        System ready - Products loaded: <?php echo count($products); ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Products Table -->
            <div>
                <div class="card">
                    <h2>Search Products</h2>
                    <input type="text" id="searchInput" class="search" placeholder="Search by name, category or supplier...">
                </div>
                
                <div class="card">
                    <h2>Product List</h2>
                    <div style="overflow-x: auto;">
                        <table id="productTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Supplier</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['size'] ?? 'General'); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['current_stock']; ?></td>
                                    <td><?php echo htmlspecialchars($product['supplier_name']); ?></td>
                                    <td>
                                        <a href="?sell=<?php echo $product['product_id']; ?>" class="action-btn sell" onclick="return confirm('Sell 1 unit?')">Sell</a>
                                        <a href="?delete=<?php echo $product['product_id']; ?>" class="action-btn delete" onclick="return confirm('Delete this product?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($products) == 0): ?>
                                <tr><td colspan="6" style="text-align: center;">No products found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Live search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#productTable tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>