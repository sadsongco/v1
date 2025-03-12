<?php
require_once(__DIR__."/includes/order_includes.php");

$submit = $_POST['submit'];
$query_items = [];
$params = [];

foreach ($_POST AS $key=>$value) {
    if($key == "submit" || $key == "item_id") continue;
    $query_items[] = "$key = ?";
    if ($key == "price" || $key == "weight" || $key == "packaging_weight") $value = floatval($value);
    $params[] = $value;
}

if ($submit == "cancel") {
    exit("<script>
        document.getElementById('item_" . $_POST['item_id'] ."').scrollIntoView({behaviour: 'smooth', block: 'start'});
    </script>");
}

try {
    $query = "UPDATE Items
    SET " . implode(", ", $query_items) . "
    WHERE item_id = ?";
    $params[] = $_POST['item_id'];
    $stmt = $db->prepare($query);
    $stmt->execute($params);

} catch(Exception $e) {
    echo "couldn't update item: ".$e->getMessage();
    exit();
}

echo "<p>Item " . $_POST['item_id'] . " Updated</p>";