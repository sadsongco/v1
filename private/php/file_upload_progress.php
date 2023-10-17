<?php

include (__DIR__."/../../php/includes/p_2.php");
// templating
require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates/partials')
));
session_start();

$key = ini_get("session.upload_progress.prefix") . "123";
if (!empty($_SESSION[$key])) {
    $current = $_SESSION[$key]["bytes_processed"];
    $total = $_SESSION[$key]["content_length"];
    $progress = $current < $total ? ceil($current / $total * 100) : 100;
    echo $m->render("fileUploadProgress", ["uploadProgress"=>$progress]);
}
else {
}

// if (isset($_SESSION)) echo "UPLOADING FILE...";

?>