<?php
$base_url = './';
// Hubungkan komponen data konfigurasi
require_once $base_url . 'config.php';

// Deklarasi global agar Intelephense VS Code tidak memunculkan garis merah
global $daftar_penginapan;

// Map rating default untuk masing-masing penginapan jika belum ada ulasan baru
$default_ratings_map = [
    'homestay-fan' => 4.5,
    'homestay-ac' => 4.6,
    'puri-karimun' => 4.7,
    'blue-laguna' => 4.8,
    'summer-inn' => 4.8,
    'dseason' => 4.9,
    'almare' => 4.8,
    'omah-alchy' => 4.9,
    'hallo-resort' => 4.8,
    'happinezz-hill' => 4.8,
    'legon-waru' => 4.9,
    'royal-ocean' => 5.0,
    'java-paradise' => 4.9
];

// Hitung rating rata-rata dinamis dari reviews.json
$lodging_ratings = [];
$lodging_counts = [];

if (isset($testimoni_pelanggan) && is_array($testimoni_pelanggan)) {
    foreach ($testimoni_pelanggan as $testi) {
        if (isset($testi['penginapan_id'])) {
            $pid = $testi['penginapan_id'];
            if (!isset($lodging_ratings[$pid])) {
                $lodging_ratings[$pid] = 0;
                $lodging_counts[$pid] = 0;
            }
            $lodging_ratings[$pid] += isset($testi['bintang']) ? intval($testi['bintang']) : 5;
            $lodging_counts[$pid]++;
        }
    }
}

$ratings_map = [];
foreach ($default_ratings_map as $pid => $def_rating) {
    if (isset($lodging_counts[$pid]) && $lodging_counts[$pid] > 0) {
        $ratings_map[$pid] = round($lodging_ratings[$pid] / $lodging_counts[$pid], 1);
    } else {
        $ratings_map[$pid] = $def_rating;
    }
}

// Filter $testimoni_pelanggan untuk hanya memuat ulasan umum / tour guide (tanpa penginapan_id)
$testimoni_tour_guide = [];
if (isset($testimoni_pelanggan) && is_array($testimoni_pelanggan)) {
    foreach ($testimoni_pelanggan as $testi) {
        if (empty($testi['penginapan_id'])) {
            $testimoni_tour_guide[] = $testi;
        }
    }
}
$testimoni_pelanggan = $testimoni_tour_guide;

// Memproses input ulasan baru
$review_success = false;
$review_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_ulasan') {
    $nama = isset($_POST['nama_review']) ? trim(htmlspecialchars($_POST['nama_review'])) : "";
    $asal = isset($_POST['asal_review']) ? trim(htmlspecialchars($_POST['asal_review'])) : "";
    $bintang = isset($_POST['bintang']) ? intval($_POST['bintang']) : 5;
    $ulasan = isset($_POST['ulasan_review']) ? trim(htmlspecialchars($_POST['ulasan_review'])) : "";

    if (!empty($nama) && !empty($ulasan) && $bintang >= 1 && $bintang <= 5) {
        $reviews_file = __DIR__ . '/reviews.json';
        $current_reviews = [];
        if (file_exists($reviews_file)) {
            $json_data = file_get_contents($reviews_file);
            $current_reviews = json_decode($json_data, true);
            if (!is_array($current_reviews)) {
                $current_reviews = [];
            }
        }

        $new_review = [
            "nama" => $nama,
            "asal" => $asal,
            "bintang" => $bintang,
            "ulasan" => $ulasan,
            "tanggal" => date('Y-m-d')
        ];

        // Tambahkan ulasan baru ke bagian teratas list
        array_unshift($current_reviews, $new_review);

        if (file_put_contents($reviews_file, json_encode($current_reviews, JSON_PRETTY_PRINT))) {
            // Redirect untuk menghindari resubmission saat refresh
            header("Location: index.php?status=success#testimoni");
            exit;
        } else {
            $review_error = "Gagal menyimpan ulasan. Silakan coba lagi.";
        }
    } else {
        $review_error = "Harap lengkapi semua kolom dan bintang rating.";
    }
}

