<?php

// database
require_once("../../../secure/scripts/ut_a_connect.php");

// utilities
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

function getArticles($db) {
    $query = "SELECT article_id FROM articles ORDER BY added DESC;";
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

if (!$auth->isLoggedIn()) {
    header('Location: '.$host);
    die();
}

$articles = getArticles($db);

foreach ($articles AS $article) {
    echo $m->render("articleLazy", ["article_id"=>$article["article_id"]]);
}

require_once("../../../secure/scripts/ut_disconnect.php");

?>