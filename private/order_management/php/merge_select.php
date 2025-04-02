<?php


require("../../../../secure/env/config.php");
include(__DIR__ . "/includes/order_includes.php");

try {
    $query = "SELECT order_id FROM Orders WHERE printed = 0 AND label_printed = 0 AND dispatched IS NULL AND order_id != ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['order_id']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

if (sizeof($result) == 0) {
    echo "No orders to merge";
    exit();
}

echo $m->render("mergeSelect", ["current_order_id"=>$_GET['order_id'], "orders"=>$result]);
