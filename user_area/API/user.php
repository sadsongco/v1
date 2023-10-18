<?php

// database
require_once("../../../secure/scripts/ut_a_connect.php");

// templating
require '../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../../php/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../../php/templates/partials')
));

// auth
require __DIR__ . '/../../php/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}


echo $m->render('userLoggedIn', ["username"=>$auth->getUsername(), "user_area"=>true]);

require_once("../../../secure/scripts/ut_disconnect.php");

?>