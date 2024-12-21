<?php
function confirmEmailInDB($email_id, $db) {
    try {
        $stmt = $db->prepare('UPDATE ut_mailing_list SET confirmed = 1 WHERE email_id = ?');
        $stmt->execute([$email_id]);
        return ["success"=>true];
    }
    catch (PDOException $e) {
        error_log($e);
        return ['success'=>false, 'status'=>'db_error'];
    }
}