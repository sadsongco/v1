<?php

include(__DIR__."/../../php/includes/p_2.php");
require_once(__DIR__."/../../../secure/scripts/ut_a_connect.php");

try {
    $query = "INSERT INTO articles VALUES (0, ?, ?);";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['title'], $_POST['body']]);
}
catch (PDOException $e) {
    die ($e->getMessage());
}

echo "Article updated in database";

require_once(__DIR__."/../../../secure/scripts/ut_disconnect.php");

?>