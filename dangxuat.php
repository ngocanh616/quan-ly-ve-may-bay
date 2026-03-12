<?php
/**
 * File: dangxuat.php
 * Mô tả: Xử lý đăng xuất
 */

// Bắt đầu session
session_start();

// Hủy tất cả session
session_unset();
session_destroy();

// Lưu message vào session mới
session_start();
$_SESSION['message'] = 'Đăng xuất thành công!';
$_SESSION['message_type'] = 'success';

// Chuyển về trang chủ
header('Location: /QLVeMayBay/index.php');
exit();
?>
