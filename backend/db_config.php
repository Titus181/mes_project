<?php
// backend/db_config.php

// 允許來自任何來源的跨域請求 (開發用，生產環境應更嚴格)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost"; // 或您的資料庫主機
$username = "root";       // 您的資料庫使用者名稱
$password = "";           // 您的資料庫密碼
$dbname = "mes_db";

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
  die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// 設定編碼為 UTF8
$conn->set_charset("utf8mb4");