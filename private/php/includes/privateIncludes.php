<?php

require_once(__DIR__."/../../../../secure/scripts/ut_a_connect.php");

include(__DIR__."/../../../php/includes/p_2.php");
include(__DIR__."/../includes/returnBytes.php");

// templating
require __DIR__.'/../../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../templates/partials')
));

?>