 
<?php
require_once "auth.php";
require_once "koneksi.php";
$filter = $_GET["status"] ?? "Semua";
if (!in_array($filter, ["Semua", "Lunas", "Belum Lunas"], true)) {
    $filter = "Semua";
}
$where = "";
if ($filter === "Lunas") {
    $where = " WHERE s.total_biaya > 0
               AND s.jumlah_bayar >= s.total_biaya";
} elseif ($filter === "Belum Lunas") {
    $where = " WHERE s.jumlah_bayar < s.total_biaya
               OR s.total_biaya <= 0";
}
$sql = "SELECT s.*, p.nama, p.nik
        FROM sewa s
        JOIN penyewa p ON p.id_penyewa = s.id_penyewa
        $where
        ORDER BY s.tanggal_mulai DESC";

$data = mysqli_query($conn, $sql);
$sumSql = "SELECT
               COALESCE(SUM(total_biaya), 0),
               COALESCE(SUM(jumlah_bayar), 0),
               COALESCE(SUM(GREATEST(total_biaya - jumlah_bayar, 0)), 0)
           FROM sewa";
if ($filter === "Lunas") {
    $sumSql .= " WHERE total_biaya > 0
                 AND jumlah_bayar >= total_biaya";
} elseif ($filter === "Belum Lunas") {
    $sumSql .= " WHERE jumlah_bayar < total_biaya
                 OR total_biaya <= 0";
}
$sum = mysqli_query($conn, $sumSql);
$tot = $sum ? mysqli_fetch_row($sum) : [0, 0, 0];

$tagihan = (int)$tot[0];
$dibayar = (int)$tot[1];
$sisa = (int)$tot[2];
$nilaiTotal = $dibayar + $sisa;

$persenDibayar = $nilaiTotal > 0
    ? round(($dibayar / $nilaiTotal) * 100, 1)
    : 0;

$persenSisa = $nilaiTotal > 0
    ? round(($sisa / $nilaiTotal) * 100, 1)
    : 0;

