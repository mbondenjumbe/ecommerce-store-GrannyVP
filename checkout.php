<?php
require_once 'config/db.php';
require_once 'components/navbar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// If a mechanic lands on the checkout layout with an empty sheet, send them back to components catalog
if (empty($cartItems)) {
    header('Location: products.php');
    exit;
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = ($subtotal > 150) ? 0.00 : 15.99;
$totalCost = $subtotal + $shipping;

// Catch final simulated order transmission requests
$orderSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // 1. Capture text fields from the checkout form inputs safely
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $delivery_method = isset($_POST['delivery_method']) ? trim($_POST['delivery_method']) : '';
    
    // 2. Compile your cart products array manifest into a readable summary string
    $summaryLines = [];
    foreach ($cartItems as $item) {
        $summaryLines[] = $item['name'] . " [OEM: " . $item['oem'] . "] x" . $item['quantity'];
    }
    $ordered_items_string = implode(', ', $summaryLines);

    // 3. Write the transaction data row straight into your new MySQL orders table
    $insertStmt = $pdo->prepare("INSERT INTO orders (first_name, last_name, address, city, delivery_method, total_price, ordered_items) 
                                 VALUES (:first_name, :last_name, :address, :city, :delivery_method, :total_price, :ordered_items)");
    $insertStmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':address' => $address,
        ':city' => $city,
        ':delivery_method' => $delivery_method,
        ':total_price' => $totalCost,
        ':ordered_items' => $ordered_items_string
    ]);

    // 4. Clear the active session cart manifest after successful database storage
    $_SESSION['cart'] = [];
    $orderSuccess = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💳 Secure Checkout - GrannyVP</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .checkout-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px; }
        .form-panel { background-color: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 24px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; background-color: #020617; border: 1px solid #334155; color: white; padding: 10px 14px; border-radius: 6px; font-size: 0.9rem; }
        .form-group input:focus { outline: none; border-color: #f97316; }
        .order-summary-list { border-bottom: 1px solid #1e293b; padding-bottom: 14px; margin-bottom: 14px; }
        .summary-item-line { display: flex; justify-content: space-between; font-size: 0.85rem; color: #cbd5e1; margin-bottom: 8px; }
        @media (max-width: 768px) { .checkout-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="directory-container">
    <h1 class="directory-title">Secure Order Checkout</h1>

    <?php if ($orderSuccess) { ?>
        <!-- Success Confirmation Slate Notification Box -->
        <div style="background-color: #0f172a; border: 1px solid #1e293b; padding: 40px; border-radius: 8px; text-align: center; margin-top: 25px;">
            <div style="font-size: 3rem; margin-bottom: 16px;">📦</div>
            <h2 style="color: #10b981; font-weight: 900;">Order Dispatched Successfully!</h2>
            <p style="color: #94a3b8; font-size: 0.95rem; margin: 12px 0 24px 0; max-width: 500px; margin-inline: auto;">
                Your components configuration manifest has been transmitted to our warehouse distribution team. Your parts are being organized for immediate workshop routing.
            </p>
            <a href="index.php" class="action-btn" style="display: inline-block; max-width: 250px;">Return to Main Dashboard</a>
        </div>
    <?php } else { ?>
        <div class="checkout-layout">
            
            <!-- Left Panel: Delivery Information Entry Form -->
            <form action="checkout.php" method="POST" class="form-panel">
                <h3 style="font-size: 1.1rem; border-bottom: 1px solid #1e293b; padding-bottom: 10px; margin-bottom: 20px; font-weight: bold;">
                    Shipping Details & Workshop Location
                </h3>
                
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label>First Name</label>
                        <input type="text" name="first_name" required placeholder="John">
                    </div>
                    <div>
                        <label>Last Name</label>
                        <input type="text" name="last_name"  required placeholder="Doe">
                    </div>
                </div>

                <div class="form-group">
                    <label>Workshop Street Address</label>
                    <input type="text" name="address" required placeholder="123 Mechanics Lane">
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px;">
                    <div>
                        <label>City Location</label>
                        <input type="text" name="city location" required placeholder="Buea">
                    </div>
                    <div>
                        <label>Postal Code</label>
                        <input type="text" name="postal code" placeholder="00237">
                    </div>
                </div>

                <div class="form-group">
                    <label>Delivery Method Tag</label>
                    <select>
                        <option>Standard Ground Freight Route</option>
                        <option>Express Overnight Air Delivery</option>
                        <option>Direct Workshop Counter Pick-up</option>
                    </select>
                </div>

                <input type="hidden" name="place_order" value="1">
                <button type="submit" class="action-btn" style="width: 100%; padding: 12px; font-weight: bold; margin-top: 10px;">
                    🔒 Authorize Delivery & Finalize Order
                </button>
            </form>

            <!-- Right Panel: Final Manifest Balance Recap Card -->
            <div class="summary-box" style="margin-top: 0;">
                <h3 style="font-size: 1.1rem; border-bottom: 1px solid #1e293b; padding-bottom: 10px; margin-bottom: 16px; font-weight: bold;">
                    Manifest Overview
                </h3>
                
                <div class="order-summary-list">
                    <?php foreach ($cartItems as $id => $item) { ?>
                        <div class="summary-item-line">
                            <span><?php echo htmlspecialchars($item['name']); ?> <b style="color: #f97316;">x<?php echo $item['quantity']; ?></b></span>
                            <span style="font-family: monospace;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php } ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal Balance:</span>
                    <span style="font-family: monospace;">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Route:</span>
                    <span style="font-family: monospace;">
                        <?php echo ($shipping == 0) ? '<span style="color:#10b981;">FREE</span>' : '$' . number_format($shipping, 2); ?>
                    </span>
                </div>
                <div class="summary-row summary-total">
                    <span>Final Account Total:</span>
                    <span style="font-family: monospace;">$<?php echo number_format($totalCost, 2); ?></span>
                </div>
            </div>

        </div>
    <?php } ?>
</div>

<footer class="footer" style="margin-top: 40px;">
    <p>&copy; <?php echo date('Y'); ?> GrannyVP Spare Parts Shop. Secure Transaction Emulator Stack.</p>
</footer>

</body>
</html>
