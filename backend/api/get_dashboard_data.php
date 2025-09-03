<?php
// backend/api/get_dashboard_data.php

require_once '../db_config.php';

$response = [];

// 獲取機台狀態
$machines_result = $conn->query("SELECT * FROM machines");
$machines = [];
while ($row = $machines_result->fetch_assoc()) {
    $machines[] = $row;
}
$response['machines'] = $machines;

// 獲取進行中與待處理的工單
$orders_result = $conn->query("SELECT * FROM work_orders WHERE status IN ('in_progress', 'pending') ORDER BY created_at DESC");
$work_orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $work_orders[] = $row;
}
$response['work_orders'] = $work_orders;

// 計算整體良率 (此處為模擬數據)
$response['yield_rate'] = 98.5;

// 計算稼動率 (OEE - 此處為模擬數據)
$running_machines = $conn->query("SELECT COUNT(*) as count FROM machines WHERE status = 'running'")->fetch_assoc()['count'];
$total_machines = $conn->query("SELECT COUNT(*) as count FROM machines")->fetch_assoc()['count'];
$response['oee'] = ($total_machines > 0) ? round(($running_machines / $total_machines) * 100, 1) : 0;

echo json_encode($response);

$conn->close();