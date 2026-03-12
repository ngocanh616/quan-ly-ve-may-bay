<?php

/**
 * File: khachhang/chuyenbay.php
 * Mô tả: Hiển thị tất cả chuyến bay hiện có (có phân trang)
 */

require_once '../config.php';

// Pagination settings
$itemsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Tối thiểu là trang 1
$offset = ($page - 1) * $itemsPerPage;

// Lấy tham số lọc
$hangBay = isset($_GET['hangbay']) ? escape($_GET['hangbay']) : '';
$diemDi = isset($_GET['diemdi']) ? escape($_GET['diemdi']) : '';
$diemDen = isset($_GET['diemden']) ? escape($_GET['diemden']) : '';
$sapxep = isset($_GET['sapxep']) ? escape($_GET['sapxep']) : 'thoigian';

// Build query
$where = ["TrangThai = 'concho'"];
if ($hangBay) {
    $where[] = "HangBay = '$hangBay'";
}
if ($diemDi) {
    $where[] = "SanBayDi = '$diemDi'";
}
if ($diemDen) {
    $where[] = "SanBayDen = '$diemDen'";
}

$whereClause = implode(' AND ', $where);

// Sắp xếp
$orderBy = 'ThoiGianBay ASC';
if ($sapxep == 'giathap') {
    $orderBy = 'GiaVe ASC';
}
if ($sapxep == 'giacao') {
    $orderBy = 'GiaVe DESC';
}

