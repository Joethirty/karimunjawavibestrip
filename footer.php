<?php
if (!isset($base_url)) {
    $base_url = './';
}
?>
<footer class="site-footer">
    <!-- Wavy Top Divider -->
    <div class="footer-wave">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z" class="wave-path"></path>
        </svg>
    </div>
    
    <div class="container footer-content-grid">
        <!-- Brand Info -->
        <div class="footer-column brand-col">
            <div class="footer-logo">KarimunJawa Vibes Trip</div>
            <p class="brand-description">Penyedia reservasi penginapan, resort, homestay, dan villa resmi di Kepulauan Karimunjawa. Kami menghadirkan kenyamanan menginap terbaik untuk liburan Anda dan keluarga.</p>
            <div class="social-links-wrapper">
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-icon-link" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                </a>
                <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-icon-link" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                </a>
                <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="social-icon-link" aria-label="YouTube">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                </a>
                <a href="https://wa.me/62895361870926" target="_blank" rel="noopener noreferrer" class="social-icon-link" aria-label="WhatsApp">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.004 2C6.516 2 2.006 6.51 2.006 12c0 1.76.46 3.42 1.27 4.9L2 22l5.25-1.38c1.44.79 3.08 1.21 4.75 1.21 5.48 0 9.99-4.51 9.99-10S17.49 2 12.004 2zm3.87 13.88c-.22.61-1.28 1.2-1.77 1.25-.48.06-.97.11-3.09-.76-2.71-1.12-4.46-3.89-4.6-4.07-.13-.18-1.1-1.46-1.1-2.79 0-1.33.7-1.99.95-2.25.26-.26.56-.33.74-.33.19 0 .37.01.53.02.17.01.4.06.61.53.22.49.74 1.81.81 1.95.07.14.12.31.02.49-.09.18-.17.3-.35.5-.18.21-.37.37-.53.56-.17.18-.35.38-.15.72.2.34.88 1.45 1.88 2.34 1.29 1.15 2.38 1.5 2.72 1.67.34.17.54.14.74-.09.2-.23.87-1.01 1.1-1.35.23-.34.46-.29.78-.17.32.12 2.05 1.01 2.4 1.18.35.17.58.26.67.41.09.15.09.87-.13 1.48z"/></svg>
                </a>
            </div>
        </div>
        
        <!-- Navigation Links -->
        <div class="footer-column">
            <h4 class="footer-title">Jelajahi</h4>
            <ul class="footer-links">
                <li><a href="<?php echo $base_url; ?>index.php#home">Home</a></li>
                <li><a href="<?php echo $base_url; ?>index.php#penginapan">Pilihan Penginapan</a></li>
                <li><a href="<?php echo $base_url; ?>galeri.php">Galeri Foto</a></li>
                <li><a href="<?php echo $base_url; ?>index.php#testimoni">Ulasan Pengunjung</a></li>
                <li><a href="<?php echo $base_url; ?>index.php#kontak">Hubungi Kami</a></li>
            </ul>
        </div>
        
        <!-- Popular Packages -->
        <div class="footer-column">
            <h4 class="footer-title">Penginapan Populer</h4>
            <ul class="footer-links">
                <?php 
                global $daftar_penginapan;
                if (isset($daftar_penginapan) && is_array($daftar_penginapan)): 
                    foreach (array_slice($daftar_penginapan, 0, 3) as $p_item): 
                ?>
                    <li><a href="<?php echo $base_url; ?>detail-page/<?php echo $p_item['id']; ?>.php"><?php echo $p_item['nama']; ?></a></li>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </ul>
        </div>
        
        <!-- Contact Info -->
        <div class="footer-column contact-col">
            <h4 class="footer-title">Kontak & Kantor</h4>
            <ul class="footer-contact-info">
                <li>
                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span>Jl. Raya Karimunjawa, Jepara, Jawa Tengah, Indonesia</span>
                </li>
                <li>
                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>+62 895-3618-70926</span>
                </li>
                <li>
                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span>info@karimunjawavibestrip.com</span>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>&copy; <?php echo date('Y'); ?> Karimunjawa Homestay & Resort. Seluruh Hak Cipta Dilindungi.</p>
            <p>Designed & Developed with ♥ by KarimunJawa Vibes Trip Team</p>
        </div>
    </div>
</footer>

<!-- Lightbox Modal Pop-up Galeri (Global) -->
<div id="lightbox_modal" style="display: none; position: fixed; z-index: 2000; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center; flex-direction: column;" onclick="tutupModalLightbox()">
    <span style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
    <img id="img_modal_target" src="" alt="" style="max-width: 85%; max-height: 80%; object-fit: contain; border: 3px solid white;">
    <p id="caption_modal_target" style="color: white; margin-top: 15px; font-size: 18px; font-family: Tahoma;"></p>
