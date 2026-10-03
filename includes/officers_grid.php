<?php
// includes/officers_grid.php
$stmt = $pdo->query("SELECT * FROM officers ORDER BY section_name ASC, order_index ASC");
$allOfficers = $stmt->fetchAll();

if (empty($allOfficers)) {
    return; // Don't show anything if no officers
}

// Group by section
$officersBySection = [];
foreach ($allOfficers as $off) {
    $sec = $off['section_name'] ?: 'Staff';
    if (!isset($officersBySection[$sec])) {
        $officersBySection[$sec] = [];
    }
    $officersBySection[$sec][] = $off;
}
?>

<div class="row mt-5 mb-4">
    <div class="col-12 text-center">
        <h2 class="section-title fw-bold">Our Leadership</h2>
    </div>
</div>

<?php foreach ($officersBySection as $sectionName => $officers): ?>
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h4 class="fw-bold text-primary border-bottom d-inline-block pb-2"><?php echo htmlspecialchars($sectionName); ?></h4>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php foreach ($officers as $officer): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 text-center">
                    <div class="card h-100 border-0 shadow-sm card-elegant bg-white p-3 py-4">
                        <div class="card-body p-0">
                            <?php if(!empty($officer['photo_path']) && file_exists(__DIR__ . '/../' . $officer['photo_path'])): ?>
                                <img src="<?php echo htmlspecialchars($officer['photo_path']); ?>" alt="<?php echo htmlspecialchars($officer['name']); ?>" class="rounded-circle mb-3 border border-3 border-light shadow-sm" style="width: 140px; height: 140px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 border border-3 border-white shadow-sm" style="width: 140px; height: 140px; font-size: 3.5rem; color: #ccc;">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                            <?php endif; ?>
                            <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($officer['name']); ?></h5>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($officer['designation']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
