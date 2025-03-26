<?php

include_once(__DIR__."/includes/order_includes.php");
require(base_path("../secure/env/config.php"));

$url = $path = RM_BASE_URL."/orders?pageSize=100";
$headers = [
    "Authorization: " . RM_API_KEY,
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $path);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$responseObj = json_decode($response);
foreach ($responseObj->orders as $order) {
    $query = "UPDATE Orders
    SET
    `dispatched` = ?,
    `rm_order_identifier` = ?,
    `rm_created` = ?,
    `rm_tracking_number` = ?
    WHERE `order_id` = ?";
    $stmt = $db->prepare($query);
    $shippedOn = isset($order->shippedOn) ? $order->shippedOn : NULL;
    $trackingNumber = isset($order->trackingNumber) ? $order->trackingNumber : NULL;
    $params = [
        $shippedOn,
        (int)$order->orderIdentifier,
        $order->createdOn,
        $trackingNumber,
        (int)$order->orderReference
    ];
    $stmt->execute($params);
}

header ('HX-Trigger:updateOrderList');
echo "<p>Orders Updated from Royal Mail</p>";