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

function getCommentNotify($db, $reply) {
    try {
        $query = "SELECT notify, user_id FROM comments WHERE comment_id = ?;";
        $stmt = $db->prepare($query);
        $stmt->execute([$reply]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result[0];
    }
    catch (Exception $e) {
        return 0;
    }
}

function sendNotification($db, $user_id) {
    try {
        $query = "SELECT email FROM users WHERE id = ?;";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $email = $result[0]['email'];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return;
    }
    echo "GOING TO SEND AN EMAIL TO $email";
}

$reply = null;
$notify = 0;
p_2($_POST);

if (isset($_POST['notify'])) $notify = true;

if (isset($_POST['comment_reply_id']) && intval($_POST['comment_reply_id']) != 0) {
    $reply = $_POST['comment_reply_id'];
    $emailNotification = getCommentNotify($db, $reply);
    if ($notify['notify'] == 1) sendNotification($db, $notify['user_id']);
}


$params = [
    "user_id"=>$auth->getUserId(),
    "article_id"=>$_POST['article_id'],
    "reply"=>$reply,
    "reply_to"=>null,
    "notify"=>$notify,
    "comment"=>$_POST['comment']
];

try {
    $query = "INSERT INTO comments VALUES (0, :user_id, :article_id, NOW(), :reply, :reply_to, 0, :notify, 0, :comment);";
    p_2($query);
    p_2($params);
    // exit();
    $stmt = $db->prepare($query);
    $stmt->execute($params);
}
catch (Exception $e) {
    echo "Error inserting comment:";
    die($e->getMessage());
}

header ('HX-Trigger:refreshComments');
echo $m->render("commentFormSolo", ["article_id"=>$params['article_id']]);
?>