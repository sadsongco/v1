<?php

require("../../../../secure/env/config.php");
include(__DIR__ . "/includes/order_includes.php");

try {
    $query = "UPDATE Order_items SET order_id = ? WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST["order_id"], $_POST["order_to_merge"]]);
    $query = "DELETE FROM Orders WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST["order_to_merge"]]);
}
catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

header ('HX-Trigger:updateOrderList');