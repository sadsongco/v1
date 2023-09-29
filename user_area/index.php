<?php

// database
require_once("../../secure/scripts/ut_a_connect.php");

// auth
require __DIR__ . '/../php/vendor/autoload.php';
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
    'loader' => new Mustache_Loader_FilesystemLoader('templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('templates/partials')
));

$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
$host = "$protocol://".$_SERVER['HTTP_HOST'];

if ($auth->isLoggedIn()) {
    echo $m->render('userArea', ["base_dir"=>$host]);
}
else {
    header('Location: '.$host);
    die();
}

require_once("../../secure/scripts/ut_disconnect.php");

?>