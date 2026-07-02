<?php
// admin.php
session_start();

// Konfigurasi Kredensial Admin
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'adminvibestrip');

// Koneksi Config Data
require_once __DIR__ . '/config.php';
global $daftar_penginapan, $galeri_foto;

// Load reviews and ensure they have IDs
$reviews_file = __DIR__ . '/reviews.json';
$all_reviews = [];
if (file_exists($reviews_file)) {
    $all_reviews = json_decode(file_get_contents($reviews_file), true);
    if (is_array($all_reviews)) {
        $reviews_changed = false;
        foreach ($all_reviews as $idx => &$testi) {
            if (!isset($testi['id'])) {
                $testi['id'] = md5(json_encode($testi) . $idx . time());
                $reviews_changed = true;
            }
        }
        unset($testi);
        if ($reviews_changed) {
            file_put_contents($reviews_file, json_encode($all_reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    } else {
        $all_reviews = [];
    }
}

// Inisialisasi Directory Uploads
$upload_dir = __DIR__ . '/assets/images/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handler Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Handler Login
$login_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : "";
    $password = isset($_POST['password']) ? trim($_POST['password']) : "";

    if ($username === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $login_error = "Username atau password salah!";
    }
}

// Cek Status Login
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Helper untuk Upload Gambar
function handleImageUpload(string $file_key, string $existing_path = ""): string
{
    global $upload_dir;
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES[$file_key]['tmp_name'];
        $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $_FILES[$file_key]['name']);
        // Tambahkan timestamp untuk mencegah bentrok nama file
        $new_name = time() . '_' . $file_name;
        $dest_path = $upload_dir . $new_name;

        if (move_uploaded_file($file_tmp, $dest_path)) {
            return "assets/images/uploads/" . $new_name;
        }
    }
    return $existing_path;
}

// Helper untuk Slugify ID
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

// Handler Operasi CRUD (Hanya jika sudah login)
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "";
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "";
unset($_SESSION['success_message'], $_SESSION['error_message']);

if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create' || $action === 'update') {
        // Ambil input data dasar
        $nama = isset($_POST['nama']) ? trim(htmlspecialchars($_POST['nama'])) : "";
        $old_id = isset($_POST['old_id']) ? $_POST['old_id'] : "";

        if ($action === 'update' && !empty($old_id)) {
            $id = $old_id;
        } else {
            $id = slugify($nama);
        }

        $deskripsi = isset($_POST['deskripsi']) ? trim(htmlspecialchars($_POST['deskripsi'])) : "";
        $harga = isset($_POST['harga']) ? trim(htmlspecialchars($_POST['harga'])) : "";
        $harga_2d1n = isset($_POST['harga_2d1n']) ? trim(htmlspecialchars($_POST['harga_2d1n'])) : "";
        $harga_4d3n = isset($_POST['harga_4d3n']) ? trim(htmlspecialchars($_POST['harga_4d3n'])) : "";
        $harga_honeymoon = isset($_POST['harga_honeymoon']) ? trim(htmlspecialchars($_POST['harga_honeymoon'])) : "";
        $lokasi = isset($_POST['lokasi']) ? trim(htmlspecialchars($_POST['lokasi'])) : "";
        $badge = isset($_POST['badge']) ? trim(htmlspecialchars($_POST['badge'])) : "";
        $badge_class = isset($_POST['badge_class']) ? trim(htmlspecialchars($_POST['badge_class'])) : "";
        $durasi = "3D2N / 3 Hari 2 Malam";
        // Cari penginapan lama jika edit
        $old_id = isset($_POST['old_id']) ? $_POST['old_id'] : "";
        $old_lodging = null;
        if (!empty($old_id)) {
            foreach ($daftar_penginapan as $p) {
                if ($p['id'] === $old_id) {
                    $old_lodging = $p;
                    break;
                }
            }
        }

        // Preservasikan status slider agar tidak ter-reset karena form tidak memiliki checkbox ini lagi
        $show_in_slider = false;
        if ($action === 'update' && $old_lodging !== null) {
            $show_in_slider = isset($old_lodging['show_in_slider']) ? $old_lodging['show_in_slider'] : false;
        }

        $detail_deskripsi = isset($_POST['detail_deskripsi']) ? trim($_POST['detail_deskripsi']) : ""; // Boleh ada tag HTML

        // Cek bentrok ID jika create baru atau ID diubah
        if (($action === 'create' || $id !== $old_id) && !empty($daftar_penginapan)) {
            foreach ($daftar_penginapan as $p) {
                if ($p['id'] === $id) {
                    $error_message = "Error: ID Penginapan '$id' sudah terdaftar! Harap gunakan ID atau Nama lain.";
                    break;
                }
            }
        }

        if (empty($error_message)) {
            // Upload Gambar Utama
            $gambar_path = isset($old_lodging['gambar']) ? $old_lodging['gambar'] : "";
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $gambar_path = handleImageUpload('gambar');
            }

            // Upload 5 Foto Galeri Utama
            $foto_galeri = [];
            for ($i = 0; $i < 5; $i++) {
                $galeri_path = isset($old_lodging['foto_galeri'][$i]) ? $old_lodging['foto_galeri'][$i] : "";

                if (isset($_FILES['foto_galeri_' . $i]) && $_FILES['foto_galeri_' . $i]['error'] === UPLOAD_ERR_OK) {
                    $galeri_path = handleImageUpload('foto_galeri_' . $i);
                }

                // fallback jika kosong, gunakan gambar utama
                if (empty($galeri_path)) {
                    $galeri_path = $gambar_path;
                }
                $foto_galeri[] = $galeri_path;
            }

            // Proses Tipe Kamar Dinamis
            $tipe_kamar = [];
            if (isset($_POST['room_type_name']) && is_array($_POST['room_type_name'])) {
                foreach ($_POST['room_type_name'] as $idx => $rt_name) {
                    if (empty(trim($rt_name)))
                        continue;

                    $rt_id = slugify($rt_name);
                    $rt_price = isset($_POST['room_type_price'][$idx]) ? trim(htmlspecialchars($_POST['room_type_price'][$idx])) : "";
                    $rt_price_2d1n = isset($_POST['room_type_price_2d1n'][$idx]) ? trim(htmlspecialchars($_POST['room_type_price_2d1n'][$idx])) : "";
                    $rt_price_4d3n = isset($_POST['room_type_price_4d3n'][$idx]) ? trim(htmlspecialchars($_POST['room_type_price_4d3n'][$idx])) : "";
                    $rt_price_honeymoon = isset($_POST['room_type_price_honeymoon'][$idx]) ? trim(htmlspecialchars($_POST['room_type_price_honeymoon'][$idx])) : "";

                    // Kumpulkan galeri foto tipe kamar
                    $rt_gallery = [];
                    for ($k = 0; $k < 5; $k++) {
                        $rt_gal_path = "";

                        // Handler upload file galeri kamar
                        $rt_file_key = 'room_type_gallery_' . $idx . '_' . $k;
                        if (isset($_FILES[$rt_file_key]) && $_FILES[$rt_file_key]['error'] === UPLOAD_ERR_OK) {
                            $rt_gal_path = handleImageUpload($rt_file_key);
                        }

                        if (empty($rt_gal_path)) {
                            // Ambil dari data lama jika ada
                            if ($old_lodging && isset($old_lodging['tipe_kamar'][$idx]['foto_galeri'][$k])) {
                                $rt_gal_path = $old_lodging['tipe_kamar'][$idx]['foto_galeri'][$k];
                            } else {
                                $rt_gal_path = $gambar_path; // default fallback
                            }
                        }
                        $rt_gallery[] = $rt_gal_path;
                    }

                    $rt_data = [
                        "id" => $rt_id,
                        "nama" => $rt_name,
                        "harga" => $rt_price,
                        "foto_galeri" => $rt_gallery
                    ];
                    if (!empty($rt_price_2d1n)) {
                        $rt_data["harga_2d1n"] = $rt_price_2d1n;
                    }
                    if (!empty($rt_price_4d3n)) {
                        $rt_data["harga_4d3n"] = $rt_price_4d3n;
                    }
                    if (!empty($rt_price_honeymoon)) {
                        $rt_data["harga_honeymoon"] = $rt_price_honeymoon;
                    }

                    $tipe_kamar[] = $rt_data;
                }
            }

            // Susun array data penginapan
            $newData = [
                "id" => $id,
                "nama" => $nama,
                "deskripsi" => $deskripsi,
                "harga" => $harga,
                "gambar" => $gambar_path,
                "badge" => $badge,
                "badge_class" => $badge_class,
                "durasi" => $durasi,
                "show_in_slider" => $show_in_slider,
                "lokasi" => $lokasi,
                "detail_deskripsi" => $detail_deskripsi,
                "foto_galeri" => $foto_galeri
            ];
            if (!empty($harga_2d1n)) {
                $newData["harga_2d1n"] = $harga_2d1n;
            }
            if (!empty($harga_4d3n)) {
                $newData["harga_4d3n"] = $harga_4d3n;
            }
            if (!empty($harga_honeymoon)) {
                $newData["harga_honeymoon"] = $harga_honeymoon;
            }
            if (!empty($tipe_kamar)) {
                $newData["tipe_kamar"] = $tipe_kamar;
            }

            // Simpan perubahan ke list global
            $new_daftar_penginapan = [];
            if ($action === 'create') {
                $new_daftar_penginapan = $daftar_penginapan;
                $new_daftar_penginapan[] = $newData;
                $success_message = "Penginapan '$nama' berhasil ditambahkan!";
            } else { // update
                foreach ($daftar_penginapan as $p) {
                    if ($p['id'] === $old_id) {
                        $new_daftar_penginapan[] = $newData;
                    } else {
                        $new_daftar_penginapan[] = $p;
                    }
                }

                // Jika ID berubah, hapus file detail PHP yang lama
                if ($id !== $old_id) {
                    $old_file = __DIR__ . '/detail-page/' . $old_id . '.php';
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $success_message = "Penginapan '$nama' berhasil diperbarui!";
            }

            // Tulis ke JSON database file
            file_put_contents(__DIR__ . '/penginapan.json', json_encode($new_daftar_penginapan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $daftar_penginapan = $new_daftar_penginapan;

            // Buat file detail PHP representatif secara otomatis
            $new_detail_file = __DIR__ . '/detail-page/' . $id . '.php';
            $file_content = "<?php\n\$_GET['id'] = '$id';\nrequire_once 'detail.php';\n?>\n";
            file_put_contents($new_detail_file, $file_content);

            $_SESSION['success_message'] = $success_message;
            header("Location: admin.php");
            exit;
        }
    }
}

