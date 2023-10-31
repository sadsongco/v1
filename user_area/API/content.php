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

function getArticles($db, $article_link=false) {
    $params = [];
    $article_cond = "";
    if ($article_link != 'null') {
        $article_cond = " WHERE article_id = ? ";
        $params = [$article_link];
    }
    $query = "SELECT article_id FROM articles $article_cond ORDER BY added DESC;";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!$auth->isLoggedIn()) {
    header('Location: '.$host);
    die();
}

$articles = getArticles($db, $_GET['article_link']);
$show_comments = $_GET["show_comments"] == 'true' ? true : false;

foreach ($articles AS $article) {
    echo $m->render("articleLazy", ["article_id"=>$article["article_id"], "show_comments"=>$show_comments]);
}

require_once("../../../secure/scripts/ut_disconnect.php");

?>