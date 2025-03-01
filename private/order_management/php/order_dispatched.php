<?php

require_once(__DIR__."/includes/order_includes.php");
try {
    $query = "UPDATE Orders SET dispatched = NOW() WHERE order_id = ?";
    if (isset($_GET["undo"]) && $_GET["undo"] == true) $query = "UPDATE Orders SET dispatched = NULL WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['order_id']]);
}
catch (PDOException $e) {
    echo $e->getMessage();
}

header ('HX-Trigger:updateOrderList');
