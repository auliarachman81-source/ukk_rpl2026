<?php
require_once '../config.php';

// Cek apakah user sudah login dan merupakan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$success = '';
$error = '';

// Proses update status
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = clean_input($_POST['status']);
    
    $query = "UPDATE pengaduan SET status = '" . escape($status) . "'";
    if ($status == 'selesai') {
        $query .= ", tanggal_selesai = NOW()";
    }
    $query .= " WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $success = 'Status berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate status!';
    }
}

// Proses tambah feedback
if (isset($_POST['tambah_feedback'])) {
    $pengaduan_id = intval($_POST['pengaduan_id']);
    $pesan = clean_input($_POST['pesan']);
    
    $query = "INSERT INTO feedback (pengaduan_id, pesan) VALUES ($pengaduan_id, '" . escape($pesan) . "')";
    
    if (mysqli_query($conn, $query)) {
        $success = 'Feedback berhasil ditambahkan!';
    } else {
        $error = 'Gagal menambahkan feedback!';
    }
}

// Filter
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_siswa = isset($_GET['siswa']) ? intval($_GET['siswa']) : 0;

// Query pengaduan dengan filter
$query_pengaduan = "SELECT p.*, u.nama_lengkap, u.nis, k.nama_kategori 
                    FROM pengaduan p
                    JOIN users u ON p.user_id = u.id
                    JOIN kategori k ON p.kategori_id = k.id
                    WHERE 1=1";

if ($filter_status) {
    $query_pengaduan .= " AND p.status = '" . escape($filter_status) . "'";
}
if ($filter_kategori) {
    $query_pengaduan .= " AND p.kategori_id = $filter_kategori";
}
if ($filter_tanggal) {
    $query_pengaduan .= " AND DATE(p.tanggal_lapor) = '" . escape($filter_tanggal) . "'";
}
if ($filter_bulan) {
    $query_pengaduan .= " AND DATE_FORMAT(p.tanggal_lapor, '%Y-%m') = '" . escape($filter_bulan) . "'";
}
if ($filter_siswa) {
    $query_pengaduan .= " AND p.user_id = $filter_siswa";
}

$query_pengaduan .= " ORDER BY p.tanggal_lapor DESC";
$result_pengaduan = mysqli_query($conn, $query_pengaduan);

// Ambil data untuk filter
$result_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$result_siswa = mysqli_query($conn, "SELECT id, nama_lengkap, nis FROM users WHERE role='siswa' ORDER BY nama_lengkap");

