<?php

include_once(__DIR__."/includes/order_includes.php");
include(__DIR__."/includes/create_rm_order.php");
require(base_path("../secure/env/config.php"));

try {
    $query = "SELECT
        Orders.order_id,
        Orders.sumup_id,
        Orders.shipping_method,
        Orders.shipping,
        Orders.order_date,
        Customers.name,
        Customers.address_1,
        Customers.address_2,
        Customers.city,
        Customers.postcode,
        Customers.country,
        Customers.email
    FROM Orders
    LEFT JOIN Customers ON Orders.customer_id = Customers.customer_id
    WHERE `label_printed` = 0
    ORDER BY Orders.order_date ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage(); 
}

$ship_items = [];

foreach ($orders as $order) {
    $order['items'] = getOrderItems($order, $db);
    $order['weight'] = 0;
    foreach ($order['items'] as $item) {
        $order['weight'] += $item['weight'] * 1000; // item weight in grams
    }
    $ship_items[] = createRMOrder($order);
}


// $rm_order = file_get_contents(base_path("../rm_example.json"));
// $rm_order = json_decode($rm_order);
$data = [
    "items"=>[
        ...$ship_items
    ]
];
    
p_2($data);
exit();
// $post_data = json_decode($rm_order);

file_put_contents(base_path("../payload.json"), json_encode($data));
exit();

$path = RM_BASE_URL."/orders";
// $path = RM_BASE_URL."/version";
$headers = [
    "Authorization: " . RM_API_KEY,
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $path);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

p_2(json_decode($response));

function getOrderItems($order, $db) {
    try {
        $query = "SELECT
            Order_items.order_price,
            Order_items.amount,
            Items.*
        FROM Order_items
        JOIN Items ON Order_items.item_id = Items.item_id
        WHERE Order_items.order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order["order_id"]]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}