<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Prevent deleting the main admin account or currently logged-in user
    if ($id != $_SESSION['user_id']) {
        if (!isSuperAdmin()) {
            // Normal admins cannot delete super_admins or the main admin
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND username != 'admin' AND role != 'super_admin'");
        } else {
             // Super Admins can delete anyone except 'admin'
             $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND username != 'admin'");
        }
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['successMsg'] = "User deleted successfully.";
        } else {
            $_SESSION['errorMsg'] = "Could not delete user. Permission denied or user not found.";
        }
    } else {
        $_SESSION['errorMsg'] = "You cannot delete your own account.";
    }
    
    header("Location: users.php");
    exit();
}

$stmt = $pdo->query("SELECT id, username, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users text-primary me-2"></i> Manage System Users</h2>
            <a href="users_edit.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus"></i> Add New User</a>
        </div>
        
        <?php if(isset($_SESSION['successMsg'])): ?>
            <div class="alert alert-success d-flex align-items-center"><i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['successMsg']; unset($_SESSION['successMsg']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['errorMsg'])): ?>
            <div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $_SESSION['errorMsg']; unset($_SESSION['errorMsg']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?php echo $user['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <?php if($user['role'] === 'super_admin'): ?>
                                    <span class="badge bg-dark rounded-pill px-3 py-2"><i class="fas fa-crown text-warning me-1"></i> Super Admin</span>
                                <?php elseif($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-2"><i class="fas fa-shield-alt me-1"></i> Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary rounded-pill px-3 py-2"><i class="fas fa-user-edit me-1"></i> Editor</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="users_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
                                <?php if($user['id'] != $_SESSION['user_id'] && $user['username'] !== 'admin' && (isSuperAdmin() || $user['role'] !== 'super_admin')): ?>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash"></i></a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary ms-1" disabled title="Cannot delete this account"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($users) === 0): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No users found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
