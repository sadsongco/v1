<?php

require_once(__DIR__."/includes/order_includes.php");

$filter = "new";
if (isset($_POST['orderFilter'])) $filter = $_POST['orderFilter'];

$filter_text = "";
switch($filter) {
    case "new":
        $filter_text = " WHERE Orders.printed = 0 AND Orders.label_printed = 0 AND Orders.dispatched IS NULL ";
        break;
    case "printed":
        $filter_text = " WHERE Orders.printed = 1 AND Orders.label_printed = 0 AND Orders.dispatched IS NULL ";
        break;
    case "label printed":
        $filter_text = " WHERE Orders.printed = 1 AND Orders.label_printed = 1 AND Orders.dispatched IS NULL ";
        break;
    case "dispatched":
        $filter_text = " WHERE Orders.printed = 1 AND Orders.label_printed = 1 AND Orders.dispatched IS NOT NULL ";
        break;
    case "all":
        $filter_text = "";
        break;
}


if (isset($_POST['nameFilter'])) $filter_text = "WHERE name LIKE '%".$_POST['nameFilter']."%'";

try {
    $query = "SELECT Orders.order_id, Orders.sumup_id, Orders.dispatched, Orders.printed,
                    Customers.name, Customers.address_1, Customers.address_2, Customers.city, Customers.postcode, Customers.country
                FROM Orders
                JOIN Customers ON Orders.customer_id = Customers.customer_id
                $filter_text
                ORDER BY Orders.sumup_id DESC
            ;";
    $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result AS &$row) {
        $sub_query = "SELECT
                        Items.name,
                        Order_items.amount,
                        FORMAT(Order_items.order_price, 2) AS price,
						FORMAT(Order_items.order_price * Order_items.amount, 2) AS item_total
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

$params["orders"] = $result;

echo $m->render("orderList", $params);
