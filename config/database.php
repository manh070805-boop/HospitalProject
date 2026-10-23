<?php
$host = 'localhost\SQLEXPRESS01';
$dbname = 'HospitalDatabase';
$username = 'sa';
$password = '123123';

$dsnCandidates = [
    "sqlsrv:Server=$host;Database=$dbname;Encrypt=0;TrustServerCertificate=1",
    "sqlsrv:Server=$host;Database=$dbname",
];

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
];

$lastException = null;

foreach ($dsnCandidates as $dsn) {
    try {
        $conn = new PDO($dsn, $username, $password, $options);
        return $conn;
    } catch (PDOException $e) {
        $lastException = $e;
    }
}

die("Lỗi kết nối cơ sở dữ liệu: " . ($lastException ? $lastException->getMessage() : 'Không xác định'));
?>