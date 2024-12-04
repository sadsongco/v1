<?php

include_once("includes/p_2.php");
require_once("secure/db_connect.php");

if (!isset($_POST["orderItems"])) {
    echo "Please add items to order before submitting";
    exit;
}

try {
    $query = "INSERT INTO Orders VALUES (0, ?, ?, FALSE, FALSE, ?, FALSE);";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST["sumup_id"], $_POST["customer_id"], $_POST["order_date"]]);
    $order_id = $db->lastInsertId();
    foreach ($_POST["orderItems"] AS $item_id) {
        $query = "INSERT INTO Order_items VALUES (0, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id, $item_id]);
    }
}
catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        exit("<p class='error'>Duplicate SumUp Order ID - check it's correct</p>");
    }
    echo $e->getMessage();
}

header ('HX-Trigger:updateOrderList');
header ('HX-Trigger-After-Settle:updateOrderForm');

require_once("secure/db_disconnect.php");

?>