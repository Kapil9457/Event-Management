<?php
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'event_management';
const DB_USER = 'root';
const DB_PASS = '';

const APP_NAME = 'Wedding Photos Event Manager';
const APP_KEY = 'change-this-key-in-production-please-32chars';
const APP_URL = 'http://localhost';

const UPLOAD_DIR = __DIR__ . '/uploads';
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
];

session_name('event_manager_session');
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