// Detail pengaduan jika ada
$detail = null;
$feedback_list = [];
if (isset($_GET['detail'])) {
    $id = intval($_GET['detail']);
    $query_detail = "SELECT p.*, u.nama_lengkap, u.nis, u.kelas, u.email, k.nama_kategori 
                     FROM pengaduan p
                     JOIN users u ON p.user_id = u.id
                     JOIN kategori k ON p.kategori_id = k.id
                     WHERE p.id = $id";
    $result_detail = mysqli_query($conn, $query_detail);
    $detail = mysqli_fetch_assoc($result_detail);
    
    // Ambil feedback
    $query_feedback = "SELECT * FROM feedback WHERE pengaduan_id = $id ORDER BY tanggal_feedback DESC";
    $result_feedback = mysqli_query($conn, $query_feedback);
    while ($fb = mysqli_fetch_assoc($result_feedback)) {
        $feedback_list[] = $fb;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengaduan - Admin</title>
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
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3 text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Admin Panel</h5>
                    <small><?= $_SESSION['nama'] ?></small>
                </div>
                <nav class="nav flex-column px-2">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="kelola_user.php">
                        <i class="bi bi-people"></i> Kelola User
                    </a>
                    <a class="nav-link" href="kelola_kategori.php">
                        <i class="bi bi-tags"></i> Kelola Kategori
                    </a>
                    <a class="nav-link active" href="kelola_pengaduan.php">
                        <i class="bi bi-clipboard-check"></i> Kelola Pengaduan
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
                <h2 class="mb-4">Kelola Pengaduan</h2>
                
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
                
                <!-- Filter Section -->
                <div class="filter-section mb-4">
                    <h5 class="mb-3"><i class="bi bi-funnel"></i> Filter Pengaduan</h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="proses" <?= $filter_status == 'proses' ? 'selected' : '' ?>>Proses</option>
                                <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="ditolak" <?= $filter_status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="kategori">
                                <option value="">Semua Kategori</option>
                                <?php 
                                mysqli_data_seek($result_kategori, 0);
                                while ($kat = mysqli_fetch_assoc($result_kategori)): 
                                ?>
                                    <option value="<?= $kat['id'] ?>" <?= $filter_kategori == $kat['id'] ? 'selected' : '' ?>>
                                        <?= $kat['nama_kategori'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="<?= $filter_tanggal ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bulan</label>
                            <input type="month" class="form-control" name="bulan" value="<?= $filter_bulan ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Tabel Pengaduan -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Daftar Pengaduan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Siswa</th>
                                        <th>Kategori</th>
                                        <th>Judul</th>
                                        <th>Lokasi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result_pengaduan)): 
                                        $badge_class = [
                                            'pending' => 'bg-warning',
                                            'proses' => 'bg-info',
                                            'selesai' => 'bg-success',
                                            'ditolak' => 'bg-danger'
                                        ];
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_lapor'])) ?></td>
                                        <td>
                                            <strong><?= $row['nama_lengkap'] ?></strong><br>
                                            <small class="text-muted"><?= $row['nis'] ?></small>
                                        </td>
                                        <td><?= $row['nama_kategori'] ?></td>
                                        <td><?= $row['judul'] ?></td>
                                        <td><?= $row['lokasi'] ?></td>
                                        <td>
                                            <span class="badge <?= $badge_class[$row['status']] ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?detail=<?= $row['id'] ?>" class="btn btn-sm btn-primary"
                                               data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id'] ?>">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal Detail -->
                                    <div class="modal fade" id="modalDetail<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">Detail Pengaduan</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php
                                                    // Get detail for this specific row
                                                    $temp_id = $row['id'];
                                                    $temp_query = "SELECT p.*, u.nama_lengkap, u.nis, u.kelas, u.email, k.nama_kategori 
                                                                   FROM pengaduan p
                                                                   JOIN users u ON p.user_id = u.id
                                                                   JOIN kategori k ON p.kategori_id = k.id
                                                                   WHERE p.id = $temp_id";
                                                    $temp_result = mysqli_query($conn, $temp_query);
                                                    $temp_detail = mysqli_fetch_assoc($temp_result);
                                                    ?>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Siswa:</strong> <?= $temp_detail['nama_lengkap'] ?><br>
                                                            <strong>NIS:</strong> <?= $temp_detail['nis'] ?><br>
                                                            <strong>Kelas:</strong> <?= $temp_detail['kelas'] ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Tanggal Lapor:</strong> <?= date('d/m/Y H:i', strtotime($temp_detail['tanggal_lapor'])) ?><br>
                                                            <strong>Kategori:</strong> <?= $temp_detail['nama_kategori'] ?><br>
                                                            <strong>Status:</strong> 
                                                            <span class="badge <?= $badge_class[$temp_detail['status']] ?>">
                                                                <?= ucfirst($temp_detail['status']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr>
                                                    
                                                    <h6><strong>Judul:</strong></h6>
                                                    <p><?= $temp_detail['judul'] ?></p>
                                                    
                                                    <h6><strong>Deskripsi:</strong></h6>
                                                    <p><?= nl2br($temp_detail['deskripsi']) ?></p>
                                                    
                                                    <h6><strong>Lokasi:</strong></h6>
                                                    <p><?= $temp_detail['lokasi'] ?></p>
                                                    
                                                    <?php if ($temp_detail['foto']): ?>
                                                    <h6><strong>Foto:</strong></h6>
                                                    <img src="../uploads/<?= $temp_detail['foto'] ?>" class="img-fluid mb-3" alt="Foto">
                                                    <?php endif; ?>
                                                    
                                                    <hr>
                                                    
                                                    <!-- Update Status -->
                                                    <h6><strong>Update Status:</strong></h6>
                                                    <form method="POST" class="mb-3">
                                                        <input type="hidden" name="id" value="<?= $temp_detail['id'] ?>">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <select class="form-select" name="status" required>
                                                                    <option value="pending" <?= $temp_detail['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                                    <option value="proses" <?= $temp_detail['status'] == 'proses' ? 'selected' : '' ?>>Proses</option>
                                                                    <option value="selesai" <?= $temp_detail['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                                    <option value="ditolak" <?= $temp_detail['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <button type="submit" name="update_status" class="btn btn-primary w-100">
                                                                    <i class="bi bi-save"></i> Update
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    
                                                    <!-- Feedback -->
                                                    <h6><strong>Feedback:</strong></h6>
                                                    <?php
                                                    $query_fb = "SELECT * FROM feedback WHERE pengaduan_id = {$temp_detail['id']} ORDER BY tanggal_feedback DESC";
                                                    $result_fb = mysqli_query($conn, $query_fb);
                                                    while ($fb = mysqli_fetch_assoc($result_fb)):
                                                    ?>
                                                    <div class="alert alert-info">
                                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($fb['tanggal_feedback'])) ?></small>
                                                        <p class="mb-0 mt-1"><?= nl2br($fb['pesan']) ?></p>
                                                    </div>
                                                    <?php endwhile; ?>
                                                    
                                                    <form method="POST">
                                                        <input type="hidden" name="pengaduan_id" value="<?= $temp_detail['id'] ?>">
                                                        <div class="input-group">
                                                            <textarea class="form-control" name="pesan" rows="2" 
                                                                      placeholder="Tulis feedback..." required></textarea>
                                                            <button type="submit" name="tambah_feedback" class="btn btn-primary">
                                                                <i class="bi bi-send"></i> Kirim
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    
                                    <?php if (mysqli_num_rows($result_pengaduan) == 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Tidak ada data pengaduan</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
