 
<?php
 require_once "auth.php";
 require_once "koneksi.php";

// Fungsi menghitung data
function total($conn, $sql) {
    $r = mysqli_query($conn, $sql);
    return $r ? (int)mysqli_fetch_row($r)[0] : 0;
}

// Data dashboard
$jmlPenyewa = total($conn, "SELECT COUNT(*) FROM penyewa");
$jmlSewa = total($conn, "SELECT COUNT(*) FROM sewa");

$belumBayar = total(
    $conn,
    "SELECT COUNT(*) FROM sewa
     WHERE total_biaya > jumlah_bayar"
);

$pendapatan = total(
    $conn,
    "SELECT COALESCE(SUM(jumlah_bayar), 0)
     FROM sewa
     WHERE total_biaya > 0
       AND jumlah_bayar >= total_biaya"
);

// Aktivitas sewa terbaru
$recent = mysqli_query(
    $conn,
    "SELECT
        s.id_sewa,
        p.nama,
        s.tanggal_mulai,
        s.tanggal_selesai,
        s.total_biaya,
        s.jumlah_bayar
     FROM sewa s
     JOIN penyewa p ON p.id_penyewa = s.id_penyewa
     ORDER BY s.id_sewa DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beranda | Sistem Informasi Kost</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>
        /* Menu navigasi atas */
        .top-menu {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 14px 28px;
            background: #ffffff;
            border-bottom: 1px solid #f0e5ef;
            box-shadow: 0 3px 12px rgba(100, 70, 110, 0.04);
        }

        .top-menu a {
            display: inline-flex;
            align-items: center;
            padding: 12px 18px;
            border-radius: 12px;
            text-decoration: none;
            color: #756b8f;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .top-menu a:hover,
        .top-menu a.active {
            background: #f1e9fc;
            color: #8065d8;
        }

        /* Konten memenuhi halaman */
        .layout {
            display: block;
            min-height: auto;
        }

        .content {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        /* Status pembayaran */
        .status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 95px;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
            white-space: nowrap;
        }

        .status.paid {
            background-color: #dff5e8;
            color: #237747;
        }

        .status.unpaid {
            background-color: #fff0d9;
            color: #9a5b12;
        }

        @media (max-width: 600px) {
            .top-menu {
                gap: 6px;
                padding: 12px;
            }

            .top-menu a {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<!-- HEADER -->
<header class="topbar">
    <div class="brand">
        <span class="brand-mark small">⌂</span>

        <div>
            <b>SISTEM INFORMASI KOST</b>
            <small>Pengelolaan kost lebih praktis</small>
        </div>
    </div>

    <div class="user-area">
        Halo, <?= htmlspecialchars($_SESSION["username"] ?? "Pengguna") ?>
        <a class="logout" href="logout.php">Keluar</a>
    </div>
</header>

<!-- MENU ATAS -->
<nav class="top-menu">
    <a class="active" href="index.php">⌂ Beranda</a>
    <a href="penyewa.php">♙ Data Penyewa</a>
    <a href="sewa.php">▣ Data Sewa</a>
    <a href="rekap.php">▤ Rekap Pembayaran</a>
</nav>

<!-- KONTEN DASHBOARD -->
<div class="layout">
    <main class="content">

        <!-- Sambutan -->
        <div class="welcome">
            <div>
                <span class="eyebrow">DASHBOARD</span>
                <h1>Selamat datang di Sistem Informasi Kost</h1>
                <p>
                    Semua informasi penyewa dan pembayaran dalam satu tempat.
                </p>
            </div>

            <div class="welcome-icon">⌂</div>
        </div>

        <!-- Ringkasan -->
        <div class="stats">

            <div class="stat-card pink">
                <span>Total Penyewa</span>
                <strong><?= $jmlPenyewa ?></strong>
                <small>Data penyewa terdaftar</small>
            </div>

            <div class="stat-card lavender">
                <span>Data Sewa</span>
                <strong><?= $jmlSewa ?></strong>
                <small>Catatan masa sewa</small>
            </div>

            <div class="stat-card peach">
                <span>Belum Lunas</span>
                <strong><?= $belumBayar ?></strong>
                <small>Pembayaran perlu dicek</small>
            </div>

            <div class="stat-card mint">
                <span>Pendapatan Lunas</span>
                <strong>
                    Rp <?= number_format($pendapatan, 0, ",", ".") ?>
                </strong>
                <small>Total pembayaran dari sewa lunas</small>
            </div>

        </div>

        <!-- Aktivitas sewa terbaru -->
        <section class="panel">

            <div class="panel-heading">
                <div>
                    <h2>Aktivitas Sewa Terbaru</h2>
                    <p>Ringkasan lima data terakhir</p>
                </div>

                <a class="btn btn-light" href="sewa.php">
                    Lihat semua
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Penyewa</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($recent && mysqli_num_rows($recent) > 0): ?>

                            <?php while ($row = mysqli_fetch_assoc($recent)): ?>

                                <?php
                                $status = (
                                    (int)$row["total_biaya"] > 0 &&
                                    (int)$row["jumlah_bayar"] >=
                                    (int)$row["total_biaya"]
                                ) ? "Lunas" : "Belum Lunas";
                                ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars($row["nama"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["tanggal_mulai"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["tanggal_selesai"]) ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format(
                                            (int)$row["total_biaya"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="status <?= $status === "Lunas" ? "paid" : "unpaid" ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="empty">
                                    Belum ada data sewa. Tambahkan data melalui menu Data Sewa.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </section>

        <p class="footer-note">
            © <?= date("Y") ?> Sistem Informasi Kost
        </p>

    </main>
</div>

</body>
</html>
 