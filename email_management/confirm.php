<?php

error_reporting(E_ALL); // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment

ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', './debug_log'); // Logging file path

include_once("./includes/html_head.php");
include_once('../../secure/secure_id/secure_id_ut.php');
require_once("../../secure/scripts/ut_m_connect.php");

include_once(__DIR__.'/../private/mailout/api/includes/replace_tags.php');
include_once(__DIR__.'/includes/get_email_id_from_db.php');
include_once(__DIR__.'/includes/confirm_email_in_db.php');
include_once(__DIR__.'/includes/send_last_mailout.php');
include_once(__DIR__.'/includes/set_last_mailout_sent.php');

$message = 'There was an error. Make sure you only access this page from your email link';

if (isset($_GET) && isset($_GET['email'])) {
    try {
        $email_id_result = getEmailIdFromDB($_GET['email'], $db);
        if (!$email_id_result["success"]) throw new Exception($email_id_result["status"]);
        $secure_id = generateSecureId($_GET['email'], $email_id_result['email_id']);
        if (!isset($_GET['check']) || $_GET['check'] != $secure_id) throw new Exception('Bad check code', 1176);
        $confirm_result = confirmEmailInDB($email_id_result['email_id'], $db);
        if (!$confirm_result["success"]) throw new Exception($confirm_result["status"]);
        $message = 'Your email is confirmed, welcome to the email list!';
    }
    catch (Exception $e) {
        if ($e->getCode() ==1176) {
            error_log('Bad check code');
        }
        error_log($e);
        exit($message);
    }
    $last_mailout_result = sendLastMailout($_GET, $secure_id);
    if ($last_mailout_result["success"]) {
        setLastMailoutSent($email_id_result['email_id'], $last_mailout_result["last_mailout"], $db);
    }
    else {
        $message .= "<br>There was an error sending the last mailout to you though. Contact info@unbelievabletruth.co.uk if you need help.";
    }
    
}


require_once("../../secure/scripts/ut_disconnect.php");

echo $message;

include_once("./includes/html_foot.php");
?>
