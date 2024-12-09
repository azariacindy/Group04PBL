<?php
include '/laragon/www/PBL/lib/Connection.php'; // Mengimpor koneksi database

function getTingkat() {
    global $db, $use_driver;
    $query = "SELECT * FROM tingkat ORDER BY nama_tingkat ASC";
    $tingkat = [];

    if ($use_driver == 'mysql') {
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $tingkat[] = $row;
        }
    } else if ($use_driver == 'sqlsrv') {
        $stmt = sqlsrv_query($db, $query);
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            die("SQL Server Query Error: " . $errors[0]['message']);
        }
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $tingkat[] = $row;
        }
    }
    return $tingkat;
}

function closeConnection() {
    global $db;
    if ($db) {
        sqlsrv_close($db);
    }
}
?>