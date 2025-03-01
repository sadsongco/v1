<?php

require_once(__DIR__."/includes/order_includes.php");
include("includes/make_label_pdf.php");

$cond = "WHERE Orders.label_printed = false\n";
$params = [];
if (isset($_POST["order_id"]) AND $_POST["order_id"] != null) {
    $cond = "WHERE Orders.sumup_id = ?";
    $params = [$_POST["order_id"]];
}

$start_label = 1;
if (isset($_POST["start_label"])) $start_label = $_POST["start_label"];

try {
    $query = "SELECT Orders.order_id, Orders.sumup_id, Orders.shipping_method, FORMAT(Orders.shipping, 2) AS shipping,
    Customers.name, Customers.address_1, Customers.address_2, Customers.city, Customers.postcode, Customers.country
    FROM Orders
    LEFT JOIN Customers ON Orders.customer_id = Customers.customer_id
    $cond
    ;";
    // p_2($query);
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo $e->getMessage();
}

MakeLabelPDF($result, $start_label);

foreach ($result as $row) {
    try {
        $query = "UPDATE Orders SET label_printed=true WHERE order_id = ?";
        $stmt = $db->prepare($query);
        // $stmt->execute([$row["order_id"]]);
    }
    catch (PDOException $e) {
        echo $e->getMessage();
    }
}
