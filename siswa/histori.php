<?php
require_once '../config.php';

// Cek apakah user sudah login dan merupakan siswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query histori pengaduan siswa
$query_histori = "SELECT p.*, k.nama_kategori 
                  FROM pengaduan p
                  JOIN kategori k ON p.kategori_id = k.id
                  WHERE p.user_id = $user_id
                  ORDER BY p.tanggal_lapor DESC";
$result_histori = mysqli_query($conn, $query_histori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Laporan - Siswa</title>
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
        .timeline-item {
            border-left: 3px solid #667eea;
            padding-left: 20px;
            padding-bottom: 20px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            width: 15px;
            height: 15px;
            background: #667eea;
            border-radius: 50%;
            position: absolute;
            left: -9px;
            top: 0;
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
                    <a class="nav-link" href="buat_laporan.php">
                        <i class="bi bi-plus-circle"></i> Buat Laporan
                    </a>
                    <a class="nav-link active" href="histori.php">
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
                <h2 class="mb-4">Histori Laporan</h2>
                
                <?php if (mysqli_num_rows($result_histori) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result_histori)): 
                        $badge_class = [
                            'pending' => 'bg-warning',
                            'proses' => 'bg-info',
                            'selesai' => 'bg-success',
                            'ditolak' => 'bg-danger'
                        ];
                        
                        $icon_class = [
                            'pending' => 'bi-hourglass-split',
                            'proses' => 'bi-gear',
                            'selesai' => 'bi-check-circle',
                            'ditolak' => 'bi-x-circle'
                        ];
                        
                        // Ambil feedback
                        $query_feedback = "SELECT * FROM feedback WHERE pengaduan_id = {$row['id']} ORDER BY tanggal_feedback DESC";
                        $result_feedback = mysqli_query($conn, $query_feedback);
                    ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title">
                                        <i class="bi <?= $icon_class[$row['status']] ?>"></i>
                                        <?= $row['judul'] ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <small>
                                            <i class="bi bi-calendar"></i> <?= date('d/m/Y H:i', strtotime($row['tanggal_lapor'])) ?> | 
                                            <i class="bi bi-tag"></i> <?= $row['nama_kategori'] ?> | 
                                            <i class="bi bi-geo-alt"></i> <?= $row['lokasi'] ?>
                                        </small>
                                    </p>
                                    <p class="card-text"><?= nl2br($row['deskripsi']) ?></p>
                                    
                                    <?php if ($row['foto']): ?>
                                    <div class="mb-3">
                                        <img src="../uploads/<?= $row['foto'] ?>" class="img-thumbnail" 
                                             style="max-width: 300px;" alt="Foto">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Feedback Section -->
                                    <?php if (mysqli_num_rows($result_feedback) > 0): ?>
                                    <div class="mt-3">
                                        <h6><i class="bi bi-chat-left-text"></i> Feedback dari Admin:</h6>
                                        <?php while ($fb = mysqli_fetch_assoc($result_feedback)): ?>
                                        <div class="alert alert-info mb-2">
                                            <small class="text-muted d-block mb-1">
                                                <i class="bi bi-clock"></i> <?= date('d/m/Y H:i', strtotime($fb['tanggal_feedback'])) ?>
                                            </small>
                                            <?= nl2br($fb['pesan']) ?>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="mb-3">
                                        <h6 class="text-muted">Status Laporan</h6>
                                        <span class="badge <?= $badge_class[$row['status']] ?> px-4 py-2" style="font-size: 1.1em;">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="progress mb-3" style="height: 25px;">
                                        <?php 
                                        $progress = [
                                            'pending' => 25,
                                            'proses' => 50,
                                            'selesai' => 100,
                                            'ditolak' => 100
                                        ];
                                        ?>
                                        <div class="progress-bar <?= $badge_class[$row['status']] ?>" 
                                             role="progressbar" 
                                             style="width: <?= $progress[$row['status']] ?>%"
                                             aria-valuenow="<?= $progress[$row['status']] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?= $progress[$row['status']] ?>%
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <small class="text-muted">Laporan Dibuat</small><br>
                                        <small><?= date('d/m/Y', strtotime($row['tanggal_lapor'])) ?></small>
                                    </div>
                                    
                                    <?php if ($row['status'] == 'proses'): ?>
                                    <div class="timeline-item">
                                        <small class="text-muted">Sedang Dikerjakan</small><br>
                                        <small><i class="bi bi-tools"></i> Proses perbaikan</small>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['status'] == 'selesai' && $row['tanggal_selesai']): ?>
                                    <div class="timeline-item">
                                        <small class="text-muted">Selesai</small><br>
                                        <small><?= date('d/m/Y', strtotime($row['tanggal_selesai'])) ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">Belum Ada Laporan</h5>
                            <p class="text-muted">Anda belum memiliki histori laporan</p>
                            <a href="buat_laporan.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Buat Laporan Baru
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
