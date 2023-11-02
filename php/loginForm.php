<?php

include("includes/baseDir.php");

// templating
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates/partials')
));

$host = getHost();
$target = "userModal";
if (isset($_GET['target'])) $target = $_GET['target'];
echo $m->render('userLogin', ["base_dir"=>$host, "target"=>$target]);

?>