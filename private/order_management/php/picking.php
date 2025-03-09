<?php

include_once(__DIR__."/includes/order_includes.php");

try {
    $query = "SELECT Items.name, SUM(Order_items.amount) AS amount
    FROM Order_items
        JOIN Items ON Order_items.item_id = Items.item_id
        JOIN Orders ON Order_items.order_id = Orders.order_id
        AND Orders.dispatched IS NULL
    GROUP BY Order_items.item_id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    echo $e->getMessage();
}

echo $m->render("picking", ["items"=>$result]);