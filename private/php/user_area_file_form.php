<?php

// templating
require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates/partials')
));

echo $m->render("uploadFileForm", ["session_upload_name"=>ini_get("session.upload_progress.name")]);

?>