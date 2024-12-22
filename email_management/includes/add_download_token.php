<?php

function addDownloadToken($email, $email_id, $db) {
    include_once(__DIR__.'/../../private/mailout/api/includes/make_unique_token.php');
    try {
        $token = makeUniqueToken($email_id, $email);
        $query = "INSERT INTO download_tokens VALUES (NULL, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$email_id, $token]);
    }
    catch (PDOException $e) {
        error_log($e);
        return false;
    }
    return true;
}