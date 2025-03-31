<?php

include_once("includes/resource_includes.php");
include_once(base_path("private/mailout/api/includes/media_upload.php"));

if (sizeof($_FILES) > 0) {
    $file_pointer = $_POST['resource_dir'] . "_file";
    $upload_dir = $parent_dir . $_POST['resource_dir'] . "/";
    if (in_array($_POST['resource_dir'], $resize_resources)) {
        $files = $_FILES[$file_pointer];
        $filename = $files['name'];
        $upload_path = $upload_dir . "full_res/" . $filename;
        $uploaded_file = $files["tmp_name"];
        if ($uploaded_file == "") die("NO TMP NAME");
        $image_file_type = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
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
                // save thumbnail
                saveThumbnail($image, $filename, $image_file_type, $upload_dir);
                // save web version
                resizeImage($upload_path . "full_res", $image_file_type, $image, $upload_dir . "web/" . $filename);
            }
            else {
                exit("<h2>Not an acceptable filetype</h2>");
            }
        }
        catch (Exception $e) {
            return ["success"=>false, "message"=>"File copy error: ".$e->getMessage()];
        }
        $meta_file_path = $parent_dir . $_POST["resource_dir"] . "/" . $_POST["meta_filename"] . ".txt";
        $res_str = $filename . "|" . $_POST['credit'] . "\n";
        file_put_contents($meta_file_path, $res_str, FILE_APPEND);
        exit("<h2>Resource Updated</h2>");
    }
    $target_file = $upload_dir . basename($_FILES[$file_pointer]["name"]);
    if (move_uploaded_file($_FILES[$file_pointer]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES[$file_pointer]["name"])). " has been uploaded.";
      } else {
        echo "Sorry, there was an error uploading your file.";
      }
}

if (isset($_POST["meta_filename"]) && $_POST["meta_filename"] != "") {
    $file_path = $parent_dir . $_POST["resource_dir"] . "/" . $_POST["meta_filename"] . ".txt";
    $fields = $update_map[$_POST["meta_filename"]];
    $res_str_arr = [];
    foreach ($fields as $field) {
        $res_str_arr[] = $_POST[$field];
    }
    $res_str = implode("|", $res_str_arr) . "\n";
    file_put_contents($file_path, $res_str, FILE_APPEND);
    echo "<h2>Resource meta file updated</h2>";
}
