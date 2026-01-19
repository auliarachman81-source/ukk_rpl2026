<?php
require_once '../config.php';

// Cek apakah user sudah login dan merupakan siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Statistik dashboard siswa
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE user_id = $user_id"))['total'];

// Laporan terbaru siswa
$query_pengaduan = "SELECT p.*, k.nama_kategori 
                    FROM pengaduan p
                    JOIN kategori k ON p.kategori_id = k.id
                    WHERE p.user_id = $user_id
                    ORDER BY p.tanggal_lapor DESC
                    LIMIT 5";
$result_pengaduan = mysqli_query($conn, $query_pengaduan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Pengaduan Sekolah</title>
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
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="buat_laporan.php">
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
                <h2 class="mb-4">Dashboard Siswa</h2>
                
                                
                             
                <!-- Laporan Terbaru -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Laporan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    while ($row = mysqli_fetch_assoc($result_pengaduan)): 
                                        $badge_class = [
                                            'pending' => 'bg-warning',
                                            'proses' => 'bg-info',
                                            'selesai' => 'bg-success',
                                            'ditolak' => 'bg-danger'
                                        ];
                                        
                                        // Cek feedback
                                        $feedback_count = mysqli_fetch_assoc(
                                            mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE pengaduan_id = {$row['id']}")
                                        )['total'];
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($row['tanggal_lapor'])) ?></td>
                                        <td><span class="badge bg-secondary"><?= $row['nama_kategori'] ?></span></td>
                                        <td><?= $row['judul'] ?></td>
                                        <td>
                                            <span class="badge <?= $badge_class[$row['status']] ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($feedback_count > 0): ?>
                                                <span class="badge bg-info"><?= $feedback_count ?> feedback</span>
                                            <?php else: ?>
                                                <span class="text-muted">Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    
                                    <?php if (mysqli_num_rows($result_pengaduan) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Belum ada laporan. <a href="buat_laporan.php">Buat laporan baru</a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($total_laporan > 5): ?>
                        <div class="text-center mt-3">
                            <a href="histori.php" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat Semua Laporan
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
