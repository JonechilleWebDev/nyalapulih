<?php

// Izinkan akses dari Flutter Web selama development.
header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept");

header("Content-Type: application/json; charset=UTF-8");

// Tangani preflight request dari browser.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
