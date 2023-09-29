<?php
// database
require_once("../../secure/scripts/ut_a_connect.php");

// auth
require __DIR__ . '/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

try {
    $auth->logOut();
}
catch (\Delight\Auth\NotLoggedInException $e) {
    die ("Not logged in");
}
catch (Exception $e) {
    echo $e->getMessage();
}

$protocol = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $protocol .= 's';
$host = "$protocol://".$_SERVER['HTTP_HOST'];
header ("HX-Redirect:$host");
die();

?>