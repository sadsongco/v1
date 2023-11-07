<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

function getTabs($db) {
    try {
        $query = "SELECT * FROM tabs";
        return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw new Exception ($e->getMessage());
    }
}

function getArticles($db, $tab_id, $article_link=false) {
    $params = [$tab_id];
    $article_cond = "WHERE tab = ?";
    if ($article_link != 'null') {
        $article_cond .= " AND article_id = ? ";
        $params[] = $article_link;
    }
    try {
        $query = "SELECT article_id FROM articles $article_cond ORDER BY added DESC;";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

$host = getHost();

if (!$auth->isLoggedIn()) {
    exit($m->render("userAreaLogin", ["base_dir"=>$host]));
}

$show_tab = $_GET["show_tab"] == "null" ? null : intval($_GET['show_tab']);

try {
    $tabs = getTabs($db);
    foreach ($tabs AS &$tab) {
        $tab['articles'] = getArticles($db, $tab['tab_id'], $_GET['article_link']);
        if ($_GET['show_tab'] == $tab['tab_id']) $tab['show_tab'] = true;
    }
}
catch (Exception $e) {
    die ("System error: ".$e->getMessage());
}

if (!$show_tab) $tabs[0]['show_tab'] = true;

// exit(p_2($tabs));
$show_comments = $_GET["show_comments"] == 'true' ? true : false;

echo $m->render("tabs", ["tabs"=>$tabs, "show_comments"=>$show_comments]);

// foreach ($articles AS $article) {
//     echo $m->render("articleLazy", ["article_id"=>$article["article_id"], "show_comments"=>$show_comments]);
// }

require_once("../../../secure/scripts/ut_disconnect.php");

?>