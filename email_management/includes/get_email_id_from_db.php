<?php

function getEmailIdFromDB($email, $db) {
    try {
    $stmt = $db->prepare('SELECT email_id, email, last_sent name FROM ut_mailing_list WHERE email=?');
    $stmt->execute([$_GET['email']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (sizeof($result) == 0) throw new PDOException('Email not found in database');
    return ["success"=>true, "email_id"=>$result[0]['email_id']];
    }
    catch (PDOException $e) {
        error_log($e);
        return ['success'=>false, 'status'=>'db_error'];
    }

}