<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
?>