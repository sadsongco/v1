<?php

include("includes/p_2.php");
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader('../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('../templates/partials')
));

$params = [];

foreach ($_POST["items"] AS $item) {
    $item_arr = explode("|", $item);
    $params["items"][] = ["item_id"=>$item_arr[0], "name"=>$item_arr[1], "price"=>$item_arr[2]];
}

echo $m->render("order_item", $params);

?>