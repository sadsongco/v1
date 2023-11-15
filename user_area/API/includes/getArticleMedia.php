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

function removeExpiredStreamingTokens($db) {
    $query = "DELETE FROM streaming_tokens WHERE timestamp < ?;";
    $stmt = $db->prepare($query);
    $stmt->execute([time()-(60*30)]); // remove timestamps longer than 30 minutes ago
}

function getAudio($audio_id, $db, $auth) {
    $track = getMediaArr("media", $audio_id, $db);
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
    return $track;
}

function getImage($image_id, $image_float, $db) {
    $image = getMediaArr("images", $image_id, $db);
    $image["path"] = IMAGE_UPLOAD_PATH.$image["filename"];
    $image["thumbpath"] = IMAGE_UPLOAD_PATH."thumbnails/".$image["filename"];
    $image_metadata = getimagesize(__DIR__.RELATIVE_ROOT.$image["path"]);
    $image["size_string"] = $image_metadata[3];
    $image["aspect_ratio"] = $image_metadata[0] . "/".$image_metadata[1];
    $image["template"] = "blockImage";
    if ($image_float) {
        switch ($image_float) {
            case "l":
                $image["float"] = "floatLeft";
                $image["template"] = "inlineImage";
                break;
            case "r":
                $image["template"] = "inlineImage";
                $image["float"] = "floatRight";
                break;
            default:
                $image["float"] = "floatCentered";
        }
    }
    return $image;
}

function getMedia($content, $db, $auth, $m, $host) {
    removeExpiredStreamingTokens($db);
    $input_arr = explode("\n", $content);
    $output = "";
    $nl_flag = false;
    foreach ($input_arr as $key=>$line) {
        // Get audio
        preg_match_all('/{{a::([0-9])+}}/', $line, $audio_ids);
        if (sizeof($audio_ids[1]) > 0) {
            $nl_flag = false;
            foreach ($audio_ids[1] as $key=>$audio_id) {
                $track = getAudio($audio_id, $db, $auth);
                $replace_el = $m->render("audioLoader", ["track"=>json_encode($track), "base_dir"=>$host]);
                $replace_str = $audio_ids[0][$key];
                $line = preg_replace("/$replace_str/", $replace_el, $line);
            }
        }
        // get images
        preg_match_all('/{{i::([0-9])+(?:::)?(l|r)?.?}}/', $line, $image_ids);
        if (sizeof($image_ids[1]) > 0) {
            $nl_flag = false;
            foreach ($image_ids[1] as $key=>$image_id) {
                $image = getImage($image_id, $image_ids[2][$key] != "" ? $image_ids[2][$key] : false , $db);
                $replace_el = $m->render($image["template"], $image);
                $replace_str = $image_ids[0][$key];
                $line = preg_replace("/$replace_str/", $replace_el, $line);    
            }
        }
        preg_match_all('/{{i::([0-9])+(?:::)?(l|r)?.?}}/', $line, $image_ids);
        $line = trim($line);
        if ($line != "" && $nl_flag) {
            $output .= "<br />$line";
            $nl_flag = true;
            continue;
        }
        $output .= $line;
        $nl_flag = true;

    }
    return $output;
}

?>