// Đếm tổng số
$totalSql = "SELECT COUNT(*) as total FROM ChuyenBay WHERE $whereClause";
$totalResult = fetch_one($totalSql);
$totalItems = $totalResult['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Lấy danh sách chuyến bay (có LIMIT)
$sql = "SELECT * FROM ChuyenBay WHERE $whereClause ORDER BY $orderBy LIMIT $itemsPerPage OFFSET $offset";
$chuyenBayList = fetch_all($sql);

// Lấy danh sách hãng bay
$hangBayList = fetch_all("SELECT DISTINCT HangBay FROM ChuyenBay ORDER BY HangBay");

// Lấy danh sách sân bay
$sanBayDi = fetch_all("SELECT DISTINCT SanBayDi as SanBay FROM ChuyenBay ORDER BY SanBayDi");
$sanBayDen = fetch_all("SELECT DISTINCT SanBayDen as SanBay FROM ChuyenBay ORDER BY SanBayDen");
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

// Build URL query string cho pagination
$queryParams = [];
if ($hangBay) $queryParams['hangbay'] = $hangBay;
if ($diemDi) $queryParams['diemdi'] = $diemDi;
if ($diemDen) $queryParams['diemden'] = $diemDen;
if ($sapxep) $queryParams['sapxep'] = $sapxep;
$queryString = http_build_query($queryParams);

$pageTitle = 'Chuyến Bay Hiện Có';
$loadKhachHangCSS = true;
require_once '../header.php';
?>

<div class="flights-page">
    <div class="container py-4">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <h1 class="page-title">
                <i class="bi bi-airplane-engines-fill me-3"></i>
                Chuyến Bay Hiện Có
            </h1>
            <p class="page-subtitle">
                Khám phá tất cả các chuyến bay đang sẵn sàng phục vụ bạn
            </p>
        </div>

        <!-- Filter Bar -->
        <div class="filter-card mb-4">
            <form method="GET" action="" class="filter-form">
                <div class="row g-3">
                    <!-- Hãng bay -->
                    <div class="col-md-3">
                        <label for="hangbay" class="form-label">
                            <i class="bi bi-building me-2"></i>Hãng bay
                        </label>
                        <select name="hangbay" id="hangbay" class="form-select">
                            <option value="">Tất cả hãng</option>
                            <?php foreach ($hangBayList as $hb): ?>
                                <option value="<?php echo $hb['HangBay']; ?>" <?php echo ($hangBay == $hb['HangBay']) ? 'selected' : ''; ?>>
                                    <?php echo $hb['HangBay']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Điểm đi -->
                    <div class="col-md-3">
                        <label for="diemdi" class="form-label">
                            <i class="bi bi-airplane-fill me-2"></i>Điểm đi
                        </label>
                        <select name="diemdi" id="diemdi" class="form-select">
                            <option value="">Tất cả điểm đi</option>
                            <?php foreach ($sanBayList as $sb): ?>
                                <option value="<?php echo $sb; ?>" <?php echo ($diemDi == $sb) ? 'selected' : ''; ?>>
                                    <?php echo $sb; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Điểm đến -->
                    <div class="col-md-3">
                        <label for="diemden" class="form-label">
                            <i class="bi bi-geo-alt-fill me-2"></i>Điểm đến
                        </label>
                        <select name="diemden" id="diemden" class="form-select">
                            <option value="">Tất cả điểm đến</option>
                            <?php foreach ($sanBayList as $sb): ?>
                                <option value="<?php echo $sb; ?>" <?php echo ($diemDen == $sb) ? 'selected' : ''; ?>>
                                    <?php echo $sb; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sắp xếp -->
                    <div class="col-md-3">
                        <label for="sapxep" class="form-label">
                            <i class="bi bi-sort-down me-2"></i>Sắp xếp
                        </label>
                        <select name="sapxep" id="sapxep" class="form-select">
                            <option value="thoigian" <?php echo ($sapxep == 'thoigian') ? 'selected' : ''; ?>>Thời gian bay</option>
                            <option value="giathap" <?php echo ($sapxep == 'giathap') ? 'selected' : ''; ?>>Giá thấp nhất</option>
                            <option value="giacao" <?php echo ($sapxep == 'giacao') ? 'selected' : ''; ?>>Giá cao nhất</option>
                        </select>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel me-2"></i>Lọc
                    </button>
                    <a href="chuyenbay.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Đặt lại
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="results-info mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-info-circle me-2"></i>
                    Tìm thấy <strong><?php echo $totalItems; ?></strong> chuyến bay
                    <span class="text-muted">
                        (Trang <?php echo $page; ?>/<?php echo $totalPages; ?>)
                    </span>
                </div>
                <?php if ($hangBay || $diemDi || $diemDen): ?>
                    <div class="text-muted">
                        <small>
                            <?php if ($hangBay): ?>
                                <span class="badge bg-primary me-2"><?php echo $hangBay; ?></span>
                            <?php endif; ?>
                            <?php if ($diemDi): ?>
                                <span class="badge bg-success me-2">Từ: <?php echo $diemDi; ?></span>
                            <?php endif; ?>
                            <?php if ($diemDen): ?>
                                <span class="badge bg-info">Đến: <?php echo $diemDen; ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Flights List -->
        <?php if (empty($chuyenBayList)): ?>
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Không tìm thấy chuyến bay nào phù hợp với bộ lọc của bạn.
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
                                    <div class="route-date"><?php echo date('d/m/Y', strtotime($cb['ThoiGianBay'])); ?></div>
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
                                    <div class="route-date"><?php echo date('d/m/Y', strtotime($cb['ThoiGianDen'])); ?></div>
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
                                    <i class="bi bi-people"></i>
                                    <span>Còn <strong><?php echo $cb['SoGheConLai']; ?></strong>/<strong><?php echo $cb['TongSoGhe']; ?></strong> ghế</span>
                                </div>
                            </div>

                            <div class="flight-price">
                                <div class="price-amount"><?php echo number_format($cb['GiaVe'], 0, ',', '.'); ?> đ</div>
                                <div class="price-label">/ người</div>
                            </div>

                            <div class="flight-action">
                                <?php if (is_logged_in()): ?>
                                    <a href="datve.php?machuyenbay=<?php echo $cb['MaChuyenBay']; ?>&sokhach=1&new=1"
                                        class="btn btn-primary">
                                        <i class="bi bi-ticket-perforated me-2"></i>Đặt vé
                                    </a>
                                <?php else: ?>
                                    <a href="../dangnhap.php" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập để đặt
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-wrapper mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Button -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            // Always show first page
                            if ($startPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&<?php echo $queryString; ?>">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo $queryString; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Always show last page -->
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $totalPages; ?>&<?php echo $queryString; ?>"><?php echo $totalPages; ?></a>
                                </li>
                            <?php endif; ?>

                            <!-- Next Button -->
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../footer.php'; ?>