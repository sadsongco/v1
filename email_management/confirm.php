<?php

include_once("./includes/html_head.php");

require_once("../../secure/scripts/ut_m_connect.php");

$message = 'There was an error. Make sure you only access this page from your email link';

if (isset($_GET) && isset($_GET['email'])) {
    try {
        $stmt = $db->prepare('SELECT email_id FROM ut_mailing_list WHERE email=?');
        $stmt->execute([$_GET['email']]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $email_id = $result[0]['email_id'];
        $secure_id = hash('ripemd128', $_GET['email'].$email_id.'AndyJasNigel');
        if ($_GET['check'] != $secure_id) throw new PDOException('Bad check code', 1176);
        $stmt = $db->prepare('UPDATE ut_mailing_list SET confirmed = 1 WHERE email_id = ?');
        $stmt->execute([$email_id]);
        $message = 'Your email is confirmed, welcome to the email list!';
    }
    catch (PDOException $e) {
        if ($e->getCode() ==1176) {
            $message = 'Bad check code';
        }
        error_log($e->getMessage());
    }
}


require_once("../../secure/scripts/ut_disconnect.php");

echo $message;

include_once("./includes/html_foot.php");
?>
