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
    $stmt->execute([$_POST['article_id']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    die("DATABASE ERROR: ".$e->getMessage());
}

// p_2($_POST);

$article = $result[0];
$article["body"] = getMedia($article["body"], $db, $auth, $m, $host);
$article["username"] = $auth->getUsername();
$article["tab_id"] = $_POST["tab_id"];
$article["show_comments"] = $_POST['show_comments'] == 'true' ? true : false;
$article["host"] = $host;
if (isset($_POST['hide'])) $article["hide"] = $_POST['hide'];

echo $m->render("article", $article);

require_once("../../../secure/scripts/ut_disconnect.php");

?>