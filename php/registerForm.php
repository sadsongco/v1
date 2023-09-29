<?php

require_once("../../secure/scripts/ut_a_connect.php");

require __DIR__ . '/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}

catch (Exception $e) {
    die($e->getMessage());
}

require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader('templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('templates/partials')
));

if ($auth->isLoggedIn()) {
    echo $m->render('userLoggedIn', ["username"=>$auth->getUsername()]);
}

else {
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
    $host = "$protocol://".$_SERVER['HTTP_HOST'];

    echo $m->render('userRegister', ["base_dir"=>$host]);
}


require_once("../../secure/scripts/ut_m_connect.php");

?>