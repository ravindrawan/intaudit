<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Fetch all services
$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll();
?>

<div class="container mt-5 mb-5 ps-0 pe-0">
    <div class="row justify-content-center mx-0">
        <div class="col-lg-10 col-xl-9 px-3">
            <h2 class="section-title mb-4 border-bottom pb-3">Our Services</h2>
            <p class="lead text-muted mb-5">
                Explore the range of services provided by our institution. Click on any service title below to view detailed information, requirements, and procedures.
            </p>
            
            <?php if(empty($services)): ?>
                <div class="alert alert-light text-center py-5 border rounded shadow-sm">
                    <i class="fas fa-info-circle fa-3x text-secondary mb-3"></i>
                    <h5 class="text-muted">No services are currently listed.</h5>
                </div>
            <?php else: ?>
                <div class="accordion shadow-sm" id="servicesAccordion">
                    <?php 
                    $i = 0;
                    foreach($services as $srv): 
                        $collapseId = "collapseSrv" . $srv['id'];
                    ?>
                    <div class="accordion-item border-0 mb-3 rounded shadow-sm overflow-hidden border">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $i !== 0 ? 'collapsed' : ''; ?> fw-bold fs-5 bg-white text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                                <span class="d-flex align-items-center w-100">
                                    <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="fas fa-clipboard-check fs-6"></i>
                                    </span>
                                    <span class="text-primary me-auto"><?php echo htmlspecialchars($srv['title']); ?></span>
                                </span>
                            </button>
                        </h2>
                        <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo $i === 0 ? 'show' : ''; ?>" data-bs-parent="#servicesAccordion">
                            <div class="accordion-body p-4 bg-light">
                                <div class="row align-items-center">
                                    <?php if(!empty($srv['image_path']) && file_exists(__DIR__.'/'.$srv['image_path']) && (!isset($srv['show_image']) || $srv['show_image'] == 1)): ?>
                                        <div class="col-md-5 mb-4 mb-md-0">
                                            <img src="<?php echo htmlspecialchars($srv['image_path']); ?>" alt="Service Image" class="img-fluid rounded shadow-sm w-100 object-fit-cover" style="max-height: 300px;">
                                        </div>
                                        <div class="col-md-7">
                                            <div class="service-description bg-white p-4 rounded shadow-sm border h-100">
                                                <?php echo $srv['description']; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="service-description bg-white p-4 rounded shadow-sm border h-100">
                                                <?php echo $srv['description']; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end mt-3">
                                    <small class="text-muted"><i class="fas fa-clock"></i> Updated: <?php echo date('F d, Y', strtotime($srv['created_at'])); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $i++;
                    endforeach; 
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Style adjustments for summernote outputs */
.service-description img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
}
.service-description ul, .service-description ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}
</style>

<?php require_once 'includes/footer.php'; ?>
