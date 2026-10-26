<?php
require_once 'config/db.php';

// Start a secure user session to remember the cart items across pages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------
// PART 1: SCRIPT LOGIC (Handling Add, Update, and Remove Actions)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // ACTION A: Add a spare part to the cart manifest
    if ($action === 'add' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        
        $stmt = $pdo->prepare("SELECT id, name, price, image_url, oem_number, stock FROM products WHERE id = :id");
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && $product['stock'] > 0) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$product_id])) {
                if ($_SESSION['cart'][$product_id]['quantity'] < $product['stock']) {
                    $_SESSION['cart'][$product_id]['quantity']++;
                }
            } else {
                $_SESSION['cart'][$product_id] = [
                    'name' => $product['name'],
                    'price' => (float)$product['price'],
                    'image' => $product['image_url'],
                    'oem' => $product['oem_number'],
                    'stock' => (int)$product['stock'],
                    'quantity' => 1
                ];
            }
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // ACTION B: Update specific element quantities directly from the cart grid
    if ($action === 'update' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]); 
            } else {
                $_SESSION['cart'][$product_id]['quantity'] = min($quantity, $_SESSION['cart'][$product_id]['stock']);
            }
        }
        header('Location: cart.php');
        exit;
    }
    
    // ACTION C: Delete an item completely from the order manifest
    if ($action === 'remove' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        header('Location: cart.php');
        exit;
    }
}

// Include your global navbar right after updating any data arrays
require_once 'components/navbar.php';

// Gather operational totals
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = ($subtotal > 150 || $subtotal == 0) ? 0.00 : 15.99;
$totalCost = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GrannyVP - Your Garage Order Manifest</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .cart-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 20px; }
        .cart-list { background-color: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 20px; }
        .cart-item-row { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #1e293b; gap: 20px; }
        .cart-item-row:last-child { border-bottom: none; }
        .item-meta { display: flex; align-items: center; gap: 16px; }
        .item-thumb { width: 80px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #1e293b; }
        .quantity-controls { display: flex; align-items: center; gap: 8px; }
        .quantity-input { width: 50px; background-color: #020617; border: 1px solid #334155; color: white; text-align: center; padding: 6px; border-radius: 4px; font-weight: bold; }
        .summary-box { background-color: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 20px; height: fit-content; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 0.95rem; color: #cbd5e1; }
        .summary-total { border-top: 2px solid #1e293b; padding-top: 14px; margin-top: 14px; font-size: 1.3rem; font-weight: 900; color: #f97316; }
        .btn-update { background-color: #1e293b; border: 1px solid #334155; color: white; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: bold; }
        .btn-update:hover { background-color: #334155; }
        .btn-remove { background: none; border: none; color: #ef4444; font-size: 0.8rem; font-weight: bold; cursor: pointer; }
        .btn-remove:hover { text-decoration: underline; }
        @media (max-width: 768px) { .cart-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="directory-container">
    <h1 class="directory-title">Your Garage Order Manifest</h1>

    <?php if (empty($cartItems)) { ?>
        <div style="background-color: #0f172a; padding: 40px; border-radius: 8px; border: 1px solid #1e293b; text-align: center; margin-top: 20px;">
            <p style="color: #94a3b8; font-size: 1.1rem; margin-bottom: 16px;">Your workshop's shopping manifest is currently empty.</p>
            <a href="products.php" class="action-btn" style="display: inline-block; max-width: 250px;">Browse Available Parts</a>
        </div>
    <?php } else { ?>
        <div class="cart-layout">
            
            <div class="cart-list">
                <?php foreach ($cartItems as $id => $item) { ?>
                    <div class="cart-item-row">
                        <div class="item-meta">
                            <img class="item-thumb" src="<?php echo htmlspecialchars($item['image']); ?>" alt="Part Photo">
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: white;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <div class="part-oem" style="margin-bottom: 0;">OEM: <?php echo htmlspecialchars($item['oem']); ?></div>
                                <div style="color: #f97316; font-weight: 800; font-size: 0.9rem; margin-top: 4px;">
                                    $<?php echo number_format($item['price'], 2); ?> each
                                </div>
                            </div>
                        </div>

                        <div class="quantity-controls">
                            <form action="cart.php" method="POST" style="display: flex; align-items: center; gap: 6px;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="quantity-input">
                                <button type="submit" class="btn-update">Update</button>
                            </form>

                            <form action="cart.php" method="POST" style="margin-left: 10px;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                <button type="submit" class="btn-remove">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="summary-box">
                <h3 style="font-size: 1.1rem; font-weight: 800; border-bottom: 1px solid #1e293b; padding-bottom: 10px; margin-bottom: 16px;">
                    Order Balance Breakdown
                </h3>
                
                <div class="summary-row">
                    <span>Parts Subtotal:</span>
                    <span style="font-family: monospace; font-weight: bold;">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Estimated Shipping:</span>
                    <span style="font-family: monospace; font-weight: bold;">
                        <?php echo ($shipping == 0) ? '<span style="color: #10b981;">FREE</span>' : '$' . number_format($shipping, 2); ?>
                    </span>
                </div>

                <?php if ($shipping > 0) { ?>
                    <div style="background-color: #020617; border: 1px solid #1e293b; padding: 8px; border-radius: 4px; font-size: 0.75rem; color: #94a3b8; margin-bottom: 14px;">
                        💡 Spend <span style="color: #f97316; font-weight: bold;">$<?php echo number_format(150 - $subtotal, 2); ?></span> more to unlock **Free Shipping**!
                    </div>
                <?php } ?>

                <div class="summary-row summary-total">
                    <span>Total Cost:</span>
                    <span style="font-family: monospace;">$<?php echo number_format($totalCost, 2); ?></span>
                </div>

                <a href="checkout.php" class="action-btn" style="display: block; text-align: center; font-weight: 700; margin-top: 20px; padding: 12px;">
                    Proceed to Secure Checkout
                </a>
            </div>

        </div>
    <?php } ?>
</div>

<footer class="footer" style="margin-top: 40px;">
