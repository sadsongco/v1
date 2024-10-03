<?php

require_once('includes/mailout_includes.php');
require_once('includes/mailout_create.php');
require_once('../../../email_management/includes/get_host.php');


define("IMAGE_UPLOAD_PATH", "/assets/images/mailout_images/");

try {
    $query = "SELECT * FROM MailoutImages WHERE img_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['img']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    exit("Problem retrieving mailout images: ".$e->getMessage());
}

$result[0]['tag'] = "<!--{{i::".$result[0]['img_id']."}}-->";
$result[0]['path'] = getHost().IMAGE_UPLOAD_PATH.$result[0]['url'];

echo $m->render('existingImage', $result[0]);