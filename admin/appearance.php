<?php
require_once 'includes/auth.php';
requireAdmin(); // Only admin can access this page
require_once '../includes/db.php';

if (!hasMenuPermission('appearance')) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    // Update settings
    $settingsToUpdate = [
        'theme_color' => $_POST['theme_color'] ?? 'blue'
    ];

    foreach ($settingsToUpdate as $key => $value) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmtCheck->execute([$key]);
        if ($stmtCheck->fetchColumn() > 0) {
            $stmtUpdate = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmtUpdate->execute([$value, $key]);
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmtInsert->execute([$key, $value]);
        }
    }

    if (!$error) {
        $success = 'Appearance settings updated successfully.';
    }
}

// Fetch current settings
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
$settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appearance Settings - Gov Admin</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .color-swatch-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .color-swatch {
            position: relative;
            cursor: pointer;
            text-align: center;
        }
        .color-swatch input[type="radio"] {
            position: absolute;
            left: -9999px;
            opacity: 0;
        }
        .color-swatch-view {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: inline-block;
            border: 4px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
        }
        .color-swatch input[type="radio"]:checked + .color-swatch-view {
            border-color: #343a40;
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .color-swatch input[type="radio"]:checked + .color-swatch-view::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #fff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        .swatch-label {
            display: block;
            margin-top: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #555;
            transition: color 0.3s;
        }
        .color-swatch input[type="radio"]:checked ~ .swatch-label {
            color: #212529;
        }
        /* Gradient Colors for a premium look */
        .swatch-blue { background: linear-gradient(135deg, #0d6efd 10%, #0dcaf0 100%); }
        .swatch-green { background: linear-gradient(135deg, #198754 10%, #20c997 100%); }
        .swatch-red { background: linear-gradient(135deg, #dc3545 10%, #ff7675 100%); }
        .swatch-purple { background: linear-gradient(135deg, #6f42c1 10%, #d63384 100%); }
        .swatch-teal { background: linear-gradient(135deg, #20c997 10%, #0dcaf0 100%); }
        .swatch-orange { background: linear-gradient(135deg, #fd7e14 10%, #ffc107 100%); }
        .swatch-black { background: linear-gradient(135deg, #000000 10%, #434343 100%); }
        .swatch-gray-1 { background: linear-gradient(135deg, #6c757d 10%, #adb5bd 100%); }
        .swatch-gray-2 { background: linear-gradient(135deg, #495057 10%, #6c757d 100%); }
        .swatch-gray-3 { background: linear-gradient(135deg, #343a40 10%, #495057 100%); }
        .swatch-maroon { background: linear-gradient(135deg, #800000 10%, #b22222 100%); }
        .swatch-brown { background: linear-gradient(135deg, #8B4513 10%, #A0522D 100%); }
        .swatch-dark-brown { background: linear-gradient(135deg, #5C4033 10%, #8B4513 100%); }
    </style>
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4">
        <h2>Appearance Settings</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Appearance Settings</li>
            </ol>
        </nav>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="appearance.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <h5 class="mb-3 text-primary"><i class="fas fa-palette"></i> Appearance Settings</h5>
                    <div class="card bg-light border-0 mb-4 rounded-3 p-4">
                        <label class="form-label fw-bold mb-1 fs-5">Site Color Theme</label>
                        <p class="text-muted mb-4 small">Select a primary color palette to be applied across the front-end components.</p>
                        
                        <div class="color-swatch-container">
                            <!-- Blue -->
                            <label class="color-swatch" title="Blue (Default)">
                                <input type="radio" name="theme_color" value="blue" <?php echo (!isset($settingsData['theme_color']) || $settingsData['theme_color'] == 'blue') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-blue"></div>
                                <span class="swatch-label">Blue</span>
                            </label>
                            
                            <!-- Green -->
                            <label class="color-swatch" title="Green">
                                <input type="radio" name="theme_color" value="green" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'green') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-green"></div>
                                <span class="swatch-label">Green</span>
                            </label>

                            <!-- Red -->
                            <label class="color-swatch" title="Red">
                                <input type="radio" name="theme_color" value="red" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'red') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-red"></div>
                                <span class="swatch-label">Red</span>
                            </label>

                            <!-- Purple -->
                            <label class="color-swatch" title="Purple">
                                <input type="radio" name="theme_color" value="purple" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'purple') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-purple"></div>
                                <span class="swatch-label">Purple</span>
                            </label>

                            <!-- Teal -->
                            <label class="color-swatch" title="Teal">
                                <input type="radio" name="theme_color" value="teal" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'teal') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-teal"></div>
                                <span class="swatch-label">Teal</span>
                            </label>

                            <!-- Orange -->
                            <label class="color-swatch" title="Orange">
                                <input type="radio" name="theme_color" value="orange" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'orange') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-orange"></div>
                                <span class="swatch-label">Orange</span>
                            </label>

                            <!-- Black -->
                            <label class="color-swatch" title="Black">
                                <input type="radio" name="theme_color" value="black" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'black') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-black"></div>
                                <span class="swatch-label">Black</span>
                            </label>

                            <!-- Gray 1 -->
                            <label class="color-swatch" title="Light Gray">
                                <input type="radio" name="theme_color" value="gray-1" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'gray-1') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-gray-1"></div>
                                <span class="swatch-label">Light Gray</span>
                            </label>

                            <!-- Gray 2 -->
                            <label class="color-swatch" title="Neutral Gray">
                                <input type="radio" name="theme_color" value="gray-2" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'gray-2') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-gray-2"></div>
                                <span class="swatch-label">Neutral Gray</span>
                            </label>

                            <!-- Gray 3 -->
                            <label class="color-swatch" title="Dark Gray">
                                <input type="radio" name="theme_color" value="gray-3" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'gray-3') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-gray-3"></div>
                                <span class="swatch-label">Dark Gray</span>
                            </label>

                            <!-- Maroon -->
                            <label class="color-swatch" title="Maroon">
                                <input type="radio" name="theme_color" value="maroon" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'maroon') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-maroon"></div>
                                <span class="swatch-label">Maroon</span>
                            </label>

                            <!-- Brown -->
                            <label class="color-swatch" title="Brown">
                                <input type="radio" name="theme_color" value="brown" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'brown') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-brown"></div>
                                <span class="swatch-label">Brown</span>
                            </label>
                            
                            <!-- Dark Brown -->
                            <label class="color-swatch" title="Dark Brown">
                                <input type="radio" name="theme_color" value="dark-brown" <?php echo (isset($settingsData['theme_color']) && $settingsData['theme_color'] == 'dark-brown') ? 'checked' : ''; ?>>
                                <div class="color-swatch-view swatch-dark-brown"></div>
                                <span class="swatch-label">Dark Brown</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm"><i class="fas fa-save me-2"></i> Save Appearance</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
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