// Muat komponen header visual
$is_homepage = true;
include_once $base_url . 'header.php';
?>

<header class="hero" id="home">
    <div class="hero-inner">
        <!-- Kolom Kiri: Informasi & Ajakan Aksi -->
        <div class="hero-left-panel">
            <span class="hero-tag">Travel Website</span>
            <h1 class="hero-main-title">NEVER STOP<br>EXPLORING THE<br>WORLD.</h1>
            <p class="hero-desc">Temukan keindahan pantai pasir putih yang tersembunyi, terumbu karang tropis yang menawan, dan pilihan penginapan mewah berkelas di Kepulauan Karimunjawa.</p>
            <a href="#mengapa-kami" class="btn-hero-learn-more">LEARN MORE</a>
        </div>
    </div>
</header>

<!-- Mengapa Memilih Kami Section -->
<section id="mengapa-kami" style="background-color: #0c2d2e; padding: 80px 16px; color: var(--off-white); border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div class="container" style="padding: 0; max-width: 1200px;">
        <h2 style="color: var(--off-white); text-transform: uppercase; font-size: 28px; letter-spacing: 1px; margin-bottom: 8px; text-align: center;">KENAPA MENGGUNAKAN JASA KARIMUNJAWA VIBES TRIP ?</h2>
        <div style="width: 50px; height: 3px; background-color: var(--primary-teal); margin: 0 auto 50px auto; border-radius: 2px;"></div>
        
        <div class="why-us-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            <!-- Card 1 -->
            <div class="why-us-card" style="background-color: var(--off-white); color: var(--dark-gray); border-radius: 12px; padding: 35px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div style="margin-bottom: 20px; font-size: 32px; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background-color: rgba(28, 187, 180, 0.1); color: var(--primary-teal);">
                    <!-- Icon: Stars/Thumb up -->
                    <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--dark-gray); margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase;">Pilihan Destinasi Terbaik</h3>
                <p style="font-size: 14px; line-height: 22px; color: var(--charcoal); margin: 0;">Nikmati perjalanan eksklusif ke 6 hingga 10 spot wisata terindah di Karimunjawa. Sebagai biro lokal tepercaya, kami aktif mengeksplorasi dan memetakan lokasi-lokasi tersembunyi yang jarang dijamah turis lain, termasuk titik terbaik menyaksikan matahari terbit (sunrise point) yang memukau.</p>
            </div>
            
            <!-- Card 2 -->
            <div class="why-us-card" style="background-color: var(--off-white); color: var(--dark-gray); border-radius: 12px; padding: 35px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div style="margin-bottom: 20px; font-size: 32px; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background-color: rgba(255, 105, 0, 0.1); color: var(--vibrant-orange);">
                    <!-- Icon: Discount tag -->
                    <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--dark-gray); margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase;">Harga Yang Kompetitif</h3>
                <p style="font-size: 14px; line-height: 22px; color: var(--charcoal); margin: 0;">Dapatkan penawaran harga liburan terbaik yang sangat bersaing. Kami menjamin efisiensi biaya tanpa sedikit pun mengurangi standar fasilitas, kenyamanan akomodasi, dan kualitas pelayanan premium yang kami suguhkan.</p>
            </div>
            
            <!-- Card 3 -->
            <div class="why-us-card" style="background-color: var(--off-white); color: var(--dark-gray); border-radius: 12px; padding: 35px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div style="margin-bottom: 20px; font-size: 32px; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background-color: rgba(255, 216, 63, 0.15); color: #E5B800;">
                    <!-- Icon: Booking / Ribbon -->
                    <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--dark-gray); margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase;">Booking Dengan Mudah</h3>
                <p style="font-size: 14px; line-height: 22px; color: var(--charcoal); margin: 0;">Proses pemesanan sangat praktis, cepat, dan tanpa ribet. Tim travel consultant profesional kami selalu siap siaga melayani konsultasi dan merespons pertanyaan Anda kapan saja selama 24 jam penuh setiap harinya.</p>
            </div>

            <!-- Card 4 -->
            <div class="why-us-card" style="background-color: var(--off-white); color: var(--dark-gray); border-radius: 12px; padding: 35px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div style="margin-bottom: 20px; font-size: 32px; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background-color: rgba(6, 106, 171, 0.1); color: var(--deep-blue);">
                    <!-- Icon: User / Guide -->
                    <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--dark-gray); margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase;">Guide Lokal Berpengalaman</h3>
                <p style="font-size: 14px; line-height: 22px; color: var(--charcoal); margin: 0;">Perjalanan Anda akan dipandu oleh guide lokal berlisensi yang ramah, komunikatif, dan sangat memahami seluk-beluk pulau. Kami memastikan petualangan Anda berlangsung aman, nyaman, dan penuh edukasi.</p>
            </div>

            <!-- Card 5 -->
            <div class="why-us-card" style="background-color: var(--off-white); color: var(--dark-gray); border-radius: 12px; padding: 35px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div style="margin-bottom: 20px; font-size: 32px; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background-color: rgba(6, 147, 227, 0.1); color: var(--sky-blue);">
                    <!-- Icon: Camera / Photo -->
                    <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--dark-gray); margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase;">Dokumentasi Premium</h3>
                <p style="font-size: 14px; line-height: 22px; color: var(--charcoal); margin: 0;">Abadikan momen liburan seru Anda secara maksimal. Kami menyediakan layanan dokumentasi foto dan video bawah air (underwater) menggunakan kamera aksi GoPro berkualitas tinggi secara cuma-cuma.</p>
            </div>

            <!-- Card 6 -->
            <div class="why-us-card" style="background-color: var(--off-white); color: var(--dark-gray); border-radius: 12px; padding: 35px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div style="margin-bottom: 20px; font-size: 32px; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 50%; background-color: rgba(224, 79, 103, 0.1); color: var(--coral-red);">
                    <!-- Icon: Heart / Customer trust -->
                    <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--dark-gray); margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase;">Layanan Prima & Terpercaya</h3>
                <p style="font-size: 14px; line-height: 22px; color: var(--charcoal); margin: 0;">Kepuasan dan kebahagiaan Anda adalah misi utama kami. Berbekal reputasi tepercaya dan ratusan testimoni positif dari para pelancong, kami berkomitmen menghadirkan momen liburan impian yang berkesan seumur hidup.</p>
            </div>
        </div>
    </div>
