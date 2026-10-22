<?php
/**
 * Database Connection Configuration (Procedural MySQLi)
 */

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'antique_store';
$db_port = getenv('DB_PORT') ?: 3306;

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, (int)$db_port);

if (!$conn) {
    // Note: In case connection fails, $conn remains false.
    // Applications can check $conn before performing queries.
    $db_error = mysqli_connect_error();
} else {
    mysqli_set_charset($conn, "utf8mb4");
}

function get_db_connection() {
    global $conn;
    return $conn;
}
