<?php

// database
require_once("../../../secure/scripts/ut_a_connect.php");

define("RELATIVE_ROOT", "/../../../");
define("IMAGE_UPLOAD_PATH", "/user_area/assets/images/");

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

try {
    $query = "SELECT * FROM articles WHERE article_id = ?;";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['article_id']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    die("DATABASE ERROR: ".$e->getMessage());
}
$article = $result[0];
$article["body"] = getMedia($article["body"], $db, $auth, $m, $host);

echo $m->render("article", $article);

require_once("../../../secure/scripts/ut_disconnect.php");

?>