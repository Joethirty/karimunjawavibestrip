<?php
$base_url = '../';
// Hubungkan komponen data konfigurasi
require_once $base_url . 'config.php';

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

// Judul halaman dinamis untuk SEO
$page_title = $penginapan ? $penginapan['nama'] . " - Penginapan Karimunjawa" : "Penginapan Tidak Ditemukan - Karimunjawavibestrip";

// Muat komponen header visual
include_once $base_url . 'header.php';
?>

<div class="btn-back-container">
    <a href="<?php echo $base_url; ?>index.php#penginapan" class="btn-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Kembali ke Semua Penginapan
    </a>
</div>

<?php if ($penginapan): ?>
    <!-- Main Container -->
    <main class="container" style="padding-top: 20px; max-width: 1200px; margin: 0 auto;">
        
        <!-- 5-Photo Grid Gallery (Spans full width at the top) -->
        <div class="lodging-detail-gallery">
            <!-- 1. Left Top Image -->
            <div class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $penginapan['foto_galeri'][1]; ?>', '<?php echo $penginapan['nama']; ?>')">
                <img src="<?php echo $base_url . $penginapan['foto_galeri'][1]; ?>" alt="<?php echo $penginapan['nama']; ?>">
            </div>
            
            <!-- 2. Middle Large Image (spans two rows) -->
            <div class="lodging-detail-gallery-item big-image" onclick="bukaModalLightbox('<?php echo $base_url . $penginapan['foto_galeri'][0]; ?>', '<?php echo $penginapan['nama']; ?>')">
                <img src="<?php echo $base_url . $penginapan['foto_galeri'][0]; ?>" alt="<?php echo $penginapan['nama']; ?>">
            </div>
            
            <!-- 3. Right Top Image -->
            <div class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $penginapan['foto_galeri'][2]; ?>', '<?php echo $penginapan['nama']; ?>')">
                <img src="<?php echo $base_url . $penginapan['foto_galeri'][2]; ?>" alt="<?php echo $penginapan['nama']; ?>">
            </div>
            
            <!-- 4. Left Bottom Image -->
            <div class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $penginapan['foto_galeri'][3]; ?>', '<?php echo $penginapan['nama']; ?>')">
                <img src="<?php echo $base_url . $penginapan['foto_galeri'][3]; ?>" alt="<?php echo $penginapan['nama']; ?>">
            </div>
            
            <!-- 5. Right Bottom Image -->
            <div class="lodging-detail-gallery-item" onclick="bukaModalLightbox('<?php echo $base_url . $penginapan['foto_galeri'][4]; ?>', '<?php echo $penginapan['nama']; ?>')">
                <img src="<?php echo $base_url . $penginapan['foto_galeri'][4]; ?>" alt="<?php echo $penginapan['nama']; ?>">
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
                </div>

                <!-- Horizontal Line of Meta Boxes with Icons -->
                <div class="lodging-meta-row">
                    <!-- Lokasi -->
                    <div class="lodging-meta-box">
                        <div class="lodging-meta-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <div class="lodging-meta-text">
                            <span class="lodging-meta-label">Lokasi</span>
                            <span class="lodging-meta-value"><?php echo str_replace(', Karimunjawa', '', $penginapan['lokasi']); ?></span>
                        </div>
                    </div>
                    <!-- Kapasitas -->
                    <div class="lodging-meta-box">
                        <div class="lodging-meta-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="lodging-meta-text">
                            <span class="lodging-meta-label">Kapasitas</span>
                            <span class="lodging-meta-value">2-3 Tamu / Kamar</span>
                        </div>
                    </div>
                    <!-- Tipe Stay -->
                    <div class="lodging-meta-box">
                        <div class="lodging-meta-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </div>
                        <div class="lodging-meta-text">
                            <span class="lodging-meta-label">Tipe Stay</span>
                            <span class="lodging-meta-value">Kamar Privat</span>
                        </div>
                    </div>
                    <!-- Konfirmasi -->
                    <div class="lodging-meta-box">
                        <div class="lodging-meta-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        </div>
                        <div class="lodging-meta-text">
                            <span class="lodging-meta-label">Status</span>
                            <span class="lodging-meta-value">Konfirmasi Instan</span>
                        </div>
                    </div>
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

                <!-- Fasilitas & Aturan Card (Styled like "What's Included") -->
                <div class="lodging-features-card">
                    <div class="lodging-features-title">Fasilitas & Kebijakan Stay</div>
                    
                    <div class="lodging-features-grid">
                        <!-- Fasilitas Kamar & Layanan -->
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: var(--primary-teal); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <span style="background-color: rgba(28, 187, 180, 0.1); width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">✓</span>
                                Fasilitas & Layanan
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($penginapan['fasilitas'] as $fasilitas): ?>
                                    <div class="lodging-feature-item">
                                        <span class="lodging-feature-bullet">&#10003;</span>
                                        <span><?php echo $fasilitas; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Kebijakan & Aturan Stay -->
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: var(--coral-red); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <span style="background-color: rgba(224, 79, 103, 0.1); width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--coral-red);">!</span>
                                Kebijakan & Aturan
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($penginapan['aturan'] as $aturan): ?>
                                    <div class="lodging-feature-item">
                                        <span class="lodging-feature-bullet" style="color: var(--coral-red); font-size: 13px;">&#9888;</span>
                                        <span><?php echo $aturan; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sidebar / Booking -->
            <aside class="detail-sidebar">
                <div class="sticky-booking-card" style="box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); border-radius: 12px; border: 1px solid #ECECEC;">
                    
                    <!-- Top Promo Badge -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700; color: #FFF; background-color: var(--dark-gray); padding: 4px 10px; border-radius: 4px; letter-spacing: 0.5px;">TOP SELLER</span>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span style="color: var(--warm-gold); font-size: 14px;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                            <span style="font-size: 12px; color: var(--light-gray); font-weight: 500;">(4.9)</span>
                        </div>
                    </div>
                    
                    <!-- Price Box -->
                    <div style="margin-top: 5px;">
                        <?php 
                        $harga_text = $penginapan['harga']; 
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
                            <?php echo $is_mulai ? 'Harga Mulai Dari' : 'Harga Paket'; ?>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 4px; flex-wrap: wrap;">
                            <span style="font-size: 22px; font-weight: 700; color: var(--primary-teal); line-height: 1.2; letter-spacing: -0.5px;"><?php echo $price_val; ?></span>
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
                    $pesan_wa = "Halo Karimunjawavibestrip, saya ingin menanyakan ketersediaan paket trip *" . $penginapan['nama'] . "*.%0A%0AMohon info ketersediaan slot tanggal stay, cara booking, dan fasilitas lainnya. Terima kasih!";
                    ?>
                    <a href="https://api.whatsapp.com/send?phone=<?php echo $nomor_whatsapp; ?>&text=<?php echo $pesan_wa; ?>" target="_blank" rel="noopener noreferrer" class="btn-booking-wa" style="background-color: #0F2D2E; border-radius: 8px; font-family: Tahoma, sans-serif; box-shadow: 0 4px 12px rgba(15, 45, 46, 0.15);">
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
