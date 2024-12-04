<?php

require_once(__DIR__."/includes/order_includes.php");

$params = [];

foreach ($_POST["items"] AS $item) {
    $item_arr = explode("|", $item);
    $params["items"][] = ["item_id"=>$item_arr[0], "name"=>$item_arr[1], "price"=>$item_arr[2]];
}

echo $m->render("order_item", $params);
