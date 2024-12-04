<?php

require_once(__DIR__."/includes/order_includes.php");

function getItems($db) {
    try {
        $query = "SELECT item_id, name, price FROM Items";
        return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw $e;
    }
}

function getCustomers($db) {
    try {
        $query = "SELECT customer_id, name, address_1, country FROM Customers ORDER BY customer_id DESC";
        return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw $e;
    }
}

$params = [];

try {
    $params["items"] = getItems($db);
    $params["customers"] = getCustomers($db);
}

catch (PDOException $e) {
    echo $e->getMessage();
}

echo $m->render("newOrderForm", $params);
