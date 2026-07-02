<?php
// config.php
$nomor_whatsapp = "62895361870926"; // Ganti dengan nomor WA operasional Anda

if (!function_exists('format_detail_deskripsi')) {
    function format_detail_deskripsi($text) {
        if (empty($text)) return '';
        
        // Jika teks sudah mengandung HTML (untuk backward compatibility)
        if (strpos($text, '<br') !== false || strpos($text, '<ol') !== false || strpos($text, '<li') !== false || strpos($text, '<p') !== false) {
            return $text;
        }
        
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $formatted = '';
        $in_list = false;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                if ($in_list) {
                    $formatted .= '</ol>';
                    $in_list = false;
                }
                $formatted .= '<br>';
                continue;
            }
            
            // Deteksi list angka: e.g. "1. " atau "1) "
            if (preg_match('/^(\d+)[\.\)]\s+(.*)$/', $trimmed, $matches)) {
                if (!$in_list) {
                    $formatted .= '<ol style="margin-top: 10px; margin-left: 20px; padding-left: 0; line-height: 1.8;">';
                    $in_list = true;
                }
                $formatted .= '<li>' . htmlspecialchars($matches[2]) . '</li>';
            } else {
                if ($in_list) {
                    $formatted .= '</ol>';
                    $in_list = false;
                }
                $formatted .= '<p style="margin-bottom: 8px;">' . htmlspecialchars($trimmed) . '</p>';
            }
        }
        
        if ($in_list) {
            $formatted .= '</ol>';
        }
        
        return $formatted;
    }
}

