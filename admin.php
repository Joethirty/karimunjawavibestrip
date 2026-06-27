<?php
// admin.php
session_start();

// Konfigurasi Kredensial Admin
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'adminvibestrip');

// Koneksi Config Data
require_once __DIR__ . '/config.php';
global $daftar_penginapan;

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
function handleImageUpload($file_key, $existing_path = "") {
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
function slugify($text) {
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
$success_message = "";
$error_message = "";

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
        $lokasi = isset($_POST['lokasi']) ? trim(htmlspecialchars($_POST['lokasi'])) : "";
        $badge = isset($_POST['badge']) ? trim(htmlspecialchars($_POST['badge'])) : "";
        $badge_class = isset($_POST['badge_class']) ? trim(htmlspecialchars($_POST['badge_class'])) : "";
        $detail_deskripsi = isset($_POST['detail_deskripsi']) ? trim($_POST['detail_deskripsi']) : ""; // Boleh ada tag HTML
        
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
                    if (empty(trim($rt_name))) continue;
                    
                    $rt_id = slugify($rt_name);
                    $rt_price = isset($_POST['room_type_price'][$idx]) ? trim(htmlspecialchars($_POST['room_type_price'][$idx])) : "";
                    
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
                    
                    $tipe_kamar[] = [
                        "id" => $rt_id,
                        "nama" => $rt_name,
                        "harga" => $rt_price,
                        "foto_galeri" => $rt_gallery
                    ];
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
                "lokasi" => $lokasi,
                "detail_deskripsi" => $detail_deskripsi,
                "foto_galeri" => $foto_galeri
            ];
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
    }
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
    } else {
        $error_message = "Penginapan tidak ditemukan!";
    }
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Karimunjawa Vibes Trip</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            background: radial-gradient(circle, rgba(28, 187, 180, 0.12) 0%, rgba(0,0,0,0) 70%);
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

        .sidebar-link:hover, .sidebar-link.active {
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
            border: 1px solid rgba(255,255,255,0.1);
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
            
            .admin-table th, .admin-table td {
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

            .header-row .btn-new, .header-row .btn-secondary {
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
            background: rgba(255,255,255,0.05);
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
            background: rgba(255,255,255,0.1);
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
            background: rgba(255,255,255,0.05);
            color: var(--text-light);
            border: 1px solid rgba(255,255,255,0.1);
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
                <div class="login-logo" style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px;">
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
                        <input class="form-control" type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                    </div>
                    <button class="btn-submit" type="submit">LOG IN</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- ADMIN DASHBOARD PANEL -->
        <!-- Mobile Top Navigation Header -->
        <div class="mobile-nav-header">
            <div class="sidebar-brand" style="margin-bottom: 0; padding-left: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                <img src="assets/images/logo.png" alt="Logo" style="height: 28px; object-fit: contain;">
                <span>KVIBESTRIP <span>ADMIN</span></span>
            </div>
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
        <div class="dashboard-wrapper">
            <!-- Sidebar Navigation -->
            <aside class="sidebar">
                <div class="sidebar-brand" style="display: flex; align-items: center; gap: 8px; margin-bottom: 30px; padding-left: 0;">
                    <img src="assets/images/logo.png" alt="Logo" style="height: 32px; object-fit: contain;">
                    <span>KVIBESTRIP <span>ADMIN</span></span>
                </div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="admin.php" class="sidebar-link <?php echo (!$edit_mode && !isset($_GET['action'])) ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                            Daftar Penginapan
                        </a>
                    </li>
                    <li>
                        <a href="admin.php?action=new" class="sidebar-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'new') ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                            Tambah Penginapan
                        </a>
                    </li>
                    <li>
                        <a href="admin.php?action=reviews" class="sidebar-link <?php echo (isset($_GET['action']) && $_GET['action'] === 'reviews') ? 'active' : ''; ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            Kelola Ulasan
                        </a>
                    </li>
                </ul>
                <div class="sidebar-footer">
                    <a href="admin.php?action=logout" class="sidebar-link" style="color: var(--accent-orange); background: rgba(255,123,84,0.05);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Log Out
                    </a>
                </div>
            </aside>

            <!-- Main Workspace -->
            <main class="main-content">
                
                <?php if ($edit_mode || (isset($_GET['action']) && $_GET['action'] === 'new')): ?>
                    <!-- FORM TAMBAH / EDIT MODE -->
                    <div class="header-row">
                        <h1 class="page-title"><?php echo $edit_mode ? 'Edit Penginapan: ' . $edit_lodging['nama'] : 'Tambah Penginapan Baru'; ?></h1>
                        <a href="admin.php" class="btn-secondary">Kembali</a>
                    </div>

                    <form class="form-card" action="admin.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="old_id" value="<?php echo $edit_lodging['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label" for="nama">Nama Penginapan</label>
                            <input class="form-control" type="text" id="nama" name="nama" placeholder="Contoh: Homestay Azza" value="<?php echo $edit_mode ? $edit_lodging['nama'] : ''; ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="harga">Harga Paket (Label)</label>
                                <input class="form-control" type="text" id="harga" name="harga" placeholder="Contoh: Rp. 1.400.000 / pax" value="<?php echo $edit_mode ? $edit_lodging['harga'] : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="lokasi">Lokasi / Alamat</label>
                                <input class="form-control" type="text" id="lokasi" name="lokasi" placeholder="Alamat lengkap di Karimunjawa" value="<?php echo $edit_mode ? $edit_lodging['lokasi'] : ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="badge">Badge Promo (Opsional)</label>
                                <input class="form-control" type="text" id="badge" name="badge" placeholder="Contoh: Best Value, Budget Friendly" value="<?php echo $edit_mode ? (isset($edit_lodging['badge']) ? $edit_lodging['badge'] : '') : ''; ?>">
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
                            <input class="form-control" type="text" id="deskripsi" name="deskripsi" placeholder="Teks promo singkat untuk beranda list..." value="<?php echo $edit_mode ? $edit_lodging['deskripsi'] : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="detail_deskripsi">Deskripsi Detail Halaman</label>
                            <textarea class="form-control" id="detail_deskripsi" name="detail_deskripsi" rows="5" placeholder="Teks penjelasan lengkap mengenai akomodasi, fasilitas tambahan, dll..." required><?php echo $edit_mode ? $edit_lodging['detail_deskripsi'] : ''; ?></textarea>
                        </div>

                        <!-- FOTO UTAMA DAN GALERI -->
                        <div class="card-subtitle-divider"><span>FOTO UTAMA & THUMBNAIL</span></div>
                        <div class="form-group">
                            <label class="form-label">Upload File Foto Utama</label>
                            <?php if ($edit_mode && !empty($edit_lodging['gambar'])): ?>
                                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 12px;">
                                    <img src="<?php echo htmlspecialchars($edit_lodging['gambar']); ?>" style="width: 100px; height: 75px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 4px 10px rgba(0,0,0,0.15);" alt="Thumbnail Sekarang">
                                    <span style="font-size: 13px; color: var(--text-muted);">Foto saat ini</span>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="gambar" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?>>
                        </div>

                        <div class="card-subtitle-divider"><span>5 FOTO GALERI UTAMA</span></div>
                        <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
                            <?php for ($i = 0; $i < 5; $i++): 
                                $gal_val = ($edit_mode && isset($edit_lodging['foto_galeri'][$i])) ? $edit_lodging['foto_galeri'][$i] : '';
                            ?>
                                <div class="form-group" style="background: rgba(10,20,20,0.3); border: 1px dashed var(--border-color); border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 10px;">
                                    <label class="form-label" style="font-size: 11px; margin-bottom: 0;">Foto Galeri <?php echo $i+1; ?></label>
                                    <?php if (!empty($gal_val)): ?>
                                        <div style="width: 100%; height: 100px; border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                            <img src="<?php echo htmlspecialchars($gal_val); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Galeri <?php echo $i+1; ?>">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="foto_galeri_<?php echo $i; ?>" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?> style="padding: 6px 12px; font-size: 12px; margin-top: auto;">
                                </div>
                            <?php endfor; ?>
                        </div>

                        <!-- DYNAMIC ROOM TYPES (TIPE KAMAR) -->
                        <div class="card-subtitle-divider">
                            <span>TIPE KAMAR</span>
                            <button type="button" class="btn-new" style="padding: 6px 12px; font-size: 12px; gap: 4px;" onclick="addRoomTypeRow()">
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
                                        <span class="room-type-title">Tipe Kamar #<?php echo $index+1; ?></span>
                                        <button type="button" class="btn-remove-rt" onclick="removeRoomTypeRow(<?php echo $index; ?>)">
                                            ✕ Hapus Kamar
                                        </button>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Nama Tipe Kamar</label>
                                        <input class="form-control" type="text" name="room_type_name[<?php echo $index; ?>]" value="<?php echo $rt['nama']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Harga Tipe Kamar</label>
                                        <input class="form-control" type="text" name="room_type_price[<?php echo $index; ?>]" placeholder="Contoh: Rp 2.050.000 / pax" value="<?php echo $rt['harga']; ?>" required>
                                    </div>
                                    
                                    <label class="form-label" style="margin-top: 15px; display: block;">5 Foto Galeri Tipe Kamar</label>
                                    <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 5px;">
                                        <?php for ($k = 0; $k < 5; $k++): 
                                            $rt_gal = isset($rt['foto_galeri'][$k]) ? $rt['foto_galeri'][$k] : '';
                                        ?>
                                            <div style="background: rgba(10,20,20,0.4); border: 1px dotted var(--border-color); border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                                                <label class="form-label" style="font-size: 10px; margin-bottom: 0;">Foto <?php echo $k+1; ?></label>
                                                <?php if (!empty($rt_gal)): ?>
                                                    <div style="width: 100%; height: 70px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                                                        <img src="<?php echo htmlspecialchars($rt_gal); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Kamar <?php echo $k+1; ?>">
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" name="room_type_gallery_<?php echo $index; ?>_<?php echo $k; ?>" accept="image/*" style="padding: 4px 8px; font-size: 11px; height: 30px; margin-top: auto;">
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
                                    <label class="form-label" style="font-size: 10px; margin-bottom: 4px;">Foto ${k+1}</label>
                                    <input type="file" class="form-control" name="room_type_gallery_${rtCounter}_${k}" accept="image/*" style="padding: 4px 8px; font-size: 11px; height: 30px;" required>
                                </div>
                            `;
                        }
                        galleryHTML += '</div>';
                        
                        itemDiv.innerHTML = `
                            <div class="room-type-header">
                                <span class="room-type-title">Tipe Kamar Baru #${rtCounter+1}</span>
                                <button type="button" class="btn-remove-rt" onclick="removeRoomTypeRow(${rtCounter})">
                                    ✕ Hapus Kamar
                                </button>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Tipe Kamar</label>
                                <input class="form-control" type="text" name="room_type_name[${rtCounter}]" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Harga Tipe Kamar</label>
                                <input class="form-control" type="text" name="room_type_price[${rtCounter}]" placeholder="Contoh: Rp 2.050.000 / pax" required>
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
                            <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Jawab ulasan dari wisatawan untuk penginapan maupun tour guide</p>
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
                                        <th style="width: 340px;">Balasan Admin</th>
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
                                                    <span style="color: var(--accent-orange); font-weight: bold; font-size: 15px; letter-spacing: -1px;">
                                                        <?php 
                                                        $stars = isset($testi['bintang']) ? intval($testi['bintang']) : 5;
                                                        for ($i = 0; $i < $stars; $i++) echo "★";
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span style="font-size: 12px; font-weight: 600; color: var(--primary-teal); background: rgba(28,187,180,0.08); padding: 4px 8px; border-radius: 6px;">
                                                        <?php echo htmlspecialchars($assoc_name); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style="font-style: italic; font-size: 13.5px; line-height: 1.4; color: var(--text-light);">
                                                        "<?php echo htmlspecialchars($testi['ulasan']); ?>"
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($testi['balasan'])): ?>
                                                        <div style="background: rgba(28,187,180,0.05); border-left: 2px solid var(--primary-teal); padding: 8px 12px; border-radius: 4px; font-size: 13px; margin-bottom: 8px; color: #D5E0E0; line-height: 1.4;">
                                                            <?php echo htmlspecialchars($testi['balasan']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Form Balas Inline -->
                                                    <form action="admin.php?action=reviews" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                                        <input type="hidden" name="action" value="reply_review">
                                                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($testi['id']); ?>">
                                                        <input class="form-control" type="text" name="balasan" placeholder="<?php echo empty($testi['balasan']) ? 'Tulis balasan...' : 'Ubah balasan...'; ?>" style="padding: 8px 12px; font-size: 12.5px; background: rgba(10,20,20,0.3); border-radius: 8px; height: 34px;" value="<?php echo isset($testi['balasan']) ? htmlspecialchars($testi['balasan']) : ''; ?>">
                                                        <button class="btn-new" type="submit" style="padding: 0 14px; font-size: 12px; height: 34px; border-radius: 8px; flex-shrink: 0; box-shadow: none;">
                                                            Simpan
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
                                                Belum ada ulasan yang masuk.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- TABEL DAFTAR PENGINAPAN -->
                    <div class="header-row">
                        <div>
                            <h1 class="page-title">Kelola Data Penginapan</h1>
                            <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Kelola daftar akomodasi Karimunjawa Vibes Trip secara langsung</p>
                        </div>
                        <a href="admin.php?action=new" class="btn-new">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Tambah Penginapan
                        </a>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            </div>
                            <div class="stat-info">
                                <h4>Total Penginapan</h4>
                                <p><?php echo count($daftar_penginapan); ?></p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon-wrapper">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
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
                                                    <img class="lodging-row-thumb" src="<?php echo $p['gambar']; ?>" alt="<?php echo $p['nama']; ?>" onerror="this.src='assets/images/paket-snorkeling.jpg'">
                                                </td>
                                                <td><strong><?php echo $p['nama']; ?></strong></td>
                                                <td><code style="color: var(--primary-teal);"><?php echo $p['id']; ?></code></td>
                                                <td>
                                                    <?php if (!empty($p['badge'])): ?>
                                                        <span class="badge-table <?php echo $badge_type_class; ?>"><?php echo $p['badge']; ?></span>
                                                    <?php else: ?>
                                                        <span style="color: var(--text-muted); font-size: 12px;">Tidak ada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $p['harga']; ?></td>
                                                <td style="text-align: right;">
                                                    <div class="action-btns" style="justify-content: flex-end;">
                                                        <!-- Preview Link -->
                                                        <a href="detail-page/<?php echo $p['id']; ?>.php" target="_blank" class="btn-action" title="Lihat Detail Page">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                        </a>
                                                        <!-- Edit -->
                                                        <a href="admin.php?action=edit&id=<?php echo $p['id']; ?>" class="btn-action" title="Edit Penginapan">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                        </a>
                                                        <!-- Delete -->
                                                        <button type="button" class="btn-action btn-delete" title="Hapus Penginapan" onclick="confirmDelete('<?php echo $p['id']; ?>', '<?php echo $p['nama']; ?>')">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px 10px;">
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
                <div class="modal-desc" id="deleteModalText">Apakah Anda yakin ingin menghapus data penginapan ini? Tindakan ini juga akan menghapus file routing halamannya secara permanen.</div>
                <div class="modal-actions">
                    <button class="btn-modal btn-modal-cancel" onclick="closeModal()">Batal</button>
                    <a id="confirmDeleteLink" href="#" class="btn-modal btn-modal-delete" style="text-decoration: none; display: inline-block; text-align: center; line-height: 20px;">Hapus</a>
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

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
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
