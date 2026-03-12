<?php
/*
 * File: admin/footer_admin.php
 * Mô tả: Footer của Admin Dashboard
 */
?>

        </div>
        
    </main>
    
</div>

<!-- Chart.js (nếu cần) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Auto hide alert sau 5 giây
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const sidebar = document.getElementById('adminSidebar');
    const mainContent = document.querySelector('.admin-main');
    
    if (!toggleBtn || !sidebar || !mainContent) return;
    
    // Lấy trạng thái từ localStorage
    const sidebarState = localStorage.getItem('sidebarVisible');
    
    // Nếu chưa có, mặc định hiển thị (true)
    let isVisible = sidebarState === null ? true : (sidebarState === 'true');
    
    // Apply initial state
    applySidebarState(isVisible);
    
    // Click handler
    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        isVisible = !isVisible;
        applySidebarState(isVisible);
        localStorage.setItem('sidebarVisible', isVisible);
    });
    
    // Apply state
    function applySidebarState(visible) {
        if (visible) {
            // Show sidebar
            sidebar.classList.remove('hidden');
            mainContent.classList.remove('full-width');
            toggleBtn.title = 'Ẩn sidebar';
        } else {
            // Hide sidebar
            sidebar.classList.add('hidden');
            mainContent.classList.add('full-width');
            toggleBtn.title = 'Hiện sidebar';
        }
    }
});

</script>

</body>
</html>
