<?php
include("includes/baseDir.php");

// database
require_once("../../secure/scripts/ut_a_connect.php");

include("includes/p_2.php");

// auth
require __DIR__ . '/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

// templating
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates/partials')
));

$host = getHost();

$target = "userModal";
if (isset($_GET['target'])) $target = $_GET['target'];

echo $m->render("requestPWReset", ["base_dir"=>$host, "target"=>$target]);

?>