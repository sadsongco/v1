<?php

require_once("secure/db_connect.php");
include_once("includes/p_2.php");

try {
    $query = "INSERT INTO Customers VALUES (0, :name, :address_1, :address_2, :city, :postcode, :country, :email);";
    $stmt = $db->prepare($query);
    $stmt->execute($_POST);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

catch (PDOException $e) {
    echo $e->getMessage();
}

header ('HX-Trigger:updateOrderForm');
header ('HX-Trigger-After-Settle:clearCustomerForm');
echo "New Customer Added";

require_once("secure/db_disconnect.php");

?>