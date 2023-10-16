<?php

// database
require_once("../../../secure/scripts/ut_a_connect.php");

define("IMAGE_UPLOAD_PATH", "./assets/images/");

include(__DIR__."/../../php/includes/p_2.php");

// auth
require __DIR__ . '/../../php/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

// templating
require '../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../templates/partials')
));

// getarticlemedia includes
include(__DIR__."/includes/getArticleMedia.php");
include(__DIR__."/includes/getHost.php");



if (!$auth->isLoggedIn()) {
    header('Location: '.$host);
    die();
}

$host = getHost();
$articles = getArticles($db);

foreach ($articles as &$article) {
    try {
        $article["body"] = getMedia($article["body"], $db, $auth, $m, $host);
    }
    catch (Exception $e){
        
    }
}

echo $m->render('userArea', ["base_dir"=>$host, "articles"=>$articles]);

require_once("../../../secure/scripts/ut_disconnect.php");

?>