$daftar_penginapan = [
    [
        "id" => "puri-karimun",
        "nama" => "Puri Karimun",
        "deskripsi" => "Hotel Puri Karimun menawarkan kenyamanan menginap yang menyenangkan dengan akses dekat pelabuhan.",
        "harga" => "Rp. 1.600.000 / pax",
        "gambar" => "assets/images/uploads/1782644420_IMG_1354.JPG",
        "badge" => "",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => true,
        "lokasi" => "Jl. Slamet Riyadi, Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "Homestay Puri merupakan penginapan fungsional yang sangat mengedepankan fungsi dasar beristirahat dengan fasilitas standar seperti pendingin ruangan and area parkir gratis. Tempat ini adalah opsi transit yang sangat praktis, khususnya bagi Anda yang memiliki jadwal padat dan berencana untuk lebih banyak menghabiskan waktu beraktivitas di luar ruangan seharian penuh.",
        "foto_galeri" => [
            "assets/images/purikarimun/1.JPG",
            "assets/images/purikarimun/2.JPG",
            "assets/images/purikarimun/3.JPG",
            "assets/images/purikarimun/4.JPG",
            "assets/images/purikarimun/5.JPG"
        ],
        "harga_2d1n" => "Rp. 1.400.000 / pax",
        "harga_4d3n" => "Rp. 1.850.000 / pax"
    ],
    [
        "id" => "the-body-tree",
        "nama" => "The Body Tree",
        "deskripsi" => "Penginapan dengan arsitektur kayu modern bernuansa alam tropis yang sejuk dan tenang.",
        "harga" => "Rp. 1.600.000 / pax",
        "harga_2d1n" => "Rp. 1.400.000 / pax",
        "harga_4d3n" => "Rp. 1.850.000 / pax",
        "gambar" => "assets/images/uploads/1782735873_IMG_1970_2.JPG",
        "badge" => "Nature Stay",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Sunan Nyamplungan, Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "The Body Tree menyajikan akomodasi bernuansa tropis dengan taman yang asri dan desain kayu yang khas. Menawarkan kenyamanan optimal, kebersihan terjamin, serta area santai outdoor yang luas untuk liburan relaksasi Anda.",
        "foto_galeri" => [
            "assets/images/uploads/1782735873_IMG_1970_2.JPG",
            "assets/images/uploads/1782735888_DJI_20260624112830_0144_D.JPG",
            "assets/images/uploads/1782735907_IMG_0892.JPG",
            "assets/images/uploads/1782735929_DJI_20260624112052_0112_D.JPG",
            "assets/images/uploads/1782735964_IMG_3051.JPG"
        ],
        "harga_honeymoon" => "Rp. 4.600.000 / couple"
    ],
    [
        "id" => "ayu-hotel",
        "nama" => "Ayu Hotel",
        "deskripsi" => "Hotel bersih dengan taman hijau, bernuansa tenang dan ramah untuk menginap keluarga.",
        "harga" => "Rp. 1.750.000 / pax",
        "harga_2d1n" => "Rp. 1.550.000 / pax",
        "harga_4d3n" => "Rp. 2.000.000 / pax",
        "gambar" => "assets/images/uploads/1782736101_IMG_1367.jpg",
        "badge" => "Family Friendly",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Sunan Nyamplungan, Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "Ayu Hotel merupakan akomodasi yang bersih, terawat, dan didekorasi dengan nuansa hijau yang menyegarkan. Sangat tenang dan nyaman untuk bersantai bersama teman atau keluarga tercinta setelah seharian menikmati keindahan laut.",
        "foto_galeri" => [
            "assets/images/uploads/1782736101_IMG_1367.jpg",
            "assets/images/uploads/1782733714_IMG_1350.jpg",
            "assets/images/uploads/1782735445_IMG_1422.jpg",
            "assets/images/uploads/1782735818_IMG_0380.jpg",
            "assets/images/uploads/1782735835_IMG_0893.JPG"
        ],
        "harga_honeymoon" => "Rp. 5.100.000 / couple"
    ],
    [
        "id" => "narayana",
        "nama" => "Hotel Narayana",
        "deskripsi" => "Hotel modern minimalis dengan fasilitas lengkap, bersih, dan sangat dekat ke pusat kota.",
        "harga" => "Rp. 3.000.000 / pax",
        "gambar" => "assets/images/narayana/2.JPG",
        "badge" => "Modern Stay",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Sunan Nyamplungan, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Menawarkan gaya bangunan modern yang cerah dan elegan, Narayana Hotel Karimunjawa menonjolkan tingkat kenyamanan premium dan kebersihan yang selalu dipuji oleh para tamu. Ditunjang oleh staf yang ramah, layanan optimal, serta ruang bersama yang estetik, hotel ini menjanjikan pengalaman menginap yang maksimal di tengah hangatnya iklim kepulauan.",
        "foto_galeri" => [
            "assets/images/narayana/1.JPG",
            "assets/images/narayana/2.JPG",
            "assets/images/narayana/3.JPG",
            "assets/images/narayana/4.JPG",
            "assets/images/narayana/5.JPG"
        ],
        "harga_2d1n" => "Rp. 2.500.000 / pax",
        "harga_4d3n" => "Rp. 3.700.000 / pax",
        "harga_honeymoon" => "Rp. 8.500.000 / couple"
    ],
    [
        "id" => "homestay-e2",
        "nama" => "Homestay E2 House",
        "deskripsi" => "Hotel modern minimalis dengan fasilitas lengkap, bersih, dan sangat dekat ke pusat kota.",
        "harga" => "Rp. 1.850.000 / pax",
        "gambar" => "assets/images/homestaye2/2.JPG",
        "badge" => "",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Karimunjawa 3 No.2, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Mendapatkan banyak ulasan positif berkat keramahtamahan pemiliknya yang luar biasa, E2 Guest House menawarkan suasana menginap yang akrab, bersih, dan sangat terawat. Selain lingkungannya yang tenang untuk beristirahat bebas gangguan, pelayanan personal dari pemiliknya yang sigap membantu mengatur jadwal tur membuat pengalaman liburan Anda menjadi jauh lebih mudah dan menyenangkan.",
        "foto_galeri" => [
            "assets/images/homestaye2/1.JPG",
            "assets/images/homestaye2/2.JPG",
            "assets/images/homestaye2/3.JPG",
            "assets/images/homestaye2/4.JPG",
            "assets/images/homestaye2/5.JPG"
        ],
        "harga_2d1n" => "Rp. 1.500.000 / pax",
        "harga_4d3n" => "Rp. 2.250.000 / pax",
        "harga_honeymoon" => "Rp. 5.250.000 / couple"
    ],
    [
        "id" => "dseason",
        "nama" => "Hotel D&#039;Season",
        "deskripsi" => "Resort bintang tiga mewah dengan fasilitas kolam renang besar di tepi pantai.",
        "harga" => "Rp. 1.875.000 / pax",
        "gambar" => "assets/images/dseason/dseason.jpg",
        "badge" => "Bintang 3",
        "badge_class" => "orange",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Danang Joyo Jl. Kapuran No.9, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Sebagai penginapan yang memadukan relaksasi dan kenyamanan standar hotel modern, d\'SEASON Hotel menawarkan fasilitas komprehensif mulai dari kolam renang outdoor, restoran, hingga fasilitas spa. Ketersediaan makanan yang mudah didapat di dalam area hotel serta beberapa tipe kamar yang menghadap ke laut menjadikannya opsi premium yang sangat memanjakan tamu di Karimunjawa. kami menyediakan 3 pilihan kelas kamar:<br>
<ol style=\'margin-top: 10px; margin-left: 20px; padding-left: 0; line-height: 1.8;\'>
    <li>Tipe Executive : Rp. 1.875.000 / pax</li>
    <li>Tipe Family : Rp. 1.875.000 / pax</li>
    <li>Tipe Bisnis : Rp. 1.875.000 / pax</li>
</ol>",
        "foto_galeri" => [
            "assets/images/paket-honeymoon.jpg",
            "assets/images/galeri-1.jpg",
            "assets/images/galeri-2.jpg",
            "assets/images/galeri-3.jpg",
            "assets/images/galeri-4.jpg"
        ],
        "tipe_kamar" => [
            [
                "id" => "tipe-executive",
                "nama" => "Tipe Executive",
                "harga" => "Rp 1.875.000 / pax",
                "foto_galeri" => [
                    "assets/images/dseason/exe1.jpg",
                    "assets/images/dseason/exe2.jpg",
                    "assets/images/dseason/exe3.jpg",
                    "assets/images/dseason/exe2.jpg",
                    "assets/images/dseason/exe3.jpg"
                ],
                "harga_2d1n" => "Rp. 1.675.000 / pax",
                "harga_4d3n" => "Rp. 2.450.000 / pax",
                "harga_honeymoon" => "Rp. 6.500.000 / couple"
            ],
            [
                "id" => "tipe-family",
                "nama" => "Tipe Family",
                "harga" => "Rp 1.875.000 / pax",
                "foto_galeri" => [
                    "assets/images/dseason/fam1.jpg",
                    "assets/images/dseason/fam2.jpg",
                    "assets/images/dseason/fam3.jpg",
                    "assets/images/dseason/fam4.jpg",
                    "assets/images/dseason/fam5.jpg"
                ],
                "harga_2d1n" => "Rp. 1.675.000 / pax",
                "harga_4d3n" => "Rp. 2.450.000 / pax",
                "harga_honeymoon" => "Rp. 6.500.000 / couple"
            ],
            [
                "id" => "tipe-bisnis",
                "nama" => "Tipe Bisnis",
                "harga" => "Rp 1.875.000 / pax",
                "foto_galeri" => [
                    "assets/images/dseason/bisnis1.jpg",
                    "assets/images/dseason/bisnis2.JPG",
                    "assets/images/dseason/bisnis3.jpg",
                    "assets/images/dseason/bisnis4.JPG",
                    "assets/images/dseason/bisnis5.jpg"
                ],
                "harga_2d1n" => "Rp. 1.675.000 / pax",
                "harga_4d3n" => "Rp. 2.450.000 / pax",
                "harga_honeymoon" => "Rp. 6.500.000 / couple"
            ]
        ],
        "harga_2d1n" => "Rp. 1.675.000 / pax",
        "harga_4d3n" => "Rp. 2.450.000 / pax",
        "harga_honeymoon" => "Rp. 6.500.000 / couple"
    ],
    [
        "id" => "almare",
        "nama" => "Almare",
        "deskripsi" => "Penginapan tepi laut eksklusif dengan dermaga kayu pribadi untuk menikmati matahari terbit.",
        "harga" => "Rp. 2.050.000 / pax",
        "gambar" => "assets/images/almare/3.JPG",
        "badge" => "Sea Front",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Pattimura, RT.02/RW.01, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Hotel Al Mare menawarkan akomodasi bernuansa homey yang berlokasi strategis di pusat aktivitas Karimunjawa, menjadikannya pilihan tepat bagi Anda yang mencari kenyamanan layaknya di rumah sendiri. Pelayanan yang ramah serta kebersihan kamar yang selalu dijaga dengan baik membuat penginapan ini sangat ideal untuk beristirahat dengan tenang setelah seharian penuh menjelajahi keindahan pulau.",
        "foto_galeri" => [
            "assets/images/almare/1.jpg",
            "assets/images/almare/2.JPG",
            "assets/images/almare/3.JPG",
            "assets/images/almare/4.JPG",
            "assets/images/almare/5.JPG"
        ],
        "harga_2d1n" => "Rp. 1.925.000 / pax",
        "harga_4d3n" => "Rp. 2.400.000 / pax",
        "harga_honeymoon" => "Rp. 6.500.000 / couple"
    ],
    [
        "id" => "omah-alchy",
        "nama" => "Omah Alchy",
        "deskripsi" => "Cottage kayu tradisional estetik di atas air laut, menyuguhkan nuansa liburan tropis yang eksotis.",
        "harga" => "Rp. 2.150.000 / pax",
        "gambar" => "assets/images/uploads/1782644522_IMG_1380.JPG",
        "badge" => "Unique Stay",
        "badge_class" => "orange",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => true,
        "lokasi" => "Karimunjawa, Kabupaten Jepara, Jawa Tengah (Terletak di pesisir kawasan hutan bakau/mangrove)",
        "detail_deskripsi" => "Tersembunyi di dalam area hutan bakau yang asri, Omah Alchy Cottages menawarkan penginapan pinggir laut berkonsep kasual dan privat yang sangat estetik. Suara ombak yang tenang saat sarapan pagi dan bangunan cottage yang menyatu indah dengan alam mangrove menciptakan suasana syahdu yang sempurna untuk relaksasi total maupun untuk menenangkan pikiran.",
        "foto_galeri" => [
            "assets/images/alchy/1.JPG",
            "assets/images/alchy/2.JPG",
            "assets/images/alchy/3.JPG",
            "assets/images/alchy/4.JPG",
            "assets/images/alchy/5.JPG"
        ],
        "harga_2d1n" => "Rp. 1.800.000 / pax",
        "harga_4d3n" => "Rp. 2.600.000 / pax",
        "harga_honeymoon" => "Rp. 6.900.000 / couple"
    ],
    [
        "id" => "hallo-resort",
        "nama" => "Hallo Resort",
        "deskripsi" => "Resort asri dengan taman hijau yang luas dan pemandangan laut dari atas ketinggian bukit.",
        "harga" => "Rp. 2.150.000 / pax",
        "gambar" => "assets/images/uploads/1782644566_IMG_1369.JPG",
        "badge" => "Hill Resort",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => true,
        "lokasi" => "Jl. Kemojan No.KM.01, Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "Halo Sustainable Resort Karimunjawa tampil unik dengan mengusung konsep ramah lingkungan (eco-friendly) yang dirancang secara sistematis untuk meminimalkan dampak terhadap alam. Desain bangunannya yang menyatu harmonis dengan rimbunnya lingkungan sekitar menciptakan suasana menginap yang asri, sejuk, dan menyegarkan bagi para tamu yang mencari kedamaian.",
        "foto_galeri" => [
            "assets/images/hallo/1.JPG",
            "assets/images/hallo/2.JPG",
            "assets/images/hallo/3.JPG",
            "assets/images/hallo/4.JPG",
            "assets/images/hallo/5.JPG"
        ],
        "harga_2d1n" => "Rp. 1.750.000 / pax",
        "harga_4d3n" => "Rp. 2.900.000 / pax",
        "harga_honeymoon" => "Rp. 7.100.000 / couple"
    ],
    [
        "id" => "happinezz-hill",
        "nama" => "The Happinezz Hill",
        "deskripsi" => "Penginapan estetik bernuansa bohemian dengan view panorama perbukitan dan sunset laut yang indah.",
        "harga" => "Rp. 2.050.000 / pax",
        "gambar" => "assets/images/happinesshill/EFB3.JPG",
        "badge" => "Best Sunset View",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Dekat titik pandang (viewpoint) Bukit Love, Jl. I. J. Kasimo, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Berlokasi di dataran tinggi dekat viewpoint Bukit Love, Hotel The Happinezz Hills Karimunjawa menjanjikan pemandangan spektakuler berupa perpaduan perbukitan dan lautan luas dari atas. Suasananya yang sangat rileks, ditambah dengan area bersantai yang menjadi titik sempurna untuk menikmati senja magis, membuat tempat ini ideal untuk melepas penat. kami menyediakan 4 pilihan kelas kamar:<br>
<ol style=\'margin-top: 10px; margin-left: 20px; padding-left: 0; line-height: 1.8;\'>
    <li>Tipe Executive Family : Rp. 2.050.000 / pax</li>
    <li>Tipe Family Bungalow : Rp. 2.050.000 / pax</li>
    <li>Tipe Twin Toom : Rp. 2.050.000 / pax</li>
    <li>Tipe Deluxe Double : Rp. 2.050.000 / pax</li>
</ol>",
        "foto_galeri" => [
            "assets/images/paket-snorkeling.jpg",
            "assets/images/galeri-1.jpg",
            "assets/images/galeri-2.jpg",
            "assets/images/galeri-3.jpg",
            "assets/images/galeri-4.jpg"
        ],
        "tipe_kamar" => [
            [
                "id" => "tipe-executive-family",
                "nama" => "Tipe Executive Family",
                "harga" => "Rp 2.050.000 / pax",
                "foto_galeri" => [
                    "assets/images/happinesshill/EFV1.JPG",
                    "assets/images/happinesshill/EFV2.JPG",
                    "assets/images/happinesshill/EFV3.JPG",
                    "assets/images/happinesshill/EFV4.JPG",
                    "assets/images/happinesshill/EFV5.JPG"
                ],
                "harga_2d1n" => "Rp. 1.775.000 / pax",
                "harga_4d3n" => "Rp. 2.550.000 / pax",
                "harga_honeymoon" => "Rp. 7.000.000 / couple"
            ],
            [
                "id" => "tipe-family-bungalow",
                "nama" => "Tipe Family Bungalow",
                "harga" => "Rp 2.050.000 / pax",
                "foto_galeri" => [
                    "assets/images/happinesshill/EFB1.JPG",
                    "assets/images/happinesshill/EFB2.JPG",
                    "assets/images/happinesshill/EFB3.JPG",
                    "assets/images/happinesshill/EFB4.JPG",
                    "assets/images/happinesshill/EFB5.JPG"
                ],
                "harga_2d1n" => "Rp. 1.775.000 / pax",
                "harga_4d3n" => "Rp. 2.550.000 / pax",
                "harga_honeymoon" => "Rp. 7.000.000 / couple"
            ],
            [
                "id" => "tipe-twin-toom",
                "nama" => "Tipe Twin Toom",
                "harga" => "Rp 2.050.000 / pax",
                "foto_galeri" => [
                    "assets/images/happinesshill/TTT1.JPG",
                    "assets/images/happinesshill/TTT2.JPG",
                    "assets/images/happinesshill/TTT3.JPG",
                    "assets/images/happinesshill/TTT4.JPG",
                    "assets/images/happinesshill/TTT2.JPG"
                ],
                "harga_2d1n" => "Rp. 1.775.000 / pax",
                "harga_4d3n" => "Rp. 2.550.000 / pax",
                "harga_honeymoon" => "Rp. 7.000.000 / couple"
            ],
            [
                "id" => "tipe-deluxe-double",
                "nama" => "Tipe Deluxe Double",
                "harga" => "Rp 2.050.000 / pax",
                "foto_galeri" => [
                    "assets/images/happinesshill/TDD1.JPG",
                    "assets/images/happinesshill/TDD2.JPG",
                    "assets/images/happinesshill/TDD3.JPG",
                    "assets/images/happinesshill/TDD4.JPG",
                    "assets/images/happinesshill/TDD2.JPG"
                ],
                "harga_2d1n" => "Rp. 1.775.000 / pax",
                "harga_4d3n" => "Rp. 2.550.000 / pax",
                "harga_honeymoon" => "Rp. 7.000.000 / couple"
            ]
        ],
        "harga_2d1n" => "Rp. 1.775.000 / pax",
        "harga_4d3n" => "Rp. 2.550.000 / pax",
        "harga_honeymoon" => "Rp. 7.000.000 / couple"
    ],
    [
        "id" => "bale-karimunjawa",
        "nama" => "Bale Karimunjawa",
        "deskripsi" => "Bungalow kayu tradisional premium dengan pemandangan laut yang sangat menenangkan.",
        "harga" => "Rp. 2.050.000 / pax",
        "harga_2d1n" => "Rp. 1.775.000 / pax",
        "harga_4d3n" => "Rp. 2.550.000 / pax",
        "gambar" => "assets/images/uploads/1782735981_DJI_20260624101503_0069_D.JPG",
        "badge" => "Sea View",
        "badge_class" => "orange",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Pesisir Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "Bale Karimunjawa menyuguhkan konsep pondok kayu (bale) tradisional berkualitas premium di tepi pantai. Rasakan kedamaian sejati mendengarkan deburan ombak dan angin sepoi-sepoi langsung dari depan kamar Anda.",
        "foto_galeri" => [
            "assets/images/uploads/1782735981_DJI_20260624101503_0069_D.JPG",
            "assets/images/uploads/1782736024_IMG_3039.JPG",
            "assets/images/uploads/1782736047_DJI_0697.JPG",
            "assets/images/uploads/1782736064_IMG_3044.JPG",
            "assets/images/uploads/1782736101_IMG_1367.jpg"
        ],
        "harga_honeymoon" => "Rp. 7.000.000 / couple"
    ],
    [
        "id" => "legon-waru",
        "nama" => "Legon Waru Cottage",
        "deskripsi" => "Cottage eksklusif yang tenang dengan pantai tersembunyi berpagar pepohonan kelapa rimbun.",
        "harga" => "Rp. 3.000.000 / pax",
        "gambar" => "assets/images/legonwaru/2.JPG",
        "badge" => "Private Beach",
        "badge_class" => "orange",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Kapuran, RT.004/RW.001, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Berada sedikit menjauh dari pusat keramaian komersial, Legon Waru Cottage menawarkan pelarian total dari kebisingan rutinitas harian di lingkungan yang sangat sepi dan tenang. Desain bangunannya yang unik berkonsep cottage eksklusif membuat pengalaman menginap terasa jauh lebih intim, personal, dan efektif untuk mengisi ulang energi Anda.",
        "foto_galeri" => [
            "assets/images/legonwaru/1.JPG",
            "assets/images/legonwaru/2.JPG",
            "assets/images/legonwaru/3.JPG",
            "assets/images/legonwaru/4.JPG",
            "assets/images/legonwaru/5.JPG"
        ],
        "harga_2d1n" => "Rp. 2.500.000 / pax",
        "harga_4d3n" => "Rp. 3.700.000 / pax",
        "harga_honeymoon" => "Rp. 8.500.000 / couple"
    ],
    [
        "id" => "royal-ocean",
        "nama" => "Royal Ocean View",
        "deskripsi" => "Akomodasi premium mewah dengan pemandangan laut luas, menawarkan 3 kelas kamar eksklusif.",
        "harga" => "Mulai Rp. 2.700.000 / pax",
        "gambar" => "assets/images/royaloceanview/1.jpg",
        "badge" => "Executive Resort",
        "badge_class" => "orange",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Kemujan, Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "Terletak di area Kemujan yang jauh dari hiruk-pikuk pusat pulau, Royal Ocean View Beach Resort menghadirkan ketenangan dan privasi luar biasa dengan suasana kasual yang tertata rapi. Resor ini sangat cocok untuk liburan bersama keluarga karena dilengkapi fasilitas memadai seperti kolam renang outdoor, ruang makan yang luas, serta beberapa kamar dengan pemandangan langsung ke arah laut. Kami menawarkan pilihan kamar berkelas tinggi untuk liburan eksklusif Anda:<br>
<ol style=\'margin-top: 10px; margin-left: 20px; padding-left: 0; line-height: 1.8;\'>
    <li>Deluxe Sea View : Rp. 2.700.000 / pax</li>
    <li>Superior Room : Rp. 3.150.000 / pax</li>
    <li>Executive Sea View : Rp. 3.950.000 / pax</li>
</ol>
Setiap kamar dirancang dengan standar resort mewah internasional dan balkon yang luas.",
        "foto_galeri" => [
            "assets/images/royaloceanview/1.jpg",
            "assets/images/royaloceanview/2.jpg",
            "assets/images/royaloceanview/3.jpg",
            "assets/images/royaloceanview/4.jpg",
            "assets/images/royaloceanview/5.jpg"
        ],
        "tipe_kamar" => [
            [
                "id" => "executive-sea-view",
                "nama" => "Executive Sea View",
                "harga" => "Rp 3.950.000 / pax",
                "foto_galeri" => [
                    "assets/images/royaloceanview/EXE1.JPG",
                    "assets/images/royaloceanview/2.jpg",
                    "assets/images/royaloceanview/3.jpg",
                    "assets/images/royaloceanview/4.jpg",
                    "assets/images/royaloceanview/5.jpg"
                ],
                "harga_2d1n" => "Rp 3.300.000 / pax",
                "harga_4d3n" => "Rp 5.700.000 / pax",
                "harga_honeymoon" => "Rp. 10.500.000 / couple"
            ],
            [
                "id" => "tipe-superior-room",
                "nama" => "Tipe Superior Room",
                "harga" => "Rp 3.150.000 / pax",
                "foto_galeri" => [
                    "assets/images/royaloceanview/TS1.JPG",
                    "assets/images/royaloceanview/2.jpg",
                    "assets/images/royaloceanview/3.jpg",
                    "assets/images/royaloceanview/4.jpg",
                    "assets/images/royaloceanview/5.jpg"
                ],
                "harga_2d1n" => "Rp 2.900.000 / pax",
                "harga_4d3n" => "Rp 4.900.000 / pax",
                "harga_honeymoon" => "Rp. 9.500.000 / couple"
            ],
            [
                "id" => "deluxe-sea-view",
                "nama" => "Deluxe Sea View",
                "harga" => "Rp 2.700.000 / pax",
                "foto_galeri" => [
                    "assets/images/royaloceanview/TDV1.JPG",
                    "assets/images/royaloceanview/TDV2.JPG",
                    "assets/images/royaloceanview/TDV3.JPG",
                    "assets/images/royaloceanview/TDV4.JPG",
                    "assets/images/royaloceanview/TDV5.JPG"
                ],
                "harga_2d1n" => "Rp 2.275.000 / pax",
                "harga_4d3n" => "Rp 4.450.000 / pax",
                "harga_honeymoon" => "Rp. 8.900.000 / couple"
            ]
        ],
        "harga_2d1n" => "Mulai Rp. 2.275.000 / pax",
        "harga_4d3n" => "Mulai Rp. 4.450.000 / pax",
        "harga_honeymoon" => "Rp. 8.900.000 / couple"
    ],
    [
        "id" => "java-paradise",
        "nama" => "Java Paradise",
        "deskripsi" => "Resort bergaya klasik tropis modern dengan 3 pilihan tipe kamar yang sangat nyaman.",
        "harga" => "Mulai Rp. 2.050.000 / pax",
        "gambar" => "assets/images/javaparadise/JTD2.JPG",
        "badge" => "Paradise Resort",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Dukuh Kapuran, RT.04/RW.01, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Mengusung desain arsitektur bernuansa kayu yang elegan dan tradisional, Java Paradise Resort menghadirkan nuansa liburan tropis yang sesungguhnya dengan fasilitas modern yang memanjakan. Area bersantai yang luas dipadukan dengan kolam renang estetis menjadikan resor ini tempat pelarian yang sempurna untuk menyegarkan pikiran bersama rombongan atau keluarga besar. kami menyediakan 3 pilihan kelas kamar:<br>
<ol style=\'margin-top: 10px; margin-left: 20px; padding-left: 0; line-height: 1.8;\'>
    <li>Family (P) : Rp. 2.050.000 / pax</li>
    <li>Superior Class : Rp. 2.200.000 / pax</li>
    <li>Executive Class : Rp. 2.450.000 / pax</li>
</ol>",
        "foto_galeri" => [
            "assets/images/paket-snorkeling.jpg",
            "assets/images/galeri-4.jpg",
            "assets/images/galeri-3.jpg",
            "assets/images/galeri-2.jpg",
            "assets/images/galeri-1.jpg"
        ],
        "tipe_kamar" => [
            [
                "id" => "family-p",
                "nama" => "Family (P)",
                "harga" => "Rp. 2.050.000 / pax",
                "foto_galeri" => [
                    "assets/images/javaparadise/JTF1.JPG",
                    "assets/images/javaparadise/JTF2.JPG",
                    "assets/images/javaparadise/JTF3.JPG",
                    "assets/images/javaparadise/JTF3.JPG",
                    "assets/images/javaparadise/JTF2.JPG"
                ],
                "harga_2d1n" => "Rp. 1.725.000 / pax",
                "harga_4d3n" => "Rp. 2.950.000 / pax",
                "harga_honeymoon" => "Rp. 7.500.000 / couple"
            ],
            [
                "id" => "superior-class",
                "nama" => "Superior Class",
                "harga" => "Rp. 2.200.000 / pax",
                "foto_galeri" => [
                    "assets/images/javaparadise/JTD1.JPG",
                    "assets/images/javaparadise/JTD2.JPG",
                    "assets/images/javaparadise/JTD3.JPG",
                    "assets/images/javaparadise/JTD4.JPG",
                    "assets/images/javaparadise/JTD5.JPG"
                ],
                "harga_2d1n" => "Rp. 1.850.000 / pax",
                "harga_4d3n" => "Rp. 3.100.000 / pax",
                "harga_honeymoon" => "Rp. 7.900.000 / couple"
            ],
            [
                "id" => "executive-class",
                "nama" => "Executive Class",
                "harga" => "Rp. 2.450.000 / pax",
                "foto_galeri" => [
                    "assets/images/javaparadise/TER1.JPG",
                    "assets/images/javaparadise/TER2.JPG",
                    "assets/images/javaparadise/TER3.JPG",
                    "assets/images/javaparadise/TER4.JPG",
                    "assets/images/javaparadise/TER5.JPG"
                ],
                "harga_2d1n" => "Rp. 2.025.000 / pax",
                "harga_4d3n" => "Rp. 3.350.000 / pax",
                "harga_honeymoon" => "Rp. 8.500.000 / couple"
            ]
        ],
        "harga_2d1n" => "Mulai Rp. 1.725.000 / pax",
        "harga_4d3n" => "Mulai Rp. 2.950.000 / pax",
        "harga_honeymoon" => "Rp. 7.500.000 / couple"
    ],
    [
        "id" => "hotel-blue-laguna-inn",
        "nama" => "Hotel Blue Laguna Inn",
        "deskripsi" => "Menghadap langsung ke perairan biru yang menenangkan",
        "harga" => "Rp. 1.850.000 / pax",
        "gambar" => "assets/images/uploads/1782645099_WhatsApp_Image_2026-06-28_at_18.05.48.jpeg",
        "badge" => "",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Dermaga Baru, Jl. Jenderal Sudirman, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59411",
        "detail_deskripsi" => "Blue Laguna Inn memberikan pengalaman menginap dengan pemandangan laut yang indah tepat di depan mata. Penginapan sederhana yang dilengkapi kamar mandi dalam dan fasilitas sarapan gratis ini juga berlokasi sangat dekat dengan dermaga, sehingga sangat memudahkan mobilitas Anda untuk melakukan kegiatan island hopping.",
        "foto_galeri" => [
            "assets/images/uploads/1782645099_IMG_1331.JPG",
            "assets/images/uploads/1782645099_IMG_1332.JPG",
            "assets/images/uploads/1782645099_IMG_1333.JPG",
            "assets/images/uploads/1782645099_IMG_1330.JPG",
            "assets/images/uploads/1782645099_IMG_1328.JPG"
        ],
        "harga_2d1n" => "Rp. 1.650.000 / pax",
        "harga_4d3n" => "Rp. 2.150.000 / pax",
        "harga_honeymoon" => "Rp. 5.250.000 / couple"
    ],
    [
        "id" => "hotel-summer-inn",
        "nama" => "Hotel Summer Inn",
        "deskripsi" => "Hotel dengan lokasi strategis dan kenyamanan maksimal untuk peristirahatan Anda.",
        "harga" => "Rp. 1.850.000 / pax",
        "harga_2d1n" => "Rp. 1.650.000 / pax",
        "gambar" => "assets/images/uploads/1782720304_IMG_7016.jpg",
        "badge" => "Popular Stay",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Sunan Nyamplungan, Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "Hotel Summer Inn menyajikan akomodasi yang bersih, nyaman, dan berlokasi strategis dekat dengan pusat aktivitas Karimunjawa. Dilengkapi dengan fasilitas modern, AC yang sejuk, kamar mandi bersih, serta pelayanan ramah untuk menunjang liburan Anda bersama keluarga.",
        "foto_galeri" => [
            "assets/images/uploads/1782720304_IMG_7016.jpg",
            "assets/images/uploads/1782733714_IMG_1350.jpg",
            "assets/images/uploads/1782735445_IMG_1422.jpg",
            "assets/images/uploads/1782735818_IMG_0380.jpg",
            "assets/images/uploads/1782735835_IMG_0893.JPG"
        ],
        "harga_4d3n" => "Rp. 2.150.000 / pax",
        "harga_honeymoon" => "Rp. 5.250.000 / couple"
    ],
    [
        "id" => "homestay-loyal",
        "nama" => "Homestay Loyal",
        "deskripsi" => "Loyal Friend Hostel Karimunjawa adalah pilihan favorit para pelancong yang menawarkan suasana komunal hangat dan tarif menginap yang sangat bersahabat di kantong.",
        "harga" => "Rp. 1.850.000 / pax",
        "gambar" => "assets/images/uploads/1782646291_IMG_1303.JPG",
        "badge" => "",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Slamet Riyadi No.RT.01, Karimunjawa, Kabupaten Jepara, Jawa Tengah 59455",
        "detail_deskripsi" => "Berkat ruang santai bersamanya yang nyaman dan lokasinya yang strategis dekat akses kuliner alun-alun malam, tempat ini menjadi titik kumpul yang sempurna untuk bersosialisasi dan merayakan momen liburan bersama sahabat.",
        "foto_galeri" => [
            "assets/images/uploads/1782646291_IMG_1090.JPG",
            "assets/images/uploads/1782646291_IMG_1303.JPG",
            "assets/images/uploads/1782646291_IMG_1091.JPG",
            "assets/images/uploads/1782646291_IMG_1092.JPG",
            "assets/images/uploads/1782646291_IMG_1094.JPG"
        ],
        "harga_2d1n" => "Rp. 1.000.000 / pax",
        "harga_4d3n" => "Rp. 1.475.000 / pax",
        "harga_honeymoon" => "Rp. 4.950.000 / couple"
    ],
    [
        "id" => "homestay-azza",
        "nama" => "Homestay Azza",
        "deskripsi" => "Penginapan berpendingin ruangan (AC) yang sejuk dengan lokasi strategis di pusat kota.",
        "harga" => "Rp. 1.400.000 / pax",
        "gambar" => "assets/images/uploads/1782647114_IMG_1295.JPG",
        "badge" => "",
        "badge_class" => "",
        "durasi" => "3D2N / 3 Hari 2 Malam",
        "show_in_slider" => false,
        "lokasi" => "Jl. Wage Rudolf Supratman, RT.3/RW.2, Kepulauan, Karimunjawa, Kabupaten Jepara, Jawa Tengah",
        "detail_deskripsi" => "Membaur dengan pemukiman warga lokal, Homestay AZZA adalah hunian nyaman yang sangat memprioritaskan kepraktisan dan harga yang bersahabat. Fasilitasnya yang memadai dipadukan dengan keramahan pemilik yang sigap membantu berbagai kebutuhan tambahan memberikan kesempatan bagi Anda untuk berinteraksi lebih hangat dengan masyarakat asli Karimunjawa.",
        "foto_galeri" => [
            "assets/images/uploads/1782647114_IMG_1294.JPG",
            "assets/images/uploads/1782647114_IMG_1293.JPG",
            "assets/images/uploads/1782647114_IMG_1292.JPG",
            "assets/images/uploads/1782647114_IMG_1291.JPG",
            "assets/images/uploads/1782647114_IMG_1295.JPG"
        ],
        "harga_2d1n" => "Rp. 1.200.000 / pax",
        "harga_4d3n" => "Rp. 1.750.000 / pax",
        "harga_honeymoon" => "Rp. 4.500.000 / couple"
    ]
];

