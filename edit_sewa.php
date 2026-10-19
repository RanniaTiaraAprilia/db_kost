<?php
require_once "auth.php";
require_once "koneksi.php";

$error = "";
$id = (int)($_GET["id"] ?? $_POST["id_sewa"] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM sewa WHERE id_sewa = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$sewa = mysqli_fetch_assoc($result);

if (!$sewa) {
    exit("Data sewa tidak ditemukan.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_penyewa = (int)($_POST["id_penyewa"] ?? 0);
    $mulai = $_POST["tanggal_mulai"] ?? "";
    $selesai = $_POST["tanggal_selesai"] ?? "";
    $biaya = (int)($_POST["total_biaya"] ?? 0);
    $bayar = (int)($_POST["jumlah_bayar"] ?? 0);
    $catatan = trim($_POST["catatan"] ?? "");

    if (
        $id_penyewa > 0 && $mulai && $selesai &&
        $selesai >= $mulai && $biaya > 0 &&
        $bayar >= 0 && $bayar <= $biaya
    ) {
        $status = ($bayar >= $biaya) ? "Lunas" : "Belum Lunas";

        $update = mysqli_prepare(
            $conn,
            "UPDATE sewa
             SET id_penyewa=?, tanggal_mulai=?, tanggal_selesai=?,
                 total_biaya=?, jumlah_bayar=?, status_bayar=?, catatan=?
             WHERE id_sewa=?"
        );

        mysqli_stmt_bind_param(
            $update,
            "issiiisi",
            $id_penyewa,
            $mulai,
            $selesai,
            $biaya,
            $bayar,
            $status,
            $catatan,
            $id
        );

        if (mysqli_stmt_execute($update)) {
            header("Location: sewa.php?edit=berhasil");
            exit;
        } else {
            $error = "Data gagal diperbarui: " . mysqli_error($conn);
        }
    } else {
        $error = "Periksa kembali penyewa, tanggal, dan nominal pembayaran.";
    }
}

$penyewa = mysqli_query(
    $conn,
    "SELECT id_penyewa, nama FROM penyewa ORDER BY nama"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Sewa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include "partials/header.php"; ?>

<div class="layout">
<?php include "partials/sidebar.php"; ?>

<main class="content">
    <div class="page-title">
        <span class="eyebrow">PENGELOLAAN</span>
        <h1>Edit Data Sewa</h1>
        <p>Ubah data sewa dan pembayaran penyewa.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Form Edit Sewa</h2>
                <p>Status pembayaran diperbarui otomatis berdasarkan nominal.</p>
            </div>
        </div>

        <form method="post" class="form-grid">
            <input type="hidden" name="id_sewa" value="<?= $id ?>">

            <div>
                <label>Nama penyewa</label>
                <select name="id_penyewa" required>
                    <?php while ($p = mysqli_fetch_assoc($penyewa)): ?>
                        <option value="<?= $p["id_penyewa"] ?>"
                            <?= (int)$p["id_penyewa"] === (int)$sewa["id_penyewa"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($p["nama"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label>Total biaya sewa (Rp)</label>
                <input type="number" name="total_biaya" min="1"
                    value="<?= (int)$sewa["total_biaya"] ?>" required>
            </div>

            <div>
                <label>Tanggal mulai</label>
                <input type="date" name="tanggal_mulai"
                    value="<?= htmlspecialchars($sewa["tanggal_mulai"]) ?>" required>
            </div>

            <div>
                <label>Tanggal selesai</label>
                <input type="date" name="tanggal_selesai"
                    value="<?= htmlspecialchars($sewa["tanggal_selesai"]) ?>" required>
            </div>

            <div>
                <label>Jumlah dibayar (Rp)</label>
                <input type="number" name="jumlah_bayar" min="0"
                    value="<?= (int)$sewa["jumlah_bayar"] ?>" required>
            </div>

            <div>
                <label>Status pembayaran</label>
                <input type="text"
                    value="<?= (int)$sewa["jumlah_bayar"] >= (int)$sewa["total_biaya"] ? "Lunas" : "Belum Lunas" ?>"
                    readonly>
            </div>

            <div class="span-2">
                <label>Catatan</label>
                <textarea name="catatan" rows="3"><?= htmlspecialchars($sewa["catatan"] ?? "") ?></textarea>
            </div>

            <div class="span-2">
                <button class="btn" type="submit">Simpan Perubahan</button>
                <a class="btn btn-light" href="sewa.php">Batal</a>
            </div>
        </form>
    </section>
</main>
</div>
</body>
</html>