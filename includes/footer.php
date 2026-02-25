<?php require_once __DIR__ . '/../config/db.php'; ?>
<!-- FOOTER -->
<footer>
    <div class="footer-category">
        <div class="container">
            <h2 class="footer-category-title">Brand directory</h2>
            <div class="footer-category-box">
                <h3 class="category-box-title">Fashion :</h3>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Men's" class="footer-category-link">T-shirt</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Men's" class="footer-category-link">Shirts</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Men's" class="footer-category-link">shorts &amp;
                    jeans</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Women's" class="footer-category-link">dress &amp;
                    frock</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Women's"
                    class="footer-category-link">innerwear</a>
            </div>
            <div class="footer-category-box">
                <h3 class="category-box-title">footwear :</h3>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Footwear&sub=Sports"
                    class="footer-category-link">sport</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Footwear&sub=Formal"
                    class="footer-category-link">formal</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Footwear&sub=Boots"
                    class="footer-category-link">Boots</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Footwear&sub=Casual"
                    class="footer-category-link">casual</a>
            </div>
            <div class="footer-category-box">
                <h3 class="category-box-title">jewellery :</h3>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Jewelry&sub=Necklace"
                    class="footer-category-link">Necklace</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Jewelry&sub=Earrings"
                    class="footer-category-link">Earrings</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Jewelry&sub=Rings"
                    class="footer-category-link">Couple rings</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Jewelry&sub=Bracelets"
                    class="footer-category-link">bracelets</a>
            </div>
            <div class="footer-category-box">
                <h3 class="category-box-title">cosmetics :</h3>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Cosmetics&sub=Shampoo"
                    class="footer-category-link">Shampoo</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Cosmetics&sub=Bodywash"
                    class="footer-category-link">Bodywash</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Cosmetics&sub=Makeup"
                    class="footer-category-link">makeup kit</a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=Perfume" class="footer-category-link">perfume</a>
            </div>
        </div>
    </div>

    <div class="footer-nav">
        <div class="container">
            <ul class="footer-nav-list">
                <li class="footer-nav-item">
                    <h2 class="nav-title">Popular Categories</h2>
                </li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/products.php?category=Men's"
                        class="footer-nav-link">Fashion</a></li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/products.php?category=Electronics"
                        class="footer-nav-link">Electronic</a></li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/products.php?category=Cosmetics"
                        class="footer-nav-link">Cosmetic</a></li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/products.php?category=Jewelry"
                        class="footer-nav-link">Jewelry</a></li>
            </ul>
            <ul class="footer-nav-list">
                <li class="footer-nav-item">
                    <h2 class="nav-title">My Account</h2>
                </li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/user/profile.php"
                        class="footer-nav-link">Profile</a></li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/user/orders.php" class="footer-nav-link">My
                        Orders</a></li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/user/wishlist.php"
                        class="footer-nav-link">Wishlist</a></li>
                <li class="footer-nav-item"><a href="<?php echo SITE_URL; ?>/user/cart.php"
                        class="footer-nav-link">Cart</a></li>
            </ul>
            <ul class="footer-nav-list">
                <li class="footer-nav-item">
                    <h2 class="nav-title">Our Company</h2>
                </li>
                <li class="footer-nav-item"><a href="#" class="footer-nav-link">About us</a></li>
                <li class="footer-nav-item"><a href="#" class="footer-nav-link">Terms and conditions</a></li>
                <li class="footer-nav-item"><a href="#" class="footer-nav-link">Privacy Policy</a></li>
                <li class="footer-nav-item"><a href="#" class="footer-nav-link">Secure payment</a></li>
            </ul>
            <ul class="footer-nav-list">
                <li class="footer-nav-item">
                    <h2 class="nav-title">Contact</h2>
                </li>
                <li class="footer-nav-item flex">
                    <div class="icon-box"><ion-icon name="location-outline"></ion-icon></div>
                    <address class="content">419 State 414 Rte, Beaver Dams, New York(NY), 14812, USA</address>
                </li>
                <li class="footer-nav-item flex">
                    <div class="icon-box"><ion-icon name="call-outline"></ion-icon></div>
                    <a href="tel:+6079368058" class="footer-nav-link">(607) 936-8058</a>
                </li>
                <li class="footer-nav-item flex">
                    <div class="icon-box"><ion-icon name="mail-outline"></ion-icon></div>
                    <a href="mailto:support@anon.com" class="footer-nav-link">support@anon.com</a>
                </li>
            </ul>
            <ul class="footer-nav-list">
                <li class="footer-nav-item">
                    <h2 class="nav-title">Follow Us</h2>
                </li>
                <li>
                    <ul class="social-link">
                        <li class="footer-nav-item"><a href="#" class="footer-nav-link"><ion-icon
                                    name="logo-facebook"></ion-icon></a></li>
                        <li class="footer-nav-item"><a href="#" class="footer-nav-link"><ion-icon
                                    name="logo-twitter"></ion-icon></a></li>
                        <li class="footer-nav-item"><a href="#" class="footer-nav-link"><ion-icon
                                    name="logo-linkedin"></ion-icon></a></li>
                        <li class="footer-nav-item"><a href="#" class="footer-nav-link"><ion-icon
                                    name="logo-instagram"></ion-icon></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <img src="<?php echo SITE_URL; ?>/assets/images/payment.png" alt="payment method" class="payment-img">
            <p class="copyright">Copyright &copy; <a href="<?php echo SITE_URL; ?>">Anon</a> all rights reserved.</p>
        </div>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<?php if (isset($extraJs))
    echo $extraJs; ?>
</body>

</html>