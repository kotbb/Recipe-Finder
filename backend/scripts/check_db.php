<?php
/** Quick DB connectivity check: run `php backend/scripts/check_db.php` from project root. */
$root = dirname(__DIR__, 2);
$conn = require $root . '/backend/config/db.php';
if (!($conn instanceof mysqli)) {
    fwrite(STDERR, "FAIL: db.php did not return mysqli\n");
    exit(1);
}
if ($conn->connect_error) {
    fwrite(STDERR, 'FAIL: ' . $conn->connect_error . "\n");
    exit(1);
}
$r = $conn->query('SELECT 1');
if (!$r) {
    fwrite(STDERR, 'FAIL query: ' . $conn->error . "\n");
    exit(1);
}
echo "OK: connected to MySQL, database recipe_db is reachable.\n";
exit(0);