</section>

<section id="penginapan" class="container">
    <div class="section-title-wrapper">
        <h2>Penginapan Karimunjawa</h2>
        <p class="section-subtitle">Harga Terbaik dan terpercaya</p>
    </div>
    
    <div class="grid-cards">
        <?php foreach ($daftar_penginapan as $index => $penginapan): 
            $is_hidden = $index >= 8;
            $card_class = "package-card-link" . ($is_hidden ? " hidden-card" : "");
            $card_style = $is_hidden ? "display: none;" : "";
        ?>
            <a href="<?php echo $base_url; ?>detail-page/<?php echo $penginapan['id']; ?>.php" class="<?php echo $card_class; ?>" style="<?php echo $card_style; ?>">
                <div class="package-card">
                    <div class="card-image-wrapper">
                        <img src="<?php echo $base_url . $penginapan['gambar']; ?>" alt="<?php echo $penginapan['nama']; ?>">
                    </div>
                    
                    <div class="card-body">
                        <?php 
                        $rating = isset($ratings_map[$penginapan['id']]) ? $ratings_map[$penginapan['id']] : 4.8;
                        $full_stars = floor($rating);
                        ?>
                        <div class="card-rating-wrapper">
                            <div class="star-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $full_stars) {
                                        echo '<span class="star">&#9733;</span>';
                                    } else {
                                        echo '<span class="star empty">&#9733;</span>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                        </div>
                        
                        <h3 class="card-title"><?php echo $penginapan['nama']; ?></h3>
                    </div>
                    
                    <?php 
                    $harga_bersih = str_replace(['Rp.', 'Mulai', 'Rp'], '', $penginapan['harga']);
                    $harga_bersih = trim(explode('/', $harga_bersih)[0]);
                    $harga_formatted = 'IDR ' . $harga_bersih;
                    ?>
                    <div class="card-footer-price">
                        <div class="price-info">
                            <span class="price-start-label">Start From</span>
                            <span class="price-val"><?php echo $harga_formatted; ?></span>
                        </div>
                        <div class="arrow-container">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="footer-arrow">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (count($daftar_penginapan) > 8): ?>
        <div class="show-more-container" style="text-align: center; margin-top: 32px;">
            <button id="btnShowMoreLodgings" class="btn-secondary" onclick="toggleLodgings()" data-showing="false" style="min-width: 220px; height: 44px; font-size: 14px; border-radius: 6px;">
                Lihat Semua Penginapan (<?php echo count($daftar_penginapan); ?>)
            </button>
        </div>
        
        <script>
            function toggleLodgings() {
                const hiddenCards = document.querySelectorAll('.hidden-card');
                const btn = document.getElementById('btnShowMoreLodgings');
                const isShowing = btn.getAttribute('data-showing') === 'true';
                
                if (isShowing) {
                    // Collapsing
                    hiddenCards.forEach(card => {
                        card.style.display = 'none';
                    });
                    btn.innerText = 'Lihat Semua Penginapan (<?php echo count($daftar_penginapan); ?>)';
                    btn.setAttribute('data-showing', 'false');
                    // Scroll smoothly back to section header
                    document.getElementById('penginapan').scrollIntoView({ behavior: 'smooth' });
                } else {
                    // Expanding
                    hiddenCards.forEach(card => {
                        card.style.display = 'flex';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.style.transition = 'opacity 0.4s ease';
                            card.style.opacity = '1';
                        }, 20);
                    });
                    btn.innerText = 'Sembunyikan Penginapan';
                    btn.setAttribute('data-showing', 'true');
                }
            }
        </script>
    <?php endif; ?>