$sudutDibayar = $nilaiTotal > 0
    ? ($dibayar / $nilaiTotal) * 360
    : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Pembayaran | Sistem Informasi Kost</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .top-menu {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 14px 28px;
            background: #fff;
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
        .layout {
            display: block;
            min-height: auto;
        }
        .content {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }
        .pie-content {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 40px;
            padding: 28px;
        }
        .pie-chart-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .pie-chart {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pie-hole {
            width: 135px;
            height: 135px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .pie-hole strong {
            color: #4d426e;
            font-size: 26px;
        }
        .pie-hole span {
            color: #8f86a3;
            font-size: 12px;
        }
        .pie-legend {
            flex: 1;
            min-width: 230px;
            max-width: 440px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 0;
            border-bottom: 1px solid #f0eaf3;
        }
        .legend-item div {
            flex: 1;
        }
        .legend-item b {
            display: block;
            color: #51466f;
        }
        .legend-item small {
            display: block;
            margin-top: 5px;
            color: #9289a5;
        }
        .legend-item > strong {
            color: #51466f;
        }
        .legend-dot {
            width: 13px;
            height: 13px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .paid-dot {
            background: #9d83e6;
        }

        .due-dot {
            background: #f2b8ce;
        }

        .pie-footnote {
            margin-top: 18px;
            color: #9289a5;
            font-size: 12px;
        }

        .filter-form {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-form select {
            min-width: 140px;
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

            .pie-content {
                padding: 18px;
                gap: 24px;
            }

            .pie-chart {
                width: 180px;
                height: 180px;
            }

            .pie-hole {
                width: 115px;
                height: 115px;
            }

            .filter-form {
                width: 100%;
            }
        }

        @media print {
            .topbar,
            .top-menu,
            .filter-form,
            .btn {
                display: none !important;
            }

            .layout,
            .content {
                display: block;
                width: 100%;
                padding: 0;
                margin: 0;
            }

            .panel {
                break-inside: avoid;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

<!-- Header -->
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

<!-- Menu atas -->
<nav class="top-menu">
    <a href="index.php">⌂ Beranda</a>
    <a href="penyewa.php">♙ Data Penyewa</a>
    <a href="sewa.php">▣ Data Sewa</a>
    <a class="active" href="rekap.php">▤ Rekap Pembayaran</a>
</nav>

<div class="layout">
    <main class="content">

        <!-- Judul halaman -->
        <div class="page-title">
            <span class="eyebrow">LAPORAN</span>
            <h1>Rekap Pembayaran</h1>
            <p>
                Ringkasan biaya sewa, pembayaran yang diterima,
                dan sisa tagihan.
            </p>
        </div>

        <!-- Ringkasan nominal -->
        <div class="stats three">

            <div class="stat-card lavender">
                <span>Total Tagihan</span>
                <strong>
                    Rp <?= number_format($tagihan, 0, ",", ".") ?>
                </strong>
                <small>Nilai seluruh biaya sewa</small>
            </div>

            <div class="stat-card mint">
                <span>Total Dibayar</span>
                <strong>
                    Rp <?= number_format($dibayar, 0, ",", ".") ?>
                </strong>
                <small>Nominal pembayaran tercatat</small>
            </div>

            <div class="stat-card peach">
                <span>Sisa Tagihan</span>
                <strong>
                    Rp <?= number_format($sisa, 0, ",", ".") ?>
                </strong>
                <small>Total sisa pembayaran</small>
            </div>

        </div>

        <!-- Diagram pembayaran -->
        <section class="panel pie-panel">

            <div class="panel-heading">
                <div>
                    <h2>Diagram Rekap Pembayaran</h2>
                    <p>
                        Perbandingan nominal yang sudah dibayar
                        dan sisa tagihan.
                    </p>
                </div>
            </div>

            <div class="pie-content">

                <div class="pie-chart-wrap">
                    <div
                        class="pie-chart"
                        role="img"
                        aria-label="Pembayaran <?= $persenDibayar ?> persen dan sisa tagihan <?= $persenSisa ?> persen"
                        style="background: conic-gradient(
                            #9d83e6 0deg <?= $sudutDibayar ?>deg,
                            #f2b8ce <?= $sudutDibayar ?>deg 360deg
                        );"
                    >
                        <div class="pie-hole">
                            <strong><?= $persenDibayar ?>%</strong>
                            <span>sudah dibayar</span>
                        </div>
                    </div>
                </div>

                <div class="pie-legend">

                    <div class="legend-item">
                        <span class="legend-dot paid-dot"></span>

                        <div>
                            <b>Sudah dibayar</b>
                            <small>
                                Rp <?= number_format($dibayar, 0, ",", ".") ?>
                            </small>
                        </div>

                        <strong><?= $persenDibayar ?>%</strong>
                    </div>

                    <div class="legend-item">
                        <span class="legend-dot due-dot"></span>

                        <div>
                            <b>Sisa tagihan</b>
                            <small>
                                Rp <?= number_format($sisa, 0, ",", ".") ?>
                            </small>
                        </div>

                        <strong><?= $persenSisa ?>%</strong>
                    </div>

                    <p class="pie-footnote">
                        Diagram mengikuti filter status di bawah.
                    </p>

                </div>
            </div>
        </section>

        <!-- Tabel rekap -->
        <section class="panel">

            <div class="panel-heading">
                <div>
                    <h2>Rincian Rekap</h2>
                    <p>
                        Gunakan filter untuk melihat status pembayaran.
                    </p>
                </div>

                <form method="get" class="filter-form">
                    <select name="status">
                        <option value="Semua"
                            <?= $filter === "Semua" ? "selected" : "" ?>>
                            Semua
                        </option>

                        <option value="Lunas"
                            <?= $filter === "Lunas" ? "selected" : "" ?>>
                            Lunas
                        </option>

                        <option value="Belum Lunas"
                            <?= $filter === "Belum Lunas" ? "selected" : "" ?>>
                            Belum Lunas
                        </option>
                    </select>

                    <button class="btn btn-light" type="submit">
                        Terapkan
                    </button>

                    <button
                        class="btn btn-light"
                        type="button"
                        onclick="window.print()">
                        Cetak
                    </button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Penyewa</th>
                            <th>NIK</th>
                            <th>Periode Sewa</th>
                            <th>Total Biaya</th>
                            <th>Dibayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($data && mysqli_num_rows($data) > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($r = mysqli_fetch_assoc($data)): ?>

                                <?php
                                $sisaBaris = max(
                                    0,
                                    (int)$r["total_biaya"] -
                                    (int)$r["jumlah_bayar"]
                                );

                                $statusTampil = (
                                    (int)$r["total_biaya"] > 0 &&
                                    (int)$r["jumlah_bayar"] >=
                                    (int)$r["total_biaya"]
                                ) ? "Lunas" : "Belum Lunas";
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
                                        <?= htmlspecialchars($r["tanggal_mulai"]) ?>
                                        –
                                        <?= htmlspecialchars($r["tanggal_selesai"]) ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format(
                                            (int)$r["total_biaya"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format(
                                            (int)$r["jumlah_bayar"],
                                            0,
                                            ",",
                                            "."
                                        ) ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format(
                                            $sisaBaris,
                                            0,
                                            ",",
                                            "."
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="status <?= $statusTampil === "Lunas" ? "paid" : "unpaid" ?>">
                                            <?= htmlspecialchars($statusTampil) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="empty">
                                    Belum ada catatan untuk filter ini.
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <p class="footer-note">
            Rekap dihitung otomatis dari data sewa yang tersimpan.
        </p>

    </main>
</div>

</body>
</html>
 