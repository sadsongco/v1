<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

define("__HOST__", getHost());

define("MEDIA_PATH", __DIR__. "/../assets/media/");

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

header('Content-Type: audio/mpeg');
header('Content-Disposition: inline;filename='.MEDIA_PATH.$filename.'');
header('Content-length: '.filesize(MEDIA_PATH.$filename));
header('Cache-Control: no-cache');
header("Content-Transfer-Encoding: binary"); 
readfile(MEDIA_PATH.$filename);


require_once("../../../secure/scripts/ut_disconnect.php");

?>