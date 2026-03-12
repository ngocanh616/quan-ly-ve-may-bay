<?php

/**
 * File: khachhang/timkiem.php
 * Mô tả: Tìm kiếm chuyến bay
 */

require_once '../config.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    $_SESSION['message'] = 'Vui lòng đăng nhập để tìm kiếm chuyến bay!';
    $_SESSION['message_type'] = 'warning';
    header('Location: ../dangnhap.php');
    exit();
}

// Xử lý tìm kiếm
$chuyenBayList = [];
$searched = false;

if (isset($_POST['timkiem'])) {
    $diemDi = escape($_POST['diemDi']);
    $diemDen = escape($_POST['diemDen']);
    $ngayBay = escape($_POST['ngayBay']);
    $soKhach = (int)$_POST['soKhach'];

    $searched = true;

    // Query tìm chuyến bay
    $sql = "SELECT * FROM ChuyenBay 
            WHERE SanBayDi = '$diemDi' 
            AND SanBayDen = '$diemDen'
            AND DATE(ThoiGianBay) = '$ngayBay'
            AND SoGheConLai >= $soKhach
            AND TrangThai = 'concho'
            ORDER BY ThoiGianBay ASC";

    $chuyenBayList = fetch_all($sql);
}

// Lấy danh sách sân bay (distinct từ ChuyenBay)
$sanBayDi = fetch_all("SELECT DISTINCT SanBayDi as SanBay FROM ChuyenBay ORDER BY SanBayDi");
$sanBayDen = fetch_all("SELECT DISTINCT SanBayDen as SanBay FROM ChuyenBay ORDER BY SanBayDen");

// Merge và unique
$sanBayList = [];
foreach ($sanBayDi as $sb) {
    if (!in_array($sb['SanBay'], $sanBayList)) {
        $sanBayList[] = $sb['SanBay'];
    }
}
foreach ($sanBayDen as $sb) {
    if (!in_array($sb['SanBay'], $sanBayList)) {
        $sanBayList[] = $sb['SanBay'];
    }
}
sort($sanBayList);

$pageTitle = 'Tìm Kiếm Chuyến Bay - Sky Airline';
$loadKhachHangCSS = true;
require_once '../header.php';
?>

