<?php

// templating
require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates/partials')
));

include(__DIR__."/includes/returnBytes.php");

define("MAX_FILE_SIZE", return_bytes(ini_get("upload_max_filesize")));

// $id = $_GET['id'];

$id = $_GET["fileId"];

echo $m->render("file_upload", ["id"=>$id, "max_size"=>MAX_FILE_SIZE]);

?>