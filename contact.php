<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($title) || empty($message)) {
        $errorMsg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (name, email, phone, title, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $title, $message]);
            $successMsg = "Thank you for your feedback! We will get back to you shortly.";
        } catch (PDOException $e) {
            $errorMsg = "An error occurred while submitting your feedback. Please try again later.";
        }
    }
}

// Fetch the static contact page content if it exists
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'contact'");
$stmt->execute();
$page = $stmt->fetch();

// Fetch the dynamic personnel directory
$stmtContacts = $pdo->query("SELECT * FROM contacts ORDER BY order_index ASC, id DESC");
$contacts = $stmtContacts->fetchAll();
?>

<div class="container mt-5 mb-5 pb-5">
    
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="section-title fw-bold">Contact Us</h1>
        </div>
    </div>

    <?php if ($page && !empty(trim(strip_tags($page['content'])))): ?>
    <div class="row justify-content-center mb-5">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm p-lg-5 p-4 bg-white card-elegant">
                <div class="page-content text-dark" style="font-size: 1.1rem; line-height: 1.8;">
                    <?php echo $page['content']; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($contacts) > 0): ?>
    <div class="row mb-4 text-center mt-5">
    </div>
    
    <div class="row g-4 justify-content-center">
        <?php foreach ($contacts as $c): ?>
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border border-light shadow-sm card-elegant bg-white p-4 text-center">
                
                <div class="mb-4">
                    <?php if(!empty($c['image_path']) && file_exists(__DIR__ . '/' . $c['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($c['image_path']); ?>" alt="<?php echo htmlspecialchars($c['name']); ?>" class="rounded-circle shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #fff;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 140px; height: 140px; border: 4px solid #fff;">
                            <i class="fas fa-user fa-4x mb-2"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($c['name']); ?></h4>
                
                <?php if(!empty($c['designation'])): ?>
                    <h6 class="text-primary fw-semibold mb-3"><?php echo htmlspecialchars($c['designation']); ?></h6>
                <?php endif; ?>

                <ul class="list-unstyled text-start mx-auto mt-4" style="max-width: 320px;">
                    <?php if(!empty($c['phone'])): ?>
                    <li class="mb-2 text-muted d-flex align-items-center"><i class="fas fa-phone-alt text-secondary me-3" style="width: 20px; text-align: center;"></i> <span class="text-dark fw-medium text-truncate"><?php echo htmlspecialchars($c['phone']); ?></span></li>
                    <?php endif; ?>

                    <?php if(!empty($c['fax'])): ?>
                    <li class="mb-2 text-muted d-flex align-items-center"><i class="fas fa-fax text-secondary me-3" style="width: 20px; text-align: center;"></i> <span class="text-dark fw-medium text-truncate"><?php echo htmlspecialchars($c['fax']); ?></span></li>
                    <?php endif; ?>

                    <?php if(!empty($c['email'])): ?>
                    <li class="mb-2 text-muted d-flex align-items-center"><i class="fas fa-envelope text-secondary me-3" style="width: 20px; text-align: center;"></i> <a href="mailto:<?php echo htmlspecialchars($c['email']); ?>" class="text-decoration-none fw-medium text-truncate"><?php echo htmlspecialchars($c['email']); ?></a></li>
                    <?php endif; ?>
                </ul>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Feedback Form Section -->
    <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-lg-5 p-4 bg-white card-elegant">
                <h3 class="fw-bold mb-4 text-center">We Value Your Feedback</h3>
                
                <?php if (!empty($successMsg)): ?>
                    <div class="alert alert-success fw-bold text-center"><?php echo $successMsg; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger fw-bold text-center"><?php echo $errorMsg; ?></div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="name" name="name" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control bg-light" id="email" name="email" required placeholder="john@example.com">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-muted">(Optional)</span></label>
                            <input type="text" class="form-control bg-light" id="phone" name="phone" placeholder="+94 77 123 4567">
                        </div>
                        <div class="col-md-6">
                            <label for="title" class="form-label">Subject / Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="title" name="title" required placeholder="Regarding our services">
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control bg-light" id="message" name="message" rows="5" required placeholder="Please type your message here..."></textarea>
                        </div>
                        <div class="col-12 text-center mt-4">
                            <button type="submit" name="submit_feedback" class="btn btn-primary px-5 py-2 fw-bold text-uppercase">Send Feedback</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