</section>

<!-- SEKSI GALERI: Menggunakan CSS Grid dengan Aspect Ratio 16:9 (Lanskap Seragam) -->
<section id="galeri" class="container">
    <h2>Keindahan Karimunjawa</h2>
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-top: 32px;">
        <?php if (isset($galeri_foto) && is_array($galeri_foto)): ?>
            <?php foreach (array_slice($galeri_foto, 0, 4) as $foto): ?>
                <!-- Mengunci bentuk kontainer kotak menjadi lanskap persegi panjang (16:9) -->
                <div style="cursor: pointer; overflow: hidden; height: auto; aspect-ratio: 16 / 9; border: 1px solid var(--very-light-gray);"
                    onclick="bukaModalLightbox('<?php echo $foto['file']; ?>', '<?php echo $foto['alt']; ?>')">
                    <!-- Menggunakan object-fit: cover dan memanggil koordinat posisi dari config.php secara dinamis -->
                    <img src="<?php echo $base_url . $foto['file']; ?>" alt="<?php echo $foto['alt']; ?>"
                        style="width: 100%; height: 100%; object-fit: cover; object-position: <?php echo !empty($foto['posisi']) ? $foto['posisi'] : 'center center'; ?>; transition: transform 0.3s;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div style="text-align: center; margin-top: 32px;">
        <a href="galeri.php" class="btn-secondary">Lihat Semua Foto (Galeri)</a>
    </div>
</section>

