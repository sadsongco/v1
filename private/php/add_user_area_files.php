<?php

require_once(__DIR__."/../../../secure/scripts/ut_a_connect.php");

include(__DIR__."/../../php/includes/p_2.php");
include(__DIR__."/includes/returnBytes.php");
define("IMAGE_UPLOAD_PATH", __DIR__."/../../user_area/assets/images/");
define("AUDIO_UPLOAD_PATH", __DIR__."/../../user_area/assets/media/");
define("MAX_IMAGE_WIDTH", 600);
define("IMAGE_THUMBNAIL_WIDTH", 80);
define("MAX_FILE_SIZE", return_bytes(ini_get("upload_max_filesize")));
define("MAX_POST_SIZE", return_bytes(ini_get("post_max_size")));

// templating
require __DIR__.'/../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates/partials')
));

function insertMediaDB ($files, $key, $db, $table_name) {
    try {
        $query = "INSERT INTO $table_name VALUES (0, ?, ?, ?);";
        $stmt = $db->prepare($query);
        $stmt->execute([$_POST["title"][$key], $files["name"][$key], $_POST["notes"][$key]]);
        return $db->lastInsertId();
    }
    catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

function fileExists($filename, $table, $tag, $db) {
    $id = $table == "media" ? "media_id" : "image_id";
    try {
        $query = "SELECT $id FROM $table WHERE filename=?;";
        $stmt = $db->prepare($query);
        $stmt->execute([$filename]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        return ["success"=>false, "message"=>"Database error: ".$e->getMessage()];
    }
    $media_id = $result[0][$id];
    return ["success"=>false, "message"=>"File exists! Either rename the file or insert the existing version.", "tag"=>"{{".$tag."::".$media_id."}}"];
}

function resizeImage($image, $file_path, $image_file_type) {
    $resized_image = imagescale($image, MAX_IMAGE_WIDTH);
    switch ($image_file_type) {
        case "jpg":
        case "jpeg":
            return imagejpeg($resized_image, $file_path);
            break;
        case "png":
            return imagepng($resized_image, $file_path);
            break;
        case "gif":
            return imagegif($resized_image, $file_path);
            break;
        default:
            throw new Exception("unsupported image type");
            break;
    }
}

function saveThumbnail($image, $filename, $image_file_type) {
    $thumbnail = imagescale($image, IMAGE_THUMBNAIL_WIDTH);
    $file_path = IMAGE_UPLOAD_PATH."thumbnails/".$filename;
    switch ($image_file_type) {
        case "jpg":
        case "jpeg":
            return imagejpeg($thumbnail, $file_path);
            break;
        case "png":
            return imagepng($thhumbnail, $file_path);
            break;
        case "gif":
            return imagegif($thumbnail, $file_path);
            break;
        default:
            throw new Exception("unsupported image type");
            break;
    }
}

function uploadMedia($files, $key, $db, $table, $image_file_type = null) {
    // this is for uploads too large - change to throw a reasonable error
    if ($files["tmp_name"][$key] == "") die ("NO TMP_NAME:<br />..");
    $upload_path = $table == "images" ? IMAGE_UPLOAD_PATH : AUDIO_UPLOAD_PATH;
    $tag  = $table == "images" ? "i" : "a";
    $files["name"][$key] = str_replace(" ", "_", $files["name"][$key]);
    if (file_exists($upload_path.$files["name"][$key])) {
        return fileExists($files["name"][$key], $table, $tag, $db);
    }
    $uploaded_file = $files["tmp_name"][$key];
    try {
        $media_id = insertMediaDB($files, $key, $db, $table);
    }
    catch (PDOException $e) {
        return ["success"=>false, "message"=>"Database error: ".$e->getMessage()];
    }
    try {
        $image = null;
        $image_fnc = "";
        switch ($image_file_type) {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($uploaded_file);
                $image_fnc = "imagejpeg";
                break;
            case "png":
                $image = imagecreatefrompng($uploaded_file);
                $image_fnc = "imagepng";
                break;
            case "gif":
                $image = imagecreatefromgif($uploaded_file);
                $image_fnc = "imagegif";
                break;
            default:
                $image = null;
        }
        if ($image) {
            // resize images and save thumbnails
            $image_size = getimagesize($uploaded_file);
            if ($image_size[0] > MAX_IMAGE_WIDTH) {
                try {
                    if (!resizeImage($image, IMAGE_UPLOAD_PATH.$files["name"][$key], $image_file_type)) {
                        throw new Exception("Failed to resize image");
                    }
                    saveThumbnail($image, $files["name"][$key], $image_file_type);
                    unlink($uploaded_file);
                }
                catch (Exception $e) {
                    throw new Exception("Failed to save image: ".$e->getMessage());
                }
            }
        } else {
            // save audio
            move_uploaded_file($uploaded_file, $upload_path.$files["name"][$key]);
        }
    }
    catch (Exception $e) {
        return ["success"=>false, "message"=>"File copy error: ".$e->getMessage()];
    }
    return ["success"=>true, "filename"=>$files["name"][$key], "tag"=>"{{".$tag."::".$media_id."}}"];
}

$uploaded_files = [];
if (!isset($_FILES) || !isset($_FILES["files"])) {
    $uploaded_files[] = ["success"=>false, "messsage"=>"No files uploaded"];
} else {
    $files = $_FILES["files"];
    $post_size = 0;
    foreach ($files["name"] as $key=>$filename) {
        if ($files["size"][$key] > MAX_FILE_SIZE || $files["tmp_name"][$key] == "") {
            $uploaded_files[] = ["success"=> false, "message"=>"File $filename is too big"];
            break;
        }
        $post_size += $files["size"][$key];
        if ($post_size > MAX_POST_SIZE) {
            $uploaded_files[] = ["success"=> false, "message"=>"File upload size too big - try doing them one at a time"];
            break;
        }        
        $image_file_type = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
        switch ($image_file_type) {
            case "jpg":
            case "jpeg":
            case "png":
            case "gif":
                try {
                    $uploaded_files[] = uploadMedia($files, $key, $db, "images", $image_file_type);
                }
                catch (Exception $e) {
                    $uploaded_files[] = ["success"=>false, "message"=>"System error: ".$e->getMessage()];
                }
                break;
            case "mp3":
                try {
                    $uploaded_files[] = uploadMedia($files, $key, $db, "media");
                }
                catch (Exception $e) {
                    $uploaded_files[] = ["success"=>false, "message"=>"System error: ".$e->getMessage()];
                }
                break;
            default:
            $uploaded_files[] = ["success"=>false, "message"=>$files["name"][$key].": $image_file_type file types are not supported"];
        }
    }
    
}

echo $m->render("uploadedFiles", ["uploaded_files"=>$uploaded_files]);

require_once(__DIR__."/../../../secure/scripts/ut_disconnect.php");

?>