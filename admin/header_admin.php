<?php
/*
 * File: admin/header_admin.php
 * Mô tả: Header/Sidebar của Admin Dashboard
 */

if (!is_logged_in() || !is_admin()) {
    redirect('../dangnhap.php');
}
$pending_count = query_single("SELECT COUNT(*) as count FROM DatVe WHERE TrangThai='choxacnhan'")['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin' ?> - Sky Airline</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-wrapper" id="adminWrapper">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-brand">
                <div class="brand-icon">✈️</div>
                <div class="brand-text">
                    <h2>SKY AIRLINE</h2>
                    <p>Admin Panel</p>
                </div>
            </a>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Tổng quan</div>
                <a href="index.php" class="menu-item <?= ($current_page ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i>Dashboard
                </a>
                <a href="thongke.php" class="menu-item <?= ($current_page ?? '') === 'thongke' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>Thống kê
                </a>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Quản lý</div>
                <a href="qly_chuyenbay.php" class="menu-item <?= ($current_page ?? '') === 'chuyenbay' ? 'active' : '' ?>">
                    <i class="fas fa-plane-departure"></i>Chuyến bay
                </a>
                <a href="qly_datve.php" class="menu-item <?= ($current_page ?? '') === 'datve' ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt"></i>Đặt vé
                    <?php if ($pending_count > 0): ?>
                    <span class="menu-badge"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="qly_nguoidung.php" class="menu-item <?= ($current_page ?? '') === 'nguoidung' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>Người dùng
                </a>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Cài đặt</div>
                <a href="caidat.php" class="menu-item <?= ($current_page ?? '') === 'caidat' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>Cấu hình
                </a>
                <a href="../index.php" class="menu-item">
                    <i class="fas fa-home"></i>Về trang chủ
                </a>
                <a href="../dangxuat.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>Đăng xuất
                </a>
            </div>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <!-- Toggle Sidebar Button -->
                <button class="toggle-sidebar-btn" id="toggleSidebarBtn" title="Ẩn/Hiện sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <h1><?= $page_title ?? 'Admin' ?></h1>
                <p><?= $page_subtitle ?? 'Quản lý hệ thống' ?></p>
            </div>
            <div class="header-right">
                <?php if (($show_search ?? true) && !($hide_search ?? false)): ?>
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Tìm kiếm..." id="searchInput">
                </div>
                <?php endif; ?>
                <div class="header-user">
                    <div class="user-avatar">
                        <?= strtoupper(substr(get_user_info()['TenDangNhap'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <p><?= htmlspecialchars(get_user_info()['TenDangNhap'] ?? 'Admin') ?></p>
                        <span>Administrator</span>
                    </div>
                </div>
            </div>
        </header>
        <div class="dashboard-content">