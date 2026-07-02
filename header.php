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
    <link rel="icon" type="image/png" href="<?php echo $base_url; ?>assets/images/logo.png">
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
                    <a href="<?php echo $base_url; ?>index.php#penginapan" class="dropdown-toggle">Paket Tour ▾</a>
                    <ul class="dropdown-menu">
                        <!-- Paket Honeymoon & Durasi Utama -->
                        <li>
                            <a href="<?php echo $base_url; ?>index.php?durasi=Honeymoon#penginapan">
                                Paket Honeymoon Karimunjawa
                            </a>
                        </li>
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=3D2N&kategori=homestay#penginapan">Paket Tour Karimunjawa 3 Hari 2 Malam - Homestay</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=3D2N&kategori=hotel#penginapan">Paket Tour Karimunjawa 3 Hari 2 Malam - Hotel</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=3D2N&kategori=resort#penginapan">Paket Tour Karimunjawa 3 Hari 2 Malam - Resort & Cottage</a></li>
                        
                        <li style="border-top: 1px dashed var(--very-light-gray); margin: 5px 0;"></li>
                        
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=2D1N&kategori=homestay#penginapan">Paket Tour Karimunjawa 2 Hari 1 Malam - Homestay</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=2D1N&kategori=hotel#penginapan">Paket Tour Karimunjawa 2 Hari 1 Malam - Hotel</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=2D1N&kategori=resort#penginapan">Paket Tour Karimunjawa 2 Hari 1 Malam - Resort & Cottage</a></li>
                        
                        <li style="border-top: 1px dashed var(--very-light-gray); margin: 5px 0;"></li>
                        
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=4D3N&kategori=homestay#penginapan">Paket Tour Karimunjawa 4 Hari 3 Malam - Homestay</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=4D3N&kategori=hotel#penginapan">Paket Tour Karimunjawa 4 Hari 3 Malam - Hotel</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php?durasi=4D3N&kategori=resort#penginapan">Paket Tour Karimunjawa 4 Hari 3 Malam - Resort & Cottage</a></li>
                        
                        <li style="border-top: 1px solid var(--very-light-gray); margin: 5px 0;"></li>
                        
                        <?php 
                        global $daftar_penginapan; 
                        $homestays = [];
                        $hotels = [];
                        $resorts_cottages = [];
                        
                        if (isset($daftar_penginapan) && is_array($daftar_penginapan)) {
                            foreach ($daftar_penginapan as $p_item) {
                                $nama_lc = strtolower($p_item['nama']);
                                if (strpos($nama_lc, 'homestay') !== false || strpos($nama_lc, 'hostel') !== false || strpos($nama_lc, 'inn') !== false) {
                                    $homestays[] = $p_item;
                                } elseif (strpos($nama_lc, 'hotel') !== false || strpos($nama_lc, 'mare') !== false || strpos($nama_lc, 'season') !== false) {
                                    $hotels[] = $p_item;
                                } else {
                                    $resorts_cottages[] = $p_item;
                                }
                            }
                        }
                        ?>

                        <!-- Sub-dropdown Kategori Penginapan -->
                        <?php if (!empty($homestays)): ?>
                            <li class="has-submenu">
                                <a href="javascript:void(0)">Paket Homestay</a>
                                <ul class="submenu">
                                    <?php foreach ($homestays as $h_item): ?>
                                        <li><a href="<?php echo $base_url; ?>detail-page/<?php echo $h_item['id']; ?>.php"><?php echo $h_item['nama']; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($hotels)): ?>
                            <li class="has-submenu">
                                <a href="javascript:void(0)">Paket Hotel</a>
                                <ul class="submenu">
                                    <?php foreach ($hotels as $ht_item): ?>
                                        <li><a href="<?php echo $base_url; ?>detail-page/<?php echo $ht_item['id']; ?>.php"><?php echo $ht_item['nama']; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($resorts_cottages)): ?>
                            <li class="has-submenu">
                                <a href="javascript:void(0)">Paket Resort & Cottage</a>
                                <ul class="submenu">
                                    <?php foreach ($resorts_cottages as $rc_item): ?>
                                        <li><a href="<?php echo $base_url; ?>detail-page/<?php echo $rc_item['id']; ?>.php"><?php echo $rc_item['nama']; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
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