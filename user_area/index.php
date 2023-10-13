<?php

// database
require_once("../../secure/scripts/ut_a_connect.php");

include(__DIR__."/../php/includes/p_2.php");

// auth
require __DIR__ . '/../php/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

// templating
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates/partials')
));


function makeUniqueToken($auth, $track) {
    return hash('xxh64', $auth->getUserId().$track["filename"]);
}

function getMediaArr($table, $ids, $db) {
    $id_id = $table == "media" ? "media_id" : "image_id";
    $query = "SELECT $id_id, filename, title, notes FROM $table WHERE $id_id = ?";
    for ($x = 1; $x < sizeof($ids); $x++) {
        $query .= " OR $id_id = ?";
    }
    $query .= ";";
    $stmt = $db->prepare($query);
    $stmt->execute($ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMedia($content, $db, $auth, $m, $host) {
    preg_match_all('/{{a::([0-9])+}}/', $content, $audio_ids);
    $audio_arr = getMediaArr("media", $audio_ids[1], $db);
    foreach ($audio_arr AS $track) {
        $replace_str = "{{a::".$track["media_id"]."}}";
        $track["token"] = makeUniqueToken($auth, $track);
        try {
            $query = "INSERT IGNORE INTO streaming_tokens VALUES (0, ?, ?, ?);";
            $stmt = $db->prepare($query);
            $stmt->execute([$track["token"], $track["media_id"], time()]);
        }
        catch (PDOException $e) {
            if ($e->getCode() == 23000) throw new Exception("streaming token already exists");
            die($e->getMessage());
        }
        $track["title"] = str_replace(" ", "_", $track["title"]);
        $track["notes"] = str_replace(" ", "_", $track["notes"]);
        $replace_el = $m->render("audioLoader", ["track"=>json_encode($track), "base_dir"=>$host]);
        $content = preg_replace('/{{a::3}}/', $replace_el, $content);
    }
    preg_match_all('/{{i::([0-9])+}}/', $content, $image_ids);
    return $content;
}

function getArticles($db) {
    $query = "SELECT title, body, DATE_FORMAT(added, '%D %b %Y') AS added FROM articles ORDER BY added DESC;";
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}

if (!$auth->isLoggedIn()) {
    header('Location: '.$host);
    die();
}

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $protocol .= 's';
$host = "$protocol://".$_SERVER['HTTP_HOST'];

$articles = getArticles($db);

foreach ($articles as &$article) {
    try {
        $article["body"] = getMedia($article["body"], $db, $auth, $m, $host);
    }
    catch (Exception $e){

    }
}

echo $m->render('userArea', ["base_dir"=>$host, "articles"=>$articles]);

require_once("../../secure/scripts/ut_disconnect.php");

?>