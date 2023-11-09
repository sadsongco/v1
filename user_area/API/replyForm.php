<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

// define("RELATIVE_ROOT", "/../../../");

$params = [
    "username"=>$auth->getUsername(),
    "article_id"=>$_POST['article_id'],
    "tab_id"=>$_POST['tab_id'],
    "comment_reply_id"=>$_POST["comment_id"]
];

echo $m->render("commentFormSolo", $params);

require_once("../../../secure/scripts/ut_disconnect.php");

?>