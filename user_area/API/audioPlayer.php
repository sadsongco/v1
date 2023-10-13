<?php

include (__DIR__."/../../php/includes/p_2.php");
// templating
require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../templates/partials')
));

$track = $_POST;
$track["title"] = str_replace("_", " ", $track["title"]);
$track["notes"] = str_replace("_", " ", nl2br($track["notes"]));

echo $m->render("audioTrack", $track);

?>