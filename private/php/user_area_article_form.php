<?php

// database
require_once("../../../secure/scripts/ut_a_connect.php");

// templating
require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates/partials')
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

echo $m->render("articleForm", ["default_date"=>date('Y-m-d\TH:i'), "tabs"=>getTabs($db)]);

?>