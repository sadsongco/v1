<?php

require_once(__DIR__."/includes/order_includes.php");

extract($_GET);

try {
    $query = "SELECT
    *
    FROM Items
    WHERE item_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$item_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    header ('HX-Trigger:editOrder');
    echo "Couldn't get item details from database: " .$e->getMessage();
    exit();
}

$params = [];

foreach ($order as $key => $value) {
    $type = "text";
    switch (gettype($value)) {
        case "string":
            $type = "text";
            break;
        case "int":
            $type = "number";
            break;
    }
    $label = ucwords(str_replace("_", " ", $key));
    $params[] = ["key"=>$key, "value"=>$value, "type"=>$type, "label"=>$label];
}


echo $m->render("editItem", ["item_id"=>$item_id, "items"=>$params]);
