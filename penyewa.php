 
<?php
 require_once "auth.php";
 require_once "koneksi.php";
$error = "";
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $nik = trim($_POST["nik"] ?? "");
    $hp = trim($_POST["no_hp"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    if ($nama && $nik && $hp && $alamat) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO penyewa (nama, nik, no_hp, alamat)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "ssss", $nama, $nik, $hp, $alamat);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: penyewa.php?ok=1");
            exit;
        } else {
            $error = "Gagal menyimpan. Pastikan NIK belum terdaftar.";
        }
    } else {
        $error = "Lengkapi semua kolom terlebih dahulu.";
    }
}

// Hapus data penyewa
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM penyewa WHERE id_penyewa = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: penyewa.php");
    exit;
}

// Ambil data penyewa
$data = mysqli_query(
    $conn,
    "SELECT * FROM penyewa ORDER BY id_penyewa DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Penyewa</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <?php  include __DIR__ . "/admin/header.php"; ?>

<nav class="top-menu">
    <a href="index.php">⌂ Beranda</a>
    <a class="active" href="penyewa.php">♙ Data Penyewa</a>
    <a href="sewa.php">▣ Data Sewa</a>
    <a href="rekap.php">▤ Rekap Pembayaran</a>
</nav>

<div class="layout">
    <main class="content">
        <div class="page-title">
            <span class="eyebrow">DATA KOST</span>
            <h1>Data Penyewa</h1>
            <p>Catat identitas penghuni kost dengan rapi.</p>
        </div>

        <?php if (isset($_GET["ok"])): ?>
            <div class="success">
                Data penyewa berhasil ditambahkan.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["edit"]) && $_GET["edit"] === "berhasil"): ?>
            <div class="success">
                Data penyewa berhasil diperbarui.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- FORM TAMBAH PENYEWA -->
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Tambah Penyewa</h2>
                    <p>Isi data penyewa baru di bawah ini.</p>
                </div>
            </div>

            <form method="post" class="form-grid">

                <div>
                    <label>Nama lengkap</label>
                    <input type="text" name="nama" required>
                </div>

                <div>
                    <label>NIK</label>
                    <input type="text" name="nik" maxlength="20" required>
                </div>

                <div>
                    <label>Nomor HP</label>
                    <input type="text" name="no_hp" maxlength="20" required>
                </div>

                <div class="span-2">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="2" required></textarea>
                </div>

                <div class="span-2">
                    <button class="btn" type="submit">
                        + Simpan Penyewa
                    </button>
                </div>

            </form>
        </section>

        <!-- DAFTAR PENYEWA -->
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Daftar Penyewa</h2>
                    <p>Data yang tersimpan dalam database.</p>
                </div>

                <span class="count">
                    <?= $data ? mysqli_num_rows($data) : 0 ?> penyewa
                </span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Penyewa</th>
                            <th>NIK</th>
                            <th>No. HP</th>
                            <th>Alamat</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if ($data && mysqli_num_rows($data) > 0):
                            $no = 1;

                            while ($r = mysqli_fetch_assoc($data)):
                        ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars($r["nama"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r["nik"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r["no_hp"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r["alamat"]) ?>
                                </td>

                                <td>
                                    <a
                                        href="edit_penyewa.php?id=<?= (int)$r["id_penyewa"] ?>"
                                        style="color:#8065d8;font-weight:600;margin-right:12px;"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        class="text-danger"
                                        href="penyewa.php?hapus=<?= (int)$r["id_penyewa"] ?>"
                                        onclick="return confirm('Hapus data penyewa ini? Data sewa terkait juga dapat terpengaruh.')"
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
                                <td colspan="6" class="empty">
                                    Belum ada data penyewa.
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
```