<?php

// database
require_once("../../../secure/scripts/ut_a_connect.php");

define("RELATIVE_ROOT", "/../../..");
define("IMAGE_UPLOAD_PATH", "/user_area/assets/images/");

// auth
require __DIR__ . '/../../php/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

// templating
require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../../user_area/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../../user_area/templates/partials')
));

// articlebuilding
include(__DIR__."/../../user_area/API/includes/getArticleMedia.php");
include(__DIR__."/../../user_area/API/includes/getHost.php");

// utitlity
include(__DIR__."/../../php/includes/p_2.php");

$host = getHost();
$article = $_POST;
try {
    $article["body"] = getMedia($article["body"], $db, $auth, $m, $host);
}
catch (Exception $e){
    echo $e->getMessage();
}

echo $m->render('article', $article);

?>