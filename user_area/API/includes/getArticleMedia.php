<?php

function makeUniqueToken($auth, $track) {
    return hash('sha1', $auth->getUsername().$track["filename"]);
}

function getMediaArr($table, $id, $db) {
    $id_id = $table == "media" ? "media_id" : "image_id";
    $query = "SELECT $id_id, filename, title, notes FROM $table WHERE $id_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0];
}

function getMedia($content, $db, $auth, $m, $host) {
    // Get audio
    preg_match_all('/{{a::([0-9])+}}/', $content, $audio_ids);
    if (sizeof($audio_ids[1]) > 0) {
        foreach ($audio_ids[1] as $key=>$audio_id) {
            $track = getMediaArr("media", $audio_id, $db);
            $replace_str = $audio_ids[0][$key];
            $track["token"] = makeUniqueToken($auth, $track);
            try {
                $query = "INSERT IGNORE INTO streaming_tokens VALUES (0, ?, ?, ?);";
                $stmt = $db->prepare($query);
                $stmt->execute([$track["token"], $track["media_id"], time()]);
            }
            catch (PDOException $e) {
                if ($e->getCode() == 23000) throw new Exception("streaming token already exists");
                die($e->getMessage());
            }
            $track["title"] = str_replace(" ", "_", $track["title"]);
            $track["notes"] = str_replace(" ", "_", $track["notes"]);
            $replace_el = $m->render("audioLoader", ["track"=>json_encode($track), "base_dir"=>$host]);
            $content = preg_replace("/$replace_str/", $replace_el, $content);
        }
    }
    // get images
    preg_match_all('/{{i::([0-9])+(?:::)?(l|r)?.?}}/', $content, $image_ids);
    if (sizeof($image_ids[1]) > 0) {
        foreach ($image_ids[1] as $key=>$image_id) {
            $image = getMediaArr("images", $image_id, $db);
            $image["path"] = IMAGE_UPLOAD_PATH.$image["filename"];
            $image_metadata = getimagesize(__DIR__.RELATIVE_ROOT.$image["path"]);
            $image["size_string"] = $image_metadata[3];
            $image["aspect_ratio"] = $image_metadata[0] . "/".$image_metadata[1];
            $replace_str = $image_ids[0][$key];
            if (isset($image_ids[2][$key])) {
                switch ($image_ids[2][$key]) {
                    case "l":
                        $image["float"] = "floatLeft";
                        break;
                    case "r":
                        $image["float"] = "floatRight";
                        break;
                    default:
                        $image["float"] = "floatCentered";
                }
                $replace_el = $m->render("imageTag", $image);
                $content = preg_replace("/$replace_str/", $replace_el, $content);
            }
        }
    }
    return nl2br($content);
}

?>