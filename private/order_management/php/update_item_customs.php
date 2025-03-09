<?php

include_once 'includes/order_includes.php';


foreach ($_POST['item_id'] AS $key=>$item_id) {
    try {
        $query = "UPDATE Items SET `customs_description` = ?, `customs_code` = ? WHERE item_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_POST['customs_description'][$key] ?? NULL, $_POST['customs_code'][$key] ?? NULL, $item_id]);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
    
echo "Items Updated";