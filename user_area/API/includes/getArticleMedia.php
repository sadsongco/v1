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
        $query = "INSERT INTO streaming_tokens VALUES (0, ?, ?, ?)
        ON DUPLICATE KEY UPDATE timestamp = ?;";
        $stmt = $db->prepare($query);
        $stmt->execute([$track["token"], $track["media_id"], time(), time()]);
    }
    catch (PDOException $e) {
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

function getMedia($line, $db, $auth, $m, $host) {
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
        return $line;
}

function parseLinks($line, $m) {
    $links = [];
    preg_match_all('/({{link}}([^}]*){{\/link}})/', $line, $links);
    $replacements = [];
    foreach ($links[0] as $key=>$link) {
        $replacements[] = ["search"=>$links[0][$key], "replace"=>$links[2][$key]];
    }
    if (sizeof($replacements)==0) return $line;
    foreach ($replacements as $replace) {
        // p_2($replace);
        $replace_arr = explode("::", $replace['search']);
        $replace_arr = (preg_replace('/{{\/?link}}/', "", $replace_arr));
        if (sizeof($replace_arr) == 1) $link_text = $link_url = $replace_arr[0];
        else {
            $link_text = $replace_arr[0];
            $link_url = $replace_arr[1];
        }
        $html_replace = $m->render('link', ["link_text"=>$link_text, "link_url"=>$link_url]);
        $line = str_replace($replace["search"], $html_replace, $line);
        // p_2($line);
    }
    return $line;
}

function parseBody($body, $db, $auth, $m, $host) {
    $content = explode("\n", str_replace("\n\r", "\n", $body));
    removeExpiredStreamingTokens($db);
    $output = "<p>";
    for ($x = 0; $x < sizeof($content); $x++) {
        if ($content[$x] == "" || $content[$x] == "\n") continue;
        $content[$x] = parseLinks($content[$x], $m);
        $content[$x] = getMedia($content[$x], $db, $auth, $m, $host);
        if ($x+1 < sizeof($content) && ($content[$x+1] == "" || $content[$x+1] == "\n")) {
            $output .= trim($content[$x])."</p>\n<p>";
            continue;
        }
        $output .= trim($content[$x])."<br />\n";
    }
    $output .= "</p>";
    return $output;
}

?>