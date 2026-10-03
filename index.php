<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Fetch recent news (limit 6)
$stmtNews = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 6");
$recentNews = $stmtNews->fetchAll();

// Fetch recent downloads (limit 5)
$stmtDownloads = $pdo->query("SELECT * FROM downloads ORDER BY uploaded_at DESC LIMIT 5");
$recentDownloads = $stmtDownloads->fetchAll();

// Fetch Sliders
$stmtSliders = $pdo->query("SELECT * FROM sliders ORDER BY order_index ASC");
$sliders = $stmtSliders->fetchAll();
$animationType = $settings['slider_animation_type'] ?? 'slide';
$animationSpeed = isset($settings['slider_animation_speed']) ? (int)$settings['slider_animation_speed'] : 3000;
?>

<style>
    /* Override default container for full-width home slider */
    main.container {
        max-width: 100% !important;
        padding: 0 !important;
    }
    #homeSlider .carousel-item img {
        height: 550px !important; 
    }
    #homeSlider {
        margin-bottom: 0 !important; /* Removes bottom margin so container mt-5 handles spacing */
    }
</style>

<!-- Full-width Slider Section -->
<?php if(count($sliders) > 0): ?>
<div id="homeSlider" class="carousel slide <?php echo $animationType === 'fade' ? 'carousel-fade' : ''; ?> mb-5" data-bs-ride="carousel" data-bs-interval="<?php echo $animationSpeed > 0 ? $animationSpeed : 'false'; ?>">
    <!-- Indicators -->
    <div class="carousel-indicators">
        <?php foreach($sliders as $index => $slide): ?>
            <button type="button" data-bs-target="#homeSlider" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"></button>
        <?php endforeach; ?>
    </div>
    
    <!-- Slider Images -->
    <div class="carousel-inner">
        <?php foreach($sliders as $index => $slide): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="<?php echo htmlspecialchars($slide['image_path']); ?>" class="d-block w-100" alt="Slider Image" style="object-fit: cover; height: 500px; filter: brightness(0.8);">
                
                <?php if(!empty($slide['title']) || !empty($slide['subtitle'])): ?>
                <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); padding: 20px; border-radius: 10px; bottom: 40px;">
                    <?php if(!empty($slide['title'])): ?>
                        <h2 class="fw-bold text-white text-uppercase" style="letter-spacing: 1px;"><?php echo htmlspecialchars($slide['title']); ?></h2>
                    <?php endif; ?>
                    <?php if(!empty($slide['subtitle'])): ?>
                        <p class="fs-5 text-light"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Navigation Buttons -->
    <button class="carousel-control-prev" type="button" data-bs-target="#homeSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
<?php endif; ?>

<div class="container mt-5">
<?php
// Initialize About Data for the Home Page widget
$visionShow = isset($settings['about_vision_show_home']) ? $settings['about_vision_show_home'] : '0';
$missionShow = isset($settings['about_mission_show_home']) ? $settings['about_mission_show_home'] : '0';
$descShow = isset($settings['about_desc_show_home']) ? $settings['about_desc_show_home'] : '0';
$chairShow = isset($settings['about_chairman_msg_show_home']) ? $settings['about_chairman_msg_show_home'] : '0';
$secShow = isset($settings['about_sec_msg_show_home']) ? $settings['about_sec_msg_show_home'] : '0';

$aboutVision = isset($settings['about_vision']) ? $settings['about_vision'] : '';
$aboutMission = isset($settings['about_mission']) ? $settings['about_mission'] : '';
$aboutDesc = isset($settings['about_desc']) ? $settings['about_desc'] : '';
$aboutChair = isset($settings['about_chairman_msg']) ? $settings['about_chairman_msg'] : '';
$aboutSec = isset($settings['about_sec_msg']) ? $settings['about_sec_msg'] : '';
$chairTitle = isset($settings['about_chairman_title']) && !empty($settings['about_chairman_title']) ? $settings['about_chairman_title'] : 'Chairman / Governor Message';
$secTitle = isset($settings['about_sec_title']) && !empty($settings['about_sec_title']) ? $settings['about_sec_title'] : 'Secretary Message';

$hasAnyAbout = ($visionShow === '1' && !empty($aboutVision)) ||
               ($missionShow === '1' && !empty($aboutMission)) ||
               ($descShow === '1' && !empty($aboutDesc)) || 
               ($chairShow === '1' && !empty($aboutChair)) || 
               ($secShow === '1' && !empty($aboutSec));
?>

