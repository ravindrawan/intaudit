<?php
require_once 'includes/auth.php';
requireAdmin(); // Adjusted if editors can manage, but typically Admin
require_once '../includes/db.php';

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if image exists and delete it
    $stmt = $pdo->prepare("SELECT photo_path FROM officers WHERE id = ?");
    $stmt->execute([$id]);
    $officer = $stmt->fetch();
    
    if ($officer && !empty($officer['photo_path']) && file_exists('../' . $officer['photo_path'])) {
        unlink('../' . $officer['photo_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM officers WHERE id = ?");
    if($stmt->execute([$id])) {
        $_SESSION['successMsg'] = "Officer deleted successfully.";
    } else {
        $_SESSION['errorMsg'] = "Failed to delete officer.";
    }
    header("Location: officers.php");
    exit();
}

$successMsg = '';
$errorMsg = '';
if(isset($_SESSION['successMsg'])) {
    $successMsg = $_SESSION['successMsg'];
    unset($_SESSION['successMsg']);
}
if(isset($_SESSION['errorMsg'])) {
    $errorMsg = $_SESSION['errorMsg'];
    unset($_SESSION['errorMsg']);
}

// Fetch officers
$stmt = $pdo->query("SELECT * FROM officers ORDER BY section_name ASC, order_index ASC");
$officers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Officers - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Manage Officers & Staff</h2>
            <a href="officers_edit.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus"></i> Add New Officer</a>
        </div>
        
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Officers</li>
            </ol>
        </nav>

        <?php if ($successMsg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($successMsg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($errorMsg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 border-top border-primary border-3 mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Photo</th>
                                <th class="py-3">Name</th>
                                <th class="py-3">Designation</th>
                                <th class="py-3">Section</th>
                                <th class="py-3 text-center">Order Index</th>
                                <th class="px-4 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($officers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No officers found. Start by adding one.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach($officers as $off): ?>
                            <tr>
                                <td class="px-4">
                                    <?php if(!empty($off['photo_path']) && file_exists('../' . $off['photo_path'])): ?>
                                        <img src="../<?php echo htmlspecialchars($off['photo_path']); ?>" alt="Photo" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #ddd;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($off['name']); ?></td>
                                <td><?php echo htmlspecialchars($off['designation']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($off['section_name']); ?></span></td>
                                <td class="text-center"><?php echo (int)$off['order_index']; ?></td>
                                <td class="px-4 text-end">
                                    <a href="officers_edit.php?id=<?php echo $off['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="officers.php?delete=<?php echo $off['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this officer?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
