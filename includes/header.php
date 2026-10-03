<?php
require_once __DIR__.'/db.php';

// Fetch global settings once per request
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Start session to check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check Under Construction Mode
if (($settings['under_construction'] ?? '0') === '1') {
    // If not logged in as Admin or Editor
    if (!isset($_SESSION['user_id'])) {
        header("Location: under_construction.php");
        exit();
    }
}

// Fetch Header Menu
$stmt = $pdo->prepare("SELECT label, url FROM menu_items WHERE menu_location = 'header_nav' ORDER BY order_index ASC");
$stmt->execute();
$headerNav = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name_en'] ?? 'Government Institute'); ?></title>
    <?php if(!empty($settings['favicon_path']) && file_exists(__DIR__.'/../'.$settings['favicon_path'])): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($settings['favicon_path']); ?>">
    <?php endif; ?>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <?php
    $theme = $settings['theme_color'] ?? 'blue';
    // Default Blue Theme
    $primaryColor = '#0b2b5e';
    $secondaryColor = '#1e4d94';
    $accentColor = '#f9a826';
    $btnHoverColor = '#082046';

    switch($theme) {
        case 'green':
            $primaryColor = '#145c32';
            $secondaryColor = '#228b22';
            $accentColor = '#ffc107';
            $btnHoverColor = '#0f4525';
            break;
        case 'red':
            $primaryColor = '#8b0000';
            $secondaryColor = '#b22222';
            $accentColor = '#ffc107';
            $btnHoverColor = '#6b0000';
            break;
        case 'purple':
            $primaryColor = '#4a148c';
            $secondaryColor = '#7b1fa2';
            $accentColor = '#ffb300';
            $btnHoverColor = '#380f6a';
            break;
        case 'teal':
            $primaryColor = '#004d40';
            $secondaryColor = '#00796b';
            $accentColor = '#ffab00';
            $btnHoverColor = '#00332a';
            break;
        case 'orange':
            $primaryColor = '#e65100';
            $secondaryColor = '#ef6c00';
            $accentColor = '#ffd54f';
            $btnHoverColor = '#b33e00';
            break;
        case 'black':
            $primaryColor = '#000000';
            $secondaryColor = '#212529';
            $accentColor = '#f9a826';
            $btnHoverColor = '#111111';
            break;
        case 'gray-1':
            $primaryColor = '#6c757d';
            $secondaryColor = '#868e96';
            $accentColor = '#f9a826';
            $btnHoverColor = '#495057';
            break;
        case 'gray-2':
            $primaryColor = '#495057';
            $secondaryColor = '#6c757d';
            $accentColor = '#f9a826';
            $btnHoverColor = '#343a40';
            break;
        case 'gray-3':
            $primaryColor = '#212529';
            $secondaryColor = '#343a40';
            $accentColor = '#f9a826';
            $btnHoverColor = '#000000';
            break;
        case 'maroon':
            $primaryColor = '#800000';
            $secondaryColor = '#b22222';
            $accentColor = '#f9a826';
            $btnHoverColor = '#660000';
            break;
        case 'brown':
            $primaryColor = '#5c4033';
            $secondaryColor = '#8b4513';
            $accentColor = '#f9a826';
            $btnHoverColor = '#3e2723';
            break;
        case 'dark-brown':
            $primaryColor = '#3e2723';
            $secondaryColor = '#4e342e';
            $accentColor = '#f9a826';
            $btnHoverColor = '#1b0000';
            break;
    }
    ?>
    <style>
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
            --accent-color: <?php echo $accentColor; ?>;
            --btn-hover-color: <?php echo $btnHoverColor; ?>;
        }

        /* Override Bootstrap Primary Colors globally */
        .btn-primary {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
            color: #fff !important;
        }
        .btn-primary:hover {
            background-color: var(--btn-hover-color) !important;
            border-color: var(--btn-hover-color) !important;
        }
        .btn-outline-primary {
            color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--secondary-color) !important;
            color: #fff !important;
        }
        .text-primary {
            color: var(--secondary-color) !important;
        }
        .bg-primary {
            background-color: var(--secondary-color) !important;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
        }
        .pagination .page-link {
            color: var(--secondary-color);
        }
        .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
            background-color: var(--secondary-color) !important;
        }
    </style>
</head>
<body>

    <!-- Top Header with Logos & Names in 3 Languages -->
    <header class="top-header">
        <div class="container container-fluid">
            <div class="row align-items-center">
                <div class="col-auto text-center text-md-start pe-md-3 pe-2">
                    <?php if(!empty($settings['logo_path']) && file_exists(__DIR__.'/../'.$settings['logo_path'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Institute Logo" class="institute-logo">
                    <?php else: ?>
                        <!-- Fallback Logo Placeholder -->
                        <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mx-md-0" style="width: 80px; height: 80px; font-weight: bold;">LOGO</div>
                    <?php endif; ?>
                </div>
                <div class="col institute-names mt-2 mt-md-0 notranslate" translate="no">
                    <h2 class="institute-title text-white mb-1"><?php echo htmlspecialchars($settings['site_name_si'] ?? ''); ?></h2>
                    <h2 class="institute-title text-white mb-1"><?php echo htmlspecialchars($settings['site_name_ta'] ?? ''); ?></h2>
                    <h2 class="institute-title text-white text-uppercase"><?php echo htmlspecialchars($settings['site_name_en'] ?? ''); ?></h2>
                </div>
                
                <!-- Google Translate Widget -->
                <div class="col-12 col-md-auto mt-3 mt-md-0 text-center text-md-end">
                    <div id="google_translate_element" class="bg-white rounded p-1 mb-2 d-inline-block shadow-sm"></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <?php foreach($headerNav as $nav): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo htmlspecialchars($nav['url']); ?>"><?php echo htmlspecialchars($nav['label']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Begin Page Content Container -->
    <main class="container">