if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_review') {
    $review_id = isset($_POST['review_id']) ? $_POST['review_id'] : '';
    $balasan = isset($_POST['balasan']) ? trim(htmlspecialchars($_POST['balasan'])) : '';

    if (!empty($review_id)) {
        foreach ($all_reviews as &$testi) {
            if (isset($testi['id']) && $testi['id'] === $review_id) {
                $testi['balasan'] = $balasan;
                break;
            }
        }
        unset($testi);
        file_put_contents($reviews_file, json_encode($all_reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $success_message = "Balasan ulasan berhasil disimpan!";
        $_SESSION['success_message'] = $success_message;
        header("Location: admin.php?action=reviews");
        exit;
    }
}

// Handler GET Hapus Ulasan
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'reviews_delete' && isset($_GET['id'])) {
    $delete_id = $_GET['id'];
    $new_reviews = [];
    $found = false;
    $nama_ulasan = "";

    foreach ($all_reviews as $testi) {
        if (isset($testi['id']) && $testi['id'] === $delete_id) {
            $found = true;
            $nama_ulasan = $testi['nama'];
        } else {
            $new_reviews[] = $testi;
        }
    }

    if ($found) {
        file_put_contents($reviews_file, json_encode($new_reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $all_reviews = $new_reviews;
        $_SESSION['success_message'] = "Ulasan dari '$nama_ulasan' berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Error: Ulasan tidak ditemukan!";
    }
    header("Location: admin.php?action=reviews");
    exit;
}

// Handler POST Update Ulasan
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reviews_update') {
    $review_id = isset($_POST['review_id']) ? $_POST['review_id'] : '';
    $nama = isset($_POST['nama']) ? trim(htmlspecialchars($_POST['nama'])) : "";
    $asal = isset($_POST['asal']) ? trim(htmlspecialchars($_POST['asal'])) : "";
    $bintang = isset($_POST['bintang']) ? intval($_POST['bintang']) : 5;
    $penginapan_id = isset($_POST['penginapan_id']) ? trim($_POST['penginapan_id']) : "";
    $ulasan = isset($_POST['ulasan']) ? trim(htmlspecialchars($_POST['ulasan'])) : "";
    $balasan = isset($_POST['balasan']) ? trim(htmlspecialchars($_POST['balasan'])) : "";
    $tanggal = isset($_POST['tanggal']) ? trim(htmlspecialchars($_POST['tanggal'])) : date('Y-m-d');

    if (!empty($review_id) && !empty($nama) && !empty($ulasan)) {
        $found = false;
        foreach ($all_reviews as &$testi) {
            if (isset($testi['id']) && $testi['id'] === $review_id) {
                $testi['nama'] = $nama;
                $testi['asal'] = $asal;
                $testi['bintang'] = $bintang;
                $testi['penginapan_id'] = $penginapan_id;
                $testi['ulasan'] = $ulasan;
                $testi['balasan'] = $balasan;
                $testi['tanggal'] = $tanggal;
                $found = true;
                break;
            }
        }
        unset($testi);

        if ($found) {
            file_put_contents($reviews_file, json_encode($all_reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $_SESSION['success_message'] = "Ulasan dari '$nama' berhasil diperbarui!";
        } else {
            $_SESSION['error_message'] = "Error: Ulasan tidak ditemukan!";
        }
    } else {
        $_SESSION['error_message'] = "Error: Nama dan Ulasan wajib diisi!";
    }
    header("Location: admin.php?action=reviews");
    exit;
}

// Handler Aksi Delete
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = $_GET['id'];
    $new_daftar_penginapan = [];
    $found = false;
    $nama_delete = "";

    foreach ($daftar_penginapan as $p) {
        if ($p['id'] === $delete_id) {
            $found = true;
            $nama_delete = $p['nama'];
        } else {
            $new_daftar_penginapan[] = $p;
        }
    }

    if ($found) {
        // Hapus file JSON & perbarui list memori
        file_put_contents(__DIR__ . '/penginapan.json', json_encode($new_daftar_penginapan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $daftar_penginapan = $new_daftar_penginapan;

        // Hapus file routing detail page
        $detail_file = __DIR__ . '/detail-page/' . $delete_id . '.php';
        if (file_exists($detail_file)) {
            unlink($detail_file);
        }

        $success_message = "Penginapan '$nama_delete' berhasil dihapus!";
        $_SESSION['success_message'] = $success_message;
    } else {
        $error_message = "Penginapan tidak ditemukan!";
        $_SESSION['error_message'] = $error_message;
    }
    header("Location: admin.php");
    exit;
}

// Ambil Ulasan Counts untuk Stat Dashboard
$reviews_file = __DIR__ . '/reviews.json';
$total_ulasan = 0;
if (file_exists($reviews_file)) {
    $reviews = json_decode(file_get_contents($reviews_file), true);
    if (is_array($reviews)) {
        $total_ulasan = count($reviews);
    }
}

// Data Penginapan Terpilih untuk Edit Mode
$edit_mode = false;
$edit_lodging = null;
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = $_GET['id'];
    foreach ($daftar_penginapan as $p) {
        if ($p['id'] === $edit_id) {
            $edit_mode = true;
            $edit_lodging = $p;
            break;
        }
    }
}

// ==========================================
// HANDLER SLIDER HERO PIN / UNPIN / UPDATE (DECOUPLED FROM CATALOG)
// ==========================================

// Handler POST Pin ke Slider
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'slider_pin') {
    $pin_id = isset($_POST['lodging_id']) ? $_POST['lodging_id'] : '';
    if (!empty($pin_id)) {
        // Cari data penginapan asal
        $source_lodging = null;
        foreach ($daftar_penginapan as $p) {
            if ($p['id'] === $pin_id) {
                $source_lodging = $p;
                break;
            }
        }

        if ($source_lodging !== null) {
            // Cek apakah sudah ada di slider_data
            $already_exists = false;
            foreach ($slider_data as $s) {
                if (isset($s['lodging_id']) && $s['lodging_id'] === $pin_id) {
                    $already_exists = true;
                    break;
                }
            }

            if (!$already_exists) {
                $new_slide = [
                    "id" => uniqid() . '-slide', // ID slide yang unik dan mandiri
                    "lodging_id" => $source_lodging['id'],
                    "nama" => $source_lodging['nama'],
                    "judul_slider" => isset($source_lodging['judul_slider']) ? $source_lodging['judul_slider'] : '',
                    "durasi" => isset($source_lodging['durasi']) ? $source_lodging['durasi'] : '3D2N',
                    "harga" => $source_lodging['harga'],
                    "gambar" => $source_lodging['gambar']
                ];
                $slider_data[] = $new_slide;
                file_put_contents(__DIR__ . '/slider.json', json_encode($slider_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $_SESSION['success_message'] = "Paket '" . $source_lodging['nama'] . "' berhasil disematkan ke Slider Hero secara mandiri!";
            } else {
                $_SESSION['error_message'] = "Error: Paket ini sudah berada di slider!";
            }
        } else {
            $_SESSION['error_message'] = "Error: Paket penginapan tidak ditemukan!";
        }
    }
    header("Location: admin.php?action=manage_slider");
    exit;
}

// Handler GET Unpin dari Slider
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'slider_unpin' && isset($_GET['id'])) {
    $unpin_id = $_GET['id'];
    $found = false;
    $nama_unpin = "";

    foreach ($slider_data as $key => $slide) {
        if ($slide['id'] === $unpin_id) {
            $nama_unpin = $slide['nama'];
            unset($slider_data[$key]);
            $found = true;
            break;
        }
    }

    if ($found) {
        $slider_data = array_values($slider_data);
        file_put_contents(__DIR__ . '/slider.json', json_encode($slider_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $_SESSION['success_message'] = "Slide '" . $nama_unpin . "' berhasil dilepas dari Slider Hero!";
    } else {
        $_SESSION['error_message'] = "Error: Slide tidak ditemukan!";
    }
    header("Location: admin.php?action=manage_slider");
    exit;
}

// Handler POST Update Detail Slide
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'slider_update') {
    $slide_id = isset($_POST['slide_id']) ? $_POST['slide_id'] : '';
    $nama = isset($_POST['nama']) ? trim(htmlspecialchars($_POST['nama'])) : "";
    $judul_slider = isset($_POST['judul_slider']) ? trim(htmlspecialchars($_POST['judul_slider'])) : "";
    $durasi = isset($_POST['durasi']) ? trim(htmlspecialchars($_POST['durasi'])) : "";
    $harga = isset($_POST['harga']) ? trim(htmlspecialchars($_POST['harga'])) : "";

    if (!empty($slide_id) && !empty($nama) && !empty($harga)) {
        $found = false;
        foreach ($slider_data as &$slide) {
            if ($slide['id'] === $slide_id) {
                // Update data slide saja (TIDAK menyentuh catalog penginapan.json)
                $slide['nama'] = $nama;
                $slide['judul_slider'] = $judul_slider;
                $slide['durasi'] = $durasi;
                $slide['harga'] = $harga;

                // Handle upload file gambar baru untuk slide saja jika ada
                if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                    $gambar_path = handleImageUpload('gambar');
                    if (!empty($gambar_path)) {
                        $slide['gambar'] = $gambar_path;
                    }
                }

                $found = true;
                break;
            }
        }
        unset($slide);

        if ($found) {
            file_put_contents(__DIR__ . '/slider.json', json_encode($slider_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $_SESSION['success_message'] = "Detail Slide '$nama' berhasil diperbarui secara mandiri!";
        } else {
            $_SESSION['error_message'] = "Error: Slide tidak ditemukan!";
        }
    } else {
        $_SESSION['error_message'] = "Error: Semua parameter wajib diisi!";
    }
    header("Location: admin.php?action=manage_slider");
    exit;
}

// Mode Edit Slide Terpilih
$slide_edit_mode = false;
$edit_slide_item = null;
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'manage_slider' && isset($_GET['edit_slide_id'])) {
    $edit_slide_id = $_GET['edit_slide_id'];
    foreach ($slider_data as $s) {
        if ($s['id'] === $edit_slide_id) {
            $slide_edit_mode = true;
            $edit_slide_item = $s;
            break;
        }
    }
}

// ==========================================
// HANDLER CRUD GALERI FOTO (JSON)
// ==========================================

// Handler POST Tambah / Update Foto
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'gallery_create' || $action === 'gallery_update') {
        $alt = isset($_POST['alt']) ? trim(htmlspecialchars($_POST['alt'])) : "";
        $kategori = isset($_POST['kategori']) ? trim($_POST['kategori']) : "destinasi";
        $tag = isset($_POST['tag']) ? trim(htmlspecialchars($_POST['tag'])) : "";
        $posisi = isset($_POST['posisi']) ? trim($_POST['posisi']) : "center center";

        $item_index = isset($_POST['item_index']) ? intval($_POST['item_index']) : -1;

        // Cari path gambar lama jika update
        $file_path = "";
        if ($action === 'gallery_update' && $item_index >= 0) {
            $file_path = isset($galeri_foto[$item_index]['file']) ? $galeri_foto[$item_index]['file'] : "";
        }

        // Handle upload file baru
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file_path = handleImageUpload('file');
        }

        if (empty($file_path)) {
            $error_message = "Error: File gambar wajib diunggah!";
        } else {
            $galleryItem = [
                "file" => $file_path,
                "alt" => $alt,
                "posisi" => $posisi,
                "kategori" => $kategori,
                "tag" => $tag
            ];

            if ($action === 'gallery_create') {
                $galeri_foto[] = $galleryItem;
                $success_message = "Foto baru berhasil ditambahkan ke galeri!";
            } else { // gallery_update
                if ($item_index >= 0 && isset($galeri_foto[$item_index])) {
                    $galeri_foto[$item_index] = $galleryItem;
                    $success_message = "Foto galeri berhasil diperbarui!";
                } else {
                    $error_message = "Error: Indeks foto galeri tidak valid!";
                }
            }

            if (empty($error_message)) {
                // Tulis kembali ke JSON file
                file_put_contents(__DIR__ . '/galeri.json', json_encode($galeri_foto, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $_SESSION['success_message'] = $success_message;
                header("Location: admin.php?action=gallery");
                exit;
            }
        }
    }
}

// Handler GET Hapus Foto
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'gallery_delete' && isset($_GET['index'])) {
    $delete_index = intval($_GET['index']);
    if (isset($galeri_foto[$delete_index])) {
        $alt_delete = isset($galeri_foto[$delete_index]['alt']) ? $galeri_foto[$delete_index]['alt'] : "Foto";

        // Hapus item dari array
        array_splice($galeri_foto, $delete_index, 1);
        file_put_contents(__DIR__ . '/galeri.json', json_encode($galeri_foto, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $success_message = "Foto '$alt_delete' berhasil dihapus dari galeri!";
        $_SESSION['success_message'] = $success_message;
    } else {
        $error_message = "Error: Foto galeri tidak ditemukan!";
        $_SESSION['error_message'] = $error_message;
    }
    header("Location: admin.php?action=gallery");
    exit;
}

// Mode Edit Galeri Terpilih
$gallery_edit_mode = false;
$edit_gallery_item = null;
$edit_gallery_index = -1;
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'gallery_edit' && isset($_GET['index'])) {
    $edit_gallery_index = intval($_GET['index']);
    if (isset($galeri_foto[$edit_gallery_index])) {
        $gallery_edit_mode = true;
        $edit_gallery_item = $galeri_foto[$edit_gallery_index];
    }
}

// Mode Edit Ulasan Terpilih
$reviews_edit_mode = false;
$edit_review_item = null;
if ($is_logged_in && isset($_GET['action']) && $_GET['action'] === 'reviews_edit' && isset($_GET['id'])) {
    $edit_review_id = $_GET['id'];
    foreach ($all_reviews as $testi) {
        if (isset($testi['id']) && $testi['id'] === $edit_review_id) {
            $reviews_edit_mode = true;
            $edit_review_item = $testi;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Karimunjawa Vibes Trip</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- HEIC to JPEG conversion library -->
    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>

    <!-- CSS Internal Dashboard Premium -->
    <style>
        :root {
            --primary-dark: #0F2D2E;
            --primary-teal: #1CBBB4;
            --accent-orange: #FF7B54;
            --dark-bg: #0B1717;
            --card-bg: rgba(20, 38, 38, 0.6);
            --border-color: rgba(28, 187, 180, 0.15);
            --text-light: #F4F6F6;
            --text-muted: #8AA0A0;
            --warm-gold: #FFB800;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Glassmorphism Background Gradients */
        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 10% 20%, rgba(15, 45, 46, 0.6) 0%, rgba(11, 23, 23, 1) 90%);
        }

        .bg-glow-1 {
            position: fixed;
            top: -20%;
            right: -20%;
            width: 60vw;
            height: 60vw;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(28, 187, 180, 0.12) 0%, rgba(0, 0, 0, 0) 70%);
            z-index: -1;
            pointer-events: none;
        }

        /* Login Screen */
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: fadeInUp 0.6s ease-out;
        }

        .login-logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-light);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-logo span {
            color: var(--primary-teal);
        }

        .login-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            background: rgba(10, 20, 20, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text-light);
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-teal);
            box-shadow: 0 0 10px rgba(28, 187, 180, 0.25);
            background: rgba(10, 20, 20, 0.8);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-teal) 0%, #159c96 100%);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(28, 187, 180, 0.3);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 187, 180, 0.5);
        }

        .alert-error {
            background-color: rgba(255, 123, 84, 0.15);
            border: 1px solid var(--accent-orange);
            color: var(--accent-orange);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }

        /* Dashboard Container */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(10, 22, 22, 0.95);
            border-right: 1px solid var(--border-color);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .sidebar-brand {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-light);
            margin-bottom: 40px;
            padding-left: 10px;
        }

        .sidebar-brand span {
            color: var(--primary-teal);
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: var(--text-light);
            background: rgba(28, 187, 180, 0.1);
        }

        .sidebar-link.active {
            border-left: 4px solid var(--primary-teal);
            border-radius: 0 12px 12px 0;
            background: linear-gradient(90deg, rgba(28, 187, 180, 0.15) 0%, rgba(28, 187, 180, 0) 100%);
        }

        .sidebar-footer {
            margin-top: auto;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 40px;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-light);
        }

        .btn-new {
            background: linear-gradient(135deg, var(--primary-teal) 0%, #159c96 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(28, 187, 180, 0.2);
            transition: all 0.3s ease;
        }

        .btn-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 187, 180, 0.4);
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: 24px;
            border-radius: 20px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(28, 187, 180, 0.3);
        }

        .stat-icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(28, 187, 180, 0.1);
            color: var(--primary-teal);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-info h4 {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .stat-info p {
            font-size: 26px;
            font-weight: 800;
        }

        /* List Table View */
        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .admin-table th {
            padding: 16px 20px;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(28, 187, 180, 0.1);
        }

        .admin-table td {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(28, 187, 180, 0.05);
            font-size: 14px;
            vertical-align: middle;
        }

        .admin-table tr:hover td {
            background: rgba(28, 187, 180, 0.02);
        }

        .lodging-row-thumb {
            width: 64px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .badge-table {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-table.teal {
            background: rgba(28, 187, 180, 0.15);
            color: var(--primary-teal);
        }

        .badge-table.orange {
            background: rgba(255, 123, 84, 0.15);
            color: var(--accent-orange);
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: rgba(28, 187, 180, 0.05);
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-action:hover {
            background: var(--primary-teal);
            color: white;
            border-color: var(--primary-teal);
            transform: scale(1.05);
        }

        .btn-action.btn-delete:hover {
            background: var(--accent-orange);
            border-color: var(--accent-orange);
        }

        /* Forms Layout */
        .form-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 32px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            margin-bottom: 40px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Responsive Top Bar */
        .mobile-nav-header {
            display: none;
        }

        .mobile-menu-toggle {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid rgba(28, 187, 180, 0.15);
            background: rgba(28, 187, 180, 0.05);
        }

        .mobile-menu-toggle:active {
            background: rgba(28, 187, 180, 0.2);
        }

        @media (max-width: 991px) {
            .mobile-nav-header {
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 70px;
                background: rgba(10, 22, 22, 0.98);
                border-bottom: 1px solid var(--border-color);
                justify-content: space-between;
                align-items: center;
                padding: 0 20px;
                z-index: 101;
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }

            .sidebar {
                position: fixed;
                top: 70px;
                left: -290px;
                bottom: 0;
                width: 280px;
                height: calc(100vh - 70px);
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 100;
                background: rgba(10, 22, 22, 0.98);
                box-shadow: 15px 0 30px rgba(0, 0, 0, 0.5);
            }

            .sidebar.open {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding: 100px 20px 30px 20px;
            }

            .dashboard-wrapper {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .form-card {
                padding: 20px;
                border-radius: 16px;
            }

            .table-card {
                padding: 16px;
                border-radius: 16px;
            }

            .admin-table th,
            .admin-table td {
                padding: 12px 14px;
            }

            .page-title {
                font-size: 24px;
            }
        }

        @media (max-width: 576px) {
            .header-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 20px;
            }

            .header-row .btn-new,
            .header-row .btn-secondary {
                width: 100%;
                justify-content: center;
                padding: 12px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                margin-bottom: 25px;
            }

            .toast-container {
                left: 20px;
                right: 20px;
            }

            .toast-alert {
                min-width: auto;
                width: 100%;
            }
        }

        .card-subtitle-divider {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary-teal);
            margin-top: 30px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(28, 187, 180, 0.15);
            padding-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-alert {
            min-width: 300px;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            animation: slideInRight 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .toast-success {
            background: linear-gradient(135deg, #0F5A47 0%, #15735b 100%);
            border: 1px solid #1CBBB4;
        }

        .toast-error {
            background: linear-gradient(135deg, #7C2A1A 0%, #9B3926 100%);
            border: 1px solid var(--accent-orange);
        }

        .toast-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 15px;
        }

        /* Room Type Dynamic Row Styling */
        .room-type-item {
            background: rgba(10, 20, 20, 0.3);
            border: 1px dashed var(--border-color);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
        }

        .room-type-item:hover {
            border-color: var(--primary-teal);
        }

        .room-type-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .room-type-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-light);
        }

        .btn-remove-rt {
            background: none;
            border: none;
            color: var(--accent-orange);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-card {
            background: rgba(15, 30, 30, 0.95);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .modal-icon {
            font-size: 40px;
            color: var(--accent-orange);
            margin-bottom: 15px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .modal-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-modal {
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }

        .btn-modal-cancel {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-modal-delete {
            background: var(--accent-orange);
            color: white;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <div class="bg-gradient"></div>
    <div class="bg-glow-1"></div>

    <!-- Toast Alerts -->
    <div class="toast-container">
        <?php if (!empty($success_message)): ?>
            <div class="toast-alert toast-success" id="successToast">
                <span>✓ <?php echo $success_message; ?></span>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="toast-alert toast-error" id="errorToast">
                <span>⚠ <?php echo $error_message; ?></span>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$is_logged_in): ?>
        <!-- LOGIN SCREEN -->
        <div class="login-container">
            <div class="login-card">
                <div class="login-logo"
                    style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px;">
                    <img src="assets/images/logo.png" alt="Logo" style="height: 36px; object-fit: contain;">
                    <span>KVIBESTRIP <span style="color: var(--primary-teal);">ADMIN</span></span>
                </div>
                <div class="login-subtitle">Dashboard Pengelolaan Penginapan</div>

                <?php if (!empty($login_error)): ?>
                    <div class="alert-error"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <form action="admin.php" method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-control" type="text" id="username" name="username"
                            placeholder="Masukkan username" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" type="password" id="password" name="password"
                            placeholder="Masukkan password" required autocomplete="current-password">
                    </div>
                    <button class="btn-submit" type="submit">LOG IN</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- ADMIN DASHBOARD PANEL -->
        <!-- Mobile Top Navigation Header -->
        <div class="mobile-nav-header">
            <div class="sidebar-brand"
                style="margin-bottom: 0; padding-left: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                <img src="assets/images/logo.png" alt="Logo" style="height: 28px; object-fit: contain;">
                <span>KVIBESTRIP <span>ADMIN</span></span>
            </div>
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="dashboard-wrapper">
            <!-- Sidebar Navigation -->
            <aside class="sidebar">
                <div class="sidebar-brand"
                    style="display: flex; align-items: center; gap: 8px; margin-bottom: 30px; padding-left: 0;">
                    <img src="assets/images/logo.png" alt="Logo" style="height: 32px; object-fit: contain;">
                    <span>KVIBESTRIP <span>ADMIN</span></span>
                </div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="admin.php"
                            class="sidebar-link <?php echo (!$edit_mode && !isset($_GET['action'])) ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="feather feather-grid">
                                <rect x="3" y="3" width="7" height="9"></rect>
                                <rect x="14" y="3" width="7" height="5"></rect>
                                <rect x="14" y="12" width="7" height="9"></rect>
                                <rect x="3" y="16" width="7" height="5"></rect>
                            </svg>
                            Daftar Penginapan
                        </a>
                    </li>
                    <li>
                        <a href="admin.php?action=new"
                            class="sidebar-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'new') ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="feather feather-plus-circle">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Tambah Penginapan
                        </a>
                    </li>
                    <li>
                        <a href="admin.php?action=manage_slider"
                            class="sidebar-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'manage_slider') ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="feather feather-sliders">
                                <line x1="4" y1="21" x2="4" y2="14"></line>
                                <line x1="4" y1="10" x2="4" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12" y2="3"></line>
                                <line x1="20" y1="21" x2="20" y2="16"></line>
                                <line x1="20" y1="12" x2="20" y2="3"></line>
                                <line x1="1" y1="14" x2="7" y2="14"></line>
                                <line x1="9" y1="8" x2="15" y2="8"></line>
                                <line x1="17" y1="16" x2="23" y2="16"></line>
                            </svg>
                            Kelola Slider Hero
                        </a>
                    </li>
                    <li>
                        <a href="admin.php?action=reviews"
                            class="sidebar-link <?php echo (isset($_GET['action']) && in_array($_GET['action'], ['reviews', 'reviews_edit'])) ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="feather feather-message-square">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            Kelola Ulasan
                        </a>
                    </li>
                    <li>
                        <a href="admin.php?action=gallery"
                            class="sidebar-link <?php echo (isset($_GET['action']) && in_array($_GET['action'], ['gallery', 'gallery_new', 'gallery_edit'])) ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="feather feather-image">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            Kelola Galeri
                        </a>
                    </li>
                </ul>
                <div class="sidebar-footer">
                    <a href="admin.php?action=logout" class="sidebar-link"
                        style="color: var(--accent-orange); background: rgba(255,123,84,0.05);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Log Out
                    </a>
                </div>
            </aside>

            <!-- Main Workspace -->
            <main class="main-content">

                <?php if ($edit_mode || (isset($_GET['action']) && $_GET['action'] === 'new')): ?>
                    <!-- FORM TAMBAH / EDIT MODE -->
                    <div class="header-row">
                        <h1 class="page-title">
                            <?php echo $edit_mode ? 'Edit Penginapan: ' . $edit_lodging['nama'] : 'Tambah Penginapan Baru'; ?>
                        </h1>
                        <a href="admin.php" class="btn-secondary">Kembali</a>
                    </div>

                    <form class="form-card"
                        action="admin.php?action=<?php echo $edit_mode ? 'edit&id=' . $edit_lodging['id'] : 'new'; ?>"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="old_id" value="<?php echo $edit_lodging['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label" for="nama">Nama Penginapan</label>
                            <input class="form-control" type="text" id="nama" name="nama" placeholder="Contoh: Homestay Azza"
                                value="<?php echo $edit_mode ? $edit_lodging['nama'] : ''; ?>" required>
                        </div>

                        <div class="form-row"
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div class="form-group">
                                <label class="form-label" for="harga">Harga Paket 3D2N</label>
                                <input class="form-control" type="text" id="harga" name="harga"
                                    placeholder="Contoh: Rp. 1.400.000 / pax"
                                    value="<?php echo $edit_mode ? $edit_lodging['harga'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="harga_2d1n">Harga Paket 2D1N</label>
                                <input class="form-control" type="text" id="harga_2d1n" name="harga_2d1n"
                                    placeholder="Contoh: Rp. 1.200.000 / pax"
                                    value="<?php echo $edit_mode ? (isset($edit_lodging['harga_2d1n']) ? $edit_lodging['harga_2d1n'] : '') : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="harga_4d3n">Harga Paket 4D3N</label>
                                <input class="form-control" type="text" id="harga_4d3n" name="harga_4d3n"
                                    placeholder="Contoh: Rp. 1.850.000 / pax"
                                    value="<?php echo $edit_mode ? (isset($edit_lodging['harga_4d3n']) ? $edit_lodging['harga_4d3n'] : '') : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="harga_honeymoon">Harga Honeymoon</label>
                                <input class="form-control" type="text" id="harga_honeymoon" name="harga_honeymoon"
                                    placeholder="Contoh: Rp. 4.500.000 / couple"
                                    value="<?php echo $edit_mode ? (isset($edit_lodging['harga_honeymoon']) ? $edit_lodging['harga_honeymoon'] : '') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="lokasi">Lokasi / Alamat</label>
                                <input class="form-control" type="text" id="lokasi" name="lokasi"
                                    placeholder="Alamat lengkap di Karimunjawa"
                                    value="<?php echo $edit_mode ? $edit_lodging['lokasi'] : ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="badge">Badge Promo (Opsional)</label>
                                <input class="form-control" type="text" id="badge" name="badge"
                                    placeholder="Contoh: Best Value, Budget Friendly"
                                    value="<?php echo $edit_mode ? (isset($edit_lodging['badge']) ? $edit_lodging['badge'] : '') : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="badge_class">Tipe Badge Class</label>
                                <select class="form-control" id="badge_class" name="badge_class">
                                    <option value="" <?php echo ($edit_mode && empty($edit_lodging['badge_class'])) ? 'selected' : ''; ?>>Biasa</option>
                                    <option value="orange" <?php echo ($edit_mode && isset($edit_lodging['badge_class']) && $edit_lodging['badge_class'] === 'orange') ? 'selected' : ''; ?>>Spesial</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="deskripsi">Deskripsi Singkat</label>
                            <input class="form-control" type="text" id="deskripsi" name="deskripsi"
                                placeholder="Teks promo singkat untuk beranda list..."
                                value="<?php echo $edit_mode ? $edit_lodging['deskripsi'] : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="detail_deskripsi">Deskripsi Detail Halaman</label>
                            <textarea class="form-control" id="detail_deskripsi" name="detail_deskripsi" rows="5"
                                placeholder="Teks penjelasan lengkap mengenai akomodasi, fasilitas tambahan, dll..."
                                required><?php echo $edit_mode ? $edit_lodging['detail_deskripsi'] : ''; ?></textarea>
                        </div>

                        <!-- FOTO UTAMA DAN GALERI -->
                        <div class="card-subtitle-divider"><span>FOTO UTAMA & THUMBNAIL</span></div>
                        <div class="form-group">
                            <label class="form-label">Upload File Foto Utama</label>
                            <?php if ($edit_mode && !empty($edit_lodging['gambar'])): ?>
                                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 12px;">
                                    <img src="<?php echo htmlspecialchars($edit_lodging['gambar']); ?>"
                                        style="width: 100px; height: 75px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.15);"
                                        alt="Thumbnail Sekarang">
                                    <span style="font-size: 13px; color: var(--text-muted);">Foto saat ini</span>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="gambar" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                        </div>

                        <div class="card-subtitle-divider"><span>5 FOTO GALERI UTAMA</span></div>
                        <div class="form-row"
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <?php for ($i = 0; $i < 5; $i++):
                                $gal_val = ($edit_mode && isset($edit_lodging['foto_galeri'][$i])) ? $edit_lodging['foto_galeri'][$i] : '';
                                ?>
                                <div class="form-group"
                                    style="background: rgba(10,20,20,0.3); border: 1px dashed var(--border-color); border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                                    <label class="form-label" style="font-size: 11px; margin-bottom: 0;">Foto Galeri
                                        <?php echo $i + 1; ?></label>
                                    <?php if (!empty($gal_val)): ?>
                                        <div
                                            style="width: 100%; height: 100px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                            <img src="<?php echo htmlspecialchars($gal_val); ?>"
                                                style="width: 100%; height: 100%; object-fit: cover;" alt="Galeri <?php echo $i + 1; ?>">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="foto_galeri_<?php echo $i; ?>" accept="image/*"
                                        <?php echo $edit_mode ? '' : 'required'; ?>
                                        style="padding: 6px 12px; font-size: 12px; margin-top: auto;">
                                </div>
                            <?php endfor; ?>
                        </div>

                        <!-- DYNAMIC ROOM TYPES (TIPE KAMAR) -->
                        <div class="card-subtitle-divider">
                            <span>TIPE KAMAR</span>
                            <button type="button" class="btn-new" style="padding: 6px 12px; font-size: 12px; gap: 4px;"
                                onclick="addRoomTypeRow()">
                                + Tambah Kelas Kamar
                            </button>
                        </div>

                        <div id="room-types-container">
                            <?php
                            if ($edit_mode && isset($edit_lodging['tipe_kamar']) && is_array($edit_lodging['tipe_kamar'])):
                                foreach ($edit_lodging['tipe_kamar'] as $index => $rt):
                                    ?>
                                    <div class="room-type-item" id="rt-item-<?php echo $index; ?>">
                                        <div class="room-type-header">
                                            <span class="room-type-title">Tipe Kamar #<?php echo $index + 1; ?></span>
                                            <button type="button" class="btn-remove-rt"
                                                onclick="removeRoomTypeRow(<?php echo $index; ?>)">
                                                ✕ Hapus Kamar
                                            </button>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Nama Tipe Kamar</label>
                                            <input class="form-control" type="text" name="room_type_name[<?php echo $index; ?>]"
                                                value="<?php echo $rt['nama']; ?>" required>
                                        </div>
                                        <div class="form-row"
                                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                                            <div class="form-group">
                                                <label class="form-label">Harga 3D2N</label>
                                                <input class="form-control" type="text" name="room_type_price[<?php echo $index; ?>]"
                                                    placeholder="Contoh: Rp 2.050.000 / pax" value="<?php echo $rt['harga']; ?>"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Harga 2D1N</label>
                                                <input class="form-control" type="text"
                                                    name="room_type_price_2d1n[<?php echo $index; ?>]"
                                                    placeholder="Contoh: Rp 1.725.000 / pax"
                                                    value="<?php echo isset($rt['harga_2d1n']) ? $rt['harga_2d1n'] : ''; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Harga 4D3N</label>
                                                <input class="form-control" type="text"
                                                    name="room_type_price_4d3n[<?php echo $index; ?>]"
                                                    placeholder="Contoh: Rp 2.950.000 / pax"
                                                    value="<?php echo isset($rt['harga_4d3n']) ? $rt['harga_4d3n'] : ''; ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Harga Honeymoon</label>
                                                <input class="form-control" type="text"
                                                    name="room_type_price_honeymoon[<?php echo $index; ?>]"
                                                    placeholder="Contoh: Rp 4.500.000 / couple"
                                                    value="<?php echo isset($rt['harga_honeymoon']) ? $rt['harga_honeymoon'] : ''; ?>">
                                            </div>
                                        </div>

                                        <label class="form-label" style="margin-top: 15px; display: block;">5 Foto Galeri Tipe
                                            Kamar</label>
                                        <div class="form-row"
                                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 5px;">
                                            <?php for ($k = 0; $k < 5; $k++):
                                                $rt_gal = isset($rt['foto_galeri'][$k]) ? $rt['foto_galeri'][$k] : '';
                                                ?>
                                                <div
                                                    style="background: rgba(10,20,20,0.4); border: 1px dotted var(--border-color); border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                                                    <label class="form-label" style="font-size: 10px; margin-bottom: 0;">Foto
                                                        <?php echo $k + 1; ?></label>
                                                    <?php if (!empty($rt_gal)): ?>
                                                        <div
                                                            style="width: 100%; height: 70px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                                                            <img src="<?php echo htmlspecialchars($rt_gal); ?>"
                                                                style="width: 100%; height: 100%; object-fit: cover;"
                                                                alt="Kamar <?php echo $k + 1; ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control"
                                                        name="room_type_gallery_<?php echo $index; ?>_<?php echo $k; ?>" accept="image/*"
                                                        style="padding: 4px 8px; font-size: 11px; height: 30px; margin-top: auto;">
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                <?php
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <!-- SUBMIT -->
                        <div style="margin-top: 40px; display: flex; gap: 15px; justify-content: flex-end;">
                            <a href="admin.php" class="btn-secondary" style="padding: 14px 28px;">Batal</a>
                            <button type="submit" class="btn-new" style="padding: 14px 32px;">
                                <?php echo $edit_mode ? 'Simpan Perubahan' : 'Tambah Penginapan'; ?>
                            </button>
                        </div>
                    </form>

                    <!-- Javascript Helper untuk Tambah Baris Kamar secara Dinamis -->
                    <script>
                        let rtCounter = <?php echo ($edit_mode && isset($edit_lodging['tipe_kamar'])) ? count($edit_lodging['tipe_kamar']) : 0; ?>;

                        function addRoomTypeRow() {
                            const container = document.getElementById('room-types-container');
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'room-type-item';
                            itemDiv.id = `rt-item-${rtCounter}`;

                            let galleryHTML = '<div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 5px;">';
                            for (let k = 0; k < 5; k++) {
                                galleryHTML += `
                                <div style="background: rgba(10,20,20,0.4); border: 1px dotted var(--border-color); border-radius: 12px; padding: 12px; text-align: center;">
                                    <label class="form-label" style="font-size: 10px; margin-bottom: 4px;">Foto ${k + 1}</label>
                                    <input type="file" class="form-control" name="room_type_gallery_${rtCounter}_${k}" accept="image/*" style="padding: 4px 8px; font-size: 11px; height: 30px;" required>
                                </div>
                            `;
                            }
                            galleryHTML += '</div>';

                            itemDiv.innerHTML = `
                            <div class="room-type-header">
                                <span class="room-type-title">Tipe Kamar Baru #${rtCounter + 1}</span>
                                <button type="button" class="btn-remove-rt" onclick="removeRoomTypeRow(${rtCounter})">
                                    ✕ Hapus Kamar
                                </button>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Tipe Kamar</label>
                                <input class="form-control" type="text" name="room_type_name[${rtCounter}]" required>
                            </div>
                            <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                                <div class="form-group">
                                    <label class="form-label">Harga 3D2N (Default)</label>
                                    <input class="form-control" type="text" name="room_type_price[${rtCounter}]" placeholder="Contoh: Rp 2.050.000 / pax" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Harga 2D1N (Opsional)</label>
                                    <input class="form-control" type="text" name="room_type_price_2d1n[${rtCounter}]" placeholder="Contoh: Rp 1.725.000 / pax">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Harga 4D3N (Opsional)</label>
                                    <input class="form-control" type="text" name="room_type_price_4d3n[${rtCounter}]" placeholder="Contoh: Rp 2.950.000 / pax">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Harga Honeymoon (Opsional)</label>
                                    <input class="form-control" type="text" name="room_type_price_honeymoon[${rtCounter}]" placeholder="Contoh: Rp 4.500.000 / couple">
                                </div>
                            </div>
                            
                            <label class="form-label" style="margin-top: 15px; display: block;">5 Foto Galeri Tipe Kamar</label>
                            ${galleryHTML}
                        `;

                            container.appendChild(itemDiv);
                            rtCounter++;
                        }

                        function removeRoomTypeRow(index) {
                            const item = document.getElementById(`rt-item-${index}`);
                            if (item) {
                                item.remove();
                            }
                        }
                    </script>


                <?php elseif (isset($_GET['action']) && $_GET['action'] === 'reviews'): ?>
                    <!-- TABEL KELOLA ULASAN -->
                    <div class="header-row">
                        <div>
                            <h1 class="page-title">Kelola Ulasan & Testimoni</h1>
                            <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Jawab ulasan dari wisatawan
                                untuk penginapan maupun tour guide</p>
                        </div>
                    </div>

                    <div class="table-card">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 160px;">Wisatawan</th>
                                        <th style="width: 110px;">Rating</th>
                                        <th style="width: 180px;">Terkait</th>
                                        <th>Ulasan</th>
                                        <th style="width: 300px;">Balasan Admin</th>
                                        <th style="width: 100px; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_reviews)): ?>
                                        <?php
                                        // Lookup lodging names
                                        $lodging_names = [];
                                        foreach ($daftar_penginapan as $p) {
                                            $lodging_names[$p['id']] = $p['nama'];
                                        }

                                        foreach ($all_reviews as $testi):
                                            $assoc_name = "Umum / Tour Guide";
                                            if (!empty($testi['penginapan_id'])) {
                                                $assoc_id = $testi['penginapan_id'];
                                                $assoc_name = isset($lodging_names[$assoc_id]) ? $lodging_names[$assoc_id] : "Penginapan (ID: $assoc_id)";
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($testi['nama']); ?></strong>
                                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                                        Dari <?php echo htmlspecialchars($testi['asal']); ?><br>
                                                        <?php echo isset($testi['tanggal']) ? $testi['tanggal'] : date('Y-m-d'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        style="color: var(--accent-orange); font-weight: bold; font-size: 15px; letter-spacing: -1px;">
                                                        <?php
                                                        $stars = isset($testi['bintang']) ? intval($testi['bintang']) : 5;
                                                        for ($i = 0; $i < $stars; $i++)
                                                            echo "★";
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        style="font-size: 12px; font-weight: 600; color: var(--primary-teal); background: rgba(28,187,180,0.08); padding: 4px 8px; border-radius: 6px;">
                                                        <?php echo htmlspecialchars($assoc_name); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div
                                                        style="font-style: italic; font-size: 13.5px; line-height: 1.4; color: var(--text-light);">
                                                        "<?php echo htmlspecialchars($testi['ulasan']); ?>"
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($testi['balasan'])): ?>
                                                        <div
                                                            style="background: rgba(28,187,180,0.05); border-left: 2px solid var(--primary-teal); padding: 8px 12px; border-radius: 4px; font-size: 13px; margin-bottom: 8px; color: #D5E0E0; line-height: 1.4;">
                                                            <?php echo htmlspecialchars($testi['balasan']); ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Form Balas Inline -->
                                                    <form id="form-reply-<?php echo htmlspecialchars($testi['id']); ?>" action="admin.php?action=reviews" method="POST"
                                                        style="margin: 0; width: 100%;">
                                                        <input type="hidden" name="action" value="reply_review">
                                                        <input type="hidden" name="review_id"
                                                            value="<?php echo htmlspecialchars($testi['id']); ?>">
                                                        <textarea class="form-control" name="balasan"
                                                            placeholder="<?php echo empty($testi['balasan']) ? 'Tulis balasan...' : 'Ubah balasan...'; ?>"
                                                            style="padding: 10px 14px; font-size: 13px; background: rgba(10,20,20,0.3); border-radius: 8px; height: 80px; resize: vertical; border: 1px solid var(--border-color); color: var(--text-light); line-height: 1.4; width: 100%; font-family: inherit;"><?php echo isset($testi['balasan']) ? htmlspecialchars($testi['balasan']) : ''; ?></textarea>
                                                    </form>
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <div style="display: flex; flex-direction: column; gap: 8px; align-items: center; justify-content: center;">
                                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                                            <a href="admin.php?action=reviews_edit&id=<?php echo htmlspecialchars($testi['id']); ?>"
                                                                class="btn-action" title="Edit Ulasan"
                                                                style="background: rgba(28,187,180,0.1); color: var(--primary-teal); border: 1px solid rgba(28,187,180,0.2); width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round" class="feather feather-edit-2">
                                                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                                    </path>
                                                                </svg>
                                                            </a>
                                                            <a href="#"
                                                                onclick="confirmReviewsDelete('<?php echo htmlspecialchars($testi['id']); ?>', '<?php echo addslashes(htmlspecialchars($testi['nama'])); ?>')"
                                                                class="btn-action btn-delete" title="Hapus Ulasan"
                                                                style="background: rgba(255,123,84,0.1); color: var(--accent-orange); border: 1px solid rgba(255,123,84,0.2); width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                    stroke-linejoin="round" class="feather feather-trash-2">
                                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                                    <path
                                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                                    </path>
                                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                                </svg>
                                                            </a>
                                                        </div>
                                                        <button type="submit" form="form-reply-<?php echo htmlspecialchars($testi['id']); ?>" class="btn-new"
                                                            style="padding: 0 12px; font-size: 11.5px; height: 30px; border-radius: 6px; flex-shrink: 0; box-shadow: none; width: 70px; display: flex; align-items: center; justify-content: center; cursor: pointer; margin: 0;">
                                                            Simpan
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6"
                                                style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
                                                Belum ada ulasan yang masuk.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif (isset($_GET['action']) && $_GET['action'] === 'reviews_edit' && $reviews_edit_mode && $edit_review_item !== null): ?>
                    <!-- FORM EDIT ULASAN -->
                    <div class="header-row">
                        <div>
                            <h1 class="page-title">Edit Ulasan Wisatawan</h1>
                            <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Ubah isi ulasan, rating,
                                tanggal, maupun balasan admin</p>
                        </div>
                        <a href="admin.php?action=reviews" class="btn-secondary">Kembali</a>
                    </div>

                    <form class="form-card"
                        action="admin.php?action=reviews_edit&id=<?php echo htmlspecialchars($edit_review_item['id']); ?>"
                        method="POST">
                        <input type="hidden" name="action" value="reviews_update">
                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($edit_review_item['id']); ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nama Wisatawan <span style="color: var(--accent-orange);*</span></label>
                                <input type=" text" name="nama" class="form-control"
                                        value="<?php echo htmlspecialchars($edit_review_item['nama']); ?>" required
                                        style="background-color: var(--card-bg); color: var(--text-light); height: 42px;">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Asal Wisatawan</label>
                                <input type="text" name="asal" class="form-control"
                                    value="<?php echo htmlspecialchars($edit_review_item['asal']); ?>"
                                    style="background-color: var(--card-bg); color: var(--text-light); height: 42px;">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Rating Bintang <span
                                        style="color: var(--accent-orange);">*</span></label>
                                <select name="bintang" class="form-control"
                                    style="background-color: var(--card-bg); color: var(--text-light); height: 42px;" required>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (intval($edit_review_item['bintang']) === $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> Bintang
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Terkait Akomodasi</label>
                                <select name="penginapan_id" class="form-control"
                                    style="background-color: var(--card-bg); color: var(--text-light); height: 42px;">
                                    <option value="" <?php echo empty($edit_review_item['penginapan_id']) ? 'selected' : ''; ?>>
                                        Umum / Tour Guide</option>
                                    <?php foreach ($daftar_penginapan as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo (isset($edit_review_item['penginapan_id']) && $edit_review_item['penginapan_id'] === $p['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tanggal Ulasan <span style="color: var(--accent-orange);">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                style="background-color: var(--card-bg); color: var(--text-light); height: 42px;"
                                value="<?php echo isset($edit_review_item['tanggal']) ? htmlspecialchars($edit_review_item['tanggal']) : date('Y-m-d'); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Isi Ulasan <span style="color: var(--accent-orange);">*</span></label>
                            <textarea name="ulasan" class="form-control" rows="4" required
                                style="background-color: var(--card-bg); color: var(--text-light); padding: 12px; font-size: 14px; line-height: 1.5;"><?php echo htmlspecialchars($edit_review_item['ulasan']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Balasan Admin</label>
                            <textarea name="balasan" class="form-control" rows="4" placeholder="Tulis balasan admin jika ada..."
                                style="background-color: var(--card-bg); color: var(--text-light); padding: 12px; font-size: 14px; line-height: 1.5;"><?php echo isset($edit_review_item['balasan']) ? htmlspecialchars($edit_review_item['balasan']) : ''; ?></textarea>
                        </div>

                        <div style="margin-top: 25px; display: flex; gap: 10px;">
                            <button type="submit" class="btn-new">Simpan Perubahan</button>
                            <a href="admin.php?action=reviews" class="btn-secondary"
                                style="line-height: 38px; display: inline-block; text-align: center; text-decoration: none;">Batal</a>
                        </div>
                    </form>

                <?php elseif (isset($_GET['action']) && $_GET['action'] === 'manage_slider'): ?>
                    <!-- KELOLA DATA SLIDER HERO -->
                    <div class="header-row">
                        <div>
                            <h1 class="page-title">Kelola Slider Hero Beranda</h1>
                            <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Kelola paket penginapan yang
                                disematkan pada banner utama halaman publik</p>
                        </div>
                    </div>

                    <?php if ($slide_edit_mode && $edit_slide_item !== null): ?>
                        <!-- Form Edit Detail Slide -->
                        <div class="table-card" style="padding: 20px; margin-top: 20px; border: 1px solid rgba(28, 187, 180, 0.3);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="font-size: 16px; font-weight: 700; color: #FFF; margin: 0;">Edit Detail Slide:
                                    <?php echo htmlspecialchars($edit_slide_item['nama']); ?></h3>
                                <a href="admin.php?action=manage_slider"
                                    style="color: var(--text-muted); font-size: 13px; text-decoration: none;">Batal ×</a>
                            </div>
                            <form action="admin.php?action=manage_slider" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="slide_id" value="<?php echo $edit_slide_item['id']; ?>">

                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label class="form-label" for="judul_slider">Judul Slider (Teks Bercetak Tebal di Halaman
                                        Utama)</label>
                                    <input class="form-control" type="text" id="judul_slider" name="judul_slider"
                                        placeholder="Contoh: Paket Karimunjawa 3 Hari 2 Malam"
                                        value="<?php echo isset($edit_slide_item['judul_slider']) ? htmlspecialchars($edit_slide_item['judul_slider']) : ''; ?>"
                                        style="background-color: #172425; color: #fff;">
                                </div>

                                <div class="form-row">
                                    <div class="form-group" style="flex: 1; min-width: 250px;">
                                        <label class="form-label" for="nama">Nama Paket (Teks Abu-abu/Nama Penginapan)</label>
                                        <input class="form-control" type="text" id="nama" name="nama"
                                            value="<?php echo htmlspecialchars($edit_slide_item['nama']); ?>" required
                                            style="background-color: #172425; color: #fff;">
                                    </div>
                                    <div class="form-group" style="flex: 1; min-width: 200px;">
                                        <label class="form-label" for="durasi">Durasi Paket</label>
                                        <input class="form-control" type="text" id="durasi" name="durasi"
                                            placeholder="Contoh: 3D2N atau 3 Hari 2 Malam"
                                            value="<?php echo isset($edit_slide_item['durasi']) ? htmlspecialchars($edit_slide_item['durasi']) : ''; ?>"
                                            style="background-color: #172425; color: #fff;">
                                    </div>
                                    <div class="form-group" style="flex: 1; min-width: 200px;">
                                        <label class="form-label" for="harga">Harga Terendah</label>
                                        <input class="form-control" type="text" id="harga" name="harga"
                                            value="<?php echo htmlspecialchars($edit_slide_item['harga']); ?>" required
                                            style="background-color: #172425; color: #fff;">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 15px;">
                                    <label class="form-label" for="gambar">Foto Thumbnail Slide</label>
                                    <input class="form-control" type="file" id="gambar" name="gambar" accept="image/*"
                                        style="background-color: #172425; color: #fff;">
                                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 12px; color: var(--text-muted);">Gambar Saat Ini:</span>
                                        <img src="<?php echo $edit_slide_item['gambar']; ?>"
                                            style="height: 50px; border-radius: 4px; border: 1px solid #334e50;"
                                            alt="Current Thumbnail">
                                    </div>
                                </div>

                                <div class="form-actions"
                                    style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                                    <a href="admin.php?action=manage_slider" class="btn-action"
                                        style="padding: 10px 16px; border-radius: 6px; text-decoration: none; color: #FFF; background: #2c3e50; display: inline-flex; align-items: center; justify-content: center; font-size: 13px;">Batal</a>
                                    <button type="submit" name="action" value="slider_update" class="btn-new"
                                        style="margin: 0; border: none; height: auto; cursor: pointer; padding: 10px 20px; border-radius: 6px;">Simpan
                                        Perubahan</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Layout Grid 2 Kolom untuk Pinning & Listing -->
                    <div style="display: grid; grid-template-columns: 1fr; gap: 24px; margin-top: 24px;">

                        <!-- Panel Sematkan / Pin Penginapan Baru -->
                        <div class="table-card" style="padding: 20px;">
                            <h3 style="font-size: 16px; font-weight: 700; color: #FFF; margin-bottom: 15px;">Sematkan Paket ke
                                Slider</h3>
                            <?php
                            // Ambil daftar penginapan yang belum berada di slider_data
                            $available_to_pin = [];
                            foreach ($daftar_penginapan as $p) {
                                $is_pinned = false;
                                foreach ($slider_data as $s) {
                                    if (isset($s['lodging_id']) && $s['lodging_id'] === $p['id']) {
                                        $is_pinned = true;
                                        break;
                                    }
                                }
                                if (!$is_pinned) {
                                    $available_to_pin[] = $p;
                                }
                            }
                            ?>
                            <?php if (!empty($available_to_pin)): ?>
                                <form action="admin.php?action=manage_slider" method="POST"
                                    style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                    <div class="form-group" style="margin: 0; min-width: 280px; flex: 1;">
                                        <select class="form-control" name="lodging_id" required
                                            style="background-color: #172425; color: #fff;">
                                            <option value="">-- Pilih Paket Penginapan --</option>
                                            <?php foreach ($available_to_pin as $ap): ?>
                                                <option value="<?php echo $ap['id']; ?>">
                                                    <?php echo htmlspecialchars($ap['nama']); ?>
                                                    (<?php echo htmlspecialchars($ap['harga']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="action" value="slider_pin" class="btn-new"
                                        style="margin: 0; border: none; height: 42px; cursor: pointer;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-plus">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        Sematkan ke Slider
                                    </button>
                                </form>
                            <?php else: ?>
                                <div
                                    style="color: var(--text-muted); font-size: 14px; background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1); border-radius: 6px; padding: 15px; text-align: center;">
                                    Semua paket penginapan yang tersedia sudah disematkan ke slider utama.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tabel Paket Aktif di Slider -->
                        <div class="table-card" style="padding: 20px;">
                            <h3 style="font-size: 16px; font-weight: 700; color: #FFF; margin-bottom: 15px;">Daftar Paket Aktif
                                di Slider</h3>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Thumbnail</th>
                                            <th>Nama Paket</th>
                                            <th>Durasi</th>
                                            <th>Harga</th>
                                            <th style="text-align: right;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $active_slider_items = $slider_data;
                                        ?>
                                        <?php if (!empty($active_slider_items)): ?>
                                            <?php foreach ($active_slider_items as $as): ?>
                                                <tr>
                                                    <td>
                                                        <img class="lodging-row-thumb" src="<?php echo $as['gambar']; ?>"
                                                            alt="<?php echo $as['nama']; ?>"
                                                            onerror="this.src='assets/images/paket-snorkeling.jpg'">
                                                    </td>
                                                    <td>
                                                        <strong><?php
                                                        if (isset($as['judul_slider']) && !empty($as['judul_slider'])) {
                                                            echo htmlspecialchars($as['judul_slider']);
                                                        } else {
                                                            echo 'Paket Karimunjawa ';
                                                            $dur_label = isset($as['durasi']) ? $as['durasi'] : '3D2N';
                                                            if ($dur_label === '3D2N')
                                                                echo '3 Hari 2 Malam';
                                                            elseif ($dur_label === '2D1N')
                                                                echo '2 Hari 1 Malam';
                                                            elseif ($dur_label === '4D3N')
                                                                echo '4 Hari 3 Malam';
                                                            else
                                                                echo htmlspecialchars($dur_label);
                                                        }
                                                        ?></strong>
                                                        <br>
                                                        <span
                                                            style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($as['nama']); ?></span>
                                                    </td>
                                                    <td><?php echo isset($as['durasi']) ? htmlspecialchars($as['durasi']) : '3D2N'; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($as['harga']); ?></td>
                                                    <td style="text-align: right;">
                                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                            <a href="admin.php?action=manage_slider&edit_slide_id=<?php echo $as['id']; ?>"
                                                                class="btn-action" title="Edit Detail Slide"
                                                                style="color: var(--primary-teal); background: rgba(28,187,180,0.1); border: 1px solid rgba(28,187,180,0.2); padding: 5px 10px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; font-size: 13px;">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round" class="feather feather-edit">
                                                                    <path
                                                                        d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7">
                                                                    </path>
                                                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z">
                                                                    </path>
                                                                </svg>
                                                                Edit Detail
                                                            </a>
                                                            <a href="admin.php?action=slider_unpin&id=<?php echo $as['id']; ?>"
                                                                class="btn-action btn-delete" title="Lepas dari Slider"
                                                                style="color: var(--accent-orange); background: rgba(255,123,84,0.1); border: 1px solid rgba(255,123,84,0.2); padding: 5px 10px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; font-size: 13px;">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                                    stroke-linejoin="round" class="feather feather-x">
                                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                                </svg>
                                                                Lepas Slider
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5"
                                                    style="text-align: center; color: var(--text-muted); padding: 30px 10px;">
                                                    Belum ada paket penginapan yang disematkan ke slider. Beranda saat ini
                                                    menggunakan data default bawaan.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                <?php elseif (isset($_GET['action']) && $_GET['action'] === 'gallery'): ?>
                    <!-- KELOLA DATA GALERI -->
                    <div class="header-row">
                        <div>
                            <h1 class="page-title">Kelola Galeri Foto</h1>
                            <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Kelola koleksi foto keindahan
                                Karimunjawa untuk halaman galeri publik</p>
                        </div>
                        <a href="admin.php?action=gallery_new" class="btn-new">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Tambah Foto Baru
                        </a>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-image">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <h4>Total Foto Galeri</h4>
                                <p><?php echo count($galeri_foto); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card list -->
                    <div class="table-card">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th style="width: 100px;">Foto</th>
                                        <th>Nama File / Info</th>
                                        <th style="width: 150px;">Kategori</th>
                                        <th style="width: 150px;">Posisi Fokus</th>
                                        <th style="width: 120px; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($galeri_foto)): ?>
                                        <?php foreach ($galeri_foto as $index => $foto):
                                            $cat_name = "Destinasi";
                                            if ($foto['kategori'] === 'aktivitas')
                                                $cat_name = "Aktivitas";
                                            if ($foto['kategori'] === 'penginapan')
                                                $cat_name = "Penginapan";
                                            $display_name = !empty($foto['alt']) ? $foto['alt'] : basename($foto['file']);
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($foto['file']); ?>"
                                                        style="width: 80px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1);"
                                                        alt="">
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($display_name); ?></strong></td>
                                                <td>
                                                    <span
                                                        style="font-size: 11px; font-weight: 600; color: var(--primary-teal); background: rgba(28,187,180,0.08); padding: 4px 8px; border-radius: 6px; text-transform: uppercase;">
                                                        <?php echo htmlspecialchars($cat_name); ?>
                                                    </span>
                                                </td>
                                                <td style="font-family: monospace; font-size: 12px; color: var(--text-muted);">
                                                    <?php echo htmlspecialchars($foto['posisi']); ?></td>
                                                <td style="text-align: center;">
                                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                                        <a href="admin.php?action=gallery_edit&index=<?php echo $index; ?>"
                                                            class="btn-action" title="Edit Foto"
                                                            style="background: rgba(28,187,180,0.1); color: var(--primary-teal); border: 1px solid rgba(28,187,180,0.2); width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" class="feather feather-edit-2">
                                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                                </path>
                                                            </svg>
                                                        </a>
                                                        <a href="#"
                                                            onclick="confirmGalleryDelete(<?php echo $index; ?>, '<?php echo addslashes(htmlspecialchars($display_name)); ?>')"
                                                            class="btn-action btn-delete" title="Hapus Foto"
                                                            style="background: rgba(255,123,84,0.1); color: var(--accent-orange); border: 1px solid rgba(255,123,84,0.2); width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" class="feather feather-trash-2">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path
                                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                                </path>
                                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6"
                                                style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
                                                Belum ada foto galeri. Klik "Tambah Foto Baru" untuk menambahkan.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif (isset($_GET['action']) && in_array($_GET['action'], ['gallery_new', 'gallery_edit'])): ?>
                    <!-- FORM TAMBAH / EDIT FOTO GALERI -->
                    <div class="header-row">
                        <h1 class="page-title">
                            <?php echo $gallery_edit_mode ? 'Edit Foto Galeri' : 'Tambah Foto Galeri Baru'; ?></h1>
                        <a href="admin.php?action=gallery" class="btn-secondary">Kembali</a>
                    </div>

                    <form class="form-card"
                        action="admin.php?action=<?php echo $gallery_edit_mode ? 'gallery_edit&index=' . $edit_gallery_index : 'gallery_new'; ?>"
                        method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action"
                            value="<?php echo $gallery_edit_mode ? 'gallery_update' : 'gallery_create'; ?>">
                        <input type="hidden" name="item_index" value="<?php echo $edit_gallery_index; ?>">

                        <div class="form-group">
                            <label class="form-label">File Foto Galeri
                                <?php echo $gallery_edit_mode ? '' : '<span style="color: var(--accent-orange);">*</span>'; ?></label>
                            <input type="file" name="file" class="form-control" <?php echo $gallery_edit_mode ? '' : 'required'; ?> accept="image/*">
                            <span style="font-size: 12px; color: var(--text-muted); display: block; margin-top: 4px;">Format
                                gambar yang didukung: JPG, JPEG, PNG, WebP.</span>

                            <?php if ($gallery_edit_mode && !empty($edit_gallery_item['file'])): ?>
                                <div style="margin-top: 12px;">
                                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 6px;">Foto saat ini:</p>
                                    <img src="<?php echo htmlspecialchars($edit_gallery_item['file']); ?>"
                                        style="max-width: 240px; max-height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.15);">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Kategori <span style="color: var(--accent-orange);">*</span></label>
                                <select name="kategori" class="form-control"
                                    style="background-color: var(--card-bg); color: var(--text-light); height: 42px;" required>
                                    <option value="destinasi" <?php echo ($gallery_edit_mode && $edit_gallery_item['kategori'] === 'destinasi') ? 'selected' : ''; ?>>Destinasi Wisata
                                    </option>
                                    <option value="aktivitas" <?php echo ($gallery_edit_mode && $edit_gallery_item['kategori'] === 'aktivitas') ? 'selected' : ''; ?>>Aktivitas Seru
                                    </option>
                                    <option value="penginapan" <?php echo ($gallery_edit_mode && $edit_gallery_item['kategori'] === 'penginapan') ? 'selected' : ''; ?>>Penginapan & Resort
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Posisi Visual Foto<span
                                        style="color: var(--accent-orange);">*</span></label>
                                <select name="posisi" class="form-control"
                                    style="background-color: var(--card-bg); color: var(--text-light); height: 42px;" required>
                                    <option value="center center" <?php echo ($gallery_edit_mode && $edit_gallery_item['posisi'] === 'center center') ? 'selected' : ''; ?>>Center Center
                                        (Tengah-Tengah - Standar)</option>
                                    <option value="center top" <?php echo ($gallery_edit_mode && $edit_gallery_item['posisi'] === 'center top') ? 'selected' : ''; ?>>Center Top
                                        (Tengah-Atas - Cocok untuk pohon kelapa/objek tinggi)</option>
                                    <option value="center bottom" <?php echo ($gallery_edit_mode && $edit_gallery_item['posisi'] === 'center bottom') ? 'selected' : ''; ?>>Center Bottom
                                        (Tengah-Bawah)</option>
                                    <option value="left center" <?php echo ($gallery_edit_mode && $edit_gallery_item['posisi'] === 'left center') ? 'selected' : ''; ?>>Left Center
                                        (Kiri-Tengah)</option>
                                    <option value="left bottom" <?php echo ($gallery_edit_mode && $edit_gallery_item['posisi'] === 'left bottom') ? 'selected' : ''; ?>>Left Bottom
                                        (Kiri-Bawah)</option>
                                    <option value="right center" <?php echo ($gallery_edit_mode && $edit_gallery_item['posisi'] === 'right center') ? 'selected' : ''; ?>>Right Center
                                        (Kanan-Tengah)</option>
                                </select>
                                <span
                                    style="font-size: 12px; color: var(--text-muted); display: block; margin-top: 4px;">Mengunci
                                    fokus visual gambar pada rasio tampilan grid agar tidak terpotong sembarangan.</span>
                            </div>
                        </div>

                        <div style="margin-top: 25px; display: flex; gap: 10px;">
                            <button type="submit" class="btn-new">Simpan Foto</button>
                            <a href="admin.php?action=gallery" class="btn-secondary"
                                style="line-height: 38px; display: inline-block; text-align: center; text-decoration: none;">Batal</a>
                        </div>
                    </form>

                <?php else: ?>
                    <!-- TABEL DAFTAR PENGINAPAN -->
                    <div class="header-row">
                        <div>
                            <h1 class="page-title">Kelola Data Penginapan</h1>
                            <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Kelola daftar akomodasi
                                Karimunjawa Vibes Trip secara langsung</p>
                        </div>
                        <a href="admin.php?action=new" class="btn-new">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Tambah Penginapan
                        </a>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-home">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <h4>Total Penginapan</h4>
                                <p><?php echo count($daftar_penginapan); ?></p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-message-square">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>
                            <div class="stat-info">
                                <h4>Total Ulasan Pelanggan</h4>
                                <p><?php echo $total_ulasan; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card list -->
                    <div class="table-card">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Nama Akomodasi</th>
                                        <th>ID / Slug</th>
                                        <th>Badge</th>
                                        <th>Harga Terendah</th>
                                        <th style="text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($daftar_penginapan)): ?>
                                        <?php foreach ($daftar_penginapan as $p):
                                            $badge_type_class = (isset($p['badge_class']) && $p['badge_class'] === 'orange') ? 'orange' : 'teal';
                                            ?>
                                            <tr>
                                                <td>
                                                    <img class="lodging-row-thumb" src="<?php echo $p['gambar']; ?>"
                                                        alt="<?php echo $p['nama']; ?>"
                                                        onerror="this.src='assets/images/paket-snorkeling.jpg'">
                                                </td>
                                                <td><strong><?php echo $p['nama']; ?></strong></td>
                                                <td><code style="color: var(--primary-teal);"><?php echo $p['id']; ?></code></td>
                                                <td>
                                                    <?php if (!empty($p['badge'])): ?>
                                                        <span
                                                            class="badge-table <?php echo $badge_type_class; ?>"><?php echo $p['badge']; ?></span>
                                                    <?php else: ?>
                                                        <span style="color: var(--text-muted); font-size: 12px;">Tidak ada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div style="font-size: 13px;"><strong>3D2N:</strong> <?php echo $p['harga']; ?>
                                                    </div>
                                                    <?php if (!empty($p['harga_2d1n'])): ?>
                                                        <div style="font-size: 13px; margin-top: 2px;"><strong>2D1N:</strong>
                                                            <?php echo $p['harga_2d1n']; ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($p['harga_4d3n'])): ?>
                                                        <div style="font-size: 13px; margin-top: 2px;"><strong>4D3N:</strong>
                                                            <?php echo $p['harga_4d3n']; ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($p['harga_honeymoon'])): ?>
                                                        <div style="font-size: 13px; margin-top: 2px;"><strong>Honeymoon:</strong>
                                                            <?php echo $p['harga_honeymoon']; ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align: right;">
                                                    <div class="action-btns" style="justify-content: flex-end;">
                                                        <!-- Preview Link -->
                                                        <a href="detail-page/<?php echo $p['id']; ?>.php" target="_blank"
                                                            class="btn-action" title="Lihat Detail Page">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" class="feather feather-external-link">
                                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6">
                                                                </path>
                                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                                            </svg>
                                                        </a>
                                                        <!-- Edit -->
                                                        <a href="admin.php?action=edit&id=<?php echo $p['id']; ?>" class="btn-action"
                                                            title="Edit Penginapan">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" class="feather feather-edit">
                                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7">
                                                                </path>
                                                                <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z">
                                                                </path>
                                                            </svg>
                                                        </a>
                                                        <!-- Delete -->
                                                        <button type="button" class="btn-action btn-delete" title="Hapus Penginapan"
                                                            onclick="confirmDelete('<?php echo $p['id']; ?>', '<?php echo $p['nama']; ?>')">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" class="feather feather-trash-2">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path
                                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                                </path>
                                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6"
                                                style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
                                                Belum ada data penginapan. Silakan tambahkan penginapan baru.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>

        <!-- MODAL KONFIRMASI HAPUS -->
        <div class="modal-overlay" id="deleteModal">
            <div class="modal-card">
                <div class="modal-icon">⚠</div>
                <div class="modal-title">Hapus Penginapan?</div>
                <div class="modal-desc" id="deleteModalText">Apakah Anda yakin ingin menghapus data penginapan ini? Tindakan
                    ini juga akan menghapus file routing halamannya secara permanen.</div>
                <div class="modal-actions">
                    <button class="btn-modal btn-modal-cancel" onclick="closeModal()">Batal</button>
                    <a id="confirmDeleteLink" href="#" class="btn-modal btn-modal-delete"
                        style="text-decoration: none; display: inline-block; text-align: center; line-height: 20px;">Hapus</a>
                </div>
            </div>
        </div>

        <script>
            // Modal logic
            function confirmDelete(id, nama) {
                const modal = document.getElementById('deleteModal');
                const text = document.getElementById('deleteModalText');
                const link = document.getElementById('confirmDeleteLink');

                if (modal && text && link) {
                    text.innerHTML = `Apakah Anda yakin ingin menghapus penginapan <strong>${nama}</strong>? Tindakan ini tidak dapat dibatalkan and akan menghapus file detail halamannya secara permanen.`;
                    link.href = `admin.php?action=delete&id=${id}`;
                    modal.style.display = 'flex';
                }
            }

            function confirmGalleryDelete(index, alt) {
                const modal = document.getElementById('deleteModal');
                const text = document.getElementById('deleteModalText');
                const link = document.getElementById('confirmDeleteLink');

                if (modal && text && link) {
                    text.innerHTML = `Apakah Anda yakin ingin menghapus foto galeri <strong>${alt}</strong>? Tindakan ini tidak dapat dibatalkan.`;
                    link.href = `admin.php?action=gallery_delete&index=${index}`;
                    modal.style.display = 'flex';
                }
            }

            function confirmReviewsDelete(id, nama) {
                const modal = document.getElementById('deleteModal');
                const text = document.getElementById('deleteModalText');
                const link = document.getElementById('confirmDeleteLink');

                if (modal && text && link) {
                    text.innerHTML = `Apakah Anda yakin ingin menghapus ulasan dari wisatawan <strong>${nama}</strong>? Tindakan ini tidak dapat dibatalkan.`;
                    link.href = `admin.php?action=reviews_delete&id=${id}`;
                    modal.style.display = 'flex';
                }
            }

            function closeModal() {
                const modal = document.getElementById('deleteModal');
                if (modal) {
                    modal.style.display = 'none';
                }
            }

            // UI Loading Overlay functions for HEIC conversion
            function showHeicLoadingOverlay(message) {
                let overlay = document.getElementById('heic-loading-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.id = 'heic-loading-overlay';
                    overlay.style.position = 'fixed';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100%';
                    overlay.style.height = '100%';
                    overlay.style.backgroundColor = 'rgba(11, 23, 23, 0.85)';
                    overlay.style.backdropFilter = 'blur(8px)';
                    overlay.style.display = 'flex';
                    overlay.style.flexDirection = 'column';
                    overlay.style.alignItems = 'center';
                    overlay.style.justifyContent = 'center';
                    overlay.style.zIndex = '99999';
                    overlay.style.color = '#F4F6F6';
                    overlay.style.fontFamily = "'Plus Jakarta Sans', sans-serif";

                    const spinner = document.createElement('div');
                    spinner.style.width = '50px';
                    spinner.style.height = '50px';
                    spinner.style.border = '5px solid rgba(28, 187, 180, 0.1)';
                    spinner.style.borderTop = '5px solid #1CBBB4';
                    spinner.style.borderRadius = '50%';
                    spinner.style.animation = 'heic-spin 1s linear infinite';
                    spinner.style.marginBottom = '20px';

                    const styleSheet = document.createElement('style');
                    styleSheet.type = 'text/css';
                    styleSheet.innerText = '@keyframes heic-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                    document.head.appendChild(styleSheet);

                    const text = document.createElement('div');
                    text.id = 'heic-loading-text';
                    text.style.fontSize = '16px';
                    text.style.fontWeight = '600';
                    text.style.letterSpacing = '0.5px';
                    text.innerText = message;

                    const subtext = document.createElement('div');
                    subtext.style.fontSize = '12px';
                    subtext.style.color = '#8AA0A0';
                    subtext.style.marginTop = '8px';
                    subtext.innerText = 'Mohon tunggu, browser sedang memproses dan mengonversi format foto Anda...';

                    overlay.appendChild(spinner);
                    overlay.appendChild(text);
                    overlay.appendChild(subtext);
                    document.body.appendChild(overlay);
                } else {
                    document.getElementById('heic-loading-text').innerText = message;
                    overlay.style.display = 'flex';
                }
            }

            function hideHeicLoadingOverlay() {
                const overlay = document.getElementById('heic-loading-overlay');
                if (overlay) {
                    overlay.style.display = 'none';
                }
            }

            // Global HEIC/HEIF to JPEG converter for file inputs
            document.addEventListener('change', async function (e) {
                if (e.target && e.target.type === 'file') {
                    const input = e.target;
                    if (!input.files || input.files.length === 0) return;

                    const files = Array.from(input.files);
                    const heicFiles = files.filter(file => {
                        const name = file.name.toLowerCase();
                        return name.endsWith('.heic') || name.endsWith('.heif');
                    });

                    if (heicFiles.length === 0) return;

                    // Verify if heic2any is loaded
                    if (typeof heic2any === 'undefined') {
                        console.error('Library heic2any tidak terdeteksi!');
                        alert('Gagal mendeteksi library pendukung konversi gambar HEIC. Silakan periksa koneksi internet Anda.');
                        return;
                    }

                    showHeicLoadingOverlay('Mengonversi file HEIC...');

                    try {
                        const dataTransfer = new DataTransfer();

                        for (const file of files) {
                            const name = file.name.toLowerCase();
                            if (name.endsWith('.heic') || name.endsWith('.heif')) {
                                try {
                                    // Process conversion
                                    const convertedBlob = await heic2any({
                                        blob: file,
                                        toType: "image/jpeg",
                                        quality: 0.8
                                    });

                                    // Create standard JPG file object from blob
                                    const baseName = file.name.substring(0, file.name.lastIndexOf('.'));
                                    const newFileName = baseName + '.jpg';
                                    const resultBlob = Array.isArray(convertedBlob) ? convertedBlob[0] : convertedBlob;
                                    const newFile = new File([resultBlob], newFileName, {
                                        type: 'image/jpeg',
                                        lastModified: new Date().getTime()
                                    });

                                    dataTransfer.items.add(newFile);
                                } catch (convErr) {
                                    console.error('Error during HEIC conversion:', convErr);
                                    // Fallback to original file if conversion fails
                                    dataTransfer.items.add(file);
                                }
                            } else {
                                dataTransfer.items.add(file);
                            }
                        }

                        input.files = dataTransfer.files;

                        // Trigger change event for any custom listeners/previews
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    } catch (err) {
                        console.error('HEIC processing pipeline failed:', err);
                    } finally {
                        hideHeicLoadingOverlay();
                    }
                }
            });

            // Auto hide toasts after 4 seconds and handle mobile menu
            document.addEventListener('DOMContentLoaded', () => {
                const successToast = document.getElementById('successToast');
                const errorToast = document.getElementById('errorToast');

                if (successToast) {
                    setTimeout(() => successToast.remove(), 4000);
                }
                if (errorToast) {
                    setTimeout(() => errorToast.remove(), 4000);
                }

                // Mobile menu toggle
                const toggleBtn = document.getElementById('mobileMenuToggle');
                const sidebar = document.querySelector('.sidebar');

                if (toggleBtn && sidebar) {
                    toggleBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        sidebar.classList.toggle('open');
                    });

                    document.addEventListener('click', (e) => {
                        if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                            sidebar.classList.remove('open');
                        }
                    });

                    const sidebarLinks = sidebar.querySelectorAll('.sidebar-link');
                    sidebarLinks.forEach(link => {
                        link.addEventListener('click', () => {
                            sidebar.classList.remove('open');
                        });
                    });
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>