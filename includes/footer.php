<footer class="main-footer">
    <!-- Curved Top Wave -->
    <div class="footer-wave-top">
        <svg viewBox="0 0 1440 120" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,120 480,0 720,50 C960,100 1200,20 1440,60 L1440,120 L0,120 Z" fill="#0c1a35"></path>
        </svg>
    </div>

    <div class="footer-inner">
        <div class="container footer-flex">
            <!-- University Info / Logo on Right in RTL -->
            <div class="footer-brand">
                <div class="footer-logo-wrap">
                    <img src="assets/images/logo-white.svg" alt="دانشگاه اراک" class="footer-logo">
                    <div class="footer-brand-text">
                        <h3 class="footer-title">دانشگاه اراک</h3>
                        <p class="footer-tagline">سامانه خبری انجمن‌های علمی دانشگاه اراک</p>
                        <span class="footer-slogan">همراه با علم، در مسیر پیشرفت</span>
                    </div>
                </div>
            </div>

            <!-- Social Links & Portal on Left in RTL -->
            <div class="footer-actions">
                <div class="footer-social-links">
                    <a href="https://t.me" target="_blank" rel="noopener noreferrer" class="social-circle" aria-label="کانال تلگرام">
                        <?= svgIcon('telegram', '', 18) ?>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-circle" aria-label="صفحه اینستاگرام">
                        <?= svgIcon('instagram', '', 18) ?>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="social-circle" aria-label="صفحه لینکدین">
                        <?= svgIcon('linkedin', '', 18) ?>
                    </a>
                    <a href="https://araku.ac.ir" target="_blank" rel="noopener noreferrer" class="social-circle" aria-label="پورتال دانشگاه اراک">
                        <?= svgIcon('grid', '', 18) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p>© <?= date('Y') ?> سامانه انجمن‌های علمی دانشگاه اراک — تمامی حقوق محفوظ است.</p>
            </div>
        </div>
    </div>
</footer>

<script src="assets/js/scripts.js"></script>
</body>
</html>
