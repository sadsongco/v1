<?php

require_once("secure/db_connect.php");
include_once("includes/p_2.php");

try {
    $query = "INSERT INTO Items VALUES (0, :name, :price);";
    $stmt = $db->prepare($query);
    $stmt->execute($_POST);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

catch (PDOException $e) {
    echo $e->getMessage();
}

header ('HX-Trigger:updateOrderForm');
echo "New Item Added";

require_once("secure/db_disconnect.php");

?>