<?php if($hasAnyAbout): ?>
<section class="mb-5">
    
    <!-- Vision & Mission Top Row -->
    <?php if(($visionShow === '1' && !empty($aboutVision)) || ($missionShow === '1' && !empty($aboutMission))): ?>
    <div class="row g-4 mb-4">
        <?php if($visionShow === '1' && !empty($aboutVision)): ?>
        <div class="col-md-<?php echo ($missionShow === '1' && !empty($aboutMission)) ? '6' : '12'; ?>">
            <div class="card h-100 border border-light shadow-sm card-elegant bg-white p-4">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-eye fa-3x mb-3 text-warning"></i>
                    <h4 class="fw-bold mb-3">Our Vision</h4>
                    <p class="fs-6 mb-0" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($aboutVision)); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($missionShow === '1' && !empty($aboutMission)): ?>
        <div class="col-md-<?php echo ($visionShow === '1' && !empty($aboutVision)) ? '6' : '12'; ?>">
            <div class="card h-100 border border-light shadow-sm card-elegant bg-white p-4">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <i class="fas fa-bullseye fa-3x mb-3 text-warning"></i>
                    <h4 class="fw-bold mb-3">Our Mission</h4>
                    <p class="fs-6 mb-0" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($aboutMission)); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Description & Leadership Row -->
    <?php if(($descShow === '1' && !empty($aboutDesc)) || ($chairShow === '1' || $secShow === '1')): ?>
    <div class="row g-4">
        
        <?php if($descShow === '1' && !empty($aboutDesc)): ?>
        <div class="col-lg-<?php echo ($chairShow === '1' || $secShow === '1') ? '7' : '12'; ?>">
            <div class="bg-white shadow-sm rounded-4 border border-light p-4 p-md-5 h-100">
                <h1 class="fw-bold section-title d-inline-block mb-4">Welcome to <?php echo htmlspecialchars($settings['site_name_en'] ?? 'our Institute'); ?></h1>
                <div class="text-muted mt-3 mb-4 page-content" style="font-size: 1.05rem; line-height: 1.8;">
                    <?php echo $aboutDesc; ?>
                </div>
                <div class="mt-4">
                    <a href="about-us.php" class="btn btn-primary px-4 shadow-sm rounded-pill fw-semibold me-2">Read Our Full Story</a>
                    <a href="contact.php" class="btn btn-outline-secondary px-4 shadow-sm rounded-pill fw-semibold">Contact Us</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($chairShow === '1' || $secShow === '1'): ?>
        <div class="col-lg-<?php echo ($descShow === '1' && !empty($aboutDesc)) ? '5' : '12'; ?>">
            <div class="d-flex flex-column h-100 gap-4">
                
                <?php if($chairShow === '1' && !empty($aboutChair)): ?>
                <div class="card border-0 shadow-sm rounded-4 flex-grow-1 bg-white">
                    <div class="card-header bg-dark text-white p-3 fw-bold rounded-top-4">
                        <i class="fas fa-comment-dots text-warning me-2"></i> <?php echo htmlspecialchars($chairTitle); ?>
                    </div>
                    <div class="card-body p-4 page-content text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                        <?php echo $aboutChair; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($secShow === '1' && !empty($aboutSec)): ?>
                <div class="card border-0 shadow-sm rounded-4 flex-grow-1 bg-white">
                    <div class="card-header bg-secondary text-white p-3 fw-bold rounded-top-4">
                        <i class="fas fa-comment-dots text-warning me-2"></i> <?php echo htmlspecialchars($secTitle); ?>
                    </div>
                    <div class="card-body p-4 page-content text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                        <?php echo $aboutSec; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php
// Fetch Services for homepage widget (limit 6)
$stmtServices = $pdo->query("SELECT * FROM services ORDER BY created_at DESC LIMIT 6");
$recentServices = $stmtServices->fetchAll();
$showServicesHome = isset($settings['show_services_home']) ? $settings['show_services_home'] : '1';
?>

