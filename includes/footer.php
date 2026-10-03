<?php
// Note: $settings and $pdo are already available from header.php
$stmt = $pdo->prepare("SELECT label, url FROM menu_items WHERE menu_location = 'footer_nav' ORDER BY order_index ASC");
$stmt->execute();
$footerNav = $stmt->fetchAll();
?>
    </main> <!-- End Main Content Container -->

    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- About Institute -->
                <div class="col-lg-5 col-md-6 mb-4 mb-lg-0">
                    <h5>About <?php echo htmlspecialchars($settings['site_name_en'] ?? 'Institute'); ?></h5>
                    <p class="text-light pe-lg-4" style="opacity: 0.8;">
                        <?php echo nl2br(htmlspecialchars($settings['footer_description'] ?? '')); ?>
                    </p>
                    <div class="mt-4">
                        <a href="admin/login.php" class="btn btn-outline-light btn-sm"><i class="fas fa-lock"></i> Staff Login</a>
                    </div>
                </div>

                <!-- Footer Menu Links -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <?php foreach($footerNav as $nav): ?>
                            <li><a href="<?php echo htmlspecialchars($nav['url']); ?>"><i class="fas fa-angle-right me-2"></i><?php echo htmlspecialchars($nav['label']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4 col-md-12">
                    <h5>Contact Us</h5>
                    <ul class="footer-links text-light" style="opacity: 0.9;">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-map-marker-alt mt-1 me-3 text-warning"></i>
                            <span><?php echo htmlspecialchars($settings['site_name_en'] ?? 'Institute'); ?><br>Sri Lanka.</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-phone mt-1 me-3 text-warning"></i>
                            <span><?php echo htmlspecialchars($settings['contact_phone'] ?? 'N/A'); ?></span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-envelope mt-1 me-3 text-warning"></i>
                            <span><?php echo htmlspecialchars($settings['contact_email'] ?? 'N/A'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center text-light" style="opacity: 0.7;">
                        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name_en'] ?? 'Institute'); ?>. All Rights Reserved. Development by Digital Division of Chief Secratry Office (NWP).
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Google Translate Script -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en', 
                includedLanguages: 'en,si,ta', 
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
