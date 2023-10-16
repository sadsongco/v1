<?php

function makeUniqueToken($auth, $track) {
    echo "make unique token: ";
    p_2($auth->getUsername());
    p_2($track["filename"]);
    echo hash('xxh64', $auth->getUsername().$track["filename"]);
    return hash('xxh64', $auth->getUsername().$track["filename"]);
}

function getMediaArr($table, $ids, $db) {
    $id_id = $table == "media" ? "media_id" : "image_id";
    $query = "SELECT $id_id, filename, title, notes FROM $table WHERE $id_id = ?";
    for ($x = 1; $x < sizeof($ids); $x++) {
        $query .= " OR $id_id = ?";
    }
    $query .= ";";
    $stmt = $db->prepare($query);
    $stmt->execute($ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMedia($content, $db, $auth, $m, $host) {
    // Get audio
    preg_match_all('/{{a::([0-9])+}}/', $content, $audio_ids);
    if (sizeof($audio_ids[1]) > 0) {
        $audio_arr = getMediaArr("media", $audio_ids[1], $db);
        foreach ($audio_arr AS $track) {
            $replace_str = "{{a::".$track["media_id"]."}}";
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
    preg_match_all('/{{i::([0-9])+}}/', $content, $image_ids);
    if (sizeof($image_ids[1]) > 0) {
        $image_arr = getMediaArr("images", $image_ids[1], $db);
        foreach ($image_arr as $image) {
            $replace_str = "{{i::".$image["image_id"]."}}";
            $image["path"] = IMAGE_UPLOAD_PATH.$image["filename"];
            $image["size_string"] = getimagesize(__DIR__."/../../".$image["path"])[3];
            $replace_el = $m->render("imageTag", $image);
            $content = preg_replace("/$replace_str/", $replace_el, $content);
        }
    }
    return nl2br($content);
}

function getArticles($db) {
    $query = "SELECT title, body, DATE_FORMAT(added, '%D %b %Y') AS added FROM articles ORDER BY added DESC;";
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}


?>