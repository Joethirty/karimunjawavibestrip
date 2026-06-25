<?php
if (!isset($base_url)) {
    $base_url = './';
}
$is_home = isset($is_homepage) && $is_homepage;
$current_page = basename($_SERVER['SCRIPT_NAME']);
$is_detail_page = (strpos($_SERVER['SCRIPT_NAME'], '/detail-page/') !== false || $current_page == 'detail.php');
?>
<!DOCTYPE html>
<html lang="id" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'KarimunJawa Vibes Trip'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
</head>
<body>

    <nav class="header-nav <?php echo $is_home ? 'transparent-header' : 'solid-header'; ?>" id="headerNav">
        <div class="nav-container">
            <div class="logo" style="cursor: pointer; display: flex; align-items: center; gap: 8px;" onclick="window.location.href='<?php echo $base_url; ?>index.php';">
                <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="KarimunJawa Vibes Trip Logo" class="logo-img">
                <span class="logo-text">KarimunJawa Vibes Trip</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><a href="<?php echo $base_url; ?>index.php">Home</a></li>
                
                <li class="nav-item dropdown <?php echo $is_detail_page ? 'active' : ''; ?>">
                    <a href="<?php echo $base_url; ?>index.php#penginapan" class="dropdown-toggle">Penginapan ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo $base_url; ?>index.php#penginapan">Semua Penginapan</a></li>
                        
                        <?php 
                        // Deklarasi global agar Intelephense VS Code tidak memunculkan garis merah
                        global $daftar_penginapan; 
                        
                        if (isset($daftar_penginapan) && is_array($daftar_penginapan)): 
                            foreach ($daftar_penginapan as $p_item): 
                        ?>
                            <li><a href="<?php echo $base_url; ?>detail-page/<?php echo $p_item['id']; ?>.php"><?php echo $p_item['nama']; ?></a></li>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </ul>
                </li>
                <li class="nav-item <?php echo ($current_page == 'galeri.php') ? 'active' : ''; ?>"><a href="<?php echo $base_url; ?>galeri.php">Galeri</a></li>
                <li class="nav-item"><a href="<?php echo $base_url; ?>index.php#kontak">Hubungi Kami</a></li>
            </ul>

            <button class="hamburger-menu" id="hamburgerMenu" aria-label="Toggle Menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('headerNav');
            const hamburger = document.getElementById('hamburgerMenu');
            const navMenu = document.querySelector('.nav-menu');
            let lastScrollY = window.scrollY;

            function checkScroll() {
                const currentScrollY = window.scrollY;

                // Toggle solid header background on scroll
                if (currentScrollY > 50) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }

                // Sembunyikan saat scroll ke bawah, tunjukkan saat scroll ke atas
                // Threshold 100px agar tidak langsung tersembunyi di bagian paling atas
                // Jangan sembunyikan jika menu mobile sedang terbuka
                if (currentScrollY > 100 && currentScrollY > lastScrollY && (!hamburger || !hamburger.classList.contains('active'))) {
                    header.classList.add('header-hidden');
                } else {
                    header.classList.remove('header-hidden');
                }

                lastScrollY = currentScrollY;
            }

            // Hamburger menu toggle logic
            if (hamburger && navMenu) {
                hamburger.addEventListener('click', function() {
                    hamburger.classList.toggle('active');
                    navMenu.classList.toggle('active');
                    header.classList.toggle('mobile-menu-open');
                });
            }

            // Close mobile menu when clicking a link
            const navLinks = document.querySelectorAll('.nav-item a:not(.dropdown-toggle), .dropdown-menu a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (hamburger && hamburger.classList.contains('active')) {
                        hamburger.classList.remove('active');
                        navMenu.classList.remove('active');
                        header.classList.remove('mobile-menu-open');
                    }
                });
            });

            // Handle dropdown toggling on mobile view
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        const dropdown = this.parentElement;
                        dropdown.classList.toggle('open');
                    }
                });
            });

            window.addEventListener('scroll', checkScroll, { passive: true });
            checkScroll(); // Cek sekali saat halaman dimuat
        });
    </script>