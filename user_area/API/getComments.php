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

function getReplies ($db, $article_id, $comment_id=null) {
    try {
        $no_reply_comments = "AND top_comment.reply IS NULL";
        $params = [$_GET["article_id"], $_GET["article_id"]];
        if ($comment_id) {
            $no_reply_comments = "AND top_comment.reply = ? ";
            $params[] = $comment_id;
        }
        $query = "SELECT
            top_comment.comment_id,
            DATE_FORMAT(top_comment.comment_date, '%H:%i %e/%m/%y') AS comment_date,
            top_comment.comment,
            users.username,
            (
                SELECT COUNT(*)
                FROM comments AS reply_comments
                WHERE reply_comments.article_id = ?
                AND reply_comments.reply = top_comment.comment_id
            ) AS no_replies
        FROM comments AS top_comment
        LEFT JOIN users ON users.id = top_comment.user_id
        WHERE top_comment.article_id = ?
        $no_reply_comments
        AND top_comment.reply_to IS NULL
        ORDER BY comment_date ASC;
        ";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as &$comment_field) {
            $comment_field["article_id"] = $article_id;
            if ($comment_field["no_replies"] > 0) {
                $comment_field["replies"] = getReplies($db, $article_id, $comment_field["comment_id"]);
            }
            else {
                $comment_field["replies"] = null;
            }
        }
        return ($result);
    }
    catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

$output = [];
try {
    $output = ["comments"=>getReplies($db, $_GET['article_id'])];
}
catch (Exception $e) {
    $output = ["success"=>false, "message"=>"Couldn't retrieve comments: ".$e->getMessage()];
}

// p_2($output);

echo $m->render("comment", $output);

require_once("../../../secure/scripts/ut_disconnect.php");

?>