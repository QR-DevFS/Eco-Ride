<?php
if (file_exists(__DIR__ . '/admin.env')) {
    $lines = file(__DIR__ . '/admin.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$admin_credentials = [
    'pseudo' => getenv('ADMIN_PSEUDO'),
    'hash' => trim(getenv('ADMIN_PASSWORD_HASH'))
];

$employe_credentials = [
    'pseudo' => getenv('EMPLOYE_PSEUDO'),
    'hash' => trim(getenv('EMPLOYE_PASSWORD_HASH'))
];