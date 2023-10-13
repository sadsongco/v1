<?php

include("../../php/includes/p_2.php");

// database
require_once("../../../secure/scripts/ut_a_connect.php");

// auth
require __DIR__ . '/../../php/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

define("MEDIA_PATH", __DIR__."/../assets/media/");

function getHost() {
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $protocol .= 's';
    return "$protocol://".$_SERVER['HTTP_HOST'];
}

function removeExpiredStreamingTokens($db, $token) {
    $query = "DELETE FROM streaming_tokens WHERE timestamp < ?;";
    $stmt = $db->prepare($query);
    $stmt->execute([time()-(60*30)]); // remove timestamps longer than 30 minutes ago
}


// stop direct access if not authorised
$host = getHost();
if (!$auth->isLoggedIn()) die(header('Location: '.$host));

$token = $_GET["track"];

try {
    $query = "SELECT filename FROM streaming_tokens
    JOIN media ON media.media_id = streaming_tokens.media_id
    WHERE token = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['track']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filename = $result[0]["filename"];
}
catch (PDOException $e) {
    echo $e->getMessage();
}

removeExpiredStreamingTokens($db, $_GET["track"]);
echo file_get_contents(MEDIA_PATH.$filename);


require_once("../../../secure/scripts/ut_disconnect.php");

?>