<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

function getTabs($db) {
    try {
        $query = "SELECT * FROM tabs ORDER BY tab_id ASC;";
        return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw new Exception ($e->getMessage());
    }
}

function getArticles($db, $tab_id) {
    $params = [$tab_id];
    try {
        $query = "SELECT article_id FROM articles WHERE tab = ? AND draft = 0 ORDER BY added DESC;";
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
$show_comments = $_GET["show_comments"] == 'true' ? true : false;

try {
    $tabs = getTabs($db);
    foreach ($tabs AS &$tab) {
        $tab['articles'] = getArticles($db, $tab['tab_id']);
        foreach ($tab['articles'] as &$article) {
            $article['tab_id'] = $tab['tab_id'];
            if ($_GET['article_link'] != "null") {
                $article['hide'] = true;
                if ($article['article_id'] == $_GET['article_link']) {
                    $article['hide'] = false;
                    $article['show_comments'] = $_GET['show_comments'];
                }
            }

        }
        if ($_GET['show_tab'] == $tab['tab_id']) {
            $tab['show_tab'] = true;
            if (isset($_GET['article_link'])) $tab['linked_article'] = $_GET['article_link'];
        }
    }
}
catch (Exception $e) {
    die ("System error: ".$e->getMessage());
}

if (!$show_tab) $tabs[0]['show_tab'] = true;


// p_2($tabs);

echo $m->render("tabs", ["tabs"=>$tabs]);

require_once("../../../secure/scripts/ut_disconnect.php");

?>