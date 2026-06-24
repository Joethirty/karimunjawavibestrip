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
            header("Location: " . $penginapan['id'] . ".php?status=success#testimoni-paket");
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
$default_rating = isset($default_ratings_map[$penginapan['id']]) ? $default_ratings_map[$penginapan['id']] : 4.8;
$rating_rata_rata = $jumlah_ulasan > 0 ? round($total_bintang / $jumlah_ulasan, 1) : $default_rating;

// Judul halaman dinamis untuk SEO
$page_title = $penginapan ? $penginapan['nama'] . " - Penginapan Karimunjawa" : "Penginapan Tidak Ditemukan - KarimunJawa Vibes Trip";

// Muat komponen header visual
include_once $base_url . 'header.php';
?>

<?php if ($penginapan): ?>
    <!-- Main Container -->
    <main class="container" style="padding-top: 80px; max-width: 1200px; margin: 0 auto;">
        
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

                        <!-- Script to handle room type switching -->
                        <script>
                        const roomTypeData = {
                            <?php foreach ($penginapan['tipe_kamar'] as $tipe): ?>
                            '<?php echo $tipe['id']; ?>': {
                                nama: '<?php echo $tipe['nama']; ?>',
                                images: [
                                    '<?php echo $base_url . $tipe['foto_galeri'][0]; ?>',
                                    '<?php echo $base_url . $tipe['foto_galeri'][1]; ?>',
                                    '<?php echo $base_url . $tipe['foto_galeri'][2]; ?>',
                                    '<?php echo $base_url . $tipe['foto_galeri'][3]; ?>',
                                    '<?php echo $base_url . $tipe['foto_galeri'][4]; ?>'
                                ],
                                price: '<?php echo str_replace(' / pax', '', $tipe['harga']); ?>',
                                waUrl: 'https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo urlencode("Halo KarimunJawa Vibes Trip, saya ingin menanyakan ketersediaan penginapan *" . $penginapan['nama'] . "* khusus dengan pilihan *" . $tipe['nama'] . "*.\n\nMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!"); ?>'
                            },
                            <?php endforeach; ?>
                        };

                        function switchRoomType(roomId) {
                            // Switch active states on selector buttons
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
                            
                            // Dynamically update Top Grid Gallery Images & Lightbox Click Handlers
                            if (roomTypeData[roomId]) {
                                for (let i = 0; i < 5; i++) {
                                    const imgEl = document.getElementById('gallery-img-' + i);
                                    if (imgEl) {
                                        imgEl.style.transition = 'opacity 0.25s ease-in-out';
                                        imgEl.style.opacity = '0';
                                        setTimeout(() => {
                                            imgEl.src = roomTypeData[roomId].images[i];
                                            imgEl.alt = roomTypeData[roomId].nama;
                                            imgEl.style.opacity = '1';
                                        }, 250);
                                    }
                                    
                                    const containerEl = document.getElementById('gallery-item-' + i);
                                    if (containerEl) {
                                        containerEl.setAttribute('onclick', `bukaModalLightbox('${roomTypeData[roomId].images[i]}', '${roomTypeData[roomId].nama}')`);
                                    }
                                }

                                // Update Sidebar price
                                const sidebarPrice = document.getElementById('sidebar-price');
                                if (sidebarPrice) {
                                    sidebarPrice.textContent = roomTypeData[roomId].price;
                                }

                                // Update Sidebar WA button link
                                const sidebarBtn = document.getElementById('sidebar-booking-btn');
                                if (sidebarBtn) {
                                    sidebarBtn.href = roomTypeData[roomId].waUrl;
                                }
                            }
                        }

                        // Set initial styles for active tab
                        document.addEventListener("DOMContentLoaded", function() {
                            const activeBtn = document.querySelector('.room-type-tab-btn.active');
                            if (activeBtn) {
                                activeBtn.style.backgroundColor = 'var(--primary-teal)';
                                activeBtn.style.color = '#ffffff';
                                activeBtn.style.borderColor = 'var(--primary-teal)';
                                activeBtn.style.boxShadow = '0 4px 12px rgba(28, 187, 180, 0.2)';
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
                    <?php endif; ?>
                </div>



                <!-- Tentang Penginapan (Detailed Description) -->
                <div>
                    <h2 style="font-size: 20px; font-weight: 700; color: var(--dark-gray); border-left: 4px solid var(--primary-teal); padding-left: 12px; margin-bottom: 16px; text-align: left;">Tentang Penginapan</h2>
                    <div class="detail-desc-text" style="font-size: 15px; color: var(--charcoal); line-height: 25px;"><?php echo $penginapan['detail_deskripsi']; ?></div>
                </div>

                <!-- Rencana Perjalanan (Itinerary) -->
                <div class="itinerary-section">
                    <h2>Rencana Perjalanan (Itinerary)</h2>
                    
                    <!-- Day Navigation Tabs -->
                    <div class="itinerary-tabs">
                        <button class="itinerary-tab-btn active" data-day="1" onclick="switchDay(1)">
                            <span class="day-num">DAY 1</span>
                            <span class="day-desc">Land Tour & Sunset</span>
                        </button>
                        <button class="itinerary-tab-btn" data-day="2" onclick="switchDay(2)">
                            <span class="day-num">DAY 2</span>
                            <span class="day-desc">Snorkeling & Marine Tour</span>
                        </button>
                        <button class="itinerary-tab-btn" data-day="3" onclick="switchDay(3)">
                            <span class="day-num">DAY 3</span>
                            <span class="day-desc">Acara Bebas & Check Out</span>
                        </button>
                    </div>
                    
                    <!-- Itinerary Content Panels -->
                    <div class="itinerary-content">
                        
                        <!-- DAY 1 CONTENT -->
                        <div id="day-1-content" class="day-content active">
                            <div class="day-header-meta">
                                <span class="time-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    11.30 - 17.30 WIB
                                </span>
                                <span class="session-badge">SIANG</span>
                            </div>
                            
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">11.30 - 12.30</div>
                                        <h4 class="timeline-title">Penjemputan & Makan Siang</h4>
                                        <p class="timeline-desc">Penjemputan peserta trip di Hotel atau Homestay tempat menginap, dilanjutkan dengan menikmati makan siang bersama.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">12.30 - 13.30</div>
                                        <h4 class="timeline-title">Persiapan Tour Darat</h4>
                                        <p class="timeline-desc">Briefing singkat bersama pemandu wisata mengenai rute perjalanan dan persiapan kelengkapan tour darat.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">13.30 - 15.00</div>
                                        <h4 class="timeline-title">Destinasi Pertama: Pantai Bobi</h4>
                                        <p class="timeline-desc">Mengunjungi Pantai Bobi, nikmati hamparan pasir putih bersih yang menawan dan pepohonan kelapa yang berjejer rapi di sepanjang pantai.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">15.00 - 16.30</div>
                                        <h4 class="timeline-title">Lanjut ke Bukit Love</h4>
                                        <p class="timeline-desc">Perjalanan dilanjutkan ke Bukit Love untuk berfoto ria di spot instagramable berlatar belakang tulisan "LOVE" raksasa dengan panorama laut lepas dari ketinggian.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">16.30 - 17.30</div>
                                        <h4 class="timeline-title">Sunset di Pantai Tanjung Gelam</h4>
                                        <p class="timeline-desc">Menikmati momen matahari terbenam yang eksotis di bawah naungan pohon kelapa miring yang sangat ikonik di Pantai Tanjung Gelam.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">17.30 - 19.00</div>
                                        <h4 class="timeline-title">Kembali ke Penginapan</h4>
                                        <p class="timeline-desc">Kembali ke penginapan/homestay untuk beristirahat, membersihkan diri, dan bersiap-siap.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">19.00 - 20.00</div>
                                        <h4 class="timeline-title">Makan Malam</h4>
                                        <p class="timeline-desc">Menyantap makan malam hangat yang disajikan oleh tim penginapan.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">20.00 - Selesai</div>
                                        <h4 class="timeline-title">Malam Acara Bebas (Alun-Alun)</h4>
                                        <p class="timeline-desc">Acara bebas di malam hari. Peserta dapat berjalan-jalan santai ke Alun-Alun Karimunjawa untuk berburu kuliner ikan bakar segar atau membeli suvenir khas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- DAY 2 CONTENT -->
                        <div id="day-2-content" class="day-content">
                            <div class="day-header-meta">
                                <span class="time-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    08.30 - 17.00 WIB
                                </span>
                                <span class="session-badge pagi">PAGI</span>
                            </div>
                            
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">08.30 - 09.00</div>
                                        <h4 class="timeline-title">Persiapan Tour Laut</h4>
                                        <p class="timeline-desc">Persiapan alat snorkeling, pelampung, briefing keselamatan, dan berjalan menuju dermaga perahu wisata.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">09.00 - 12.00</div>
                                        <h4 class="timeline-title">Spot Snorkeling Pertama & Nemo</h4>
                                        <p class="timeline-desc">Menuju spot snorkeling pertama untuk melihat terumbu karang indah dan bercengkrama langsung dengan gerombolan ikan badut (Nemo) yang lucu.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">12.00 - 13.30</div>
                                        <h4 class="timeline-title">Makan Siang di Pulau BBQ</h4>
                                        <p class="timeline-desc">Merapat ke pulau pasir putih untuk menikmati makan siang lezat prasmanan dengan menu utama BBQ ikan bakar segar khas Karimunjawa.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">13.30 - 15.00</div>
                                        <h4 class="timeline-title">Spot Snorkeling Kedua & Terumbu Karang</h4>
                                        <p class="timeline-desc">Melanjutkan perjalanan ke spot snorkeling kedua untuk mengeksplorasi keanekaragaman biota laut dan formasi terumbu karang warna-warni yang sehat.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">15.00 - 16.30</div>
                                        <h4 class="timeline-title">Mengunjungi Penangkaran Hiu</h4>
                                        <p class="timeline-desc">Berkunjung ke area penangkaran hiu. Rasakan sensasi menegangkan berfoto di dalam air bersama ikan hiu yang sudah jinak dan bersahabat.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">16.30 - 17.00</div>
                                        <h4 class="timeline-title">Kembali ke Penginapan</h4>
                                        <p class="timeline-desc">Meninggalkan penangkaran hiu, kembali berlayar ke pelabuhan, and diantar kembali menuju penginapan untuk beristirahat.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- DAY 3 CONTENT -->
                        <div id="day-3-content" class="day-content">
                            <div class="day-header-meta">
                                <span class="time-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    07.00 - 11.00 WIB
                                </span>
                                <span class="session-badge pagi">PAGI</span>
                            </div>
                            
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">07.00 - 09.30</div>
                                        <h4 class="timeline-title">Acara Bebas</h4>
                                        <p class="timeline-desc">Menikmati sarapan pagi khas Karimunjawa di penginapan. Setelahnya Anda memiliki waktu luang untuk berburu suvenir, kaos khas, atau sekadar berfoto di sekitar area penginapan.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">09.30 - 10.30</div>
                                        <h4 class="timeline-title">Persiapan Check Out</h4>
                                        <p class="timeline-desc">Peserta mulai berkemas barang bawaan dan menyelesaikan administrasi check out dengan pihak penginapan.</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-info">
                                        <div class="timeline-time">10.30 - 11.00</div>
                                        <h4 class="timeline-title">Transfer ke Pelabuhan & Trip Selesai</h4>
                                        <p class="timeline-desc">Peserta diantar menuju ke Pelabuhan Karimunjawa untuk proses boarding kapal penyeberangan kembali ke Jepara/Semarang. Perjalanan wisata bersama Karimunjawa Vibes Trip resmi selesai!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <script>
                function switchDay(dayNum) {
                    // Remove active class from all tabs
                    document.querySelectorAll('.itinerary-tab-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    
                    // Remove active class from all content panes
                    document.querySelectorAll('.day-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab and its content pane
                    document.querySelector(`.itinerary-tab-btn[data-day="${dayNum}"]`).classList.add('active');
                    document.getElementById(`day-${dayNum}-content`).classList.add('active');
                }
                </script>

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
                            <span style="font-size: 13px; color: var(--medium-gray); font-weight: 500;">/ <?php echo $price_unit; ?></span>
                        </div>
                    </div>
                    
                    <!-- Detail Fields -->
                    <div class="booking-card-details" style="margin-top: 14px;">
                        <div class="booking-card-detail-item">
                            <span class="booking-card-detail-label">Lokasi</span>
                            <span class="booking-card-detail-val" style="font-size: 13px;"><?php echo str_replace(', Karimunjawa', '', $penginapan['lokasi']); ?></span>
                        </div>
                        <div class="booking-card-detail-item">
                            <span class="booking-card-detail-label">Durasi</span>
                            <span class="booking-card-detail-val">3D2N / 3 Hari 2 Malam</span>
                        </div>
                    </div>

                    <!-- Key Amenities Row -->
                    <div class="booking-card-amenities">
                        <!-- Amenity 1 -->
                        <div class="booking-amenity-item">
                            <div class="booking-amenity-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"></path><path d="M1.42 9a16 16 0 0 1 21.16 0"></path><path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path><line x1="12" y1="20" x2="12.01" y2="20"></line></svg>
                            </div>
                            <span class="booking-amenity-label">Free Wi-Fi</span>
                        </div>
                        <!-- Amenity 2 -->
                        <div class="booking-amenity-item">
                            <div class="booking-amenity-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg>
                            </div>
                            <span class="booking-amenity-label">Kamar AC</span>
                        </div>
                        <!-- Amenity 3 -->
                        <div class="booking-amenity-item">
                            <div class="booking-amenity-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            </div>
                            <span class="booking-amenity-label">Breakfast</span>
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
