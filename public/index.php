<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = trim(parse_url($uri, PHP_URL_PATH), '/');

// Include the router
require_once __DIR__ . '/../routes/router.php';
