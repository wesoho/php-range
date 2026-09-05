<?php
require_once dirname(__DIR__) . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_POST['level']) && in_array($_POST['level'], ['low','medium','high','impossible'], true)) {
    $_SESSION['level'] = $_POST['level'];
}
if (isset($_POST['hint']) && in_array($_POST['hint'], ['none','hint','full'], true)) {
    $_SESSION['hint'] = $_POST['hint'];
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/index.php'));
