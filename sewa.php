 <?php
require_once "auth.php";
require_once "koneksi.php";

$error = "";

// Tambah data sewa
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id_penyewa"] ?? 0);
    $mulai = $_POST["tanggal_mulai"] ?? "";
    $selesai = $_POST["tanggal_selesai"] ?? "";
    $biaya = (int)($_POST["total_biaya"] ?? 0);
    $bayar = (int)($_POST["jumlah_bayar"] ?? 0);
    $catatan = trim($_POST["catatan"] ?? "");

    if (
        $id > 0 &&
        $mulai &&
        $selesai &&
        $biaya > 0 &&
        $selesai >= $mulai &&
        $bayar >= 0 &&
        $bayar <= $biaya
    ) {
        // Status pembayaran ditentukan otomatis
        $status = ($bayar >= $biaya) ? "Lunas" : "Belum Lunas";

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO sewa
            (id_penyewa, tanggal_mulai, tanggal_selesai,
             total_biaya, jumlah_bayar, status_bayar, catatan)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "issiiss",
            $id,
            $mulai,
            $selesai,
            $biaya,
            $bayar,
            $status,
            $catatan
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: sewa.php?ok=1");
            exit;
        } else {
            $error = "Data sewa gagal disimpan.";
        }
    } else {
        $error = "Periksa kembali penyewa, tanggal, dan nominal pembayaran.";
    }
}

// Hapus data sewa
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM sewa WHERE id_sewa = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: sewa.php");
    exit;
}

// Ambil data penyewa untuk pilihan form
$penyewa = mysqli_query(
    $conn,
    "SELECT id_penyewa, nama FROM penyewa ORDER BY nama"
);

// Ambil daftar sewa
$data = mysqli_query(
    $conn,
    "SELECT s.*, p.nama
     FROM sewa s
     JOIN penyewa p ON p.id_penyewa = s.id_penyewa
     ORDER BY s.id_sewa DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Sewa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php  include __DIR__ . "/admin/header.php"; ?>

<nav class="top-menu">
    <a href="index.php">⌂ Beranda</a>
    <a href="penyewa.php">♙ Data Penyewa</a>
    <a class="active" href="sewa.php">▣ Data Sewa</a>
    <a href="rekap.php">▤ Rekap Pembayaran</a>
</nav>

<div class="layout">
    <?php 
    if (file_exists(__DIR__ . "/sidebar.php")) {
        include __DIR__ . "/sidebar.php"; 
    }
    ?>

    <main class="content">

        <div class="page-title">
            <span class="eyebrow">PENGELOLAAN</span>
            <h1>Data Sewa</h1>
            <p>Atur periode sewa dan pembayaran penghuni kost.</p>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="success">
                Data sewa berhasil ditambahkan.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["edit"]) && $_GET["edit"] === "berhasil"): ?>
            <div class="success">
                Data sewa berhasil diperbarui.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- FORM TAMBAH SEWA -->
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Tambah Data Sewa</h2>
                    <p>Nominal menggunakan rupiah tanpa tanda titik.</p>
                </div>
            </div>

            <?php if (!$penyewa || mysqli_num_rows($penyewa) === 0): ?>
                <div class="notice">
                    Tambahkan data penyewa terlebih dahulu di menu Data Penyewa.
                </div>
            <?php else: ?>

                <form method="post" class="form-grid">

                    <div>
                        <label>Nama penyewa</label>
                        <select name="id_penyewa" required>
                            <option value="">Pilih penyewa</option>

                            <?php while ($p = mysqli_fetch_assoc($penyewa)): ?>
                                <option value="<?= $p["id_penyewa"] ?>">
                                    <?= htmlspecialchars($p["nama"]) ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div>
                        <label>Total biaya sewa (Rp)</label>
                        <input
                            type="number"
                            name="total_biaya"
                            min="1"
                            required
                        >
                    </div>

                    <div>
                        <label>Tanggal mulai</label>
                        <input
                            type="date"
                            name="tanggal_mulai"
                            required
                        >
                    </div>

                    <div>
                        <label>Tanggal selesai</label>
                        <input
                            type="date"
                            name="tanggal_selesai"
                            required
                        >
                    </div>

                    <div>
                        <label>Jumlah dibayar (Rp)</label>
                        <input
                            type="number"
                            name="jumlah_bayar"
                            min="0"
                            value="0"
                            required
                        >
                    </div>

                    <div>
                        <label>Status pembayaran</label>
                        <input
                            type="text"
                            value="Dihitung otomatis"
                            readonly
                        >
                    </div>

                    <div class="span-2">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan" rows="2"></textarea>
                    </div>

                    <div class="span-2">
                        <button class="btn" type="submit">
                            + Simpan Data Sewa
                        </button>
                    </div>

                </form>
            <?php endif; ?>
        </section>

        <!-- DAFTAR SEWA -->
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Daftar Sewa</h2>
                    <p>Periode, biaya, dan status pembayaran.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Penyewa</th>
                            <th>Periode</th>
                            <th>Total Biaya</th>
                            <th>Dibayar</th>
                            <th>Status</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if ($data && mysqli_num_rows($data) > 0):
                            $no = 1;

                            while ($r = mysqli_fetch_assoc($data)):
                                $statusTampil =
                                    ((int)$r["jumlah_bayar"] >= (int)$r["total_biaya"])
                                    ? "Lunas"
                                    : "Belum Lunas";
                        ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars($r["nama"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r["tanggal_mulai"]) ?>
                                    s.d.
                                    <?= htmlspecialchars($r["tanggal_selesai"]) ?>
                                </td>

                                <td>
                                    Rp <?= number_format($r["total_biaya"], 0, ",", ".") ?>
                                </td>

                                <td>
                                    Rp <?= number_format($r["jumlah_bayar"], 0, ",", ".") ?>
                                </td>

                                <td>
                                    <span class="status <?= $statusTampil === "Lunas" ? "paid" : "unpaid" ?>">
                                        <?= htmlspecialchars($statusTampil) ?>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="edit_sewa.php?id=<?= (int)$r["id_sewa"] ?>"
                                        style="color:#8065d8;font-weight:600;margin-right:12px;"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        class="text-danger"
                                        href="sewa.php?hapus=<?= (int)$r["id_sewa"] ?>"
                                        onclick="return confirm('Yakin ingin menghapus data sewa ini?')"
                                    >
                                        Hapus
                                    </a>
                                </td>
                            </tr>

                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="empty">
                                    Belum ada data sewa.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

</body>
</html>