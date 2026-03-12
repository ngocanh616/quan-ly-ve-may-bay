<?php

/**
 * File: header.php
 * Mô tả: Header chuyên nghiệp cho khách hàng
 */

require_once __DIR__ . '/config.php';

$pageTitle = $pageTitle ?? 'Quản Lý Vé Máy Bay';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Custom CSS -->
    <link href="/QLVeMayBay/assets/css/style.css" rel="stylesheet">

    <!-- Auth CSS (chỉ load khi cần) -->
    <?php if (in_array(basename($_SERVER['PHP_SELF']), ['dangnhap.php', 'dangky.php', 'quenmatkhau.php'])): ?>
        <link href="/QLVeMayBay/assets/css/auth.css" rel="stylesheet">
    <?php endif; ?>

    <!-- Khách Hàng CSS -->
    <?php if (isset($loadKhachHangCSS) && $loadKhachHangCSS): ?>
        <link href="/QLVeMayBay/assets/css/khachhang.css" rel="stylesheet">
    <?php endif; ?>

</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-aviation">
        <div class="container px-4">
            <a class="navbar-brand" href="/QLVeMayBay/index.php">
                <i class="bi bi-airplane-engines-fill"></i>
                <div class="brand-name">
                    <span class="brand-main">SKY AIRLINE</span>
                    <span class="brand-sub">TICKET SYSTEM</span>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <!-- Menu Admin -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/admin/index.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == 'ql_chuyenbay.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/admin/qly_chuyenbay.php">
                                    <i class="bi bi-airplane-engines"></i> Chuyến Bay
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == ' ql_datve.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/admin/qly_datve.php">
                                    <i class="bi bi-ticket-perforated"></i> Đặt Vé
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == 'ql_nguoidung.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/admin/qly_nguoidung.php">
                                    <i class="bi bi-people"></i> Người Dùng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == 'thongke.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/admin/thongke.php">
                                    <i class="bi bi-bar-chart-line"></i> Thống Kê
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- Menu Khách hàng -->
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == 'chuyenbay.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/khachhang/chuyenbay.php">
                                    <i class="bi bi-airplane-engines"></i> Chuyến Bay
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == 'timkiem.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/khachhang/timkiem.php">
                                    <i class="bi bi-search"></i> Tìm Chuyến Bay
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage == 'lichsu.php' ? 'active' : ''; ?>"
                                    href="/QLVeMayBay/khachhang/lichsu.php">
                                    <i class="bi bi-clock-history"></i> Lịch Sử Đặt Vé
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="user-badge">
                                    <?php echo strtoupper(mb_substr($_SESSION['HoTen'], 0, 1, 'UTF-8')); ?>
                                </span>
                                <span class="d-none d-lg-inline"><?php echo $_SESSION['HoTen']; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/QLVeMayBay/khachhang/thongtin.php">
                                        <i class="bi bi-person-circle"></i> Hồ Sơ Của Tôi
                                    </a>
                                <li>
                                    <a class="dropdown-item" href="/QLVeMayBay/dangxuat.php">
                                        <i class="bi bi-box-arrow-right"></i> Đăng Xuất
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/QLVeMayBay/dangnhap.php">
                                <i class="bi bi-box-arrow-in-right"></i> Đăng Nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/QLVeMayBay/dangky.php">
                                <i class="bi bi-person-plus"></i> Đăng Ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Message -->
    <?php
    $msg = get_message();
    if ($msg):
    ?>
        <div class="container mt-4">
            <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $msg['type'] == 'success' ? 'check-circle' : ($msg['type'] == 'danger' ? 'exclamation-triangle' : 'info-circle'); ?>-fill me-2"></i>
                <strong><?php echo $msg['message']; ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container-fluid px-4">