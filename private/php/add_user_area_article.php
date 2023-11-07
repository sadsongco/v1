<?php

include(__DIR__."/../../php/includes/p_2.php");
require_once(__DIR__."/../../../secure/scripts/ut_a_connect.php");

// exit(p_2($_POST));

try {
    $query = "INSERT INTO articles VALUES (0, ?, ?, NOW(), ?);";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['title'], $_POST['body'], $_POST['articleTab']]);
}
catch (PDOException $e) {
    die ($e->getMessage());
}

echo "Article updated in database";

require_once(__DIR__."/../../../secure/scripts/ut_disconnect.php");

?>