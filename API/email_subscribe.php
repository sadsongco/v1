<?php

require_once("../../secure/scripts/ut_m_connect.php");

$output = "404 Not Found";

$post = file_get_contents('php://input');

$post = json_decode($post, true);

if (isset($post['email']) && $post['email'] != '') {
    try {
        $stmt = $db->prepare("INSERT INTO ut_mailing_list (email, domain, name, last_sent, subscribed) VALUES (?, SUBSTRING_INDEX(?, '@', -1), ?, ?, ?)");
        $stmt->execute([$post['email'], $post['email'], $post['name'], 0, 1]);
        $output = ['success'=> true];
    }
    catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $output = ['success'=> false, 'status'=>'exists'];
        } else {
            $output = ['succes'=>false, 'status'=>'db_error'];
            error_log($e->getMessage());
        }
    }
}

echo json_encode($output);


require_once("../../secure/scripts/ut_disconnect.php");

?>