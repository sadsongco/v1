<?php

require_once(__DIR__."/includes/order_includes.php");

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
