<?php
require_once dirname(__DIR__) . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
session_destroy();
header('Location: /login.php');
