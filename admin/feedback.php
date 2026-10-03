<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

if (!hasMenuPermission('feedback')) {
    header("Location: index.php");
    exit();
}

// Pagination settings
$limit = 10; // Number of entries to show per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Delete feedback if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: feedback.php?msg=deleted");
    exit();
}

// Get total records for pagination
$totalRecords = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Fetch feedbacks with limit and offset
$stmt = $pdo->prepare("SELECT * FROM feedback ORDER BY created_at DESC LIMIT :start, :limit");
// Bind values as integer for LIMIT clause
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$feedbacks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Feedback - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Customer Feedback</h2>
                </div>
                
                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success">Feedback deleted successfully.</div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email / Phone</th>
                                    <th>Subject</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($feedbacks as $fb): ?>
                                <tr class="<?php echo $fb['is_read'] ? '' : 'table-warning fw-bold'; ?>">
                                    <td>
                                        <?php if(!$fb['is_read']): ?>
                                            <span class="badge bg-danger me-2">New</span>
                                        <?php endif; ?>
                                        <?php echo date('Y-m-d H:i', strtotime($fb['created_at'])); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($fb['name']); ?></td>
                                    <td>
                                        <div><a href="mailto:<?php echo htmlspecialchars($fb['email']); ?>"><?php echo htmlspecialchars($fb['email']); ?></a></div>
                                        <?php if(!empty($fb['phone'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($fb['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($fb['title']); ?></td>
                                    <td class="text-end">
                                        <a href="feedback_print.php?id=<?php echo $fb['id']; ?>" class="btn btn-sm btn-info text-white" title="View & Print"><i class="fas fa-print"></i></a>
                                        <?php if(isAdmin()): ?>
                                        <a href="feedback.php?delete=<?php echo $fb['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this feedback?');" title="Delete"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($feedbacks) === 0): ?>
                                <tr><td colspan="5" class="text-center py-4">No feedback received yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>

            </main>
        </div>
    </div>
</body>
</html>
