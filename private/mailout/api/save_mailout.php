<?php

include_once("includes/mailout_includes.php");
$content_dir = "../assets/content";
$mailout_content = $_POST['subject']."\n".$_POST['heading']."\n".$_POST['content'];

try {
    $query = "INSERT INTO mailouts VALUES (NULL, NOW(), ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['subject'], $_POST['heading'], $_POST['content']]);
}
catch (PDOException $e) {
    exit("Couldn't save mailout: ".$e->getMessage());
}

$filename = "test.txt";

$path = $content_dir."/".$filename;

$fp = fopen($path, "w");

fwrite($fp, $mailout_content);

fclose($fp);

$filename = $_POST['filename'].".txt";

$path = $content_dir."/".$filename;

$fp = fopen($path, "w");

if(fwrite($fp, $mailout_content)) {
    header("HX-Trigger: listChange");
    echo "mailout saved";
} else {
    echo "mailout save failed";
}