<section id="testimoni"
    style="background-color: #F9F9F9; border-top: 1px solid var(--very-light-gray); border-bottom: 1px solid var(--very-light-gray);">
    <div class="container" style="max-width: 900px;">
        <h2>Apa Kata Wisatawan?</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="review-alert review-alert-success"
                style="max-width: 600px; margin: 0 auto 20px auto; text-align: center;">
                <strong>Berhasil!</strong> Ulasan Anda telah berhasil disimpan dan diterbitkan.
            </div>
        <?php endif; ?>

        <?php if (!empty($review_error)): ?>
            <div class="review-alert review-alert-danger"
                style="max-width: 600px; margin: 0 auto 20px auto; text-align: center;">
                <strong>Error:</strong> <?php echo $review_error; ?>
            </div>
        <?php endif; ?>

        <?php
        // Hitung Rating Rata-rata
        $total_bintang = 0;
        $jumlah_ulasan = count($testimoni_pelanggan);
        foreach ($testimoni_pelanggan as $testi) {
            $total_bintang += isset($testi['bintang']) ? intval($testi['bintang']) : 5;
        }
        $rating_rata_rata = $jumlah_ulasan > 0 ? round($total_bintang / $jumlah_ulasan, 1) : 0;
        $bintang_bulat = round($rating_rata_rata);
        ?>

        <!-- Summary Rating -->
        <div class="rating-summary-wrapper">
            <div class="rating-summary-number"><?php echo $rating_rata_rata; ?></div>
            <div class="rating-summary-stars">
                <div class="stars-gold">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $bintang_bulat) {
                            echo "&#9733;";
                        } else {
                            echo "<span class='stars-gray'>&#9733;</span>";
                        }
                    }
                    ?>
                </div>
                <div style="font-size: 14px; color: var(--medium-gray);">
                    Berdasarkan <?php echo $jumlah_ulasan; ?> ulasan wisatawan
                </div>
            </div>
        </div>

        <!-- Grid Ulasan Wisatawan -->
        <div class="reviews-grid-list">
            <?php if (isset($testimoni_pelanggan) && is_array($testimoni_pelanggan)): ?>
                <?php foreach ($testimoni_pelanggan as $idx => $testi):
                    $is_hidden = $idx >= 4;
                    $hidden_class = $is_hidden ? 'review-item-card-hidden' : '';
                    $hidden_style = $is_hidden ? 'display: none;' : '';
                    ?>
                    <div class="review-item-card <?php echo $hidden_class; ?>" style="<?php echo $hidden_style; ?>">
                        <div class="review-card-header">
                            <div class="review-card-stars">
                                <?php
                                $bintang_ulasan = isset($testi['bintang']) ? intval($testi['bintang']) : 5;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $bintang_ulasan) {
                                        echo "&#9733;";
                                    } else {
                                        echo "<span class='stars-gray' style='color:#DDDDDD;'>&#9733;</span>";
                                    }
                                }
                                ?>
                            </div>
                            <div class="review-card-date">
                                <?php echo isset($testi['tanggal']) ? $testi['tanggal'] : date('Y-m-d'); ?>
                            </div>
                        </div>
                        <p class="review-card-text">"<?php echo $testi['ulasan']; ?>"</p>

                        <?php if (!empty($testi['balasan'])): ?>
                            <div class="review-reply-admin" style="background-color: rgba(28, 187, 180, 0.05); border-left: 3px solid var(--primary-teal); padding: 10px 14px; margin-top: 12px; border-radius: 8px; font-size: 13px; text-align: left; line-height: 1.5; color: var(--charcoal);">
                                <strong style="color: var(--primary-teal); display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Balasan Admin:</strong>
                                <?php echo htmlspecialchars($testi['balasan']); ?>
                            </div>
                        <?php endif; ?>

                        <div
                            style="display: flex; align-items: center; gap: 12px; margin-top: 12px; border-top: 1px solid #F0F0F0; padding-top: 12px;">
                            <div class="review-avatar-circle"
                                style="width: 32px; height: 32px; border-radius: 50%; background-color: var(--primary-teal); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px; text-transform: uppercase; flex-shrink: 0; box-shadow: 0 2px 4px rgba(28, 187, 180, 0.2);">
                                <?php echo substr($testi['nama'], 0, 1); ?>
                            </div>
                            <div class="review-card-author">
                                <?php echo $testi['nama']; ?> <span
                                    style="display: block; font-size: 12px; font-weight: normal; color: var(--light-gray); margin-top: 2px;"><?php echo $testi['asal']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tombol Aksi Ulasan (Lihat Semua & Tulis Baru) -->
        <div class="review-actions-container">
            <?php if ($jumlah_ulasan > 4): ?>
                <button id="toggleMoreReviewsBtn" class="btn-review-secondary" onclick="toggleMoreReviews()">Lihat Semua
                    Ulasan (<?php echo $jumlah_ulasan; ?>)</button>
            <?php endif; ?>
            <button id="toggleReviewBtn" class="btn-review-primary" onclick="toggleReviewForm()">Tulis Ulasan
                Baru</button>
        </div>

        <!-- Form Tambah Ulasan (Toggled) -->
        <div id="reviewFormContainer" class="review-form-box" style="display: none;">
            <h3 style="margin-bottom: 20px; border-bottom: 1px solid var(--very-light-gray); padding-bottom: 10px;">
                Berikan Ulasan Anda</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="tambah_ulasan">

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="nama_review">Nama Lengkap</label>
                    <input class="text-input" type="text" id="nama_review" name="nama_review"
                        placeholder="Contoh: Sarah Aulia" required style="width: 100%;">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="asal_review">Asal Kota</label>
                    <input class="text-input" type="text" id="asal_review" name="asal_review"
                        placeholder="Contoh: Bandung" required style="width: 100%;">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Rating Pelayanan</label>
                    <div class="rating-input-group">
                        <div class="star-rating-form">
                            <input type="radio" id="bintang5" name="bintang" value="5" required />
                            <label for="bintang5" title="Sangat Puas">&#9733;</label>
                            <input type="radio" id="bintang4" name="bintang" value="4" />
                            <label for="bintang4" title="Puas">&#9733;</label>
                            <input type="radio" id="bintang3" name="bintang" value="3" />
                            <label for="bintang3" title="Cukup Puas">&#9733;</label>
                            <input type="radio" id="bintang2" name="bintang" value="2" />
                            <label for="bintang2" title="Kurang Puas">&#9733;</label>
                            <input type="radio" id="bintang1" name="bintang" value="1" />
                            <label for="bintang1" title="Sangat Kecewa">&#9733;</label>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="ulasan_review">Komentar Ulasan</label>
                    <textarea class="text-input" id="ulasan_review" name="ulasan_review"
                        placeholder="Bagikan pengalaman Anda menggunakan jasa layanan kami..." required
                        style="width: 100%; height: 100px; resize: vertical; padding: 12px; font-family: Tahoma, sans-serif;"></textarea>
                </div>

                <div class="form-review-actions">
                    <button type="button" class="btn-review-secondary" onclick="toggleReviewForm()">Batal</button>
                    <button type="submit" class="btn-review-primary">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section id="kontak" class="booking-section">
    <div class="container">
        <h2>Konsultasi Liburan Anda</h2>
        <form class="form-grid" onsubmit="kirimKeWhatsApp(event)">
            <div class="form-group">
                <label class="form-label" for="nama">Nama Lengkap</label>
                <input class="text-input" type="text" id="nama" placeholder="Contoh: Budi Santoso" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="paket_pilihan">Pilih Penginapan</label>
                <select class="text-input" id="paket_pilihan" required style="height: 40px; padding: 0 12px;">
                    <option value="">-- Pilih Penginapan --</option>
                    <?php foreach ($daftar_penginapan as $penginapan): ?>
                        <option value="<?php echo $penginapan['nama']; ?>"><?php echo $penginapan['nama']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary-large">Tanya Ketersediaan Kamar via WA</button>
            </div>
        </form>
    </div>
</section>

<?php
// Muat komponen footer dan script
include_once $base_url . 'footer.php';
?>