<?php
session_start();
require_once "config/db.php";

$sql = "SELECT id, name, description, price, category, image, stock
FROM products
ORDER BY created_at DESC
LIMIT 4";

$result = mysqli_query($conn, $sql);

if (!$result) {
  die("Unable to load products.");
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techora | Phone Aaccessories Store</title>
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
    <a href="#about">About</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <span>
            Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
        </span>

        <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="admin/dashboard.php">Admin Dashboard</a>
<?php endif; ?>

        <a href="my_orders.php">My Orders</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>

  </header>

  <main>
    <section class="hero">
      <h1>Accessories That Power Your World</h1>
      <p>Discover quality phone accessories at Techora.</p>
      <a href="products.php" class="btn">Explore Products</a>
    </section>

   <section id="products" class="products">
    <h2>Shop By Category</h2>
    <p>Explore our range of quality phone accessories.</p>

    <div class="category-list">

        <a href="products.php?category=Phone%20Cases" class="category-card">
           <img src="assets/images/phone-cases.png" alt="Phone Cases">
            <h3>Phone Cases</h3>
            <p>Protect your phone in style.</p>
        </a>

        <a href="products.php?category=Chargers" class="category-card">
          <img src="assets/images/chargers.png" alt="Chargers">
            <h3>Chargers</h3>
            <p>Keep your devices powered.</p>
        </a>

        <a href="products.php?category=USB%20Cables" class="category-card">
            <h3>USB Cables</h3>
             <img src="assets/images/usb-cables.png" alt="USB Cables">
            <p>Reliable charging and data cables.</p>
        </a>

        <a href="products.php?category=Earphones" class="category-card">
            <h3>Earphones</h3>
            <img src="assets/images/earphones.png" alt="Earphones">
            <p>Enjoy your favourite music.</p>
        </a>

        <a href="products.php?category=Power%20Banks" class="category-card">
            <h3>Power Banks</h3>
            <img src="assets/images/power-banks.png" alt="Power Banks">
            <p>Power on the go.</p>
        </a>

        <a href="products.php?category=Screen%20Protectors" class="category-card">
            <h3>Screen Protectors</h3>
            <img src="assets/images/screen-protectors.png" alt="Screen Protectors">
            <p>Keep your screen protected.</p>
        </a>

    </div>
</section>

   <section id="about" class="about-section">
    <h2>About Techora</h2>

    <p>
        Techora is an online store dedicated to providing quality
        phone accessories at affordable prices. We make it easy
        for customers to find accessories that protect, power and
        enhance their mobile devices.
    </p>

    <p>
        Our goal is to provide a convenient shopping experience,
        reliable products and excellent customer service.
    </p>
</section>

<section id="contact" class="contact-section">
    <h2>Contact Us</h2>
    <p>Have a question or need assistance? We'd love to hear from you.</p>

    <form class="contact-form" method="POST" action="contact.php" id="contactForm">

        <input type="text" name="name" placeholder="Your Name" required>

        <input type="email" name="email" placeholder="Your Email" required>

     <textarea name="message" id="contactMessage" placeholder="Write your message..." rows="6" maxlength="500" required></textarea>

<p id="messageCounter">0 / 500 characters</p>
        <button type="submit">Send Message</button>

    </form>
</section>

    <section class="featured-products">
    <h2>Featured Products</h2>
    <p>Check out some of our latest phone accessories.</p>

    <div class="product-list">
        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>

                <article class="product-card">

                    <?php if (!empty($product['image'])): ?>
                        <img
                            class="product-card-image"
                            src="<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                        >
                    <?php endif; ?>

                    <div class="product-card-content">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>

                        <p><?php echo htmlspecialchars($product['description']); ?></p>

                        <p>Price: MWK <?php echo number_format((float) $product['price']); ?></p>

                        <a href="product_details.php?id=<?php echo (int) $product['id']; ?>"
                           class="view-details-btn">
                            View Details
                        </a>
                    </div>

                </article>

            <?php endwhile; ?>

        <?php else: ?>
            <p>No featured products available yet.</p>
        <?php endif; ?>
    </div>

    <a href="products.php" class="view-all-products">
        View All Products
    </a>
</section>
  </main>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> Techora. all rights reserved.</p>
  </footer>

<script>
const contactMessage = document.getElementById('contactMessage');
const messageCounter = document.getElementById('messageCounter');

function updateMessageCounter() {

    const characterCount = contactMessage.value.length;

    messageCounter.textContent = characterCount + ' / 500 characters';

    if (characterCount >= 500) {
        messageCounter.style.color = 'red';
    } 
    else if (characterCount >= 400) {
        messageCounter.style.color = 'orange';
    } 
    else {
        messageCounter.style.color = 'green';
    }
}

contactMessage.addEventListener('input', updateMessageCounter);

</script>


<button id="scrollToTop" title="Back to top">↑</button>
<script>
const scrollToTopButton = document.getElementById('scrollToTop');

window.addEventListener('scroll', function () {

    if (window.scrollY > 300) {
        scrollToTopButton.style.display = 'block';
    } else {
        scrollToTopButton.style.display = 'none';
    }

});

scrollToTopButton.addEventListener('click', function () {

    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

});
</script>

<script>
const contactForm = document.getElementById('contactForm');

contactForm.addEventListener('submit', function () {

    const submitButton = contactForm.querySelector('button[type="submit"]');

    submitButton.textContent = 'Sending...';
    submitButton.disabled = true;

});
</script>
  </body>
</html>