<?php if(count($recentServices) > 0 && $showServicesHome === '1'): ?>
<section class="py-5 bg-light mb-5 rounded-4 shadow-sm border border-light">
    <div class="px-4">
        <div class="text-center mb-5">
            <h2 class="section-title fw-bold d-inline-block">Our Services</h2>
            <p class="text-muted mt-2">Discover the key services we offer to the public.</p>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach($recentServices as $srv): ?>
            <div class="col">
                <a href="services.php" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm card-elegant service-card bg-white">
                        <?php if(!empty($srv['image_path']) && file_exists(__DIR__.'/'.$srv['image_path']) && (!isset($srv['show_image']) || $srv['show_image'] == 1)): ?>
                            <img src="<?php echo htmlspecialchars($srv['image_path']); ?>" class="card-img-top" alt="Service" style="height: 180px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="fas fa-clipboard-list fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body text-center p-4">
                            <h5 class="card-title fw-bold text-dark mb-0"><?php echo htmlspecialchars($srv['title']); ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="services.php" class="btn btn-primary px-4 shadow-sm rounded-pill fw-semibold">View All Services</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Quick Downloads Row -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card card-elegant bg-light border-0 shadow-sm p-4 rounded-4">
            <h3 class="section-title mb-4 fw-bold text-dark border-bottom pb-2"><i class="fas fa-download text-primary me-2"></i> Recent Uploads</h3>
            <div class="row g-3">
                <?php foreach($recentDownloads as $file): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 hover-effect">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="me-3 text-danger"><i class="fas fa-file-pdf fa-2x"></i></div>
                            <div class="overflow-hidden">
                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="text-decoration-none fw-semibold text-dark d-block mb-1 text-truncate" title="<?php echo htmlspecialchars($file['title']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($file['title']); ?>
                                </a>
                                <small class="text-muted"><i class="fas fa-clock"></i> <?php echo date('Y-m-d', strtotime($file['uploaded_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(count($recentDownloads) === 0): ?>
                <div class="col-12 text-muted">No downloads available.</div>
                <?php endif; ?>
            </div>
            
            <?php if(count($recentDownloads) > 0): ?>
            <div class="mt-4 text-center">
                <a href="downloads.php" class="btn btn-outline-dark rounded-pill px-4 shadow-sm fw-semibold"><i class="fas fa-folder-open me-2"></i> Browse All Files</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent News Carousel Row -->
<div class="row mb-5">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="section-title mb-0 fw-bold"><i class="fas fa-newspaper text-primary me-2"></i> Recent Updates & News</h3>
            <?php if(count($recentNews) > 0): ?>
                <a href="news.php" class="btn btn-link text-decoration-none d-none d-md-block fw-semibold">View All News Alerts &rarr;</a>
            <?php endif; ?>
        </div>
        
        <div class="card card-elegant border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <?php if(count($recentNews) > 0): ?>
            <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-inner">
                    <?php foreach($recentNews as $index => $news): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-5">
                                <?php if(!empty($news['image_path']) && file_exists(__DIR__.'/'.$news['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($news['image_path']); ?>" class="img-fluid w-100" alt="News Image" style="height: 350px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center w-100" style="height: 350px;">
                                        <i class="fas fa-newspaper fa-5x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7 p-4 p-md-5">
                                <small class="text-primary mb-3 fw-bold d-inline-block"><i class="fas fa-calendar-alt me-2"></i><?php echo date('F d, Y', strtotime($news['created_at'])); ?></small>
                                <h3 class="card-title fw-bold mb-3 text-dark"><?php echo htmlspecialchars($news['title']); ?></h3>
                                <p class="card-text text-muted mb-4 fs-6" style="line-height: 1.8;">
                                    <?php echo substr(strip_tags($news['content']), 0, 200) . '...'; ?>
                                </p>
                                <a href="news.php?id=<?php echo $news['id']; ?>" class="btn btn-primary px-4 shadow-sm rounded-pill fw-semibold">Read Full Article <i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if(count($recentNews) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev" style="width: 5%; background: rgba(0,0,0,0.1);">
                    <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(1) grayscale(100); width: 2rem; height: 2rem;"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next" style="width: 5%; background: rgba(0,0,0,0.1);">
                    <span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(1) grayscale(100); width: 2rem; height: 2rem;"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="p-5 text-center text-muted">No news available at the moment.</div>
            <?php endif; ?>
        </div>
        
        <?php if(count($recentNews) > 0): ?>
            <div class="text-center mt-4 d-md-none">
                <a href="news.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm fw-semibold">View All News</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Officers Section conditional display -->
<?php if(isset($settings['show_officers_home']) && $settings['show_officers_home'] === '1'): ?>
    <section class="py-5 bg-light mb-5 rounded-4 shadow-sm border border-light">
        <div class="px-4">
            <?php include 'includes/officers_grid.php'; ?>
        </div>
    </section>
<?php endif; ?>
</div> <!-- End Content Container wrapper -->

<?php require_once 'includes/footer.php'; ?>
