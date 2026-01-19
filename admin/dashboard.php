<?php
require_once '../config.php';

// Cek apakah user sudah login dan merupakan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Statistik dashboard
$total_pengaduan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan"))['total'];
$total_siswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='siswa'"))['total'];

// Pengaduan terbaru
$query_pengaduan = "SELECT p.*, u.nama_lengkap, u.nis, k.nama_kategori 
                    FROM pengaduan p
                    JOIN users u ON p.user_id = u.id
                    JOIN kategori k ON p.kategori_id = k.id
                    ORDER BY p.tanggal_lapor DESC
                    LIMIT 10";
$result_pengaduan = mysqli_query($conn, $query_pengaduan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pengaduan Sekolah</title>
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
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
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
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="kelola_user.php">
                        <i class="bi bi-people"></i> Kelola User
                    </a>
                    <a class="nav-link" href="kelola_kategori.php">
                        <i class="bi bi-tags"></i> Kelola Kategori
                    </a>
                    <a class="nav-link" href="kelola_pengaduan.php">
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
                <h2 class="mb-4">Dashboard</h2>
             
                <!-- Pengaduan Terbaru -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Pengaduan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Siswa</th>
                                        <th>Kategori</th>
                                        <th>Judul</th>
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
                                        <td><?= date('d/m/Y H:i', strtotime($row['tanggal_lapor'])) ?></td>
                                        <td>
                                            <strong><?= $row['nama_lengkap'] ?></strong><br>
                                            <small class="text-muted"><?= $row['nis'] ?></small>
                                        </td>
                                        <td><?= $row['nama_kategori'] ?></td>
                                        <td><?= $row['judul'] ?></td>
                                        <td>
                                            <span class="badge <?= $badge_class[$row['status']] ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="kelola_pengaduan.php?detail=<?= $row['id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if (mysqli_num_rows($result_pengaduan) == 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada pengaduan</td>
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
