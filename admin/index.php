<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

// Fetch quick stats
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pageCount = $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
$newsCount = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$downloadCount = $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn();

// Fetch recent unread feedbacks
$stmtRecentFb = $pdo->query("SELECT * FROM feedback ORDER BY is_read ASC, created_at DESC LIMIT 5");
$recentFeedbacks = $stmtRecentFb->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gov Admin</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">
                <h2>Dashboard</h2>
                <hr>
                
                <div class="row mt-4">
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h3><?php echo $pageCount; ?></h3>
                                <p class="mb-0">Total Pages</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h3><?php echo $newsCount; ?></h3>
                                <p class="mb-0">News Articles</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <h3><?php echo $downloadCount; ?></h3>
                                <p class="mb-0">Downloads Available</p>
                            </div>
                        </div>
                    </div>
                    <?php if (isAdmin()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body">
                                <h3><?php echo $userCount; ?></h3>
                                <p class="mb-0">Registered Users</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Feedback Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="mb-0 fw-bold"><i class="fas fa-comments text-primary me-2"></i> Recent Customer Feedback</h5>
                                <a href="feedback.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recentFeedbacks) > 0): ?>
                                            <?php foreach($recentFeedbacks as $fb): ?>
                                            <tr class="<?php echo $fb['is_read'] ? '' : 'table-warning fw-bold'; ?>">
                                                <td>
                                                    <?php if(!$fb['is_read']): ?>
                                                        <span class="badge bg-danger me-1">New</span>
                                                    <?php endif; ?>
                                                    <?php echo date('M d, g:i a', strtotime($fb['created_at'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($fb['name']); ?></td>
                                                <td><?php echo htmlspecialchars($fb['title']); ?></td>
                                                <td class="text-end">
                                                    <a href="feedback_print.php?id=<?php echo $fb['id']; ?>" class="btn btn-sm btn-info text-white" title="View"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted">No recent feedback.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