<div class="search-page">
    <div class="container py-4">
        <!-- Search Form -->
        <div class="search-card">
            <div class="search-header">
                <h2 class="search-title">
                    <i class="bi bi-search"></i>
                    Tìm Kiếm Chuyến Bay
                </h2>
                <p class="search-subtitle">Tìm chuyến bay phù hợp với hành trình của bạn</p>
            </div>

            <form method="POST" action="" class="search-form">
                <div class="row g-3">
                    <!-- Điểm đi -->
                    <div class="col-md-6">
                        <label for="diemDi" class="form-label">
                            <i class="bi bi-airplane-fill me-2"></i>Điểm đi
                        </label>
                        <select name="diemDi" id="diemDi" class="form-select" required>
                            <option value="">-- Chọn điểm đi --</option>
                            <?php foreach ($sanBayList as $sb): ?>
                                <option value="<?php echo $sb; ?>"
                                    <?php echo (isset($_POST['diemDi']) && $_POST['diemDi'] == $sb) ? 'selected' : ''; ?>>
                                    <?php echo $sb; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Điểm đến -->
                    <div class="col-md-6">
                        <label for="diemDen" class="form-label">
                            <i class="bi bi-geo-alt-fill me-2"></i>Điểm đến
                        </label>
                        <select name="diemDen" id="diemDen" class="form-select" required>
                            <option value="">-- Chọn điểm đến --</option>
                            <?php foreach ($sanBayList as $sb): ?>
                                <option value="<?php echo $sb; ?>"
                                    <?php echo (isset($_POST['diemDen']) && $_POST['diemDen'] == $sb) ? 'selected' : ''; ?>>
                                    <?php echo $sb; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ngày bay -->
                    <div class="col-md-6">
                        <label for="ngayBay" class="form-label">
                            <i class="bi bi-calendar-event me-2"></i>Ngày bay
                        </label>
                        <input type="date"
                            name="ngayBay"
                            id="ngayBay"
                            class="form-control"
                            min="<?php echo date('Y-m-d'); ?>"
                            value="<?php echo isset($_POST['ngayBay']) ? $_POST['ngayBay'] : date('Y-m-d'); ?>"
                            required>
                    </div>

                    <!-- Số khách -->
                    <div class="col-md-6">
                        <label for="soKhach" class="form-label">
                            <i class="bi bi-people-fill me-2"></i>Số hành khách
                        </label>
                        <input type="number"
                            name="soKhach"
                            id="soKhach"
                            class="form-control"
                            min="1"
                            max="10"
                            value="<?php echo isset($_POST['soKhach']) ? $_POST['soKhach'] : 1; ?>"
                            required>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" name="timkiem" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-search me-2"></i>Tìm Chuyến Bay
                    </button>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <?php if ($searched): ?>
            <div class="results-section mt-4">
                <div class="results-header">
                    <h3>
                        <i class="bi bi-list-ul me-2"></i>
                        Kết quả tìm kiếm
                        <?php if (!empty($chuyenBayList)): ?>
                            <span class="badge bg-primary"><?php echo count($chuyenBayList); ?> chuyến bay</span>
                        <?php endif; ?>
                    </h3>
                </div>

                <?php if (empty($chuyenBayList)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Không tìm thấy chuyến bay phù hợp. Vui lòng thử lại với tiêu chí khác.
                    </div>
                <?php else: ?>
                    <div class="flights-list">
                        <?php foreach ($chuyenBayList as $cb): ?>
                            <div class="flight-card">
                                <div class="flight-header">
                                    <div class="flight-route">
                                        <div class="route-point">
                                            <div class="route-city"><?php echo $cb['SanBayDi']; ?></div>
                                            <div class="route-time"><?php echo date('H:i', strtotime($cb['ThoiGianBay'])); ?></div>
                                        </div>

                                        <div class="route-arrow">
                                            <i class="bi bi-arrow-right"></i>
                                            <div class="route-duration">
                                                <?php
                                                $start = new DateTime($cb['ThoiGianBay']);
                                                $end = new DateTime($cb['ThoiGianDen']);
                                                $diff = $start->diff($end);
                                                echo $diff->h . 'h ' . $diff->i . 'm';
                                                ?>
                                            </div>
                                        </div>

                                        <div class="route-point">
                                            <div class="route-city"><?php echo $cb['SanBayDen']; ?></div>
                                            <div class="route-time"><?php echo date('H:i', strtotime($cb['ThoiGianDen'])); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flight-body">
                                    <div class="flight-info">
                                        <div class="info-item">
                                            <i class="bi bi-airplane"></i>
                                            <span><strong><?php echo $cb['MaChuyenBayText']; ?></strong></span>
                                        </div>
                                        <div class="info-item">
                                            <i class="bi bi-building"></i>
                                            <span><?php echo $cb['HangBay']; ?></span>
                                        </div>
                                        <div class="info-item">
                                            <i class="bi bi-calendar3"></i>
                                            <span><?php echo date('d/m/Y', strtotime($cb['ThoiGianBay'])); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <i class="bi bi-people"></i>
                                            <span>Còn <strong><?php echo $cb['SoGheConLai']; ?></strong> ghế</span>
                                        </div>
                                    </div>

                                    <div class="flight-price">
                                        <div class="price-amount"><?php echo number_format($cb['GiaVe'], 0, ',', '.'); ?> đ</div>
                                        <div class="price-label">/ người</div>
                                    </div>

                                    <div class="flight-action">
                                        <a href="datve.php?machuyenbay=<?php echo $cb['MaChuyenBay']; ?>&sokhach=1&new=1"
                                            class="btn btn-primary">
                                            <i class="bi bi-ticket-perforated me-2"></i>Đặt vé
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../footer.php'; ?>