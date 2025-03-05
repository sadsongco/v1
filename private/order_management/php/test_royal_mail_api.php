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
    ORDER BY Orders.order_date DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $order = $result[0];
} catch (PDOException $e) {
    echo $e->getMessage(); 
}

$rm_order = createRMOrder($order);
// $rm_order = file_get_contents(base_path("../rm_example.json"));
// $rm_order = json_decode($rm_order);
$data = [
    "items"=>[
        $rm_order
    ]
];

// $post_data = json_decode($rm_order);

file_put_contents(base_path("../payload.json"), json_encode($data));
p_2($data);
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