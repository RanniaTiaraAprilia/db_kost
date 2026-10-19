```php
<?php
require_once "auth.php";
require_once "koneksi.php";

$error = "";

$id = (int)($_GET["id"] ?? $_POST["id_penyewa"] ?? 0);

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM penyewa WHERE id_penyewa = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$penyewa = mysqli_fetch_assoc($result);

if (!$penyewa) {
    exit("Data penyewa tidak ditemukan.");
}

$nama = $penyewa["nama"];
$nik = $penyewa["nik"];
$no_hp = $penyewa["no_hp"] ?? "";
$alamat = $penyewa["alamat"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $nik = trim($_POST["nik"] ?? "");
    $no_hp = trim($_POST["no_hp"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    if ($nama !== "" && $nik !== "") {
        $update = mysqli_prepare(
            $conn,
            "UPDATE penyewa
             SET nama = ?, nik = ?, no_hp = ?, alamat = ?
             WHERE id_penyewa = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "ssssi",
            $nama,
            $nik,
            $no_hp,
            $alamat,
            $id
        );

        if (mysqli_stmt_execute($update)) {
            header("Location: penyewa.php?edit=berhasil");
            exit;
        } else {
            $error = "Data penyewa gagal diperbarui.";
        }
    } else {
        $error = "Nama dan NIK wajib diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Data Penyewa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include "partials/header.php"; ?>

<div class="layout">
    <?php include "partials/sidebar.php"; ?>

    <main class="content">
        <div class="page-title">
            <span class="eyebrow">PENGELOLAAN</span>
            <h1>Edit Data Penyewa</h1>
            <p>Perbarui informasi penyewa kost.</p>
        </div>

        <?php if ($error !== ""): ?>
            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Form Edit Penyewa</h2>
                    <p>Ubah informasi penyewa, lalu simpan perubahan.</p>
                </div>
            </div>

            <form method="post" class="form-grid">
                <input
                    type="hidden"
                    name="id_penyewa"
                    value="<?= $id ?>"
                >

                <div>
                    <label>Nama Penyewa</label>
                    <input
                        type="text"
                        name="nama"
                        value="<?= htmlspecialchars($nama) ?>"
                        required
                    >
                </div>

                <div>
                    <label>NIK</label>
                    <input
                        type="text"
                        name="nik"
                        value="<?= htmlspecialchars($nik) ?>"
                        required
                    >
                </div>

                <div>
                    <label>Nomor HP</label>
                    <input
                        type="text"
                        name="no_hp"
                        value="<?= htmlspecialchars($no_hp) ?>"
                    >
                </div>

                <div>
                    <label>Alamat</label>
                    <textarea
                        name="alamat"
                        rows="3"
                    ><?= htmlspecialchars($alamat) ?></textarea>
                </div>

                <div class="span-2">
                    <button type="submit" class="btn">
                        Simpan Perubahan
                    </button>

                    <a href="penyewa.php" class="btn btn-light">
                        Batal
                    </a>
                </div>
            </form>
        </section>
    </main>
</div>

</body>
</html>
```