<?php
$username = 'root';
$password = '';
$database = 'pbl_presma';

try{
$db = new mysqli('localhost', $username, $password, $database); if($db->connect_error){
die('Connection DB failed: ' . $db->connect_error);
}
}catch(Exception $e){ die($e->getMessage());
}
?>
