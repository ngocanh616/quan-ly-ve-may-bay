<?php
/**
 * File: index.php
 * Mô tả: Trang chủ hệ thống
 */

$pageTitle = 'Trang Chủ - Sky Airline';
require_once 'header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Content -->
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="hero-content">
                    <div class="hero-badge mb-3">
                        <i class="bi bi-stars"></i>
                        <span>Hệ thống đặt vé #1 Việt Nam</span>
                    </div>
                    
                    <h1 class="hero-title mb-4">
                        Chào Mừng Đến Với<br>
                        <span class="text-gradient">Sky Airline</span>
                    </h1>
                    
                    <p class="hero-subtitle mb-4">
                        Đặt vé máy bay nhanh chóng, tiện lợi và an toàn. 
                        Hãy bay cao hơn cùng chúng tôi!
                    </p>
                    
                    <?php if (!is_logged_in()): ?>
                        <div class="hero-buttons">
                            <a href="dangnhap.php" class="btn btn-primary btn-lg me-2 mb-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng Nhập
                            </a>
                            <a href="dangky.php" class="btn btn-outline-primary btn-lg mb-2">
                                <i class="bi bi-person-plus me-2"></i>Đăng Ký
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if (is_admin()): ?>
                            <a href="admin/index.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-speedometer2 me-2"></i>Vào Admin Dashboard
                            </a>
                        <?php else: ?>
                            <a href="khachhang/timkiem.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-search me-2"></i>Tìm Chuyến Bay Ngay
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Image -->
            <div class="col-lg-6">
                <div class="hero-image-wrapper">
                    <div class="hero-floating-card card-1">
                        <i class="bi bi-airplane-engines-fill"></i>
                        <div>
                            <h6>500+</h6>
                            <p>Chuyến bay</p>
                        </div>
                    </div>
                    
                    <div class="hero-floating-card card-2">
                        <i class="bi bi-people-fill"></i>
                        <div>
                            <h6>10,000+</h6>
                            <p>Khách hàng</p>
                        </div>
                    </div>
                    
                    <div class="hero-image-main">
                        <svg viewBox="0 0 500 400" xmlns="http://www.w3.org/2000/svg">
                            <!-- Sky background -->
                            <rect width="500" height="400" fill="url(#skyGradient)"/>
                            <defs>
                                <linearGradient id="skyGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#E3F2FD;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#90CAF9;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            
                            <!-- Clouds -->
                            <ellipse cx="80" cy="100" rx="40" ry="25" fill="#ffffff" opacity="0.6"/>
                            <ellipse cx="100" cy="95" rx="45" ry="28" fill="#ffffff" opacity="0.6"/>
                            <ellipse cx="120" cy="100" rx="35" ry="22" fill="#ffffff" opacity="0.6"/>
                            
                            <ellipse cx="350" cy="150" rx="45" ry="28" fill="#ffffff" opacity="0.5"/>
                            <ellipse cx="370" cy="145" rx="50" ry="30" fill="#ffffff" opacity="0.5"/>
                            <ellipse cx="395" cy="150" rx="40" ry="25" fill="#ffffff" opacity="0.5"/>
                            
                            <!-- Airplane -->
                            <g transform="translate(250, 200)">
                                <!-- Fuselage -->
                                <ellipse cx="0" cy="0" rx="80" ry="20" fill="#0080FF"/>
                                <ellipse cx="-60" cy="0" rx="20" ry="18" fill="#006BA6"/>
                                
                                <!-- Wings -->
                                <path d="M -20,-20 L -20,-50 L 40,-45 L 40,-15 Z" fill="#FF6B35"/>
                                <path d="M -20,20 L -20,50 L 40,45 L 40,15 Z" fill="#FF6B35"/>
                                
                                <!-- Tail -->
                                <path d="M -70,-5 L -90,-25 L -70,-15 Z" fill="#006BA6"/>
                                <path d="M -70,5 L -90,25 L -70,15 Z" fill="#006BA6"/>
                                
                                <!-- Windows -->
                                <circle cx="-40" cy="0" r="4" fill="#E#E3F2FD"/>
                                <circle cx="-25" cy="0" r="4" fill="#E3F2F2FD"/>
                                <circle cx="-10" cy="0" r="4" fill="#E3F3F2FD"/>
                                <circle cx="5" cy="0" r="4" fill="#E3F2F2FD"/>
                                <circle cx="20" cy="0" r="4" fill="#E3F3F2FD"/>
                                <circle cx="35" cy="0" r="4" fill="#E#E3F2FD"/>
                            </g>
                            
                            <!-- Flight path line -->
                            <path d="M 50 250 Q 200 200 450 280" stroke="#0080FF" stroke-width="2" fill="none" stroke-dasharray="10,5" opacity="0.5"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container my-5">
    <div class="row text-center mb-5">
        <div class="col-12">
            <h2 class="section-title">Tại Sao Chọn Chúng Tôi?</h2>
            <p class="section-subtitle text-muted">Những lý do khách hàng tin tưởng Sky Airline</p>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h5 class="feature-title">Đặt Vé Nhanh Chóng</h5>
                <p class="feature-text">
                    Chỉ với vài bước đơn giản, bạn đã có vé máy bay trên tay. 
                    Tiết kiệm thời gian tối đa.
                </p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5 class="feature-title">An Toàn Bảo Mật</h5>
                <p class="feature-text">
                    Thông tin cá nhân và thanh toán được bảo mật tuyệt đối 
                    với công nghệ mã hóa hiện đại.
                </p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <h5 class="feature-title">Hỗ Trợ 24/7</h5>
                <p class="feature-text">
                    Đội ngũ hỗ trợ luôn sẵn sàng giải đáp mọi thắc mắc 
                    của bạn mọi lúc, mọi nơi.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="stats-section my-5 py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="bi bi-airplane-engines stat-icon"></i>
                    <h3 class="stat-number">500+</h3>
                    <p class="stat-label">Chuyến Bay</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="bi bi-people stat-icon"></i>
                    <h3 class="stat-number">10,000+</h3>
                    <p class="stat-label">Khách Hàng</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="bi bi-ticket-perforated stat-icon"></i>
                    <h3 class="stat-number">50,000+</h3>
                    <p class="stat-label">Vé Đã Bán</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="bi bi-star-fill stat-icon"></i>
                    <h3 class="stat-number">4.8/5</h3>
                    <p class="stat-label">Đánh Giá</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
