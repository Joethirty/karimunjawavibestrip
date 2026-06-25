<?php
$base_url = './';
// Hubungkan komponen data konfigurasi
require_once $base_url . 'config.php';

// Judul halaman dinamis untuk SEO
$page_title = "Galeri Wisata & Penginapan Karimunjawa - Karimunjawa Vibes Strip";

// Muat komponen header visual
include_once $base_url . 'header.php';

// Gabungkan galeri foto umum dengan foto utama dari setiap penginapan
$semua_foto_galeri = $galeri_foto;
if (isset($daftar_penginapan) && is_array($daftar_penginapan)) {
    foreach ($daftar_penginapan as $p) {
        $tag = "Resort";
        if (stripos($p['nama'], 'Homestay') !== false || stripos($p['nama'], 'Hostel') !== false || stripos($p['nama'], 'Guest House') !== false) {
            $tag = "Homestay";
        } elseif (stripos($p['nama'], 'Hotel') !== false) {
            $tag = "Hotel";
        } elseif (stripos($p['nama'], 'Cottage') !== false || stripos($p['nama'], 'Omah') !== false) {
            $tag = "Cottage";
        }
        
        $semua_foto_galeri[] = [
            "file" => $p['gambar'],
            "alt" => $p['nama'],
            "posisi" => "center center",
            "kategori" => "penginapan",
            "tag" => $tag
        ];
    }
}
?>

<!-- Main Gallery Section -->
<main class="container" style="padding-top: 80px;">
    <!-- Tombol Kembali di Pojok Kiri Atas -->
    <div style="margin-bottom: 20px; text-align: left;">
        <a href="<?php echo $base_url; ?>index.php" class="btn-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            KEMBALI KE BERANDA
        </a>
    </div>
    <h1 style="text-align: center; margin-bottom: 8px;">Galeri Keindahan Karimunjawa</h1>
    <p style="text-align: center; color: var(--medium-gray); max-width: 600px; margin: 0 auto 32px auto; font-size: 16px; line-height: 24px;">
        Jelajahi keindahan panorama alam, terumbu karang tropis, aktivitas wisata bahari seru, dan kenyamanan resort/homestay pilihan kami di Kepulauan Karimunjawa.
    </p>

    <!-- Kategori Filter Tab (Easy to Use for Laymen) -->
    <div class="gallery-filters">
        <button class="filter-btn active" data-category="all">Semua Foto</button>
        <button class="filter-btn" data-category="destinasi">Destinasi Wisata</button>
        <button class="filter-btn" data-category="aktivitas">Aktivitas Seru</button>
        <button class="filter-btn" data-category="penginapan">Penginapan & Resort</button>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid">
        <?php if (isset($semua_foto_galeri) && is_array($semua_foto_galeri)): ?>
            <?php foreach ($semua_foto_galeri as $foto): 
                $kategori = isset($foto['kategori']) ? $foto['kategori'] : 'destinasi';
                $tag = isset($foto['tag']) ? $foto['tag'] : 'Wisata';
            ?>
                <div class="gallery-card" data-category="<?php echo $kategori; ?>" onclick="bukaModalLightbox('<?php echo $base_url . $foto['file']; ?>', '<?php echo $foto['alt']; ?>')">
                    <img src="<?php echo $base_url . $foto['file']; ?>" 
                         alt="<?php echo $foto['alt']; ?>" 
                         style="object-position: <?php echo !empty($foto['posisi']) ? $foto['posisi'] : 'center center'; ?>;">
                    <div class="gallery-card-info">
                        <span class="gallery-card-tag"><?php echo $tag; ?></span>
                        <h3 class="gallery-card-title"><?php echo $foto['alt']; ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- JavaScript Filter Interaktif & Halus -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryCards = document.querySelectorAll('.gallery-card');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Hapus kelas aktif dari semua tombol filter
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const category = this.getAttribute('data-category');

            galleryCards.forEach(card => {
                const cardCat = card.getAttribute('data-category');

                // Matikan animasi sementara untuk mereset
                card.style.animation = 'none';
                card.offsetHeight; // Memicu reflow browser agar animasi ter-reset

                if (category === 'all' || cardCat === category) {
                    card.style.display = 'block';
                    card.style.animation = 'cardFadeIn 0.4s ease forwards';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php
// Muat komponen footer dan script
include_once $base_url . 'footer.php';
?>
