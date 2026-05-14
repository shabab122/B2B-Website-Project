<?php
function getDBConnection() 
{
    static $conn = null;

    if ($conn === null) {
        $db_server   = getenv('DB_SERVER') ?: 'localhost';
        $db_username = getenv('DB_USERNAME') ?: 'root';
        $db_password = getenv('DB_PASSWORD') ?: '';
        $db_name     = getenv('DB_NAME') ?: 'db_trimart';
        $db_port     = getenv('DB_PORT') ?: 3306;

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $conn = new mysqli($db_server, $db_username, $db_password, $db_name, $db_port);
            $conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    return $conn;
}

function closeDBConnection()
{
    global $conn;
    if ($conn instanceof mysqli) {
        $conn->close();
        $conn = null;
    }
}
?>