<?php

include_once('includes/mailout_includes.php');
include_once('includes/media_upload.php');

function return_bytes($val)
{
    preg_match('/(?<value>\d+)(?<option>.?)/i', trim($val), $matches);
    $inc = array(
        'g' => 1073741824, // (1024 * 1024 * 1024)
        'm' => 1048576, // (1024 * 1024)
        'k' => 1024
    );
    
    $value = (int) $matches['value'];
    $key = strtolower(trim($matches['option']));
    if (isset($inc[$key])) {
        $value *= $inc[$key];
    }

    return $value;
}

define("IMAGE_UPLOAD_PATH", __DIR__."/../../../assets/images/mailout_images/");
define("MAX_IMAGE_WIDTH", 200);
define("IMAGE_THUMBNAIL_WIDTH", 100);
define("MAX_FILE_SIZE", return_bytes(ini_get("upload_max_filesize")));
define("MAX_POST_SIZE", return_bytes(ini_get("post_max_size")));

$files = $_FILES['image_upload'];

foreach ($files['name'] as $key=>$filename) {
    $image_file_type = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    $uploaded_file = uploadMedia($files, $key, $db, 'MailoutImages', $image_file_type);
}

echo $m->render('uploadedFile', ["uploaded_file"=>$uploaded_file]);