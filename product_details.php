<?php
session_start();
require_once "config/db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$product_id = (int) $_GET['id'];

$sql = "SELECT id, name, description, price, category, image, stock
        FROM products
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            die("Unable to prepare product query.");
        }

        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);  
        $product = mysqli_fetch_assoc($result);  

        if (!$product) {
            die("Product not found.");
        }


$review_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $review = trim($_POST['review'] ?? '');

    if ($rating === false || $rating === null || $rating < 1 || $rating > 5) {
        $review_message = "Please select a rating between 1 and 5.";

    } elseif ($review === '') {
        $review_message = "Please write a review.";

    } else {

        $insert_sql = "
            INSERT INTO product_reviews
            (product_id, user_id, rating, review)
            VALUES (?, ?, ?, ?)
        ";

        $insert_stmt = mysqli_prepare($conn, $insert_sql);

        mysqli_stmt_bind_param(
            $insert_stmt,
            "iiis",
            $product_id,
            $_SESSION['user_id'],
            $rating,
            $review
        );

        try {

    if (mysqli_stmt_execute($insert_stmt)) {
        header("Location: product_details.php?id=" . $product_id . "&review=success");
        exit;
    }

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() === 1062) {
        $review_message = "You have already reviewed this product.";
    } else {
        $review_message = "Could not submit your review. Please try again.";
    }

}

        mysqli_stmt_close($insert_stmt);
    }
}

// Calculate average product rating
$rating_sql = "
    SELECT 
        COUNT(*) AS total_reviews,
        AVG(rating) AS average_rating
    FROM product_reviews
    WHERE product_id = ?
";

$rating_stmt = mysqli_prepare($conn, $rating_sql);

mysqli_stmt_bind_param($rating_stmt, "i", $product_id);

mysqli_stmt_execute($rating_stmt);

$rating_result = mysqli_stmt_get_result($rating_stmt);

$rating_data = mysqli_fetch_assoc($rating_result);

$total_reviews = (int) $rating_data['total_reviews'];
$average_rating = $rating_data['average_rating'] !== null
    ? round((float) $rating_data['average_rating'], 1)
    : 0;

mysqli_stmt_close($rating_stmt);

        // Retrieve product reviews
        $reviews_sql = "
    SELECT product_reviews.rating,
           product_reviews.review,
           product_reviews.created_at,
           users.full_name
    FROM product_reviews
    JOIN users ON product_reviews.user_id = users.id
    WHERE product_reviews.product_id = ?
    ORDER BY product_reviews.created_at DESC
";

    $reviews_stmt = mysqli_prepare($conn, $reviews_sql);

    mysqli_stmt_bind_param($reviews_stmt, "i", $product_id);

    mysqli_stmt_execute($reviews_stmt);

    $reviews_result = mysqli_stmt_get_result($reviews_stmt);

     ?>


        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Techora | Product Details</title>
                <link rel="stylesheet" href="assets/css/style.css">
            </head>
            <body>

            <header>
                    <a href="index.php" class="logo">
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
            echo array_sum($_SESSION['cart'] ?? []);
        ?></span>)
    </a>

    <a href="index.php#about">About Us</a>
</nav>
                </header>

                <main class="products">
                    <h1>Product Details</h1>
                    <article class="product-details">
                        <h2>
                            <?php echo htmlspecialchars($product['name']); ?>

                        </h2>

                        <p>
                            <strong>Price:</strong> MK <?php echo number_format((float)$product['price']);?>
                        </p>
                <div class="product-average-rating">

    <?php if ($total_reviews > 0): ?>

        <span class="average-rating-number">
            <?php echo number_format($average_rating, 1); ?>/5
        </span>

        <span class="review-stars">
            <?php echo str_repeat('★', (int) round($average_rating)); ?>
            <?php echo str_repeat('☆', 5 - (int) round($average_rating)); ?>
        </span>

        <span class="review-count">
            (<?php echo $total_reviews; ?>
            <?php echo $total_reviews === 1 ? 'review' : 'reviews'; ?>)
        </span>

    <?php else: ?>

        <span class="review-count">No ratings yet</span>

    <?php endif; ?>

</div>

                        <p>
                            <strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?>
                        </p>

                        <p>
                            <strong>Availability:</strong> <?php echo (int)$product['stock']; ?>
                        </p>

                       <form action="cart.php" method="POST" id="addToCartForm">
                            <input
                               type="hidden"
                               name="product_id"
           value="<?php echo (int)$product['id']; ?>"
                            >
              <button type="submit" name="add_to_cart">Add to Cart</button>
             </form>

<h2>Write a Product Review</h2>

<?php if (!empty($review_message)): ?>
    <p><?php echo htmlspecialchars($review_message); ?></p>
<?php endif; ?>

<?php if (isset($_GET['review']) && $_GET['review'] === 'success'): ?>
    <p>Your review was submitted successfully!</p>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>

   <form class="review-form" method="POST" action="product_details.php?id=<?php echo (int) $product_id; ?>">

        <label for="rating">Your Rating:</label><br>

        <select name="rating" id="rating" required>
            <option value="">Select a rating</option>
            <option value="5">★★★★★ - Excellent</option>
            <option value="4">★★★★ - Very Good</option>
            <option value="3">★★★ - Good</option>
            <option value="2">★★ - Fair</option>
            <option value="1">★ - Poor</option>
        </select>

        <br><br>

        <label for="review">Your Review:</label><br>

        <textarea
            name="review"
            id="review"
            rows="5"
            placeholder="Share your experience with this product..."
            required></textarea>

        <br><br>

        <button type="submit" name="submit_review">
            Submit Review
        </button>

    </form>

<?php else: ?>

    <p>Please <a href="login.php">log in</a> to write a product review.</p>

<?php endif; ?>


                   <a href="products.php">Back to Products</a>


<hr>

<h2>Customer Reviews</h2>

<?php if (mysqli_num_rows($reviews_result) > 0): ?>

    <?php while ($review_row = mysqli_fetch_assoc($reviews_result)): ?>

        <div class="review-card">

            <h3>
                <?php echo htmlspecialchars($review_row['full_name']); ?>
            </h3>

           <p>
    <strong>Rating:</strong>
    <span class="review-stars">
        <?php echo str_repeat('★', (int) $review_row['rating']); ?>
        <?php echo str_repeat('☆', 5 - (int) $review_row['rating']); ?>
    </span>
</p>

            <p>
                <?php echo nl2br(htmlspecialchars($review_row['review'])); ?>
            </p>

            <small>
                <?php echo htmlspecialchars($review_row['created_at']); ?>
            </small>

        </div>

        <hr>

    <?php endwhile; ?>

<?php else: ?>

    <p>No reviews yet. Be the first to review this product!</p>

<?php endif; ?>

                    </article>
                </main>

                <footer>
                    <p>
                        &copy; <?php echo date("Y"); ?> Techora. All rights reserved.
                    </p>
                </footer>

            </body>
        </html>

                