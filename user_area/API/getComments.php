<?php
// database
require_once("../../../secure/scripts/ut_a_connect.php");

define("RELATIVE_ROOT", "/../../../");

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

$output = ["comments"=>[
    ["comment"=>"Comment 1 hi hi"],
    ["comment"=>"Comment 2 yo yo"]
]];

$output = [];
try {
    $query = "SELECT comment_id, DATE_FORMAT(comment_date, '%H:%i %e/%m/%y') AS comment_date, comment,
        username
        FROM comments
        LEFT JOIN users ON users.id = comments.user_id
        WHERE article_id = ?
        AND reply IS NULL
        AND reply_to IS NULL
        ORDER BY comment_date ASC
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET["article_id"]]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output = ["comments"=>$result];
}
catch (Exception $e) {
    $output = ["success"=>false, "message"=>"Couldn't retrieve comments: ".$e->getMessage()];
}

echo $m->render("comment", $output);

require_once("../../../secure/scripts/ut_disconnect.php");

?>