<?php
session_start();

require_once "config/db.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if (!$product_id || $product_id < 1) {
        $_SESSION['cart_message'] = "Unable to add this product to your cart.";
    } else {
        $sql = "SELECT stock FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt === false) {
            die("Unable to prepare product query.");
        }

        mysqli_stmt_bind_param($stmt, "i", $product_id);

        if (!mysqli_stmt_execute($stmt)) {
            die("Unable to check product availability.");
        }

        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if (!$product) {
            $_SESSION['cart_message'] = "This product is no longer available.";
        } else {
            $quantity = (int) ($_SESSION['cart'][$product_id] ?? 0);
            $stock = (int) $product['stock'];

            if ($quantity >= $stock) {
                $_SESSION['cart_message'] = "There is not enough stock to add this product.";
            } else {
                $_SESSION['cart'][$product_id] = $quantity + 1;
                $_SESSION['cart_message'] = "Product added to your cart.";
            }
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity_change'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity_change = $_POST['quantity_change'];

    if (!$product_id || $product_id < 1 || !isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart_message'] = "Unable to update this cart item.";
    } elseif (!in_array($quantity_change, ['increase', 'decrease'], true)) {
        $_SESSION['cart_message'] = "Invalid quantity change.";
    } elseif ($quantity_change === 'decrease') {
        if ($_SESSION['cart'][$product_id] > 1) {
            $_SESSION['cart'][$product_id]--;
            $_SESSION['cart_message'] = "Cart quantity updated.";
        }
    } else {
        $sql = "SELECT stock FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt === false) {
            die("Unable to prepare product query.");
        }

        mysqli_stmt_bind_param($stmt, "i", $product_id);

        if (!mysqli_stmt_execute($stmt)) {
            die("Unable to check product availability.");
        }

        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if (!$product) {
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['cart_message'] = "This product is no longer available and was removed from your cart.";
        } elseif ($_SESSION['cart'][$product_id] >= (int) $product['stock']) {
            $_SESSION['cart_message'] = "There is not enough stock to increase this quantity.";
        } else {
            $_SESSION['cart'][$product_id]++;
            $_SESSION['cart_message'] = "Cart quantity updated.";
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $remove_id = filter_input(INPUT_POST, 'remove_id', FILTER_VALIDATE_INT);

    if ($remove_id && isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }

    header("Location: cart.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techora | Shopping Cart</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo" aria-label="Techora home">
            <span class="logo-mark">
                <img src="assets/images/techora-logo.jpg" alt="Techora logo" width="170" height="113">
            </span>
            <span class="logo-tagline">Phone Accessories Store</span>
        </a>

        <nav>
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
           <a href="cart.php">
    Cart (<span id="cartCount"><?php
        echo array_sum($_SESSION['cart']);
    ?></span>)
</a>
        </nav>
    </header>

    <main class="products">
        <h1>Your Shopping Cart</h1>

        <?php if (isset($_SESSION['cart_message'])): ?>
            <p role="status" id="cartMessage"><?php echo htmlspecialchars($_SESSION['cart_message'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php unset($_SESSION['cart_message']); ?>
        <?php endif; ?>

        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty</p>
            <a href="products.php">Browse Products</a>
        <?php else: ?>
            <?php
            $cart_ids = array_keys($_SESSION['cart']);
            $total = 0;
            ?>

            <?php foreach ($cart_ids as $id): ?>
                <?php
                $sql = "SELECT id, name, price, stock
                        FROM products
                        WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt === false) {
                    continue;
                }

                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);

                if (!$product) {
                    continue;
                }

                $quantity = (int) $_SESSION['cart'][$id];
                $subtotal = (float) $product['price'] * $quantity;
                $total += $subtotal;
                ?>

    <article class="product-card cart-item"
    data-product-id="<?php echo (int) $product['id']; ?>"
    data-price="<?php echo (float) $product['price']; ?>"
    data-stock="<?php echo (int) $product['stock']; ?>">
 <h3><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
  <p>
    Price: MK <span class="item-subtotal" data-subtotal="<?php echo $subtotal; ?>">
        <?php echo number_format($subtotal, 2); ?>
    </span>
</p>
        <div class="quantity-controls">

    <button
        type="button"
        class="quantity-btn"
        data-action="decrease"
        aria-label="Decrease quantity of <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
        <?php echo $quantity <= 1 ? 'disabled' : ''; ?>
    >-</button>

    <span class="item-quantity">
        <?php echo $quantity; ?>
    </span>

    <button
        type="button"
        class="quantity-btn"
        data-action="increase"
        aria-label="Increase quantity of <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
        <?php echo $quantity >= (int) $product['stock'] ? 'disabled' : ''; ?>
    >+</button>

</div>

<button
    type="button"
    class="remove-cart-item"
    data-product-id="<?php echo (int) $product['id']; ?>"
>
    Remove
</button>
   </article>
 <?php endforeach; ?>
<h2>
    Total: MK <span id="cartTotal"><?php echo number_format($total, 2); ?></span>
</h2>

<a href="products.php">Continue Shopping</a>

<a href="checkout.php" class="checkout-btn">
    Proceed to Checkout
</a>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Techora. All rights reserved</p>
    </footer>

    <script>
const cartMessage = document.getElementById('cartMessage');

if (cartMessage) {
    cartMessage.style.padding = '12px';
    cartMessage.style.backgroundColor = '#e6f7e9';
    cartMessage.style.color = '#207a35';
    cartMessage.style.borderRadius = '6px';

    setTimeout(function () {
        cartMessage.style.opacity = '0';

        setTimeout(function () {
            cartMessage.style.display = 'none';
        }, 500);

    }, 3000);
}

</script>


<script>
document.querySelectorAll('.quantity-btn').forEach(function (button) {

    button.addEventListener('click', function () {

        const cartItem = button.closest('.cart-item');

        const productId = cartItem.dataset.productId;
        const action = button.dataset.action;

        const formData = new FormData();

        formData.append('product_id', productId);
        formData.append('action', action);

        // Prevent repeated clicks while updating.
        const buttons = cartItem.querySelectorAll('.quantity-btn');

        buttons.forEach(function (btn) {
            btn.disabled = true;
        });

        fetch('update_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {

            if (!data.success) {
                alert(data.message);
                return;
            }

            // Update the displayed quantity.
            cartItem.querySelector('.item-quantity').textContent =
                data.quantity;

            // Update the product subtotal.
            cartItem.querySelector('.item-subtotal').textContent =
                data.subtotal;

            // Update the overall cart total.
            document.getElementById('cartTotal').textContent =
                data.total;

                // Update the navigation cart counter.
document.getElementById('cartCount').textContent =
    data.cart_count;
    
                // Update the navigation cart counter.
document.getElementById('cartCount').textContent =
    data.cart_count;

        })
        .catch(function (error) {
            alert('Unable to update your cart. Please try again.');
            console.error(error);
        })
        .finally(function () {

            const currentQuantity = Number(
                cartItem.querySelector('.item-quantity').textContent.trim()
            );
            const availableStock = Number(cartItem.dataset.stock);

            buttons.forEach(function (btn) {
                const buttonAction = btn.dataset.action;
                btn.disabled = buttonAction === 'decrease'
                    ? currentQuantity <= 1
                    : currentQuantity >= availableStock;
            });
        });

    });
});
</script>


<script>
document.querySelectorAll('.remove-cart-item').forEach(function (button) {

    button.addEventListener('click', function () {

        const cartItem = button.closest('.cart-item');
        const productId = button.dataset.productId;

        if (!confirm('Are you sure you want to remove this product?')) {
            return;
        }

        button.disabled = true;

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch('remove_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {

            if (!data.success) {
                alert(data.message);
                button.disabled = false;
                return;
            }

            // Remove the product card from the page.
            cartItem.remove();

            // Update the grand total.
            document.getElementById('cartTotal').textContent =
                data.total;

            // Show an empty-cart message if no products remain.
            if (data.empty) {
                location.reload();
            }

        })
        .catch(function (error) {
            alert('Unable to remove this product. Please try again.');
            console.error(error);
            button.disabled = false;
        });

    });

});
</script>


</body>
</html>