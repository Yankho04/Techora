<?php
require_once "config/db.php";

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');


$sql = "SELECT 
            products.id,
            products.name,
            products.description,
            products.price,
            products.category,
            products.image,
            products.stock,
            COUNT(product_reviews.id) AS total_reviews,
            AVG(product_reviews.rating) AS average_rating
        FROM products
        LEFT JOIN product_reviews
            ON products.id = product_reviews.product_id";

if ($search !== '') {
    $sql .= " WHERE (products.name LIKE ? 
                OR products.description LIKE ? 
                OR products.category LIKE ?)";
}

if ($category !== '') {
    $sql .= ($search !== '') ? " AND " : " WHERE ";
    $sql .= " products.category = ?";
}

$sql .= " GROUP BY products.id
          ORDER BY products.created_at DESC";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to prepare product query.");
}

if ($search !== '' && $category !== '') {
    $term = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "ssss", $term, $term, $term, $category);
} elseif ($search !== '') {
    $term = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "sss", $term, $term, $term);
} elseif ($category !== '') {
    mysqli_stmt_bind_param($stmt, "s", $category);
}

if (!mysqli_stmt_execute($stmt)) {
    die("Unable to load products.");
}
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Techora | Products</title>
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
    <a href="login.php">Login</a>
</nav>
    </header>

    <main class="products">
        <h1>Our Products</h1>
        <div class="category-shortcuts">

    <a href="products.php?category=Phone%20Cases">Phone Cases</a>
    <a href="products.php?category=Chargers">Chargers</a>
    <a href="products.php?category=USB%20Cables">USB Cables</a>
    <a href="products.php?category=Earphones">Earphones</a>
    <a href="products.php?category=Power%20Banks">Power Banks</a>
    <a href="products.php?category=Screen%20Protectors">Screen Protectors</a>

</div>
        <p>Explore our phone accessories and find what you need.</p>

        <form action="products.php" method="GET" class="search-form">
            <?php if ($category !== ''): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
          <input
    type="text"
    id="liveSearch"
    name="search"
    placeholder="Search phone accessories..."
    value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
>

            <button type="submit">Search</button>
        </form>

        <form action="products.php" method="GET" class="category-form">
            <?php if ($search !== ''): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <label for="category">Filter by category</label>

            <select name="category" id="liveCategory">
                <option value="">All Categories</option>
                <option value="Phone Cases" <?php echo $category === 'Phone Cases' ? 'selected' : ''; ?>>Phone Cases</option>
                <option value="Chargers" <?php echo $category === 'Chargers' ? 'selected' : ''; ?>>Chargers</option>
                <option value="USB Cables" <?php echo $category === 'USB Cables' ? 'selected' : ''; ?>>USB Cables</option>
                <option value="Earphones" <?php echo $category === 'Earphones' ? 'selected' : ''; ?>>Earphones</option>
                <option value="Power Banks" <?php echo $category === 'Power Banks' ? 'selected' : ''; ?>>Power Banks</option>
                <option value="Screen Protectors" <?php echo $category === 'Screen Protectors' ? 'selected' : ''; ?>>Screen Protectors</option>
            </select>

            <button type="submit">Filter</button>
        </form>

        <div class="product-list">

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($product = mysqli_fetch_assoc($result)): ?>

    <article class="product-card"
    data-search="<?php echo htmlspecialchars(
        strtolower($product['name'] . ' ' . $product['description'] . ' ' . $product['category']),
        ENT_QUOTES,
        'UTF-8'
    ); ?>"

    data-category="<?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?>">
     <a href="product_details.php?id=<?php echo (int) $product['id']; ?>">

     <?php if (!empty($product['image'])): ?>
     <img
      class="product-card-image"
      src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
      alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
      loading="lazy"
                        >
      <?php endif; ?>

      <div class="product-card-content">

     <h3><?php echo htmlspecialchars($product['name']); ?></h3>

   <p><?php echo htmlspecialchars($product['description']); ?></p>

   <p>Price: MK <?php echo number_format((float) $product['price']); ?></p>

   <div class="product-average-rating">

    <?php if ($product['total_reviews'] > 0): ?>

        <span class="average-rating-number">
            <?php echo number_format((float)$product['average_rating'], 1); ?>/5
        </span>

        <span class="review-stars">
            <?php
                $rounded_rating = (int) round($product['average_rating']);

                echo str_repeat('★', $rounded_rating);
                echo str_repeat('☆', 5 - $rounded_rating);
            ?>
        </span>

        <span class="review-count">
            (<?php echo (int)$product['total_reviews']; ?>
            reviews)
        </span>

    <?php else: ?>

        <span class="review-count">No ratings yet</span>

    <?php endif; ?>

</div>

   <p>Category: <?php echo htmlspecialchars($product['category']); ?></p>

   <p><?php echo (int) $product['stock']; ?> in stock</p>

  </div>

        </a>

     </article>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No products are available yet.</p>

    <?php endif; ?>

</div>
        </div>
    </main>

    <script>
const searchInput = document.getElementById('liveSearch');
const categorySelect = document.getElementById('liveCategory');
const productCards = document.querySelectorAll('.product-card');

function filterProducts() {

    const searchTerm = searchInput.value.toLowerCase().trim();
    const selectedCategory = categorySelect.value;

    productCards.forEach(function (card) {

        const productInfo = card.dataset.search;
        const productCategory = card.dataset.category;

        const matchesSearch = productInfo.includes(searchTerm);

        const matchesCategory =
            selectedCategory === '' ||
            productCategory === selectedCategory;

        if (matchesSearch && matchesCategory) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }

    });
}

searchInput.addEventListener('input', filterProducts);
categorySelect.addEventListener('change', filterProducts);

</script>
    </body>
</html>