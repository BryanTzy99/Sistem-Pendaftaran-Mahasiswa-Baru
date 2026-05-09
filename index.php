<?php
session_start();

// Inisialisasi session
if (!isset($_SESSION['pendaftar'])) {
    $_SESSION['pendaftar'] = [];
}

// Logika Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    unset($_SESSION['pendaftar'][$id]);
    $_SESSION['pendaftar'] = array_values($_SESSION['pendaftar']);
    header("Location: index.php");
    exit();
}

// Logika Ambil Data untuk Edit
$edit_data = null;
$edit_id = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    if (isset($_SESSION['pendaftar'][$edit_id])) {
        $edit_data = $_SESSION['pendaftar'][$edit_id];
    }
}

// Logika Simpan (Tambah atau Update)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $errors = [];
    
    // Validasi data
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tgl_lahir = $_POST['tgl_lahir'];
    $jk = $_POST['jk'];
    $peks_ortu = trim($_POST['peks_ortu']);
    
    if (empty($kode)) $errors[] = "Kode pendaftaran harus diisi";
    if (empty($nama)) $errors[] = "Nama harus diisi";
    if (empty($tempat_lahir)) $errors[] = "Tempat lahir harus diisi";
    if (empty($tgl_lahir)) $errors[] = "Tanggal lahir harus diisi";
    if (empty($peks_ortu)) $errors[] = "Pekerjaan orang tua harus diisi";
    
    // Validasi nilai di sisi PHP (Limit 0-100)
    $mat = isset($_POST['mat']) ? max(0, min(100, (float)$_POST['mat'])) : 0;
    $ingg = isset($_POST['ingg']) ? max(0, min(100, (float)$_POST['ingg'])) : 0;
    $umum = isset($_POST['umum']) ? max(0, min(100, (float)$_POST['umum'])) : 0;
    
    // Logika Tempat Tes
    $prefix = strtoupper(substr($kode, 0, 1));
    $tempat_tes = "Tidak Diketahui";
    if ($prefix == 'A') $tempat_tes = "Gedung A";
    elseif ($prefix == 'B') $tempat_tes = "Gedung B";
    elseif ($prefix == 'V') $tempat_tes = "Viktor";
    
    // Hitung Rata-rata & Keterangan
    $rata = ($mat + $ingg + $umum) / 3;
    if ($rata >= 70) {
        $keterangan = "Lulus";
        $status_color = "success";
    } elseif ($rata >= 60) {
        $keterangan = "Cadangan";
        $status_color = "warning";
    } else {
        $keterangan = "Tidak Lulus";
        $status_color = "danger";
    }
    
    // Generate ID unik untuk pendaftar
    $pendaftar_id = isset($_POST['id_edit']) && $_POST['id_edit'] !== "" ? $_POST['id_edit'] : uniqid();
    
    $data_baru = [
        'id' => $pendaftar_id,
        'kode' => $kode,
        'nama' => $nama,
        'tempat_lahir' => $tempat_lahir,
        'tgl_lahir' => $tgl_lahir,
        'jk' => $jk,
        'peks_ortu' => $peks_ortu,
        'tempat_tes' => $tempat_tes,
        'mat' => $mat,
        'ingg' => $ingg,
        'umum' => $umum,
        'rata' => number_format($rata, 2),
        'keterangan' => $keterangan,
        'status_color' => $status_color,
        'tgl_daftar' => date('Y-m-d H:i:s')
    ];
    
    if (empty($errors)) {
        if (isset($_POST['id_edit']) && $_POST['id_edit'] !== "") {
            // Update Data
            foreach ($_SESSION['pendaftar'] as $key => $p) {
                if ($p['id'] == $_POST['id_edit']) {
                    $_SESSION['pendaftar'][$key] = $data_baru;
                    break;
                }
            }
        } else {
            // Tambah Data Baru
            $_SESSION['pendaftar'][] = $data_baru;
        }
        header("Location: index.php?success=1");
        exit();
    }
}

