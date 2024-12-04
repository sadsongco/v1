<?php

require_once(__DIR__."/includes/order_includes.php");

try {
    $query = "DELETE FROM Order_items WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['order_id']]);
    $query = "DELETE FROM Orders WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['order_id']]);
}
catch (PDOException $e) {
    echo $e->getMessage();
}

header ('HX-Trigger:updateOrderList');
