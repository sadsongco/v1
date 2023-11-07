<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

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

$host = getHost();

if (!$auth->isLoggedIn()) {
    exit($m->render("userAreaLogin", ["base_dir"=>$host]));
}

$articles = getArticles($db, $_GET['article_link']);
$show_comments = $_GET["show_comments"] == 'true' ? true : false;

foreach ($articles AS $article) {
    echo $m->render("articleLazy", ["article_id"=>$article["article_id"], "show_comments"=>$show_comments]);
}

require_once("../../../secure/scripts/ut_disconnect.php");

?>