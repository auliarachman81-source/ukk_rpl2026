<?php
require_once '../config.php';

// Cek apakah user sudah login dan merupakan siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit();
}

$success = '';
$error = '';

// Proses submit laporan
if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $kategori_id = intval($_POST['kategori_id']);
    $judul = clean_input($_POST['judul']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $lokasi = clean_input($_POST['lokasi']);
    $foto = '';
    
    // Upload foto jika ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            
            // Buat folder uploads jika belum ada
            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto = $new_filename;
            }
        }
    }
    
    $query = "INSERT INTO pengaduan (user_id, kategori_id, judul, deskripsi, lokasi, foto) 
              VALUES ($user_id, $kategori_id, '" . escape($judul) . "', '" . escape($deskripsi) . "', 
                      '" . escape($lokasi) . "', '" . escape($foto) . "')";
    
    if (mysqli_query($conn, $query)) {
        $success = 'Laporan berhasil dikirim!';
    } else {
        $error = 'Gagal mengirim laporan!';
    }
}

// Ambil kategori
$result_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3 text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Siswa</h5>
                    <small><?= $_SESSION['nama'] ?></small>
                </div>
                <nav class="nav flex-column px-2">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="buat_laporan.php">
                        <i class="bi bi-plus-circle"></i> Buat Laporan
                    </a>
                    <a class="nav-link" href="histori.php">
                        <i class="bi bi-clock-history"></i> Histori
                    </a>
                    <hr class="text-white">
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-3">
                <h2 class="mb-4">Buat Laporan Pengaduan</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Form Laporan</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Kategori Pengaduan *</label>
                                        <select class="form-select" name="kategori_id" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php while ($kat = mysqli_fetch_assoc($result_kategori)): ?>
                                                <option value="<?= $kat['id'] ?>"><?= $kat['nama_kategori'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Judul Laporan *</label>
                                        <input type="text" class="form-control" name="judul" 
                                               placeholder="Contoh: Kursi Patah di Ruang Kelas" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Lokasi Kerusakan *</label>
                                        <input type="text" class="form-control" name="lokasi" 
                                               placeholder="Contoh: Ruang Kelas XII RPL 1" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi Detail *</label>
                                        <textarea class="form-control" name="deskripsi" rows="6" 
                                                  placeholder="Jelaskan secara detail kerusakan yang terjadi..." required></textarea>
                                        <small class="text-muted">Jelaskan kondisi kerusakan dengan lengkap</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto Kerusakan (Optional)</label>
                                        <input type="file" class="form-control" name="foto" accept="image/*">
                                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-send"></i> Kirim Laporan
                                        </button>
                                        <a href="dashboard.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left"></i> Kembali
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-info-circle"></i> Panduan Pelaporan</h6>
                                <ol class="small">
                                    <li class="mb-2">Pilih kategori yang sesuai dengan jenis kerusakan</li>
                                    <li class="mb-2">Berikan judul yang jelas dan singkat</li>
                                    <li class="mb-2">Sebutkan lokasi kerusakan dengan spesifik</li>
                                    <li class="mb-2">Jelaskan detail kerusakan secara lengkap</li>
                                    <li class="mb-2">Upload foto jika memungkinkan untuk mempercepat proses</li>
                                </ol>
                                
                                <hr>
                                
                                <h6 class="card-title"><i class="bi bi-exclamation-triangle"></i> Catatan Penting</h6>
                                <ul class="small text-muted">
                                    <li>Laporan akan diproses oleh admin</li>
                                    <li>Anda akan mendapat feedback dari admin</li>
                                    <li>Cek status laporan secara berkala di menu Histori</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
