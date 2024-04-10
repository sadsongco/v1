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
    $query = "SELECT
        article_id,
        title,
        body,
        added,
        tab,
        posted_by,
        tab_name
        FROM articles
        LEFT JOIN tabs ON tab = tabs.tab_id
        WHERE article_id = ?;";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['article_id']]); // VALIDATE?
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (Exception $e) {
    die("DATABASE ERROR: ".$e->getMessage());
}


$article = $result[0];
$article["body"] = getMedia($article["body"], $db, $auth, $m, $host);
$article["username"] = $auth->getUsername();
$article["tab_id"] = $_POST["tab_id"]; //VALIDATE POST below?
$article["show_comments"] = $_POST['show_comments'] == 'true' ? true : false;
$article["host"] = $host;
if (isset($_POST['hide'])) $article["hide"] = $_POST['hide'];
if ($article["tab_name"] == "blogs") $article["blog"] = true;

echo $m->render("article", $article);

require_once("../../../secure/scripts/ut_disconnect.php");

?>