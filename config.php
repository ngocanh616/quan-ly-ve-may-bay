<?php
/*
 * File: config.php
 * Mô tả: Cấu hình database và các hàm helper
 */

// Bật session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// KẾT NỐI DATABASE
// ============================================

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "QLVeMayBay";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Lỗi kết nối: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

// ============================================
// CÁC HÀM HELPER CŨ (GIỮ NGUYÊN)
// ============================================

/**
 * Thực thi câu query
 */
function query($sql) {
    global $conn;
    return $conn->query($sql);
}

/**
 * Lấy một dòng dữ liệu
 */
function fetch_one($sql) {
    global $conn;
    $result = $conn->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

/**
 * Lấy nhiều dòng dữ liệu
 */
function fetch_all($sql) {
    global $conn;
    $result = $conn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

/**
 * Escape string để tránh SQL injection
 */
function escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

/**
 * Lấy ID vừa insert
 */
function get_last_insert_id() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Kiểm tra đăng nhập
 */
function is_logged_in() {
    return isset($_SESSION['MaNguoiDung']);
}

/**
 * Kiểm tra quyền admin
 */
function is_admin() {
    return isset($_SESSION['VaiTro']) && $_SESSION['VaiTro'] == 'admin';
}

/**
 * Chuyển hướng
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Hiển thị thông báo (dùng cho khách hàng - cách cũ)
 */
function show_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Set flash message (dùng cho admin - cách mới)
 * Alias của show_message() để tương thích với code admin
 */
function set_message($type, $message) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Lấy và xóa thông báo (tương thích cả 2 cách)
 */
function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Format số tiền VND
 */
function format_money($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

/**
 * Format ngày giờ
 */
function format_datetime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Format ngày
 */
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate số điện thoại (10 số)
 */
function is_valid_phone($phone) {
    return preg_match('/^0[0-9]{9}$/', $phone);
}

// ============================================
// HÀM MỚI CHO ADMIN DASHBOARD (THÊM MỚI)
// ============================================

/**
 * Thực thi query và trả về 1 dòng duy nhất
 */
function query_single($sql, $params = []) {
    global $conn;
    
    // Nếu không có params, dùng cách cũ
    if (empty($params)) {
        $result = $conn->query($sql);
        if (!$result) {
            error_log("Query error: " . $conn->error);
            return null;
        }
        return $result->fetch_assoc();
    }
    
    // Nếu có params, dùng prepared statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare error: " . $conn->error);
        return null;
    }
    
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    return $data;
}

/**
 * Lấy thông tin user từ session
 */
function get_user_info() {
    if (!is_logged_in()) {
        return null;
    }
    
    global $conn;
    $user_id = $_SESSION['MaNguoiDung'];
    
    $sql = "SELECT * FROM NguoiDung WHERE MaNguoiDung = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

/**
 * Thực thi INSERT/UPDATE/DELETE với prepared statement
 */
function execute($sql, $params = []) {
    global $conn;
    
    if (empty($params)) {
        return $conn->query($sql);
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare error: " . $conn->error);
        return false;
    }
    
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

?>
