<?php
require_once '../config.php';

// Cek apakah user sudah login dan merupakan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$success = '';
$error = '';

// Proses tambah kategori
if (isset($_POST['tambah'])) {
    $nama_kategori = clean_input($_POST['nama_kategori']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    if (!empty($nama_kategori)) {
        $query = "INSERT INTO kategori (nama_kategori, deskripsi) 
                  VALUES ('" . escape($nama_kategori) . "', '" . escape($deskripsi) . "')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Kategori berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan kategori: ' . mysqli_error($conn);
        }
    } else {
        $error = 'Nama kategori tidak boleh kosong!';
    }
}

// Proses update kategori
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nama_kategori = clean_input($_POST['nama_kategori']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    if (!empty($nama_kategori)) {
        $query = "UPDATE kategori 
                  SET nama_kategori = '" . escape($nama_kategori) . "', 
                      deskripsi = '" . escape($deskripsi) . "'
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Kategori berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate kategori: ' . mysqli_error($conn);
        }
    } else {
        $error = 'Nama kategori tidak boleh kosong!';
    }
}

// Proses hapus kategori
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Cek apakah kategori sedang digunakan
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE kategori_id = $id");
    $result = mysqli_fetch_assoc($check);
    
    if ($result['total'] > 0) {
        $error = 'Kategori tidak dapat dihapus karena masih digunakan oleh pengaduan!';
    } else {
        $query = "DELETE FROM kategori WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $success = 'Kategori berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus kategori: ' . mysqli_error($conn);
        }
    }
}

// Ambil semua kategori
$query_kategori = "SELECT k.*, 
                   (SELECT COUNT(*) FROM pengaduan WHERE kategori_id = k.id) as total_pengaduan
                   FROM kategori k 
                   ORDER BY k.nama_kategori";
$result_kategori = mysqli_query($conn, $query_kategori);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin</title>
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
        .category-card {
            transition: transform 0.3s;
            border-left: 4px solid #667eea;
        }
        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                    <a class="nav-link active" href="kelola_kategori.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-tags"></i> Kelola Kategori</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle"></i> Tambah Kategori
                    </button>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Daftar Kategori -->
                <div class="row">
                    <?php while ($kategori = mysqli_fetch_assoc($result_kategori)): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card category-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-tag text-primary"></i> 
                                        <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                    </h5>
                                    <span class="badge bg-primary"><?= $kategori['total_pengaduan'] ?> pengaduan</span>
                                </div>
                                <p class="card-text text-muted small">
                                    <?= htmlspecialchars($kategori['deskripsi']) ?>
                                </p>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" 
                                            onclick="editKategori(<?= $kategori['id'] ?>, '<?= htmlspecialchars($kategori['nama_kategori'], ENT_QUOTES) ?>', '<?= htmlspecialchars($kategori['deskripsi'], ENT_QUOTES) ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <?php if ($kategori['total_pengaduan'] == 0): ?>
                                    <a href="?hapus=<?= $kategori['id'] ?>" 
                                       class="btn btn-outline-danger" 
                                       onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-outline-secondary" disabled title="Tidak dapat dihapus karena masih digunakan">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="tambahModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_kategori" required 
                                   placeholder="Contoh: Kerusakan Meja/Kursi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3" 
                                      placeholder="Deskripsi kategori (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Kategori -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_kategori" id="edit_nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editKategori(id, nama, deskripsi) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