// Hitung Statistik
$total_pendaftar = count($_SESSION['pendaftar']);
$total_lulus = 0;
$total_cadangan = 0;
$total_tidak_lulus = 0;
$rata_nilai_keseluruhan = 0;
$total_rata = 0;

foreach ($_SESSION['pendaftar'] as $p) {
    if ($p['keterangan'] == "Lulus") $total_lulus++;
    if ($p['keterangan'] == "Cadangan") $total_cadangan++;
    if ($p['keterangan'] == "Tidak Lulus") $total_tidak_lulus++;
    $total_rata += $p['rata'];
}
if ($total_pendaftar > 0) {
    $rata_nilai_keseluruhan = $total_rata / $total_pendaftar;
}

// Pesan sukses
$success_message = isset($_GET['success']) ? "Data berhasil disimpan!" : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pendaftaran Mahasiswa Baru | UTS Pemrograman Web II</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1em;
            opacity: 0.9;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            display: inline-block;
        }
        
        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
            font-size: 0.9em;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: #667eea;
        }
        
        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95em;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Button */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9em;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
        }
        
        .btn-edit:hover {
            background: #ffb300;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        /* Table */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85em;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 15px 12px;
            text-align: center;
            font-weight: 600;
            white-space: nowrap;
        }
        
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tr:hover {
            background: #f8f9fa;
            transition: background 0.3s ease;
        }
        
        /* Status Badge */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85em;
            display: inline-block;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
            font-size: 0.9em;
        }
        
        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.5s ease;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8em;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            color: white;
            font-size: 0.85em;
            opacity: 0.9;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }
            
            .card {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-graduation-cap"></i> Sistem Pendaftaran Mahasiswa Baru</h1>
        <p>Universitas Pamulang | Tahun Akademik 2026/2027</p>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= $success_message ?>
        </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= $total_pendaftar ?></div>
            <div class="stat-label">Total Pendaftar</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle" style="color: #28a745;"></i></div>
            <div class="stat-value"><?= $total_lulus ?></div>
            <div class="stat-label">Lulus</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock" style="color: #ffc107;"></i></div>
            <div class="stat-value"><?= $total_cadangan ?></div>
            <div class="stat-label">Cadangan</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-times-circle" style="color: #dc3545;"></i></div>
            <div class="stat-value"><?= $total_tidak_lulus ?></div>
            <div class="stat-label">Tidak Lulus</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line" style="color: #667eea;"></i></div>
            <div class="stat-value"><?= number_format($rata_nilai_keseluruhan, 1) ?></div>
            <div class="stat-label">Rata-rata Nilai</div>
        </div>
    </div>
    
    <div class="card">
        <h3 class="card-title">
            <i class="fas fa-<?= $edit_data ? 'edit' : 'plus-circle' ?>"></i>
            <?= $edit_data ? 'Edit Data Pendaftar' : 'Form Pendaftaran Mahasiswa' ?>
        </h3>
        
        <form method="POST">
            <input type="hidden" name="id_edit" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label><i class="fas fa-barcode"></i> Kode Pendaftaran</label>
                    <input type="text" name="kode" value="<?= $edit_data['kode'] ?? '' ?>" required placeholder="Contoh: A2-101-9">
                    <small style="color: #666; margin-top: 5px;">Kode dimulai dengan A, B, atau V untuk menentukan tempat tes</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= $edit_data['nama'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= $edit_data['tempat_lahir'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" value="<?= $edit_data['tgl_lahir'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-venus-mars"></i> Jenis Kelamin</label>
                    <select name="jk" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-Laki" <?= (isset($edit_data['jk']) && $edit_data['jk'] == 'Laki-Laki') ? 'selected' : '' ?>>Laki-Laki</option>
                        <option value="Perempuan" <?= (isset($edit_data['jk']) && $edit_data['jk'] == 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-briefcase"></i> Pekerjaan Orang Tua</label>
                    <input type="text" name="peks_ortu" value="<?= $edit_data['peks_ortu'] ?? '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calculator"></i> Nilai Matematika (0-100)</label>
                    <input type="number" name="mat" value="<?= $edit_data['mat'] ?? '' ?>" min="0" max="100" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-language"></i> Nilai Bahasa Inggris (0-100)</label>
                    <input type="number" name="ingg" value="<?= $edit_data['ingg'] ?? '' ?>" min="0" max="100" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-globe"></i> Nilai Pengetahuan Umum (0-100)</label>
                    <input type="number" name="umum" value="<?= $edit_data['umum'] ?? '' ?>" min="0" max="100" step="0.01" required>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-<?= $edit_data ? 'save' : 'paper-plane' ?>"></i>
                    <?= $edit_data ? 'Update Data' : 'Simpan Pendaftaran' ?>
                </button>
                <?php if($edit_data): ?>
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal Edit
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card">
        <h3 class="card-title">
            <i class="fas fa-table"></i>
            Data Pendaftar Aktif
            <span style="font-size: 0.8em; background: #667eea; color: white; padding: 2px 8px; border-radius: 20px; margin-left: 10px;">
                <?= $total_pendaftar ?> Pendaftar
            </span>
        </h3>
        
        <div class="table-wrapper">
            <?php if (empty($_SESSION['pendaftar'])): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada data pendaftar</p>
                    <small>Silakan isi form di atas untuk menambahkan pendaftar baru</small>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Tempat Lahir</th>
                            <th>JK</th>
                            <th>Tgl Lahir</th>
                            <th>Pekerjaan Ortu</th>
                            <th>Tempat Tes</th>
                            <th>Mat</th>
                            <th>Ingg</th>
                            <th>Umum</th>
                            <th>Rata-rata</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['pendaftar'] as $index => $data): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($data['kode']) ?></strong></td>
                                <td><?= htmlspecialchars($data['nama']) ?></td>
                                <td><?= htmlspecialchars($data['tempat_lahir']) ?></td>
                                <td><?= $data['jk'] == 'Laki-Laki' ? '<i class="fas fa-mars"></i>' : '<i class="fas fa-venus"></i>' ?> <?= $data['jk'] ?></td>
                                <td><?= date('d/m/Y', strtotime($data['tgl_lahir'])) ?></td>
                                <td><?= htmlspecialchars($data['peks_ortu']) ?></td>
                                <td><i class="fas fa-building"></i> <?= $data['tempat_tes'] ?></td>
                                <td><?= $data['mat'] ?></td>
                                <td><?= $data['ingg'] ?></td>
                                <td><?= $data['umum'] ?></td>
                                <td><strong><?= $data['rata'] ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= $data['status_color'] ?? ($data['rata'] >= 70 ? 'success' : ($data['rata'] >= 60 ? 'warning' : 'danger')) ?>">
                                        <i class="fas fa-<?= $data['keterangan'] == 'Lulus' ? 'check-circle' : ($data['keterangan'] == 'Cadangan' ? 'clock' : 'times-circle') ?>"></i>
                                        <?= $data['keterangan'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?= $index ?>" class="btn btn-edit btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?hapus=<?= $index ?>" class="btn btn-delete btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2026 Bryan Prathama Selbi | All Rights Reserved</p>
        <p style="margin-top: 10px;">
            <i class="fas fa-info-circle"></i> Sistem Informasi Pendaftaran Mahasiswa Baru
        </p>
    </div>
</div>

<script>
    // Auto hide alert after 3 seconds
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }
    }, 3000);
    
    // Validasi nilai input agar tidak melebihi 100
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value > 100) this.value = 100;
            if (this.value < 0) this.value = 0;
        });
    });
</script>
</body>
</html>