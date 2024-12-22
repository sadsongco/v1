<?php

require_once("../../secure/scripts/ut_m_connect.php");

if (ob_get_level()) {
    ob_end_clean();
}

include_once(__DIR__."/../email_management/includes/get_host.php");
include_once(__DIR__."/../private/mailout/api/includes/make_unique_token.php");

define("__HOST__", getHost());
define("FILENAME", "RaF-SO.mp3");
define("MEDIA_PATH", __DIR__. "/../private/assets/media/");

// Load Mustache
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/templates/partials')
));

if (!isset($_GET['email']) || !isset($_GET['u_token'])) exit("Invalid request");

try {
    $query = "SELECT email_id FROM ut_mailing_list WHERE email = ?;";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['email']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    exit ("Database error: ".$e->getMessage());
}

if ($stmt->rowCount() == 0) exit("email not found");

$token = $result[0]['email_id'];

$u_token = makeUniqueToken($token, $_GET['email']);
if ($u_token != $_GET['u_token']) exit("Invalid token");

try {
    $query = "SELECT * FROM download_tokens WHERE email_id = ? AND token = ?;";
    $stmt = $db->prepare($query);
    $stmt->execute([$token, $u_token]);
    $stmt->fetch();
    $result = $stmt->rowCount();
}
catch (PDOException $e) {
    error_log($e);
    exit ("Sorry, there was a technical error. Please contact info@unbelievabletruth.co.uk");
}

if ($result == 0) exit("Token already downloaded. Please contact info@unbelievabletruth.co.uk if you need help.");

if ($result > 0) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: audio/mpeg");
    header("Content-Disposition: attachment; filename=".FILENAME);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".filesize(MEDIA_PATH.FILENAME));
    readfile(MEDIA_PATH.FILENAME);
}

$query = "DELETE FROM download_tokens WHERE token = ?;";
$stmt = $db->prepare($query);
$stmt->execute([$u_token]);
if ($stmt->rowCount() < 1) {
    exit ("Database error.");
}