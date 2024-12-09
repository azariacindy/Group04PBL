<?php
$use_driver = 'sqlsrv'; // mysql atau sqlsrv 

$host     = 'LAPTOP-PRS935BJ';
$username = ''; //'sa'; 
$password = '';
$database = 'pbl';
$db;

if ($use_driver == 'mysql') {
    try {
        $db = new mysqli('localhost', $username, $password, $database);
        if ($db->connect_error) {
            die('Connection DB failed: ' . $db->connect_error);
        }
    } catch (Exception $e) {
        die($e->getMessage());
    }
} else if ($use_driver == 'sqlsrv') {
    $credential = [
        'Database' => $database,
        'UID' => $username,
        'PWD' => $password
    ];

    try {
        $db = sqlsrv_connect($host, $credential);

        if (!$db) {
            $msg = sqlsrv_errors();
            die($msg[0]['message']);
        }
    } catch (Exception $e) {
        die($e->getMessage());
    }

    // function getTingkat(){
    //     global $db, $use_driver;
    //     $query = "SELECT * FROM tingkat ORDER BY nama_tingkat ASC";
    //     $kategori = [];
    
    //     if ($use_driver == 'mysql') {
    //         $result = $db->query($query);
    //         while ($row = $result->fetch_assoc()) {
    //             $kategori[] = $row;
    //         }
    //     } else if ($use_driver == 'sqlsrv') {
    //         $stmt = sqlsrv_query($db, $query);
    //         if ($stmt === false) {
    //             $errors = sqlsrv_errors();
    //             die("SQL Server Query Error: " . $errors[0]['message']);
    //         }
    //         while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    //             $kategori[] = $row;
    //         }
    //     }
    
    //     return $kategori;
    // }
}


