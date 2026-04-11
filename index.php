<?php
session_start();

require_once "Routing.php";

// Get current URL path without query parameters
$path = trim($_SERVER["REQUEST_URI"], '/');
$path = parse_url($path, PHP_URL_PATH);

// Pass cleaned path to router
Routing::run($path);