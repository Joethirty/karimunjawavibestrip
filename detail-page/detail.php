<?php
$base_url = '../';
// Hubungkan komponen data konfigurasi
require_once $base_url . 'config.php';

// Deklarasi global agar Intelephense VS Code tidak memunculkan garis merah
global $daftar_penginapan;

// Validasi parameter ID penginapan
$penginapan_id = isset($_GET['id']) ? trim(htmlspecialchars($_GET['id'])) : '';
$penginapan = null;

if (!empty($penginapan_id)) {
    foreach ($daftar_penginapan as $p) {
        if ($p['id'] === $penginapan_id) {
            $penginapan = $p;
            break;
        }
    }
}

// Deteksi durasi terpilih (default 3D2N)
$selected_durasi = isset($_GET['durasi']) ? trim(htmlspecialchars($_GET['durasi'])) : '3D2N';
$selected_durasi_upper = strtoupper($selected_durasi);
if ($selected_durasi_upper !== '2D1N' && $selected_durasi_upper !== '4D3N' && $selected_durasi_upper !== 'HONEYMOON') {
    $selected_durasi = '3D2N';
} else {
    $selected_durasi = $selected_durasi_upper;
}

// Memproses input ulasan baru khusus penginapan ini
$review_success = false;
$review_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_ulasan_detail' && $penginapan) {
    $nama = isset($_POST['nama_review']) ? trim(htmlspecialchars($_POST['nama_review'])) : "";
    $asal = isset($_POST['asal_review']) ? trim(htmlspecialchars($_POST['asal_review'])) : "";
    $bintang = isset($_POST['bintang']) ? intval($_POST['bintang']) : 5;
    $ulasan = isset($_POST['ulasan_review']) ? trim(htmlspecialchars($_POST['ulasan_review'])) : "";
    
    if (!empty($nama) && !empty($ulasan) && $bintang >= 1 && $bintang <= 5) {
        $reviews_file = $base_url . 'reviews.json';
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
            "tanggal" => date('Y-m-d'),
            "penginapan_id" => $penginapan['id']
        ];
        
        // Tambahkan ke bagian teratas list
        array_unshift($current_reviews, $new_review);
        
        if (file_put_contents($reviews_file, json_encode($current_reviews, JSON_PRETTY_PRINT))) {
            // Redirect untuk menghindari resubmission saat refresh
            header("Location: " . $penginapan['id'] . ".php?status=success&durasi=" . urlencode($selected_durasi) . "#testimoni-paket");
            exit;
        } else {
            $review_error = "Gagal menyimpan ulasan. Silakan coba lagi.";
        }
    } else {
        $review_error = "Harap lengkapi semua kolom dan bintang rating.";
    }
}

// Ambil ulasan spesifik penginapan ini
$lodging_reviews = [];
if (isset($testimoni_pelanggan) && is_array($testimoni_pelanggan)) {
    foreach ($testimoni_pelanggan as $testi) {
        if (isset($testi['penginapan_id']) && $testi['penginapan_id'] === $penginapan['id']) {
            $lodging_reviews[] = $testi;
        }
    }
}

$total_bintang = 0;
$jumlah_ulasan = count($lodging_reviews);
foreach ($lodging_reviews as $testi) {
    $total_bintang += isset($testi['bintang']) ? intval($testi['bintang']) : 5;
}

