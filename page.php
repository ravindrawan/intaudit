<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$page = null;

if (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
}

if (!$page) {
    echo "<div class='alert alert-warning mt-5'>Page not found or is currently unavailable.</div>";
    require_once 'includes/footer.php';
    exit();
}
?>

<div class="row mt-4 mb-5 justify-content-center">
    <div class="col-lg-10">
        <div class="card card-elegant border-0 p-lg-5 p-4 bg-white">
            <h1 class="section-title mb-4"><?php echo htmlspecialchars($page['title']); ?></h1>
            <div class="page-content text-dark" style="font-size: 1.1rem; line-height: 1.8;">
                <!-- HTML content saved from WYSIWYG -->
                <?php echo $page['content']; ?>
            </div>
            
            <hr class="mt-5 mb-3">
            <p class="text-muted" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> Last updated on <?php echo date('F d, Y, h:i A', strtotime($page['last_updated'])); ?>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
