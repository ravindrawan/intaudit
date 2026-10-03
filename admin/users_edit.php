<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

$error = '';
$user = [
    'username' => '',
    'role' => 'editor'
];
$isEdit = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("User not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $username = trim($_POST['username']);
    $role = $_POST['role'] ?? 'editor';
    $password = $_POST['password'] ?? '';
    
    if (empty($username)) {
        $error = "Username is required.";
    } elseif ($isEdit && $user['username'] === 'admin' && $role !== 'super_admin' && $role !== 'admin') {
        $error = "The main admin role cannot be downgraded.";
    } elseif ($role === 'super_admin' && !isSuperAdmin()) {
        $error = "Only a Super Admin can grant Super Admin privileges.";
    } elseif ($isEdit && $user['role'] === 'super_admin' && !isSuperAdmin()) {
        $error = "Only a Super Admin can modify another Super Admin account.";
    } else {
        // Check uniqueness of username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $isEdit ? $_GET['id'] : 0]);
        if($stmt->fetch()) {
            $error = "Username already exists. Please choose another.";
        } else {
            if ($isEdit) {
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET username=?, role=?, password=? WHERE id=?");
                    $stmt->execute([$username, $role, $hashed, $_GET['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username=?, role=? WHERE id=?");
                    $stmt->execute([$username, $role, $_GET['id']]);
                }
                
                // If the user changed their own username, update session
                if ($_SESSION['user_id'] == $_GET['id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                }
                
                $_SESSION['successMsg'] = "User updated successfully.";
            } else {
                if (empty($password)) {
                    $error = "Password is required for a new user.";
                } else {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $hashed, $role]);
                    $_SESSION['successMsg'] = "User created successfully.";
                    header("Location: users.php");
                    exit();
                }
            }
            if(empty($error)) {
                header("Location: users.php");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit User' : 'Add User'; ?> - Gov Admin</title>
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
        <div class="card shadow-sm border-0 border-top border-primary border-3 w-50 mx-auto">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0 fw-bold"><i class="fas <?php echo $isEdit ? 'fa-user-edit' : 'fa-user-plus'; ?> text-primary me-2"></i> <?php echo $isEdit ? 'Edit User' : 'Add New User'; ?></h4>
                <a href="users.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
            </div>
            <div class="card-body p-4">
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Password <?php if(!$isEdit) echo '<span class="text-danger">*</span>'; ?></label>
                        <input type="password" class="form-control" name="password" <?php if(!$isEdit) echo 'required'; ?>>
                        <?php if($isEdit): ?>
                            <small class="text-muted d-block mt-1">Leave blank to keep the current password.</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">User Role <span class="text-danger">*</span></label>
                        <?php if ($isEdit && $user['username'] === 'admin' && !isSuperAdmin()): ?>
                            <!-- Legacy protection: If normal admin tries to view main admin -->
                            <select class="form-select" name="role" disabled>
                                <option value="admin" selected>Admin</option>
                            </select>
                            <input type="hidden" name="role" value="admin">
                            <small class="text-muted d-block mt-1">The main admin role cannot be downgraded.</small>
                        <?php elseif ($isEdit && $user['role'] === 'super_admin' && !isSuperAdmin()): ?>
                             <!-- Normal admin viewing a super_admin -->
                            <select class="form-select" name="role" disabled>
                                <option value="super_admin" selected>Super Admin</option>
                            </select>
                            <input type="hidden" name="role" value="super_admin">
                            <small class="text-danger d-block mt-1">You cannot modify Super Admin privileges.</small>
                        <?php else: ?>
                            <select class="form-select" name="role" required>
                                <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <?php if (isSuperAdmin()): ?>
                                <option value="super_admin" <?php echo $user['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                <?php endif; ?>
                            </select>
                            <?php if (!isSuperAdmin()): ?>
                                <small class="text-muted d-block mt-1">Admins have full access. Editors may be restricted.</small>
                            <?php else: ?>
                                <small class="text-muted d-block mt-1">Super Admins have absolute control, including menu delegation.</small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    <div class="text-end">
                        <a href="users.php" class="btn btn-light shadow-sm me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-2"></i> <?php echo $isEdit ? 'Update User' : 'Save User'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
