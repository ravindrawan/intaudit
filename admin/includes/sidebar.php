<style>
    .sidebar { min-height: 100vh; background-color: #343a40; }
    .sidebar a { color: #fff; text-decoration: none; padding: 10px 15px; display: block; border-bottom: 1px solid #454d55;}
    .sidebar a:hover { background-color: #495057; }
    .sidebar .active { background-color: #0d6efd; border-bottom: none;}
</style>
<nav class="col-md-3 col-lg-2 d-md-block sidebar py-3">
    <div class="text-center text-white mb-4">
        <h5>Gov Admin</h5>
        <small>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></small>
        <div class="mt-3">
            <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-external-link-alt"></i> Visit Website</a>
        </div>
    </div>
    <div class="nav flex-column">
        <?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
        <a href="index.php" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home me-2"></i> Dashboard</a>
        
        <?php if (hasMenuPermission('pages')): ?>
        <a href="pages.php" class="<?php echo in_array($currentPage, ['pages.php', 'pages_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-file-alt me-2"></i> Manage Pages</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('about_settings')): ?>
        <a href="about_settings.php" class="<?php echo $currentPage == 'about_settings.php' ? 'active' : ''; ?>"><i class="fas fa-building me-2"></i> Manage About Us</a>
        <a href="officers.php" class="<?php echo in_array($currentPage, ['officers.php', 'officers_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-id-badge me-2"></i> Manage Officers</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('services')): ?>
        <a href="services.php" class="<?php echo in_array($currentPage, ['services.php', 'services_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-clipboard-list me-2"></i> Manage Services</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('contacts')): ?>
        <a href="contacts.php" class="<?php echo in_array($currentPage, ['contacts.php', 'contacts_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-address-book me-2"></i> Manage Contacts</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('feedback')): ?>
        <a href="feedback.php" class="<?php echo in_array($currentPage, ['feedback.php', 'feedback_print.php']) ? 'active' : ''; ?>"><i class="fas fa-comments me-2"></i> Manage Feedback</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('news')): ?>
        <a href="news.php" class="<?php echo in_array($currentPage, ['news.php', 'news_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-newspaper me-2"></i> Manage News</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('downloads')): ?>
        <a href="downloads.php" class="<?php echo in_array($currentPage, ['downloads.php', 'downloads_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-download me-2"></i> Manage Downloads</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('download_tags')): ?>
        <a href="download_tags.php" class="<?php echo $currentPage == 'download_tags.php' ? 'active' : ''; ?>"><i class="fas fa-tags me-2"></i> File Categories</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('menu')): ?>
        <a href="menu.php" class="<?php echo in_array($currentPage, ['menu.php', 'menu_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-bars me-2"></i> Manage Menus</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('sliders')): ?>
        <a href="sliders.php" class="<?php echo in_array($currentPage, ['sliders.php', 'sliders_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-images me-2"></i> Manage Slider</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('settings')): ?>
        <a href="settings.php" class="<?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-cog me-2"></i> General Settings</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('appearance')): ?>
        <a href="appearance.php" class="<?php echo $currentPage == 'appearance.php' ? 'active' : ''; ?>"><i class="fas fa-palette me-2"></i> Appearance Settings</a>
        <?php endif; ?>
        
        <?php if (hasMenuPermission('users')): ?>
        <a href="users.php" class="<?php echo in_array($currentPage, ['users.php', 'users_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-users me-2"></i> Manage Users</a>
        <?php endif; ?>
        
        <?php if (isSuperAdmin()): ?>
        <a href="role_permissions.php" class="<?php echo $currentPage == 'role_permissions.php' ? 'active' : ''; ?>"><i class="fas fa-key me-2 text-warning"></i> Role Permissions</a>
        <?php endif; ?>
        
        <a href="logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
</nav>
