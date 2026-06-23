<?php
$base_url = './';
// Hubungkan komponen data konfigurasi
require_once $base_url . 'config.php';

// Map rating untuk masing-masing penginapan
$ratings_map = [
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
            <a href="#penginapan" class="btn-hero-learn-more">LEARN MORE</a>
        </div>
    </div>
</header>

<section id="penginapan" class="container">
    <div class="section-title-wrapper">
        <h2>Paket Penginapan Karimunjawa</h2>
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
                    <?php if (!empty($penginapan['badge'])): ?>
                        <div class="promotional-badge <?php echo $penginapan['badge_class']; ?>">
                            <?php echo $penginapan['badge']; ?>
                        </div>
                    <?php endif; ?>

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
                        
                        <h3 class="card-title">Paket <?php echo $penginapan['nama']; ?></h3>
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
                    $is_hidden = $idx >= 3;
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
            <?php if ($jumlah_ulasan > 3): ?>
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