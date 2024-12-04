<?php

require_once("secure/db_connect.php");

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

require_once("secure/db_disconnect.php");
header ('HX-Trigger:updateOrderList');

?>