</div>

    <script>
        // Fitur 1: Memilih paket otomatis dari kartu produk ke dropdown form
        function pilihPaketOtomatis(namaPaket) {
            const selectElement = document.getElementById('paket_pilihan');
            if (selectElement) {
                selectElement.value = namaPaket;
                // Efek fokus otomatis agar pengguna tahu form sudah terisi
                selectElement.focus();
                selectElement.style.borderColor = "var(--primary-teal)";
            }
        }

        // Fitur 2: Validasi input data sebelum redirect WhatsApp API
        function kirimKeWhatsApp(event) {
            event.preventDefault();

            const nama = document.getElementById('nama').value.trim();
            const paket = document.getElementById('paket_pilihan').value;
            
            <?php 
            global $nomor_whatsapp; 
            ?>
            const nomorHandphone = "<?php echo isset($nomor_whatsapp) ? $nomor_whatsapp : ''; ?>"; 

            if (nama === "" || paket === "") {
                alert("Harap isi Nama dan Pilih Paket terlebih dahulu!");
                return;
            }

            // Membangun teks draf pesan terenkripsi yang estetik untuk WhatsApp
            const pesan = `Halo KarimunJawa Vibes Trip Guide, saya ingin konsultasi paket wisata.%0A%0A` +
                          `*Nama:* ${encodeURIComponent(nama)}%0A` +
                          `*Paket Wisata:* ${encodeURIComponent(paket)}%0A%0A` +
                          `Mohon informasi ketersediaan slot dan jadwal keberangkatan terdekat. Terima kasih!`;

            const urlWA = `https://api.whatsapp.com/send?phone=${nomorHandphone}&text=${pesan}`;
            
            // Membuka draf chat otomatis di tab baru
            window.open(urlWA, '_blank');
        }

        // SISIPAN Fitur 3: Kontrol Modal Pop-up Lightbox Galeri
        function bukaModalLightbox(src, alt) {
            const modal = document.getElementById('lightbox_modal');
            const modalImg = document.getElementById('img_modal_target');
            const modalCaption = document.getElementById('caption_modal_target');
            
            if(modal && modalImg && modalCaption) {
                modal.style.display = "flex";
                modalImg.src = src;
                modalCaption.innerText = alt;
            }
        }

        function tutupModalLightbox() {
            const modal = document.getElementById('lightbox_modal');
            if(modal) modal.style.display = "none";
        }

        // Fitur 6: Toggle Tampilan Form Ulasan
        function toggleReviewForm() {
            const formContainer = document.getElementById('reviewFormContainer');
            const toggleBtn = document.getElementById('toggleReviewBtn');
            if (formContainer && toggleBtn) {
                if (formContainer.style.display === "none") {
                    formContainer.style.display = "block";
                    toggleBtn.innerText = "Tutup Form Ulasan";
                    // Scroll smooth ke container form
                    formContainer.scrollIntoView({ behavior: 'smooth' });
                } else {
                    formContainer.style.display = "none";
                    toggleBtn.innerText = "Tulis Ulasan Baru";
                }
            }
        }

        // Fitur 7: Tampilkan/Sembunyikan Ulasan Tambahan
        function toggleMoreReviews() {
            const hiddenCards = document.querySelectorAll('.review-item-card-hidden');
            const toggleBtn = document.getElementById('toggleMoreReviewsBtn');
            if (hiddenCards.length > 0 && toggleBtn) {
                const isHidden = hiddenCards[0].style.display === "none";
                
                hiddenCards.forEach(card => {
                    if (isHidden) {
                        card.style.display = "flex";
                        card.style.animation = "fadeEffect 0.5s";
                    } else {
                        card.style.display = "none";
                    }
                });

                if (isHidden) {
                    toggleBtn.innerText = "Sembunyikan Ulasan";
                } else {
                    toggleBtn.innerText = `Lihat Semua Ulasan (<?php echo isset($testimoni_pelanggan) ? count($testimoni_pelanggan) : 0; ?>)`;
                    // Scroll smooth kembali ke atas seksi testimoni setelah disembunyikan
                    document.getElementById('testimoni').scrollIntoView({ behavior: 'smooth' });
                }
            }
        }

        // SISIPAN Fitur 4: Logika Perputaran Slider Testimoni Otomatis (Ubah timer dari 5 ke 10 jika ingin lebih lambat)
        let indeksSlideSaatIni = 0;
        const semuaSlides = document.querySelectorAll('.slide-testi');

        if (semuaSlides.length > 0) {
            setInterval(() => {
                semuaSlides[indeksSlideSaatIni].style.display = 'none';
                indeksSlideSaatIni = (indeksSlideSaatIni + 1) % semuaSlides.length;
                semuaSlides[indeksSlideSaatIni].style.display = 'block';
            }, 5000); 
        }

        // Fitur 5: Penanda menu Navigasi Aktif secara dinamis saat di-scroll (Hanya berjalan di Beranda)
        if (document.getElementById('home')) {
            window.addEventListener('scroll', () => {
                const sections = document.querySelectorAll('section, header');
                const navItems = document.querySelectorAll('.nav-menu .nav-item');
                
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (pageYOffset >= sectionTop - 60) {
                        current = section.getAttribute('id') || '';
                    }
                });

                navItems.forEach(item => {
                    item.classList.remove('active');
                    const href = item.querySelector('a').getAttribute('href');
                    const cleanHref = href.replace(/^(\.\.\/|\.\/)/, '');
                    if (cleanHref === `#${current}` || cleanHref === `index.php#${current}` || (current === '' && (cleanHref === '#' || cleanHref === 'index.php'))) {
                        item.classList.add('active');
                    }
                });
            });
        }
    </script>
</body>
</html>