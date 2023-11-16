<?php

require_once(__DIR__."/../../../../secure/scripts/ut_a_connect.php");

include(__DIR__."/../../../php/includes/p_2.php");
include(__DIR__."/../includes/returnBytes.php");

// templating
require __DIR__.'/../../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../templates/partials')
));

function getTabs($db) {
    try {
        $query = "SELECT * FROM tabs";
        return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw new Exception ($e->getMessage());
    }
}

function getPosters($db) {
    try {
        $query = "SELECT column_type FROM information_schema.columns WHERE table_name = 'articles' AND column_name = 'posted_by'";
        $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $result_str = str_replace(array("enum('", "')", "''"), array('', '', "'"), $result[0]["COLUMN_TYPE"]);
        $arr = explode("','", $result_str);
        if (sizeof($arr) == 0) $arr = ["Nigel", "Andy", "Jason", "Admin"];
        $posters = [];
        foreach ($arr as $poster) {
            $posters[] = ["name"=>$poster];
        }
        return $posters;
    }
    catch (PDOException $e) {
        throw new Exception ($e->getMessage());
    }
}

?>