<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

define("IMAGE_UPLOAD_PATH", "/user_area/assets/images/");

// getarticlemedia includes
define("RELATIVE_ROOT", "/../../../");
include(__DIR__."/includes/getArticleMedia.php");

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
$article["username"] = $auth->getUsername();
$article["show_comments"] = $_GET['show_comments'] == 'true' ? true : false;

echo $m->render("article", $article);

require_once("../../../secure/scripts/ut_disconnect.php");

?>