// Load dynamically from JSON if exists, otherwise save current hardcoded array as seed
$json_file = __DIR__ . '/penginapan.json';
if (file_exists($json_file)) {
    $daftar_penginapan = json_decode(file_get_contents($json_file), true);
} else {
    file_put_contents($json_file, json_encode($daftar_penginapan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// DATA GALERI: Menyimpan koleksi foto Karimunjawa beserta posisi fokus dan kategori (Dinamis dari galeri.json)
$galeri_file = __DIR__ . '/galeri.json';
if (file_exists($galeri_file)) {
    $galeri_foto = json_decode(file_get_contents($galeri_file), true);
    if (!is_array($galeri_foto)) {
        $galeri_foto = [];
    }
} else {
    $galeri_foto = [
        [
            "file" => "assets/images/galeri-1.jpg",
            "alt" => "Pantai Tanjung Gelam",
            "posisi" => "center top", // Mengunci bagian atas foto agar pohon kelapa terlihat utuh & eye-catching
            "kategori" => "destinasi",
            "tag" => "Pantai"
        ],
        [
            "file" => "assets/images/galeri-2.jpg",
            "alt" => "Pulau Menjangan Kecil",
            "posisi" => "center center",
            "kategori" => "destinasi",
            "tag" => "Pulau"
        ],
        [
            "file" => "assets/images/galeri-3.jpg",
            "alt" => "Bukit Love Karimunjawa",
            "posisi" => "left bottom", // Mengunci plang nama agar teks tidak terpotong menjadi "KA"
            "kategori" => "destinasi",
            "tag" => "Bukit"
        ],
        [
            "file" => "assets/images/galeri-4.jpg",
            "alt" => "Penangkaran Hiu",
            "posisi" => "center center",
            "kategori" => "aktivitas",
            "tag" => "Aktivitas"
        ],
        [
            "file" => "assets/images/paket-snorkeling.jpg",
            "alt" => "Spot Terumbu Karang Menawan",
            "posisi" => "center center",
            "kategori" => "aktivitas",
            "tag" => "Snorkeling"
        ],
        [
            "file" => "assets/images/paket-family.jpg",
            "alt" => "Keseruan Outbound di Pantai",
            "posisi" => "center center",
            "kategori" => "aktivitas",
            "tag" => "Outbound"
        ],
        [
            "file" => "assets/images/galeri-5.jpg",
            "alt" => "Pesona Sunset Pantai Karimunjawa",
            "posisi" => "center center",
            "kategori" => "destinasi",
            "tag" => "Sunset"
        ],
        [
            "file" => "assets/images/galeri-6.jpg",
            "alt" => "Snorkeling Terumbu Karang Pulau Cilik",
            "posisi" => "center center",
            "kategori" => "aktivitas",
            "tag" => "Snorkeling"
        ],
        [
            "file" => "assets/images/galeri-7.jpg",
            "alt" => "Dermaga Kayu Pelabuhan Karimunjawa",
            "posisi" => "center center",
            "kategori" => "destinasi",
            "tag" => "Dermaga"
        ],
        [
            "file" => "assets/images/galeri-8.jpg",
            "alt" => "Pantai Pasir Putih Pulau Geleang",
            "posisi" => "center center",
            "kategori" => "destinasi",
            "tag" => "Pantai"
        ]
    ];
    file_put_contents($galeri_file, json_encode($galeri_foto, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// DATA TESTIMONI PELANGGAN (Dinamis dari reviews.json)
$reviews_file = __DIR__ . '/reviews.json';
if (file_exists($reviews_file)) {
    $testimoni_pelanggan = json_decode(file_get_contents($reviews_file), true);
    if (!is_array($testimoni_pelanggan)) {
        $testimoni_pelanggan = [];
    }
} else {
    $testimoni_pelanggan = [
        [
            "nama" => "Rendi Pratama",
            "asal" => "Bandung",
            "bintang" => 5,
            "ulasan" => "Pelayanan tour guide dari Karimunjawa Vibes Trip luar biasa ramah dan sabar! Snorkeling dipandu dengan baik, dokumentasi underwater-nya super jernih dan keren.",
            "tanggal" => "2026-06-23"
        ],
        [
            "nama" => "Clarissa",
            "asal" => "Tangerang",
            "bintang" => 5,
            "ulasan" => "Sangat puas trip dengan agen ini! Jadwal kapal terorganisir dengan rapi, guide-nya informatif menjelaskan sejarah pulau, dan diajak ke spot sunset rahasia.",
            "tanggal" => "2026-06-24"
        ],
        [
            "nama" => "Ahmad Fauzi",
            "asal" => "Yogyakarta",
            "bintang" => 5,
            "ulasan" => "Pengalaman tak terlupakan bersama kru Karimunjawa Vibes Trip. Makanan bakar ikan di pulau tak berpenghuni rasanya juara banget! Sangat direkomendasikan.",
            "tanggal" => "2026-06-24"
        ],
        [
            "nama" => "Andi Wijaya",
            "asal" => "Semarang",
            "bintang" => 5,
            "ulasan" => "Tempatnya sangat bersih dan nyaman sekali. Pelayanan dari host ramah banget, dekat sekali dengan alun-alun!",
            "tanggal" => "2026-06-20",
            "penginapan_id" => "homestay-fan"
        ],
        [
            "nama" => "Siti Rahma",
            "asal" => "Jakarta",
            "bintang" => 5,
            "ulasan" => "Honeymoon di Lighthouse Resort luar biasa berkesan. Kolam renang privatnya menghadap laut langsung dan sunset-nya juara!",
            "tanggal" => "2026-06-21",
            "penginapan_id" => "java-paradise"
        ],
        [
            "nama" => "Rian Utama",
            "asal" => "Surabaya",
            "bintang" => 5,
            "ulasan" => "Vila kayunya estetik parah, pemandangan laut dari atas balkon kamar benar-benar memanjakan mata. Fasilitas sewa motor gratisnya sangat membantu.",
            "tanggal" => "2026-06-22",
            "penginapan_id" => "omah-alchy"
        ]
    ];
    file_put_contents($reviews_file, json_encode($testimoni_pelanggan, JSON_PRETTY_PRINT));
}

// DATA SLIDER: Menyimpan konfigurasi slide hero utama (Dinamis dari slider.json)
$slider_file = __DIR__ . '/slider.json';
if (file_exists($slider_file)) {
    $slider_data = json_decode(file_get_contents($slider_file), true);
    if (!is_array($slider_data)) {
        $slider_data = [];
    }
} else {
    // Seeding awal dengan 3 paket pilihan default
    $slider_data = [];
    $default_ids = ['homestay-loyal', 'dseason', 'blue-laguna'];
    foreach ($default_ids as $pid) {
        foreach ($daftar_penginapan as $p) {
            if ($p['id'] === $pid) {
                $slider_data[] = [
                    "id" => $p['id'] . '-slide', // Unique slide ID
                    "lodging_id" => $p['id'],
                    "nama" => $p['nama'],
                    "judul_slider" => isset($p['judul_slider']) ? $p['judul_slider'] : '',
                    "durasi" => isset($p['durasi']) ? $p['durasi'] : '3D2N',
                    "harga" => $p['harga'],
                    "gambar" => $p['gambar']
                ];
                break;
            }
        }
    }
    file_put_contents($slider_file, json_encode($slider_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>