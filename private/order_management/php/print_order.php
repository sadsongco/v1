<?php

require_once(__DIR__."/includes/order_includes.php");
require("includes/make_order_pdf.php");

try {
    $query = "SELECT Orders.order_id, Orders.sumup_id,
                    Customers.name, Customers.address_1, Customers.address_2, Customers.city, Customers.postcode, Customers.country,
                    DATE_FORMAT(Orders.order_date, '%D %M %Y') AS order_date,
                    Orders.shipping, Orders.shipping_method
                FROM Orders
                LEFT JOIN Customers ON Orders.customer_id = Customers.customer_id
                WHERE Orders.order_id = ?
            ;";
        $stmt = $db->prepare($query);
        $stmt->execute([$_GET["order_id"]]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result AS &$row) {
        $sub_query = "SELECT Items.name, FORMAT(Items.price, 2) AS price
                        FROM Order_items
                        LEFT JOIN Items ON Order_items.item_id = Items.item_id
                        WHERE Order_items.order_id = ?;";
        $stmt = $db->prepare($sub_query);
        $stmt->execute([$row["order_id"]]);
        $row["items"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}

catch (PDOException $e) {
    echo $e->getMessage();
}

// p_2($result);

$order = $result[0];

makeOrderPDF($order);

try {
    $query = "UPDATE Orders SET printed = 1 WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET["order_id"]]);
}
catch (PDOException $e) {
    echo $e->getMessage();
}
