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

$params = [
    "user_id"=>$auth->getUserId(),
    "article_id"=>$_POST['article_id'],
    "reply"=>null,
    "reply_to"=>null,
    "notify"=>0,
    "comment"=>$_POST['comment']
];

try {
    $query = "INSERT INTO comments VALUES (0, :user_id, :article_id, NOW(), :reply, :reply_to, 0, :notify, 0, :comment);";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
}
catch (Exception $e) {
    die($e->getMessage());
}

header ('HX-Trigger:refreshComments');
echo $m->render("commentFormSolo", ["article_id"=>$params['article_id']]);
?>