$default_ratings_map = [
    'homestay-loyal' => 4.5,
    'homestay-fan' => 4.5,
    'homestay-azza' => 4.6,
    'homestay-ac' => 4.6,
    'puri-karimun' => 4.7,
    'the-body-tree' => 4.8,
    'ayu-hotel' => 4.8,
    'bale-karimunjawa' => 4.8,
    'hotel-blue-laguna-inn' => 4.8,
    'blue-laguna' => 4.8,
    'hotel-summer-inn' => 4.8,
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
$default_rating = isset($default_ratings_map[$penginapan['id']]) ? $default_ratings_map[$penginapan['id']] : 4.8;
$rating_rata_rata = $jumlah_ulasan > 0 ? round($total_bintang / $jumlah_ulasan, 1) : $default_rating;

// Judul halaman dinamis untuk SEO
$page_title = $penginapan ? $penginapan['nama'] . " - Penginapan Karimunjawa" : "Penginapan Tidak Ditemukan - KarimunJawa Vibes Trip";

// Muat komponen header visual
include_once $base_url . 'header.php';
?>

<?php if ($penginapan): ?>
    <!-- Main Container -->
    <main class="container" style="padding-top: 120px; max-width: 1200px; margin: 0 auto;">
        
        <!-- Tombol Kembali di Pojok Kiri Atas di atas Gambar -->
        <div style="margin-bottom: 20px; text-align: left;">
            <a href="<?php echo $base_url; ?>index.php#penginapan" class="btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                KEMBALI KE BERANDA
            </a>
        </div>
        
        <!-- 5-Photo Grid Gallery (Spans full width at the top) -->
        <?php
        $active_gallery = $penginapan['foto_galeri'];
        if (!empty($penginapan['tipe_kamar'])) {
            $active_gallery = $penginapan['tipe_kamar'][0]['foto_galeri'];
        }
        ?>
        <div class="lodging-detail-gallery-container">
            <div class="lodging-detail-gallery">
                <!-- 1. Left Top Image -->
                <div id="gallery-item-1" class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $active_gallery[1]; ?>', '<?php echo $penginapan['nama']; ?>')">
                    <img id="gallery-img-1" src="<?php echo $base_url . $active_gallery[1]; ?>" alt="<?php echo $penginapan['nama']; ?>">
                </div>
                
                <!-- 2. Middle Large Image (spans two rows) -->
                <div id="gallery-item-0" class="lodging-detail-gallery-item big-image" onclick="bukaModalLightbox('<?php echo $base_url . $active_gallery[0]; ?>', '<?php echo $penginapan['nama']; ?>')">
                    <img id="gallery-img-0" src="<?php echo $base_url . $active_gallery[0]; ?>" alt="<?php echo $penginapan['nama']; ?>">
                </div>
                
                <!-- 3. Right Top Image -->
                <div id="gallery-item-2" class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $active_gallery[2]; ?>', '<?php echo $penginapan['nama']; ?>')">
                    <img id="gallery-img-2" src="<?php echo $base_url . $active_gallery[2]; ?>" alt="<?php echo $penginapan['nama']; ?>">
                </div>
                
                <!-- 4. Left Bottom Image -->
                <div id="gallery-item-3" class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $active_gallery[3]; ?>', '<?php echo $penginapan['nama']; ?>')">
                    <img id="gallery-img-3" src="<?php echo $base_url . $active_gallery[3]; ?>" alt="<?php echo $penginapan['nama']; ?>">
                </div>
                
                <!-- 5. Right Bottom Image -->
                <div id="gallery-item-4" class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $active_gallery[4]; ?>', '<?php echo $penginapan['nama']; ?>')">
                    <img id="gallery-img-4" src="<?php echo $base_url . $active_gallery[4]; ?>" alt="<?php echo $penginapan['nama']; ?>">
                </div>
            </div>
            <!-- Mobile Badge Indicator -->
            <div class="gallery-badge-mobile">1 / 5</div>
        </div>

        <!-- Script to handle mobile gallery scroll badge update -->
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const gallery = document.querySelector('.lodging-detail-gallery');
            const badge = document.querySelector('.gallery-badge-mobile');
            
            if (gallery && badge) {
                gallery.addEventListener('scroll', function() {
                    const width = gallery.getBoundingClientRect().width;
                    if (width > 0) {
                        const activeIndex = Math.round(gallery.scrollLeft / width) + 1;
                        badge.textContent = activeIndex + ' / 5';
                    }
                }, { passive: true });
            }
        });
        </script>

        <!-- 2-Column Split Layout -->
        <div class="detail-grid">
            
            <!-- Left Column: Content -->
            <div class="detail-main">
                
                <!-- Main Header Details -->
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--primary-teal); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                        <?php echo !empty($penginapan['badge']) ? $penginapan['badge'] : 'Akomodasi Pilihan'; ?> &bull; Karimunjawa
                    </div>
                    <h1 style="font-size: 32px; font-weight: 700; color: var(--dark-gray); margin-bottom: 12px;"><?php echo $penginapan['nama']; ?></h1>
                    <p style="font-size: 15px; color: var(--medium-gray); line-height: 24px; margin-bottom: 0;"><?php echo $penginapan['deskripsi']; ?></p>
                    
                    <!-- Selector Tipe Kamar -->
                    <?php if (!empty($penginapan['tipe_kamar'])): ?>
                        <div style="margin-top: 20px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <span style="font-size: 14px; font-weight: 700; color: var(--dark-gray); line-height: 1;">Pilih Tipe Kamar:</span>
                            <div class="room-type-tabs" style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <?php foreach ($penginapan['tipe_kamar'] as $index => $tipe): ?>
                                    <button class="room-type-tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                             onclick="switchRoomType('<?php echo $tipe['id']; ?>')" 
                                             data-room-id="<?php echo $tipe['id']; ?>"
                                             style="padding: 8px 18px; font-size: 13px; font-weight: 700; border-radius: 30px; cursor: pointer; border: 1px solid #ECECEC; background-color: #F9F9F9; color: var(--charcoal); transition: all 0.3s ease; font-family: inherit;">
                                        <?php echo $tipe['nama']; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <script>
                    <?php 
                    $init_dur = $selected_durasi;
                    if (strcasecmp($init_dur, 'Honeymoon') === 0) {
                        $init_dur = 'HONEYMOON_3D2N';
                    }
                    ?>
                    let currentDuration = '<?php echo $init_dur; ?>';
                    let currentRoomId = '<?php echo !empty($penginapan['tipe_kamar']) ? $penginapan['tipe_kamar'][0]['id'] : ''; ?>';

                    <?php if (!empty($penginapan['tipe_kamar'])): ?>
                    const roomTypeData = {
                        <?php foreach ($penginapan['tipe_kamar'] as $tipe): 
                            $harga_3d2d = $tipe['harga'];
                            $harga_2d1n = isset($tipe['harga_2d1n']) ? $tipe['harga_2d1n'] : $tipe['harga'];
                            $harga_4d3n = isset($tipe['harga_4d3n']) ? $tipe['harga_4d3n'] : $tipe['harga'];
                            
                            $harga_h_2d1n = isset($tipe['harga_honeymoon_2d1n']) && !empty($tipe['harga_honeymoon_2d1n']) ? $tipe['harga_honeymoon_2d1n'] : (isset($tipe['harga_honeymoon']) ? $tipe['harga_honeymoon'] : (isset($penginapan['harga_honeymoon_2d1n']) ? $penginapan['harga_honeymoon_2d1n'] : ''));
                            $harga_h_3d2n = isset($tipe['harga_honeymoon']) && !empty($tipe['harga_honeymoon']) ? $tipe['harga_honeymoon'] : (isset($penginapan['harga_honeymoon']) ? $penginapan['harga_honeymoon'] : '');
                            $harga_h_3d2n_smg = isset($tipe['harga_honeymoon_3d2n_smg']) && !empty($tipe['harga_honeymoon_3d2n_smg']) ? $tipe['harga_honeymoon_3d2n_smg'] : (isset($tipe['harga_honeymoon']) ? $tipe['harga_honeymoon'] : (isset($penginapan['harga_honeymoon_3d2n_smg']) ? $penginapan['harga_honeymoon_3d2n_smg'] : ''));
                            $harga_h_4d3n = isset($tipe['harga_honeymoon_4d3n']) && !empty($tipe['harga_honeymoon_4d3n']) ? $tipe['harga_honeymoon_4d3n'] : (isset($tipe['harga_honeymoon']) ? $tipe['harga_honeymoon'] : (isset($penginapan['harga_honeymoon_4d3n']) ? $penginapan['harga_honeymoon_4d3n'] : ''));
                            
                            $pesan_wa_3d2n = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "* untuk paket *3 Hari 2 Malam (3D2N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                            $pesan_wa_2d1n = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "* untuk paket *2 Hari 1 Malam (2D1N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                            $pesan_wa_4d3n = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "* untuk paket *4 Hari 3 Malam (4D3N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                            
                            $pesan_wa_h_2d1n = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "* untuk *Paket Honeymoon 2 Hari 1 Malam (2D1N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                            $pesan_wa_h_3d2n = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "* untuk *Paket Honeymoon 3 Hari 2 Malam (3D2N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                            $pesan_wa_h_3d2n_smg = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "* untuk *Paket Honeymoon 3 Hari 2 Malam Semarang*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                            $pesan_wa_h_4d3n = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "* untuk *Paket Honeymoon 4 Hari 3 Malam (4D3N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                        ?>
                        '<?php echo $tipe['id']; ?>': {
                            nama: '<?php echo $tipe['nama']; ?>',
                            images: [
                                '<?php echo $base_url . $tipe['foto_galeri'][0]; ?>',
                                '<?php echo $base_url . $tipe['foto_galeri'][1]; ?>',
                                '<?php echo $base_url . $tipe['foto_galeri'][2]; ?>',
                                '<?php echo $base_url . $tipe['foto_galeri'][3]; ?>',
                                '<?php echo $base_url . $tipe['foto_galeri'][4]; ?>'
                            ],
                            prices: {
                                '3D2N': '<?php echo $harga_3d2d; ?>',
                                '2D1N': '<?php echo $harga_2d1n; ?>',
                                '4D3N': '<?php echo $harga_4d3n; ?>',
                                'HONEYMOON_2D1N': '<?php echo $harga_h_2d1n; ?>',
                                'HONEYMOON_3D2N': '<?php echo $harga_h_3d2n; ?>',
                                'HONEYMOON_3D2N_SMG': '<?php echo $harga_h_3d2n_smg; ?>',
                                'HONEYMOON_4D3N': '<?php echo $harga_h_4d3n; ?>'
                            },
                            waUrls: {
                                '3D2N': 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode($pesan_wa_3d2n); ?>',
                                '2D1N': 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode($pesan_wa_2d1n); ?>',
                                '4D3N': 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode($pesan_wa_4d3n); ?>',
                                'HONEYMOON_2D1N': 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode($pesan_wa_h_2d1n); ?>',
                                'HONEYMOON_3D2N': 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode($pesan_wa_h_3d2n); ?>',
                                'HONEYMOON_3D2N_SMG': 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode($pesan_wa_h_3d2n_smg); ?>',
                                'HONEYMOON_4D3N': 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode($pesan_wa_h_4d3n); ?>'
                            }
                        },
                        <?php endforeach; ?>
                    };
                    <?php else: ?>
                    const lodgingPriceData = {
                        '3D2N': {
                            price: '<?php echo $penginapan['harga']; ?>',
                            waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* untuk paket *3 Hari 2 Malam (3D2N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                        },
                        '2D1N': {
                            price: '<?php echo isset($penginapan['harga_2d1n']) ? $penginapan['harga_2d1n'] : $penginapan['harga']; ?>',
                            waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* untuk paket *2 Hari 1 Malam (2D1N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                        },
                        '4D3N': {
                            price: '<?php echo isset($penginapan['harga_4d3n']) ? $penginapan['harga_4d3n'] : $penginapan['harga']; ?>',
                            waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* untuk paket *4 Hari 3 Malam (4D3N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                        },
                        'HONEYMOON_2D1N': {
                            price: '<?php echo isset($penginapan['harga_honeymoon_2d1n']) && !empty($penginapan['harga_honeymoon_2d1n']) ? $penginapan['harga_honeymoon_2d1n'] : (isset($penginapan['harga_honeymoon']) ? $penginapan['harga_honeymoon'] : ''); ?>',
                            waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* untuk *Paket Honeymoon 2 Hari 1 Malam (2D1N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                        },
                        'HONEYMOON_3D2N': {
                            price: '<?php echo isset($penginapan['harga_honeymoon']) ? $penginapan['harga_honeymoon'] : ''; ?>',
                            waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* untuk *Paket Honeymoon 3 Hari 2 Malam (3D2N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                        },
                        'HONEYMOON_3D2N_SMG': {
                            price: '<?php echo isset($penginapan['harga_honeymoon_3d2n_smg']) && !empty($penginapan['harga_honeymoon_3d2n_smg']) ? $penginapan['harga_honeymoon_3d2n_smg'] : (isset($penginapan['harga_honeymoon']) ? $penginapan['harga_honeymoon'] : ''); ?>',
                            waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* untuk *Paket Honeymoon 3 Hari 2 Malam Semarang*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                        },
                        'HONEYMOON_4D3N': {
                            price: '<?php echo isset($penginapan['harga_honeymoon_4d3n']) && !empty($penginapan['harga_honeymoon_4d3n']) ? $penginapan['harga_honeymoon_4d3n'] : (isset($penginapan['harga_honeymoon']) ? $penginapan['harga_honeymoon'] : ''); ?>',
                            waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* untuk *Paket Honeymoon 4 Hari 3 Malam (4D3N)*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                        }
                    };
                    <?php endif; ?>

                    function updateBookingCard() {
                        let priceText = '';
                        let waUrl = '';

                        if (currentRoomId) {
                            const room = roomTypeData[currentRoomId];
                            if (room) {
                                priceText = room.prices[currentDuration];
                                waUrl = room.waUrls[currentDuration];
                            }
                        } else {
                            const data = lodgingPriceData[currentDuration];
                            if (data) {
                                priceText = data.price;
                                waUrl = data.waUrl;
                            }
                        }

                        const sidebarPrice = document.getElementById('sidebar-price');
                        const sidebarPriceUnit = document.getElementById('sidebar-price-unit');
                        if (sidebarPrice) {
                            let parts = priceText.split('/');
                            let displayPrice = parts[0].trim();
                            displayPrice = displayPrice.replace(/Rp\.?/i, 'Rp ').replace(/\s+/, ' ');
                            sidebarPrice.textContent = displayPrice;
                            if (sidebarPriceUnit && parts[1]) {
                                sidebarPriceUnit.textContent = '/' + parts[1].trim();
                            }
                        }

                        const sidebarBtn = document.getElementById('sidebar-booking-btn');
                        if (sidebarBtn) {
                            sidebarBtn.href = waUrl;
                        }
                    }

                    function switchDuration(duration) {
                        currentDuration = duration;
                        updateBookingCard();
                        if (typeof renderItineraryDetail === 'function') {
                            renderItineraryDetail(duration);
                        }
                    }

                    function switchRoomType(roomId) {
                        currentRoomId = roomId;
                        
                        document.querySelectorAll('.room-type-tab-btn').forEach(btn => {
                            if (btn.getAttribute('data-room-id') === roomId) {
                                btn.classList.add('active');
                                btn.style.backgroundColor = 'var(--primary-teal)';
                                btn.style.color = '#ffffff';
                                btn.style.borderColor = 'var(--primary-teal)';
                                btn.style.boxShadow = '0 4px 12px rgba(28, 187, 180, 0.2)';
                            } else {
                                btn.classList.remove('active');
                                btn.style.backgroundColor = '#F9F9F9';
                                btn.style.color = 'var(--charcoal)';
                                btn.style.borderColor = '#ECECEC';
                                btn.style.boxShadow = 'none';
                            }
                        });
                        
                        if (roomTypeData[roomId]) {
                            for (let i = 0; i < 5; i++) {
                                const imgEl = document.getElementById('gallery-img-' + i);
                                if (imgEl) {
                                    imgEl.src = roomTypeData[roomId].images[i];
                                    imgEl.alt = roomTypeData[roomId].nama;
                                }
                                
                                const containerEl = document.getElementById('gallery-item-' + i);
                                if (containerEl) {
                                    containerEl.setAttribute('onclick', `bukaModalLightbox('${roomTypeData[roomId].images[i]}', '${roomTypeData[roomId].nama}')`);
                                }
                            }

                            const gallery = document.querySelector('.lodging-detail-gallery');
                            if (gallery) {
                                gallery.scrollLeft = 0;
                            }
                        }

                        updateBookingCard();
                    }

                    document.addEventListener("DOMContentLoaded", function() {
                        // Set initial duration select value if the element exists
                        const durSelect = document.getElementById('duration-select');
                        if (durSelect) {
                            durSelect.value = currentDuration;
                        }

                        // Set initial active room tab style
                        const activeBtn = document.querySelector('.room-type-tab-btn.active');
                        if (activeBtn) {
                            activeBtn.style.backgroundColor = 'var(--primary-teal)';
                            activeBtn.style.color = '#ffffff';
                            activeBtn.style.borderColor = 'var(--primary-teal)';
                            activeBtn.style.boxShadow = '0 4px 12px rgba(28, 187, 180, 0.2)';
                        }

                        // Run initial update to sync with duration
                        updateBookingCard();
                        if (typeof renderItineraryDetail === 'function') {
                            renderItineraryDetail(currentDuration);
                        }

                        // Preload images
                        if (typeof roomTypeData !== 'undefined') {
                            for (const key in roomTypeData) {
                                if (roomTypeData.hasOwnProperty(key)) {
                                    roomTypeData[key].images.forEach(src => {
                                        const img = new Image();
                                        img.src = src;
                                    });
                                }
                            }
                        }
                    });
                    </script>

                    <style>
                    /* Styling room type tab buttons hover effect */
                    .room-type-tab-btn:not(.active):hover {
                        background-color: rgba(28, 187, 180, 0.05) !important;
                        border-color: var(--primary-teal) !important;
                        color: var(--primary-teal) !important;
                    }
                    </style>
                </div>



                <!-- Tentang Penginapan (Detailed Description) -->
                <div>
                    <h2 style="font-size: 20px; font-weight: 700; color: var(--dark-gray); border-left: 4px solid var(--primary-teal); padding-left: 12px; margin-bottom: 16px; text-align: left;">Tentang Penginapan</h2>
                    <div class="detail-desc-text" style="font-size: 15px; color: var(--charcoal); line-height: 25px;"><?php echo format_detail_deskripsi($penginapan['detail_deskripsi']); ?></div>
                </div>

                <!-- Rencana Perjalanan (Itinerary) -->
                <div class="itinerary-section">
                    <h2>Rencana Perjalanan (Itinerary)</h2>
                    
                    <!-- Day Navigation Tabs -->
                    <div class="itinerary-tabs" id="itinerary-tabs-container">
                        <!-- Navigation buttons will be generated by JS -->
                    </div>
                    
                    <!-- Itinerary Content Panels -->
                    <div class="itinerary-content" id="itinerary-panels-container">
                        <!-- Timeline panels will be generated by JS -->
                    </div>
                </div>

                <script>
                // Data itinerary dari PHP database
                let itinerarySourceData = <?php 
                    $it_data = isset($penginapan['itinerary']) ? $penginapan['itinerary'] : null;
                    echo ($it_data && is_array($it_data)) ? json_encode($it_data) : 'null';
                ?>;
                
                const defaultItineraryTemplate = {
                    "2D1N": [
                        {
                            "day": 1,
                            "desc": "Snorkeling & Sunset",
                            "activities": [
                                {"time": "09.30 - 10.30", "title": "Penjemputan & Check-in", "desc": "Penjemputan di pelabuhan Karimunjawa dan check-in penginapan."},
                                {"time": "10.30 - 12.30", "title": "Snorkeling di Terumbu Karang", "desc": "Menuju spot snorkeling terbaik untuk menikmati keindahan bawah laut."},
                                {"time": "12.30 - 14.00", "title": "Makan Siang Bakar Ikan", "desc": "Makan siang dengan hidangan ikan bakar khas di pinggir pantai."},
                                {"time": "14.00 - 16.00", "title": "Aktivitas Pantai Pasir Putih", "desc": "Bermain air, berfoto ria, atau bersantai di hamparan pasir putih yang bersih."},
                                {"time": "16.00 - 17.30", "title": "Sunset Tanjung Gelam", "desc": "Menikmati matahari terbenam yang eksotis di Pantai Tanjung Gelam."},
                                {"time": "19.00 - 20.00", "title": "Makan Malam", "desc": "Kembali ke penginapan untuk makan malam bersama."},
                                {"time": "20.00 - Selesai", "title": "Acara Bebas Alun-Alun", "desc": "Acara bebas berjalan-jalan ke pusat kuliner Alun-Alun Karimunjawa."}
                            ]
                        },
                        {
                            "day": 2,
                            "desc": "Bukit Love & Kepulangan",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.30 - 10.00", "title": "Wisata Bukit Love", "desc": "Menikmati pemandangan perbukitan dan foto di spot ikonik Bukit Love."},
                                {"time": "10.00 - 11.00", "title": "Packing & Check Out", "desc": "Persiapan bagasi barang bawaan dan check out penginapan."},
                                {"time": "11.00 - 12.00", "title": "Transfer ke Pelabuhan", "desc": "Diantar menuju Pelabuhan Karimunjawa untuk perjalanan pulang."}
                            ]
                        }
                    ],
                    "3D2N": [
                        {
                            "day": 1,
                            "desc": "Land Tour & Sunset",
                            "activities": [
                                {"time": "11.30 - 12.30", "title": "Penjemputan & Makan Siang", "desc": "Penjemputan peserta trip di Hotel atau Homestay tempat menginap, dilanjutkan dengan menikmati makan siang bersama."},
                                {"time": "12.30 - 13.30", "title": "Persiapan Tour Darat", "desc": "Briefing singkat bersama pemandu wisata mengenai rute perjalanan dan persiapan kelengkapan tour darat."},
                                {"time": "13.30 - 15.00", "title": "Destinasi Pertama: Pantai Bobi", "desc": "Mengunjungi Pantai Bobi, nikmati hamparan pasir putih bersih yang menawan dan pepohonan kelapa yang berjejer rapi di sepanjang pantai."},
                                {"time": "15.00 - 16.30", "title": "Lanjut ke Bukit Love", "desc": "Perjalanan dilanjutkan ke Bukit Love untuk berfoto ria di spot instagramable berlatar belakang tulisan \"LOVE\" raksasa dengan panorama laut lepas dari ketinggian."},
                                {"time": "16.30 - 17.30", "title": "Sunset di Pantai Tanjung Gelam", "desc": "Menikmati momen matahari terbenam yang eksotis di bawah naungan pohon kelapa miring yang sangat ikonik di Pantai Tanjung Gelam."},
                                {"time": "17.30 - 19.00", "title": "Kembali ke Penginapan", "desc": "Kembali ke penginapan/homestay untuk beristirahat, membersihkan diri, dan bersiap-siap."},
                                {"time": "19.00 - 20.00", "title": "Makan Malam", "desc": "Menyantap makan malam hangat yang disajikan oleh tim penginapan."},
                                {"time": "20.00 - Selesai", "title": "Malam Acara Bebas (Alun-Alun)", "desc": "Acara bebas di malam hari. Peserta dapat berjalan-jalan santai ke Alun-Alun Karimunjawa untuk berburu kuliner ikan bakar segar atau membeli suvenir khas."}
                            ]
                        },
                        {
                            "day": 2,
                            "desc": "Snorkeling & Marine Tour",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi & Persiapan", "desc": "Sarapan pagi di penginapan dan bersiap untuk berlayar menuju pulau-pulau."},
                                {"time": "08.30 - 10.30", "title": "Snorkeling Spot Pulau Cemara", "desc": "Meluncur ke spot terumbu karang indah dekat Pulau Cemara Kecil untuk berenang bersama aneka biota laut."},
                                {"time": "10.30 - 12.30", "title": "Snorkeling Spot Nemo", "desc": "Menikmati keindahan bawah laut berfoto bersama ikan badut (Nemo) di spot khusus terumbu karang."},
                                {"time": "12.30 - 14.00", "title": "Makan Siang Bakar Ikan di Pantai", "desc": "Menikmati makan siang barbecue ikan segar yang disiapkan tim di pinggir pantai pasir putih Pulau Cemara."},
                                {"time": "14.00 - 15.30", "title": "Singgah di Pulau Geleang", "desc": "Bermain air, bersantai, and berfoto di bentangan pasir putih yang bersih di Pulau Geleang."},
                                {"time": "15.30 - 17.00", "title": "Kunjungan Penangkaran Hiu", "desc": "Uji nyali berenang bersama ikan hiu jinak di kolam penangkaran dan berfoto bersama."},
                                {"time": "17.00 - 18.00", "title": "Perjalanan Kembali & Istirahat", "desc": "Perjalanan laut kembali menuju Pelabuhan Karimunjawa dan diantar ke penginapan untuk istirahat."},
                                {"time": "19.00 - 20.00", "title": "Makan Malam", "desc": "Makan malam hangat bersama di penginapan."},
                                {"time": "20.00 - Selesai", "title": "Acara Bebas Malam Hari", "desc": "Acara santai bebas untuk bersiap check out besok pagi."}
                            ]
                        },
                        {
                            "day": 3,
                            "desc": "Acara Bebas & Check Out",
                            "activities": [
                                {"time": "07.00 - 08.00", "title": "Sarapan Pagi", "desc": "Menikmati sarapan pagi terakhir di penginapan."},
                                {"time": "08.00 - 10.00", "title": "Berburu Oleh-oleh", "desc": "Membeli suvenir, kaos khas, kerajinan tangan, atau makanan ringan di pusat toko oleh-oleh."},
                                {"time": "10.00 - 11.00", "title": "Check Out Penginapan", "desc": "Persiapan bagasi barang bawaan, check-out penginapan, dan persiapan transfer."},
                                {"time": "11.00 - 12.00", "title": "Transfer ke Pelabuhan", "desc": "Diantar menuju Pelabuhan Karimunjawa oleh tim kendaraan penjemput."},
                                {"time": "12.00 - Selesai", "title": "Perjalanan Pulang", "desc": "Perjalanan kapal kembali menuju pelabuhan asal (Jepara/Semarang). Trip Selesai!"}
                            ]
                        }
                    ],
                    "4D3N": [
                        {
                            "day": 1,
                            "desc": "Land Tour & Sunset",
                            "activities": [
                                {"time": "11.30 - 12.30", "title": "Penjemputan & Makan Siang", "desc": "Penjemputan di pelabuhan/hotel, check-in, dan makan siang bersama."},
                                {"time": "13.30 - 15.00", "title": "Wisata Pantai Bobi", "desc": "Menikmati keindahan pasir putih Pantai Bobi."},
                                {"time": "15.00 - 16.30", "title": "Spot Foto Bukit Love", "desc": "Berfoto ria berlatar pemandangan perbukitan dan laut."},
                                {"time": "16.30 - 17.30", "title": "Sunset Tanjung Gelam", "desc": "Melihat sunset indah di Pantai Tanjung Gelam."},
                                {"time": "19.00 - Selesai", "title": "Makan Malam & Acara Bebas", "desc": "Makan malam hangat dan acara bebas santai."}
                            ]
                        },
                        {
                            "day": 2,
                            "desc": "Snorkeling Barat (Spot Cemara)",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.30 - 12.30", "title": "Snorkeling Terumbu Karang", "desc": "Snorkeling di spot terumbu karang indah dekat Pulau Cemara Kecil."},
                                {"time": "12.30 - 14.00", "title": "Makan Siang Bakar Ikan", "desc": "Piknik barbecue makan siang di Pulau Cemara Kecil."},
                                {"time": "14.00 - 15.30", "title": "Pulau Geleang", "desc": "Bersantai di Pulau Geleang."},
                                {"time": "15.30 - 17.00", "title": "Penangkaran Hiu", "desc": "Mengunjungi kolam penangkaran hiu."},
                                {"time": "19.00 - Selesai", "title": "Makan Malam", "desc": "Makan malam bersama di penginapan."}
                            ]
                        },
                        {
                            "day": 3,
                            "desc": "Snorkeling Timur (Spot Cilik)",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.30 - 12.30", "title": "Snorkeling Terumbu Karang", "desc": "Snorkeling di spot terumbu karang indah dekat Pulau Cilik/Tengah."},
                                {"time": "12.30 - 14.00", "title": "Makan Siang di Pulau Cilik", "desc": "Piknik makan siang di pantai Pulau Cilik."},
                                {"time": "14.00 - 15.30", "title": "Pantai Bobby Timur", "desc": "Menikmati pemandangan pantai timur Karimunjawa."},
                                {"time": "19.00 - Selesai", "title": "Makan Malam & Barbecue", "desc": "Makan malam spesial ulasan bersama kru."}
                            ]
                        },
                        {
                            "day": 4,
                            "desc": "Oleh-oleh & Check Out",
                            "activities": [
                                {"time": "07.00 - 08.00", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.00 - 10.00", "title": "Belanja Oleh-oleh", "desc": "Berburu suvenir khas Karimunjawa."},
                                {"time": "10.00 - 11.00", "title": "Check Out & Transfer", "desc": "Packing barang dan diantar ke pelabuhan."},
                                {"time": "12.00 - Selesai", "title": "Perjalanan Pulang", "desc": "Kapal kembali ke Jepara/Semarang. Trip selesai!"}
                            ]
                        }
                    ],
                    "HONEYMOON_2D1N": [
                        {
                            "day": 1,
                            "desc": "Arrival & Sunset Romantic",
                            "activities": [
                                {"time": "11.30 - 12.30", "title": "Penjemputan & Check-in", "desc": "Penjemputan VIP di pelabuhan dan check-in di penginapan bernuansa romantis."},
                                {"time": "13.30 - 16.00", "title": "Acara Santai Berdua", "desc": "Menikmati suasana penginapan dan beristirahat."},
                                {"time": "16.00 - 17.30", "title": "Sunset Romantic Tanjung Gelam", "desc": "Berfoto romantis di pantai kelapa miring Tanjung Gelam berlatar sunset."},
                                {"time": "19.00 - Selesai", "title": "Romantic Dinner Setup", "desc": "Makan malam romantis berdua dengan dekorasi bunga di pinggir laut/resort."}
                            ]
                        },
                        {
                            "day": 2,
                            "desc": "Private Snorkeling Trip & Check Out",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.30 - 11.30", "title": "Private Boat Snorkeling", "desc": "Berlayar dengan perahu sewaan khusus berdua menuju spot snorkeling terbaik."},
                                {"time": "11.30 - 12.30", "title": "Packing & Check Out", "desc": "Kembali ke penginapan, packing barang bawaan dan check out."},
                                {"time": "12.30 - Selesai", "title": "Transfer & Kepulangan", "desc": "Diantar ke pelabuhan untuk pulang ke kota asal. Trip selesai!"}
                            ]
                        }
                    ],
                    "HONEYMOON_3D2N": [
                        {
                            "day": 1,
                            "desc": "Arrival & Sunset Romantic",
                            "activities": [
                                {"time": "11.30 - 12.30", "title": "Penjemputan & Check-in", "desc": "Penjemputan VIP di pelabuhan dan check-in di penginapan bernuansa romantis."},
                                {"time": "13.30 - 16.00", "title": "Acara Santai Berdua", "desc": "Menikmati suasana penginapan dan beristirahat."},
                                {"time": "16.00 - 17.30", "title": "Sunset Romantic Tanjung Gelam", "desc": "Berfoto romantis di pantai kelapa miring Tanjung Gelam berlatar sunset."},
                                {"time": "19.00 - Selesai", "title": "Romantic Dinner Setup", "desc": "Makan malam romantis berdua dengan dekorasi bunga di pinggir laut/resort."}
                            ]
                        },
                        {
                            "day": 2,
                            "desc": "Private Snorkeling Trip",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.30 - 12.30", "title": "Private Boat Snorkeling", "desc": "Berlayar dengan perahu sewaan khusus berdua menuju spot snorkeling terbaik."},
                                {"time": "12.30 - 14.30", "title": "Romantic Picnic Lunch", "desc": "Makan siang piknik privat berdua di pulau terpencil tanpa gangguan pengunjung lain."},
                                {"time": "14.30 - 16.30", "title": "Singgah Pulau Pasir Putih", "desc": "Berjalan berdua menyusuri hamparan pasir putih."},
                                {"time": "19.00 - Selesai", "title": "Makan Malam Rileks", "desc": "Makan malam hangat di penginapan."}
                            ]
                        },
                        {
                            "day": 3,
                            "desc": "Souvenir & Check Out",
                            "activities": [
                                {"time": "07.00 - 08.00", "title": "Sarapan Pagi", "desc": "Sarapan pagi bersama di penginapan."},
                                {"time": "08.00 - 10.00", "title": "Bukit Love & Souvenir", "desc": "Mengunjungi Bukit Love berfoto dan membeli kenang-kenangan suvenir berdua."},
                                {"time": "10.00 - 11.00", "title": "Packing & Check Out", "desc": "Check-out penginapan."},
                                {"time": "11.00 - Selesai", "title": "Transfer & Kepulangan", "desc": "Diantar ke pelabuhan untuk pulang ke kota asal. Trip selesai!"}
                            ]
                        }
                    ],
                    "HONEYMOON_3D2N_SMG": [
                        {
                            "day": 1,
                            "desc": "Arrival & Sunset Romantic",
                            "activities": [
                                {"time": "11.30 - 12.30", "title": "Penjemputan & Check-in", "desc": "Penjemputan VIP di pelabuhan dan check-in di penginapan bernuansa romantis."},
                                {"time": "13.30 - 16.00", "title": "Acara Santai Berdua", "desc": "Menikmati suasana penginapan dan beristirahat."},
                                {"time": "16.00 - 17.30", "title": "Sunset Romantic Tanjung Gelam", "desc": "Berfoto romantis di pantai kelapa miring Tanjung Gelam berlatar sunset."},
                                {"time": "19.00 - Selesai", "title": "Romantic Dinner Setup", "desc": "Makan malam romantis berdua dengan dekorasi bunga di pinggir laut/resort."}
                            ]
                        },
                        {
                            "day": 2,
                            "desc": "Private Snorkeling Trip",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.30 - 12.30", "title": "Private Boat Snorkeling", "desc": "Berlayar dengan perahu sewaan khusus berdua menuju spot snorkeling terbaik."},
                                {"time": "12.30 - 14.30", "title": "Romantic Picnic Lunch", "desc": "Makan siang piknik privat berdua di pulau terpencil tanpa gangguan pengunjung lain."},
                                {"time": "14.30 - 16.30", "title": "Singgah Pulau Pasir Putih", "desc": "Berjalan berdua menyusuri hamparan pasir putih."},
                                {"time": "19.00 - Selesai", "title": "Makan Malam Rileks", "desc": "Makan malam hangat di penginapan."}
                            ]
                        },
                        {
                            "day": 3,
                            "desc": "Souvenir & Check Out (Semarang)",
                            "activities": [
                                {"time": "07.00 - 08.00", "title": "Sarapan Pagi", "desc": "Sarapan pagi bersama di penginapan."},
                                {"time": "08.00 - 10.00", "title": "Bukit Love & Souvenir", "desc": "Mengunjungi Bukit Love berfoto dan membeli kenang-kenangan suvenir berdua."},
                                {"time": "10.00 - 11.00", "title": "Packing & Check Out", "desc": "Check-out penginapan."},
                                {"time": "11.00 - Selesai", "title": "Transfer & Kepulangan (Semarang)", "desc": "Diantar ke pelabuhan untuk pulang menggunakan kapal cepat menuju Semarang. Trip selesai!"}
                            ]
                        }
                    ],
                    "HONEYMOON_4D3N": [
                        {
                            "day": 1,
                            "desc": "Arrival & Sunset Romantic",
                            "activities": [
                                {"time": "11.30 - 12.30", "title": "Penjemputan & Check-in", "desc": "Penjemputan VIP di pelabuhan dan check-in di penginapan bernuansa romantis."},
                                {"time": "13.30 - 16.00", "title": "Acara Santai Berdua", "desc": "Menikmati suasana penginapan dan beristirahat."},
                                {"time": "16.00 - 17.30", "title": "Sunset Romantic Tanjung Gelam", "desc": "Berfoto romantis di pantai kelapa miring Tanjung Gelam berlatar sunset."},
                                {"time": "19.00 - Selesai", "title": "Romantic Dinner Setup", "desc": "Makan malam romantis berdua dengan dekorasi bunga di pinggir laut/resort."}
                            ]
                        },
                        {
                            "day": 2,
                            "desc": "Private Snorkeling Trip",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi di penginapan."},
                                {"time": "08.30 - 12.30", "title": "Private Boat Snorkeling", "desc": "Berlayar dengan perahu sewaan khusus berdua menuju spot snorkeling terbaik."},
                                {"time": "12.30 - 14.30", "title": "Romantic Picnic Lunch", "desc": "Makan siang piknik privat berdua di pulau terpencil tanpa gangguan pengunjung lain."},
                                {"time": "14.30 - 16.30", "title": "Singgah Pulau Pasir Putih", "desc": "Berjalan berdua menyusuri hamparan pasir putih."},
                                {"time": "19.00 - Selesai", "title": "Makan Malam Rileks", "desc": "Makan malam hangat di penginapan."}
                            ]
                        },
                        {
                            "day": 3,
                            "desc": "Mangrove Forest & Sunset",
                            "activities": [
                                {"time": "07.30 - 08.30", "title": "Sarapan Pagi", "desc": "Sarapan pagi bersama di penginapan."},
                                {"time": "09.00 - 12.00", "title": "Tracking Mangrove Forest", "desc": "Berjalan santai menyusuri jembatan kayu di dalam hutan mangrove yang sejuk dan asri."},
                                {"time": "12.00 - 14.00", "title": "Makan Siang Romantis", "desc": "Makan siang santai bersama pasangan di restoran lokal pilihan."},
                                {"time": "15.00 - 17.30", "title": "Sunset Spot Bukit Anora", "desc": "Menikmati pemandangan senja yang menakjubkan dari puncak Bukit Anora berdua."},
                                {"time": "19.00 - Selesai", "title": "Makan Malam & Acara Bebas", "desc": "Makan malam di penginapan dan menikmati malam terakhir Karimunjawa."}
                            ]
                        },
                        {
                            "day": 4,
                            "desc": "Souvenir & Check Out",
                            "activities": [
                                {"time": "07.00 - 08.00", "title": "Sarapan Pagi", "desc": "Sarapan pagi bersama di penginapan."},
                                {"time": "08.00 - 10.00", "title": "Bukit Love & Souvenir", "desc": "Mengunjungi Bukit Love berfoto dan membeli suvenir khas berdua."},
                                {"time": "10.00 - 11.00", "title": "Packing & Check Out", "desc": "Check-out penginapan."},
                                {"time": "11.00 - Selesai", "title": "Transfer & Kepulangan", "desc": "Diantar ke pelabuhan untuk pulang ke kota asal. Trip selesai!"}
                            ]
                        }
                    ]
                };

                const activeItineraryData = itinerarySourceData || defaultItineraryTemplate;
                
                // Ensure all keys exist in activeItineraryData
                const keys = ['2D1N', '3D2N', '4D3N', 'HONEYMOON_2D1N', 'HONEYMOON_3D2N', 'HONEYMOON_3D2N_SMG', 'HONEYMOON_4D3N'];
                keys.forEach(k => {
                    if (!activeItineraryData[k] || !Array.isArray(activeItineraryData[k])) {
                        activeItineraryData[k] = defaultItineraryTemplate[k];
                    }
                });

                function renderItineraryDetail(duration) {
                    const tabsContainer = document.getElementById('itinerary-tabs-container');
                    const panelsContainer = document.getElementById('itinerary-panels-container');
                    
                    if (!tabsContainer || !panelsContainer) return;
                    
                    tabsContainer.innerHTML = '';
                    panelsContainer.innerHTML = '';
                    
                    const days = activeItineraryData[duration] || [];
                    
                    if (days.length === 0) {
                        tabsContainer.style.display = 'none';
                        panelsContainer.innerHTML = '<p style="text-align:center; padding:30px 10px; color:var(--medium-gray);">Rencana perjalanan belum tersedia untuk paket ini.</p>';
                        return;
                    }
                    
                    tabsContainer.style.display = 'flex';
                    
                    days.forEach((day, idx) => {
                        const activeClass = idx === 0 ? 'active' : '';
                        
                        // Render Navigation Tab
                        const tabBtn = document.createElement('button');
                        tabBtn.className = `itinerary-tab-btn ${activeClass}`;
                        tabBtn.setAttribute('data-day', day.day);
                        tabBtn.setAttribute('onclick', `switchDay(${day.day})`);
                        tabBtn.innerHTML = `
                            <span class="day-num">DAY ${day.day}</span>
                            <span class="day-desc">${escapeHTML(day.desc || '')}</span>
                        `;
                        tabsContainer.appendChild(tabBtn);
                        
                        // Render Content Panel
                        const panel = document.createElement('div');
                        panel.id = `day-${day.day}-content`;
                        panel.className = `day-content ${activeClass}`;
                        
                        let activitiesHTML = '';
                        const activities = day.activities || [];
                        
                        activities.forEach(act => {
                            activitiesHTML += `
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">${escapeHTML(act.time || '')}</div>
                                        <h4 class="timeline-title">${escapeHTML(act.title || '')}</h4>
                                        <p class="timeline-desc">${escapeHTML(act.desc || '')}</p>
                                    </div>
                                </div>
                            `;
                        });
                        
                        panel.innerHTML = `
                            <div class="day-header-meta">
                                <span class="time-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    ${escapeHTML(day.time_range || '07.30 - Selesai')}
                                </span>
                                ${day.session ? `<span class="session-badge">${escapeHTML(day.session)}</span>` : ''}
                            </div>
                            <div class="timeline">
                                ${activitiesHTML}
                            </div>
                        `;
                        
                        panelsContainer.appendChild(panel);
                    });
                }

                function escapeHTML(str) {
                    if (!str) return '';
                    return str
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }

                function switchDay(dayNum) {
                    document.querySelectorAll('.itinerary-tab-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.querySelectorAll('.day-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    const activeBtn = document.querySelector(`.itinerary-tab-btn[data-day="${dayNum}"]`);
                    if (activeBtn) activeBtn.classList.add('active');
                    
                    const activePanel = document.getElementById(`day-${dayNum}-content`);
                    if (activePanel) activePanel.classList.add('active');
                }
                </script>

                <!-- Info Tambahan Paket -->
                <div class="lodging-features-card" style="margin-top: 30px;">
                    <div class="lodging-features-title" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                        <span>Informasi Tambahan Paket</span>
                    </div>
                    
                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--dark-gray); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-teal)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Fasilitas yang di dapat paket honeymoon:
                        </h3>
                        <ul style="list-style-type: disc; margin-left: 20px; color: var(--charcoal); font-size: 14.5px; line-height: 1.8;">
                            <li>Tiket express bahari pulang pergi sesuai paket yang di pilih</li>
                            <li>Transportasi selama di karimunjawa ( inova / avanza )</li>
                            <li>Hotel sesuai paket yang di pilih</li>
                            <li>Makan full board (pagi, siang, malam) sesuai paket yang di pilih</li>
                            <li>Tour leader</li>
                            <li>Asuransi selama wisata berlangsung</li>
                            <li>Tour darat (mobil inova / avanza )</li>
                            <li>Tour laut privat 1x</li>
                            <li>Snorkeling equipment & life jacket</li>
                            <li>Guide HPI lesensi & Guide Karimunjawa journey</li>
                            <li>Welcome drink kelapa muda</li>
                            <li>Candle Dinner dari team karimunjawa journey</li>
                            <li>Camera underwater, gopro hero, dome, sony mirolles, dan drone dji</li>
                            <li>P3K</li>
                            <li>BBQ di pulau, dan air mineral</li>
                            <li>Tiket masuk wisata & retribusi</li>
                            <li>Foto & video dokumentasi</li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--dark-gray); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-teal)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                            Perlengkapan yang harus dibawa di Karimunjawa:
                        </h3>
                        <ul style="list-style-type: disc; margin-left: 20px; color: var(--charcoal); font-size: 14.5px; line-height: 1.8;">
                            <li>Tas daypak (koper tidak disarankan karna harus naik turun kapal)</li>
                            <li>Pakaian ganti secukupnya</li>
                            <li>Baju mudah kering untuk berenang</li>
                            <li>Sunblok & obat obatan pribadi</li>
                            <li>Flashdisk untuk copy foto & video dokumentasi (16GB)</li>
                            <li>Lotion anti nyamuk</li>
                            <li>Alat pancing bila ingin memancing</li>
                            <li>Cash money (dikarimujawa hanya ada BRI/ATM bersama)</li>
                            <li>Kacamata hitam, topi, dll.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--dark-gray); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary-teal)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            PAKET HONEYMOON KARIMUNJAWA – TEMPAT YANG DI KUNJUNGI
                        </h3>
                        <ul style="list-style-type: disc; margin-left: 20px; color: var(--charcoal); font-size: 14.5px; line-height: 1.8;">
                            <li>Pulau menjangan kecil</li>
                            <li>Pantai tanjung gelam</li>
                            <li>Bukit love</li>
                            <li>Pantai pancuran ( sunset )</li>
                            <li>Tracking hutan mangrove</li>
                            <li>Bukit anora</li>
                            <li>Pantai tanjung gelam</li>
                            <li>Pulau menjangan kecil</li>
                            <li>Pulau geleyang</li>
                            <li>Pulau cemara kecil</li>
                            <li>Pulau cemara besar</li>
                            <li>Pulau cilik</li>
                            <li>Gosong cemara kecil</li>
                            <li>Gosong tengah</li>
                        </ul>
                    </div>
                </div>

                <!-- Testimoni & Ulasan Section -->
                <div class="lodging-features-card" id="testimoni-paket" style="margin-top: 30px;">
                    <div class="lodging-features-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <span>Testimoni & Ulasan</span>
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 15px; font-weight: normal; color: var(--charcoal);">
                            <span class="stars-gold" style="color: var(--warm-gold); font-size: 18px;">
                                <?php
                                $rounded_rating = round($rating_rata_rata);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rounded_rating) {
                                        echo '&#9733;';
                                    } else {
                                        echo '<span style="color:#DDDDDD;">&#9733;</span>';
                                    }
                                }
                                ?>
                            </span>
                            <strong><?php echo $rating_rata_rata; ?></strong> (<?php echo $jumlah_ulasan; ?> ulasan)
                        </div>
                    </div>
                    
                    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                        <div class="review-alert review-alert-success" style="background-color: #E2F0D9; border: 1px solid #385723; color: #385723; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                            <strong>Berhasil!</strong> Ulasan Anda untuk penginapan ini telah berhasil disimpan dan diterbitkan.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($review_error)): ?>
                        <div class="review-alert review-alert-danger" style="background-color: #FADBD8; border: 1px solid #C0392B; color: #C0392B; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                            <strong>Error:</strong> <?php echo $review_error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Daftar Ulasan -->
                    <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px;">
                        <?php if ($jumlah_ulasan > 0): ?>
                            <?php foreach ($lodging_reviews as $idx => $testi): ?>
                                <div style="background-color: rgba(28, 187, 180, 0.03); border: 1px solid #ECECEC; border-radius: 8px; padding: 16px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div style="color: var(--warm-gold); font-size: 14px;">
                                            <?php
                                            $bintang_ulasan = isset($testi['bintang']) ? intval($testi['bintang']) : 5;
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $bintang_ulasan) {
                                                    echo "&#9733;";
                                                } else {
                                                    echo "<span style='color:#DDDDDD;'>&#9733;</span>";
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--light-gray);">
                                            <?php echo isset($testi['tanggal']) ? $testi['tanggal'] : date('Y-m-d'); ?>
                                        </div>
                                    </div>
                                    <p style="font-style: italic; font-size: 14px; line-height: 20px; color: var(--charcoal); margin: 0 0 10px 0;">"<?php echo $testi['ulasan']; ?>"</p>
                                    <?php if (!empty($testi['balasan'])): ?>
                                        <div style="background-color: rgba(28, 187, 180, 0.05); border-left: 3px solid var(--primary-teal); padding: 8px 12px; margin-bottom: 12px; border-radius: 6px; font-size: 13px; text-align: left; line-height: 1.5; color: var(--charcoal);">
                                            <strong style="color: var(--primary-teal); display: block; font-size: 11px; font-weight: 700; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;">Balasan Admin:</strong>
                                            <?php echo htmlspecialchars($testi['balasan']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background-color: var(--primary-teal); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px; text-transform: uppercase;">
                                            <?php echo substr($testi['nama'], 0, 1); ?>
                                        </div>
                                        <div style="font-size: 12px; font-weight: 700; color: var(--primary-teal);">
                                            <?php echo $testi['nama']; ?> <span style="font-weight: 400; color: var(--medium-gray);">dari <?php echo $testi['asal']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px 10px; color: var(--medium-gray); font-size: 14px; border: 1px dashed #DDD; border-radius: 8px;">
                                Belum ada ulasan untuk penginapan ini. Jadilah yang pertama memberikan ulasan!
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tombol Tulis Ulasan -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <button id="toggleReviewBtn" class="btn-review-primary" style="background-color: var(--primary-teal); color: white; border: none; padding: 10px 24px; font-size: 14px; font-weight: 700; border-radius: 6px; cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--primary-teal-hover)'" onmouseout="this.style.backgroundColor='var(--primary-teal)'" onclick="toggleReviewForm()">Tulis Ulasan Baru</button>
                    </div>

                    <!-- Form Tambah Ulasan (Toggled) -->
                    <div id="reviewFormContainer" class="review-form-box" style="display: none; background-color: #FAFAFA; border: 1px solid #ECECEC; border-radius: 8px; padding: 20px; box-shadow: none; margin-top: 20px;">
                        <h3 style="margin-bottom: 16px; border-bottom: 1px solid var(--very-light-gray); padding-bottom: 8px; font-size: 16px;">Berikan Ulasan Anda</h3>
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="tambah_ulasan_detail">

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label class="form-label" for="nama_review" style="display: block; font-size: 13px; font-weight: 700; color: var(--dark-gray); margin-bottom: 6px;">Nama Lengkap</label>
                                <input class="text-input" type="text" id="nama_review" name="nama_review" placeholder="Contoh: Sarah Aulia" required style="width: 100%; padding: 10px; border: 1px solid var(--very-light-gray); border-radius: 6px; font-size: 14px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label class="form-label" for="asal_review" style="display: block; font-size: 13px; font-weight: 700; color: var(--dark-gray); margin-bottom: 6px;">Asal Kota</label>
                                <input class="text-input" type="text" id="asal_review" name="asal_review" placeholder="Contoh: Bandung" required style="width: 100%; padding: 10px; border: 1px solid var(--very-light-gray); border-radius: 6px; font-size: 14px;">
                            </div>

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label class="form-label" style="display: block; font-size: 13px; font-weight: 700; color: var(--dark-gray); margin-bottom: 6px;">Rating Pelayanan</label>
                                <div class="rating-input-group">
                                    <div class="star-rating-form">
                                        <input type="radio" id="bintang5_det" name="bintang" value="5" required />
                                        <label for="bintang5_det" title="Sangat Puas">&#9733;</label>
                                        <input type="radio" id="bintang4_det" name="bintang" value="4" />
                                        <label for="bintang4_det" title="Puas">&#9733;</label>
                                        <input type="radio" id="bintang3_det" name="bintang" value="3" />
                                        <label for="bintang3_det" title="Cukup Puas">&#9733;</label>
                                        <input type="radio" id="bintang2_det" name="bintang" value="2" />
                                        <label for="bintang2_det" title="Kurang Puas">&#9733;</label>
                                        <input type="radio" id="bintang1_det" name="bintang" value="1" />
                                        <label for="bintang1_det" title="Sangat Kecewa">&#9733;</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label class="form-label" for="ulasan_review" style="display: block; font-size: 13px; font-weight: 700; color: var(--dark-gray); margin-bottom: 6px;">Ulasan Anda</label>
                                <textarea class="text-input" id="ulasan_review" name="ulasan_review" rows="4" placeholder="Tuliskan pengalaman menyenangkan Anda menginap di sini..." required style="width: 100%; padding: 10px; border: 1px solid var(--very-light-gray); border-radius: 6px; font-size: 14px; font-family: inherit; resize: vertical;"></textarea>
                            </div>

                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <button type="button" class="btn-review-secondary" style="background-color: transparent; border: 1px solid var(--light-gray); color: var(--charcoal); padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 700; cursor: pointer;" onclick="toggleReviewForm()">Batal</button>
                                <button type="submit" class="btn-review-primary" style="background-color: var(--primary-teal); border: none; color: white; padding: 8px 20px; border-radius: 6px; font-size: 14px; font-weight: 700; cursor: pointer;" onmouseover="this.style.backgroundColor='var(--primary-teal-hover)'" onmouseout="this.style.backgroundColor='var(--primary-teal)'">Kirim Ulasan</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: left;">
                    <a href="<?php echo $base_url; ?>index.php#penginapan" class="btn-back" style="font-size: 15px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        KEMBALI KE SEMUA PENGINAPAN
                    </a>
                </div>

            </div>

            <!-- Right Column: Sidebar / Booking -->
            <aside class="detail-sidebar">
                <div class="sticky-booking-card" style="box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); border-radius: 12px; border: 1px solid #ECECEC;">
                    
                    <!-- Top Promo Badge -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700; color: #FFF; background-color: var(--dark-gray); padding: 4px 10px; border-radius: 4px; letter-spacing: 0.5px;">TOP SELLER</span>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span style="color: var(--warm-gold); font-size: 14px;">
                                <?php
                                $rounded_rating = round($rating_rata_rata);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rounded_rating) {
                                        echo '&#9733;';
                                    } else {
                                        echo '<span style="color:#DDDDDD;">&#9733;</span>';
                                    }
                                }
                                ?>
                            </span>
                            <span style="font-size: 12px; color: var(--light-gray); font-weight: 500;">(<?php echo $rating_rata_rata; ?>)</span>
                        </div>
                    </div>
                    
                    <!-- Price Box -->
                    <div style="margin-top: 5px;">
                        <?php 
                        $harga_text = $penginapan['harga']; 
                        if (!empty($penginapan['tipe_kamar'])) {
                            $harga_text = $penginapan['tipe_kamar'][0]['harga'];
                        }
                        $is_mulai = false;
                        if (stripos($harga_text, 'Mulai') !== false) {
                            $is_mulai = true;
                            $harga_text = trim(str_ireplace('Mulai', '', $harga_text));
                        }
                        $parts = explode('/', $harga_text);
                        $price_val = trim($parts[0]);
                        $price_val = str_ireplace('Rp.', 'Rp', $price_val);
                        $price_val = str_ireplace('Rp', 'Rp ', $price_val);
                        $price_val = preg_replace('/\s+/', ' ', $price_val);
                        $price_unit = isset($parts[1]) ? trim($parts[1]) : 'pax';
                        ?>
                        <div style="font-size: 11px; color: var(--light-gray); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                            <?php echo $is_mulai ? 'Harga Mulai Dari' : 'Harga'; ?>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 4px; flex-wrap: wrap;">
                            <span id="sidebar-price" style="font-size: 22px; font-weight: 700; color: var(--primary-teal); line-height: 1.2; letter-spacing: -0.5px;"><?php echo $price_val; ?></span>
                            <span id="sidebar-price-unit" style="font-size: 13px; color: var(--medium-gray); font-weight: 500;">/ <?php echo $price_unit; ?></span>
                        </div>
                    </div>
                    
                    <!-- Detail Fields -->
                    <div class="booking-card-details" style="margin-top: 14px;">
                        <div class="booking-card-detail-item">
                            <span class="booking-card-detail-label">Lokasi</span>
                            <span class="booking-card-detail-val" style="font-size: 13px;"><?php echo str_replace(', Karimunjawa', '', $penginapan['lokasi']); ?></span>
                        </div>
                        <div class="booking-card-detail-item" style="flex-direction: column; align-items: stretch; gap: 8px;">
                            <span class="booking-card-detail-label" style="margin-bottom: 2px;">Pilih Durasi</span>
                            <div style="position: relative; width: 100%;">
                                <select id="duration-select" onchange="switchDuration(this.value)" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #ECECEC; background-color: #FFF; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--charcoal); cursor: pointer; outline: none; transition: all 0.2s ease; -webkit-appearance: none; -moz-appearance: none; appearance: none; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    <option value="3D2N">3D2N / 3 Hari 2 Malam</option>
                                    <option value="2D1N">2D1N / 2 Hari 1 Malam</option>
                                    <option value="4D3N">4D3N / 4 Hari 3 Malam</option>
                                    <option value="HONEYMOON_2D1N">Honeymoon 2D1N / 2 Hari 1 Malam</option>
                                    <option value="HONEYMOON_3D2N">Honeymoon 3D2N / 3 Hari 2 Malam</option>
                                    <option value="HONEYMOON_3D2N_SMG">Honeymoon 3D2N Semarang</option>
                                    <option value="HONEYMOON_4D3N">Honeymoon 4D3N / 4 Hari 3 Malam</option>
                                </select>
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; font-size: 10px; color: var(--medium-gray);">▼</span>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Bikin pesan custom WA terenkripsi yang estetik
                    $pesan_wa = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "*.%0A%0AMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                    if (!empty($penginapan['tipe_kamar'])) {
                        $pesan_wa = "Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $penginapan['tipe_kamar'][0]['nama'] . "*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                        $pesan_wa = urlencode($pesan_wa);
                    }
                    ?>
                    <a id="sidebar-booking-btn" href="https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo $pesan_wa; ?>" target="_blank" rel="noopener noreferrer" class="btn-booking-wa" style="background-color: #0F2D2E; border-radius: 8px; font-family: Tahoma, sans-serif; box-shadow: 0 4px 12px rgba(15, 45, 46, 0.15);">
                        Pesan Sekarang via WA
                    </a>

                    <div class="quick-help-box" style="background-color: #FAFAFA; border: 1px solid #F0F0F0; border-radius: 8px; padding: 14px; font-size: 12px; color: var(--medium-gray);">
                        <div class="quick-help-title" style="font-weight: 700; color: var(--dark-gray); font-size: 13px;">
                            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary-teal);"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            Butuh Bantuan?
                        </div>
                        Silakan hubungi kami untuk kustomisasi sewa, pemesanan rombongan, atau rekomendasi jadwal penyeberangan kapal.
                    </div>
                </div>
            </aside>

        </div>
    </main>

<?php else: ?>
    <!-- Error Not Found State -->
    <main class="container" style="text-align: center; padding: 120px 16px;">
        <div style="font-size: 80px; color: var(--coral-red); margin-bottom: 24px;">☹</div>
        <h1 style="margin-bottom: 16px;">Penginapan Tidak Ditemukan</h1>
        <p style="margin-bottom: 32px; font-size: 16px; color: var(--medium-gray);">Maaf, penginapan yang Anda cari tidak tersedia atau telah dihapus.</p>
        <a href="<?php echo $base_url; ?>index.php" class="btn-primary-large">Kembali ke Beranda</a>
    </main>
<?php endif; ?>

<?php
// Muat komponen footer dan script
include_once $base_url . 'footer.php';
?>
