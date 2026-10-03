<?php
require_once 'includes/db.php';

// Fetch global settings to display the correct name
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name_en', 'logo_path', 'under_construction')");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// If the site is NOT under construction, redirect back to index
if (($settings['under_construction'] ?? '0') !== '1') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Construction - <?php echo htmlspecialchars($settings['site_name_en'] ?? 'Institute'); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0b2b5e 0%, #1e4d94 100%);
            color: white;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .construction-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px 30px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
        }
        .loader {
            margin: 0 auto 30px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid #f9a826;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="construction-box mx-auto">
                    <?php if(!empty($settings['logo_path']) && file_exists(__DIR__.'/'.$settings['logo_path'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Logo" class="mb-4" style="max-height: 100px;">
                    <?php endif; ?>
                    
                    <div class="loader"></div>
                    
                    <h1 class="fw-bold mb-3 text-warning">We Are Upgrading!</h1>
                    <h4 class="mb-4"><?php echo htmlspecialchars($settings['site_name_en'] ?? 'Our Institute'); ?></h4>
                    <p class="fs-5 text-light" style="opacity: 0.9;">
                        Our website is currently undergoing scheduled maintenance to improve our services and your experience. 
                        We will be back online shortly.
                    </p>
                    
                    <div class="mt-5">
                        <a href="admin/login.php" class="btn btn-outline-light rounded-pill px-4"><i class="fas fa-lock me-2"></i> Staff Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
