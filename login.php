<?php
session_start();
require_once "koneksi.php";
if (!empty($_SESSION["login"])) { header("Location: index.php"); exit; }
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
     
    if ($username === "admin" && $password === "admin123") {
        session_regenerate_id(true);
        $_SESSION["login"] = true;
        $_SESSION["username"] = $username;
        header("Location: index.php");
        exit;
    }
    // Jika tabel pengguna tersedia, akun database juga diperiksa.
    $check = @mysqli_query($conn, "SHOW TABLES LIKE 'pengguna'");
    if ($check && mysqli_num_rows($check) > 0) {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM pengguna WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row["password"]) || hash_equals((string)$row["password"], $password)) {
                session_regenerate_id(true);
                $_SESSION["login"] = true;
                $_SESSION["username"] = $row["username"];
                header("Location: index.php");
                exit;
            }
        }
    }
    $error = "Nama pengguna atau kata sandi belum sesuai.";
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk | Sistem Informasi Kost</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="login-page"><main class="login-card">
  <div class="brand-mark">⌂</div>
  <h1>SISTEM INFORMASI KOST</h1><p class="muted center">Kelola penyewa dan pembayaran kost dengan mudah</p>
  <h2>Masuk Pengguna</h2>
  <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <label>Nama pengguna</label><input name="username" autocomplete="username" placeholder="Masukkan nama pengguna" required>
    <label>Kata sandi</label><input type="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
    <button class="btn full" type="submit">MASUK</button>
  </form>
  <p class="login-note">Akun demo: <b>admin</b> · Kata sandi: <b>admin123</b></p>
</main></body></html>