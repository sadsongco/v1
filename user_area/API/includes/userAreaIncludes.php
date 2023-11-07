<?php

// database
require_once(__DIR__."/../../../../secure/scripts/ut_a_connect.php");

// utilities
include_once(__DIR__."/../../../php/includes/p_2.php");
include_once(__DIR__."/getHost.php");

// auth
require_once(__DIR__ . '/../../../php/vendor/autoload.php');
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

// templating
require_once(__DIR__.'/../../../lib/mustache.php-main/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../../templates/partials')
));

?>