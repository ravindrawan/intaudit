<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Single News View
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();

    if (!$news) {
        echo "<div class='alert alert-warning mt-5'>Article not found.</div>";
        require_once 'includes/footer.php';
        exit();
    }
    ?>
    <div class="row mt-4 mb-5 justify-content-center">
        <div class="col-lg-9">
            <div class="card card-elegant border-0 mb-4">
                <?php if(!empty($news['image_path']) && file_exists(__DIR__.'/'.$news['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($news['image_path']); ?>" class="card-img-top img-fluid rounded-top" alt="News Featured Image" style="max-height: 450px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body p-lg-5 p-4">
                    <span class="badge bg-primary mb-3"><i class="fas fa-calendar-alt"></i> <?php echo date('F d, Y', strtotime($news['created_at'])); ?></span>
                    <h1 class="fw-bold mb-4 text-dark"><?php echo htmlspecialchars($news['title']); ?></h1>
                    
                    <div class="article-content" style="font-size: 1.1rem; line-height: 1.8;">
                        <?php echo $news['content']; ?>
                    </div>
                </div>
            </div>
            <a href="news.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to News</a>
        </div>
    </div>
    <?php
} else {
    // News Listing
    $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
    $newsItems = $stmt->fetchAll();
    ?>
    <div class="mt-4 mb-5">
        <h2 class="section-title">Latest News & Announcements</h2>
        <div class="row mt-5">
            <?php foreach($newsItems as $news): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card card-elegant h-100 border-light shadow-sm">
                    <?php if(!empty($news['image_path']) && file_exists(__DIR__.'/'.$news['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($news['image_path']); ?>" class="card-img-top" alt="News Image" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-newspaper fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column p-4">
                        <small class="text-muted mb-2"><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($news['created_at'])); ?></small>
                        <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($news['title']); ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?php echo substr(strip_tags($news['content']), 0, 120) . '...'; ?>
                        </p>
                        <a href="news.php?id=<?php echo $news['id']; ?>" class="btn btn-primary mt-3 text-uppercase fw-semibold btn-sm w-100">Read More</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(count($newsItems) === 0): ?>
                <div class="col-12"><div class="alert alert-info">No news articles found.</div></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

require_once 'includes/footer.php';
?>
