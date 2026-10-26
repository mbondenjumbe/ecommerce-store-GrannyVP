<?php
require_once 'config/db.php';
require_once 'components/navbar.php';

// Fetch every single placed order from your new MySQL tracking table
try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
    $ordersLog = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database read failure: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrannyVP - Workshop Orders Log</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .invoice-card { background-color: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
        .invoice-header { display: flex; justify-content: space-between; border-bottom: 1px solid #1e293b; padding-bottom: 10px; margin-bottom: 12px; }
    </style>
</head>
<body>

<div class="directory-container">
    <h1 class="directory-title">⚙️ Incoming Warehouse Orders Log</h1>
    
    <?php if (empty($ordersLog)) { ?>
        <div style="background-color: #0f172a; padding: 40px; border-radius: 8px; border: 1px solid #1e293b; text-align: center;">
            <p style="color: #64748b; font-size: 1rem;">No client orders have been stored inside the database logs yet.</p>
        </div>
    <?php } else { ?>
        <?php foreach ($ordersLog as $order) { ?>
            <div class="invoice-card">
                <div class="invoice-header">
                    <div>
                        <span style="color: #f97316; font-weight: bold; font-size: 1.1rem;">ORDER ID: #00<?php echo $order['id']; ?></span>
                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 2px;"><?php echo $order['created_at']; ?></p>
                    </div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #10b981;">
                        $<?php echo number_format($order['total_price'], 2); ?>
                    </div>
                </div>
                
                <div style="font-size: 0.9rem; margin-bottom: 12px; line-height: 1.5; color: #cbd5e1;">
                    <strong>Customer:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                    <strong>Destination:</strong> <?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?><br>
                    <strong>Freight Router:</strong> <?php echo htmlspecialchars($order['delivery_method']); ?>
                </div>
                
                <div style="background-color: #020617; border: 1px solid #1e293b; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 0.85rem; color: #cbd5e1; border-left: 3px solid #f97316;">
                    <strong>Manifest Contents:</strong><br>
                    <?php echo htmlspecialchars($order['ordered_items']); ?>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
</div>

</body>
</html>
