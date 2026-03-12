<?php
require_once '../config.php';

$page_title = "Cấu hình Hệ thống";
$page_subtitle = "Thông tin & cài đặt chung";
$current_page = 'caidat';

require_once 'header_admin.php';
?>

<!-- System Information -->
<div class="info-grid">
    <div class="info-card">
        <h4><i class="fas fa-cube"></i> Server</h4>
        <p><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></p>
    </div>
    
    <div class="info-card">
        <h4><i class="fas fa-code"></i> PHP Version</h4>
        <p><?= PHP_VERSION ?></p>
    </div>
    
    <div class="info-card">
        <h4><i class="fas fa-database"></i> MySQL</h4>
        <p><?= mysqli_get_server_info($conn) ?></p>
    </div>
    
    <div class="info-card">
        <h4><i class="fas fa-calendar"></i> Server Time</h4>
        <p><?= date('d/m/Y H:i:s') ?></p>
    </div>
</div>

<!-- Application Settings -->
<div class="settings-section">
    <h3><i class="fas fa-sliders-h"></i> Cài đặt ứng dụng</h3>
    
    <div class="settings-table">
        <table>
            <tr>
                <td><strong>Tên ứng dụng</strong></td>
                <td>Sky Airline Panel</td>
            </tr>
            <tr>
                <td><strong>Phiên bản</strong></td>
                <td>1.0.0</td>
            </tr>
            <tr>
                <td><strong>Email hỗ trợ</strong></td>
                <td>support@skyairline.vn</td>
            </tr>
            <tr>
                <td><strong>Hotline</strong></td>
                <td>1900 1508</td>
            </tr>
            <tr>
                <td><strong>Địa chỉ</strong></td>
                <td>Hà Nội, Việt Nam</td>
            </tr>
            <tr>
                <td><strong>Múi giờ</strong></td>
                <td>Asia/Ho_Chi_Minh (UTC+7)</td>
            </tr>
            <tr>
                <td><strong>Loại tiền</strong></td>
                <td>VND (₫)</td>
            </tr>
        </table>
    </div>
</div>

<!-- System Statistics -->
<div class="settings-section">
    <h3><i class="fas fa-chart-bar"></i> Thống kê hệ thống</h3>
    
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-icon"><i class="fas fa-database"></i></div>
            <div class="stat-info">
                <div class="stat-label">Dung lượng DB</div>
                <div class="stat-value">
                    <?php
                    $result = query_single("
                        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size 
                        FROM information_schema.tables 
                        WHERE table_schema = DATABASE()
                    ");
                    echo ($result['size'] ?? 0) . ' MB';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon"><i class="fas fa-table"></i></div>
            <div class="stat-info">
                <div class="stat-label">Tổng bảng</div>
                <div class="stat-value">
                    <?php
                    $result = query_single("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
                    echo $result['count'] ?? 0;
                    ?>
                </div>
            </div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon"><i class="fas fa-server"></i></div>
            <div class="stat-info">
                <div class="stat-label">Uptime Server</div>
                <div class="stat-value">
                    <?php
                    $result = query_single("SHOW STATUS WHERE variable_name = 'Uptime'");
                    $uptime = $result['Value'] ?? 0;
                    $days = floor($uptime / 86400);
                    $hours = floor(($uptime % 86400) / 3600);
                    echo $days . 'd ' . $hours . 'h';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon"><i class="fas fa-link"></i></div>
            <div class="stat-info">
                <div class="stat-label">Connections</div>
                <div class="stat-value">
                    <?php
                    $result = query_single("SHOW STATUS WHERE variable_name = 'Threads_connected'");
                    echo $result['Value'] ?? 0;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Environment Info -->
<div class="settings-section">
    <h3><i class="fas fa-info-circle"></i> Thông tin môi trường</h3>
    
    <div class="settings-table">
        <table>
            <tr>
                <td><strong>OS</strong></td>
                <td><?= php_uname() ?></td>
            </tr>
            <tr>
                <td><strong>Document Root</strong></td>
                <td><?= $_SERVER['DOCUMENT_ROOT'] ?></td>
            </tr>
            <tr>
                <td><strong>Server IP</strong></td>
                <td><?= $_SERVER['SERVER_ADDR'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td><strong>Max Upload Size</strong></td>
                <td><?= ini_get('upload_max_filesize') ?></td>
            </tr>
            <tr>
                <td><strong>Max Post Size</strong></td>
                <td><?= ini_get('post_max_size') ?></td>
            </tr>
            <tr>
                <td><strong>Memory Limit</strong></td>
                <td><?= ini_get('memory_limit') ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Installed Extensions -->
<div class="settings-section">
    <h3><i class="fas fa-plug"></i> PHP Extensions</h3>
    
    <div class="extensions-list">
        <?php
        $extensions = [
            'mysqli' => 'Database',
            'curl' => 'HTTP Requests',
            'json' => 'JSON Processing',
            'gd' => 'Image Processing',
            'openssl' => 'SSL/TLS',
            'mbstring' => 'String Functions'
        ];
        
        foreach ($extensions as $ext => $name) {
            $loaded = extension_loaded($ext) ? '✓' : '✗';
            $color = extension_loaded($ext) ? '#10B981' : '#EF4444';
            echo "<div class='ext-item'><span style='color: $color; font-weight: bold;'>$loaded</span> $name ($ext)</div>";
        }
        ?>
    </div>
</div>

<style>
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.info-card {
    background: linear-gradient(135deg, #0066CC 0%, #004C99 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.info-card h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}

.info-card p {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.settings-section {
    background: white;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.settings-section h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
    color: #1E293B;
    display: flex;
    align-items: center;
    gap: 8px;
}

.settings-table table {
    width: 100%;
    border-collapse: collapse;
}

.settings-table tr {
    border-bottom: 1px solid #E2E8F0;
}

.settings-table tr:last-child {
    border-bottom: none;
}

.settings-table td {
    padding: 12px;
    font-size: 14px;
}

.settings-table td:first-child {
    color: #64748B;
    width: 30%;
}

.settings-table td:last-child {
    color: #1E293B;
    font-weight: 500;
    word-break: break-all;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.stat-box {
    background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%);
    border: 2px solid #0284C7;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    background: #0284C7;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-label {
    font-size: 12px;
    color: #475569;
    font-weight: 600;
    text-transform: uppercase;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #0066CC;
    margin-top: 4px;
}

.extensions-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
}

.ext-item {
    padding: 12px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<?php require_once 'footer_admin.php'; ?>
