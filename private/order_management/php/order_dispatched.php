<?php

require_once(__DIR__."/includes/order_includes.php");

$dispatched = 1;
if (isset($_GET["undo"]) && $_GET["undo"] == true) $dispatched = 0;

try {
    $query = "UPDATE Orders SET dispatched = ? WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$dispatched, $_GET['order_id']]);
}
catch (PDOException $e) {
    echo $e->getMessage();
}

header ('HX-Trigger:updateOrderList');
