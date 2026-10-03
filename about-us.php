<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Fetch About Us settings
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'about_%'");
$stmt->execute();
$aboutData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Initialize array keys if empty
$defaults = ['about_vision', 'about_mission', 'about_desc', 'about_chairman_msg', 'about_sec_msg', 'about_chairman_title', 'about_sec_title', 'about_chairman_msg_show_about', 'about_sec_msg_show_about'];
foreach($defaults as $k) {
    if(!isset($aboutData[$k])) $aboutData[$k] = '';
}
?>

<div class="container mt-5 mb-5 pb-5">
    
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="section-title fw-bold">About Our Institute</h1>
            <p class="lead text-muted mt-3">Discover our legacy, vision, and the core mission that drives our services to the public.</p>
        </div>
    </div>

    <!-- Vision & Mission -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card h-100 border border-light shadow-sm card-elegant bg-white p-4">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-3x mb-4 text-warning"></i>
                    <h3 class="fw-bold mb-3">Our Vision</h3>
                    <p class="fs-5" style="line-height: 1.8;">
                        <?php echo !empty($aboutData['about_vision']) ? nl2br(htmlspecialchars($aboutData['about_vision'])) : '<em>Vision statement not updated yet.</em>'; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border border-light shadow-sm card-elegant bg-white p-4">
                <div class="card-body text-center">
                    <i class="fas fa-bullseye fa-3x mb-4 text-warning"></i>
                    <h3 class="fw-bold mb-3">Our Mission</h3>
                    <p class="fs-5" style="line-height: 1.8;">
                        <?php echo !empty($aboutData['about_mission']) ? nl2br(htmlspecialchars($aboutData['about_mission'])) : '<em>Mission statement not updated yet.</em>'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Description -->
    <?php if(!empty($aboutData['about_desc'])): ?>
    <div class="row justify-content-center mb-5">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm p-lg-5 p-4 bg-white">
                <h3 class="fw-bold border-bottom pb-3 mb-4 text-primary"><i class="fas fa-landmark me-2"></i> Who We Are</h3>
                <div class="page-content text-dark" style="font-size: 1.15rem; line-height: 1.9;">
                    <?php echo $aboutData['about_desc']; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Leadership Messages -->
    <div class="row g-4 justify-content-center">
        <?php if(!empty($aboutData['about_chairman_msg']) && ($aboutData['about_chairman_msg_show_about'] === '1' || $aboutData['about_chairman_msg_show_about'] === '')): ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-header bg-dark text-white p-3 fw-bold fs-5 text-center">
                    <i class="fas fa-comment-dots text-warning me-2"></i> <?php echo htmlspecialchars($aboutData['about_chairman_title'] ?: 'Message from the Chairman'); ?>
                </div>
                <div class="card-body p-4 text-dark" style="font-size: 1.05rem; line-height: 1.7;">
                    <?php echo $aboutData['about_chairman_msg']; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($aboutData['about_sec_msg']) && ($aboutData['about_sec_msg_show_about'] === '1' || $aboutData['about_sec_msg_show_about'] === '')): ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-header bg-secondary text-white p-3 fw-bold fs-5 text-center">
                    <i class="fas fa-comment-dots text-warning me-2"></i> <?php echo htmlspecialchars($aboutData['about_sec_title'] ?: 'Message from the Secretary'); ?>
                </div>
                <div class="card-body p-4 text-dark" style="font-size: 1.05rem; line-height: 1.7;">
                    <?php echo $aboutData['about_sec_msg']; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Officers / Staff Section -->
    <?php include 'includes/officers_grid.php'; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
