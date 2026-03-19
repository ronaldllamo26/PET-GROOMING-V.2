<?php
// config/database.php

define('DB_HOST',    'localhost');
define('DB_NAME',    'pet_grooming');   // <-- palitan kung iba ang pangalan mo
define('DB_USER',    'root');           // <-- palitan ng iyong DB username
define('DB_PASS',    '');              // <-- palitan ng iyong DB password
define('DB_CHARSET', 'utf8mb4');

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
