<?php

include_once 'includes/order_includes.php';

try {
    $query = "SELECT item_id, customs_description, customs_code, name FROM Items";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}   
echo $m->render('item_form', ['items'=>$items]);