<?php

function insertMediaDB ($files, $key, $db, $table_name) {
    $params = [];
    $params['caption'] = $_POST['caption'];
    $params['url'] = $files['name'][$key];
    try {
        $query = "INSERT INTO $table_name VALUES (NULL, :url, :caption);";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $db->lastInsertId();
    }
    catch (PDOException $e) {
        die("Error inserting image into database: ".$e->getMessage());
    }
}

function fileExists($filename, $table, $tag, $db) {
    $id = "img_id";
    try {
        $query = "SELECT $id FROM $table WHERE url=?;";
        $stmt = $db->prepare($query);
        $stmt->execute([$filename]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        return ["success"=>false, "message"=>"Database error: ".$e->getMessage()];
    }
    $media_id = $result[0][$id];
    return ["success"=>false, "message"=>"File exists! Either rename the file or insert the existing version.", "tag"=>"<!--{{".$tag."::".$media_id."}}-->"];
}

function resizeImage($file_path, $image_file_type, $image=null, $target_filepath=null) {
    $target_filepath = $target_filepath ?? $file_path;
    switch ($image_file_type) {
        case "jpg":
        case "jpeg":
            $image =  $image ?? imagecreatefromjpeg($file_path);
            $resized_image = imagescale($image, MAX_IMAGE_WIDTH);
            imagejpeg($resized_image, $target_filepath);
            break;
        case "png":
            $image = $image ?? imagecreatefrompng($file_path);
            $resized_image = imagescale($image, MAX_IMAGE_WIDTH);
            imagepng($resized_image, $target_filepath);
            break;
        case "gif":
            $image = $image ?? imagecreatefromgif($file_path);
            $resized_image = imagescale($image, MAX_IMAGE_WIDTH);
            imagegif($resized_image, $target_filepath);
            break;
        default:
            throw new Exception("unsupported image type");
            break;
    }
}

function uploadMedia($files, $key, $db, $table, $image_file_type = null) {
    // this is for uploads too large - change to throw a reasonable error
    if ($files["tmp_name"][$key] == "") die ("NO TMP_NAME:<br />..");
    $files["name"][$key] = str_replace(" ", "_", $files["name"][$key]);
    $upload_path = IMAGE_UPLOAD_PATH.$files["name"][$key];
    $tag  = "i";
    if (file_exists($upload_path)) {
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
                imagejpeg($image, $upload_path);
                break;
            case "png":
                $image = imagecreatefrompng($uploaded_file);
                imagepng($image, $upload_path);
                break;
            case "gif":
                $image = imagecreatefromgif($uploaded_file);
                imagegif($image, $upload_path);
                break;
            default:
                $image = null;
        }
        
        if ($image) {
            // resize images and save thumbnails
            $image_size = getimagesize($uploaded_file);
            if ($image_size[0] > MAX_IMAGE_WIDTH){
                try {
                    resizeImage($upload_path, $image_file_type);
                }
                catch (Exception $e) {
                    return ["success"=>false, "message"=>"Image resize failed"];
                }
            }
        }
    }
    catch (Exception $e) {
        return ["success"=>false, "message"=>"File copy error: ".$e->getMessage()];
    }
    return ["success"=>true, "filename"=>$files["name"][$key], "tag"=>"<!--{{".$tag."::".$media_id."}}-->"];
}

function saveThumbnail($image, $filename, $image_file_type, $upload_path=IMAGE_UPLOAD_PATH) {
    $thumbnail = imagescale($image, IMAGE_THUMBNAIL_WIDTH);
    $file_path = $upload_path."thumbnail/".$filename;
    switch ($image_file_type) {
        case "jpg":
        case "jpeg":
            return imagejpeg($thumbnail, $file_path);
            break;
        case "png":
            return imagepng($thumbnail, $file_path);
            break;
        case "gif":
            return imagegif($thumbnail, $file_path);
            break;
        default:
            throw new Exception("unsupported image type");
            break;
    }
}