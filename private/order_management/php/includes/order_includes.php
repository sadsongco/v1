<?php

require_once(__DIR__."/../../../../../secure/scripts/ut_o_connect.php");

include_once("p_2.php");

require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader('../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('../templates/partials')
));

function base_path($path) {
    return __DIR__.'/../../../../'.$path;
}