<?php

require_once(__DIR__."/../../../secure/scripts/ut_a_connect.php");

include(__DIR__."/../../php/includes/p_2.php");
define("IMAGE_UPLOAD_PATH", __DIR__."/../../user_area/assets/images/");
define("AUDIO_UPLOAD_PATH", __DIR__."/../../user_area/assets/media/");

function insertMediaDB ($files, $key, $db, $table_name) {
    try {
        $query = "INSERT INTO $table_name VALUES (0, ?, '');";
        $stmt = $db->prepare($query);
        $stmt->execute([$files["name"][$key]]);
        return $db->lastInsertId();
    }
    catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

function uploadMedia($files, $key, $db, $table) {
    $upload_path = $table == "images" ? IMAGE_UPLOAD_PATH : AUDIO_UPLOAD_PATH;
    $tag  = $table == "images" ? "i" : "a";
    try {
        $media_id = insertMediaDB($files, $key, $db, $table);
    }
    catch (PDOException $e) {
        throw new Exception ($e->getMessage());
    }
    try {
        move_uploaded_file($files["tmp_name"][$key], $upload_path.str_replace(" ", "_", $files["name"][$key]));
    }
    catch (Exception $e) {
        throw new Exception ($e->getMessage());
    }
    echo "Upload media ".$files["tmp_name"][$key]." to ".$upload_path.str_replace(" ", "_", $files["name"][$key])." with id of $media_id<br />";
    echo "{{".$tag."::".$media_id."}}";
}

$files = $_FILES["files"];
foreach ($files["name"] as $key=>$filename) {
    $imageFileType = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    switch ($imageFileType) {
        case "jpg":
        case "jpeg":
        case "png":
        case "gif":
            try {
                uploadMedia($files, $key, $db, "images");
            }
            catch (Exception $e) {
                die ($e->getMessage());
            }
            break;
            case "mp3":
                try {
                    uploadMedia($files, $key, $db, "media");
                }
                catch (Exception $e) {
                    die ($e->getMessage());
                }
            break;
        default:
            die ("That file type is not supported");
    }
}

require_once(__DIR__."/../../../secure/scripts/ut_disconnect.php");

?>