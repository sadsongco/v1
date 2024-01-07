<?php

require_once(__DIR__."/includes/privateIncludes.php");
require_once(__DIR__."/../../../secure/env/ut_reserved_usernames.php");

$username_options = [];

function isUsernameRegistered($username, $db) {
    $query = "SELECT * FROM users WHERE username = ?;";
    $stmt = $db->prepare($query);
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        return true;
    }
    return false;
}

foreach ($reserved_usernames as $reserved_username) {
    if (isUsernameRegistered($reserved_username, $db)) continue;
    $username_options[] = ["username"=>$reserved_username];
}

echo $m->render('registerMember', ["username_options"=>$username_options]);

?>

