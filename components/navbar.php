<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-logo">🔧 GrannyVP</a>
        <div class="nav-links">
            <a href="products.php">All Parts</a>
            <div class="dropdown">
                <button class="dropbtn">Varieties ▼</button>
                <div class="dropdown-content">
                    <a href="products.php?category=brakes">Brakes & Suspension</a>
                    <a href="products.php?category=engine">Engine & Drivetrain</a>
                    <a href="products.php?category=electrical">Electrical & Lighting</a>
                    <a href="products.php?category=filters">Filters & Fluids</a>
                    <a href="products.php?category=body">Body Parts</a>
                </div>
            </div>
        </div>
        <form action="products.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by OEM or part name...">
            <button type="submit">Search</button>
        </form>
        <div class="nav-cart">
            <a href="cart.php">🛒 Cart <span class="cart-badge"><?php echo $cart_count; ?></span></a>
        </div>
    </div>
</nav>
