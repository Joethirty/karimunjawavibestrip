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
    <title><?php echo isset($page_title) ? $page_title : 'Karimunjawavibestrip'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
</head>
<body>

    <nav class="header-nav <?php echo $is_home ? 'transparent-header' : 'solid-header'; ?>" id="headerNav">
        <div class="nav-container">
            <div class="logo" style="cursor: pointer; display: flex; align-items: center; gap: 8px;" onclick="window.location.href='<?php echo $base_url; ?>index.php';">
                <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="Karimunjawavibestrip Logo" class="logo-img">
                <span class="logo-text">Karimunjawavibestrip</span>
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
        </div>
    </nav>

    <?php if ($is_home): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('headerNav');
            function checkScroll() {
                if (window.scrollY > 50) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }
            }
            window.addEventListener('scroll', checkScroll);
            checkScroll(); // Check once in case page loads scrolled down
        });
    </script>
    <?php endif; ?>