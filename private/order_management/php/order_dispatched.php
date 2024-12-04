<?php

require_once("secure/db_connect.php");

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

require_once("secure/db_disconnect.php");
header ('HX-Trigger:updateOrderList');

?>