<?php
require_once '../../config.php';

// Kiểm tra quyền admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing user ID']);
    exit;
}

$id = (int)$_GET['id'];

// Lấy thông tin người dùng
$user = fetch_one("SELECT * FROM NguoiDung WHERE MaNguoiDung = $id");

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

// Trả về JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'user' => [
        'MaNguoiDung' => $user['MaNguoiDung'],
        'TenDangNhap' => htmlspecialchars($user['TenDangNhap']),
        'HoTen' => htmlspecialchars($user['HoTen'] ?? 'N/A'),
        'Email' => htmlspecialchars($user['Email']),
        'SoDienThoai' => htmlspecialchars($user['SoDienThoai'] ?? 'N/A'),
        'DiaChi' => htmlspecialchars($user['DiaChi'] ?? 'N/A'),
        'NgayTao' => $user['NgayTao'],
        'TrangThai' => $user['TrangThai']
    ]
]);
?>
