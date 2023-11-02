<?php

// cd /home/thesadso/unbelievabletruth.co.uk/private/mailout/API/; /usr/local/bin/php -q mailout.php

include_once('../includes/replace_tags.php');

function makeLogDir ($path) {
    return is_dir($path) || mkdir($path);
}

function write_to_log ($log_fp, $output) {
    fwrite($log_fp, $output);
    fclose($log_fp);
}

function delete_current_mailout() {
    $fp = fopen('current_mailout.txt', 'w');
    fwrite($fp, '');
    fclose($fp);
}

function email_admin($mail, $msg) {
    $mail->Subject = 'Unbelievable Truth mailout admin email';
    $mail->msgHTML($msg);
    $mail->addAddress('info@thesadsongco.com', 'Info');
    $mail->send();
}

function get_email_addresses($db, $mailout_id, $log_fp) {
    try {
        if ($mailout_id == 'test') {
            $mailout_id = 1;
            $mailing_table = "test_mailing_list";
        }
        else {
            $mailout_id = (int)$mailout_id;
            $mailing_table = "ut_mailing_list";
        };
        // TAKE THE '%thesadsongco' LINE OUT BEFORE PRODUCTION
        $query = "SELECT email, name, email_id
        FROM $mailing_table
        WHERE last_sent < ?
        AND subscribed = 1
        AND error = 0
        ORDER BY domain
        LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$mailout_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        global $mail;
        write_to_log($log_fp, "\nget_email_addresses Database Error: " . $e->getMessage());
        email_admin($mail, "<p>get_email_addresses Database Error: " . $e->getMessage()."</p>");
        exit();
    }
}

function mark_as_sent($db, $current_mailout, $row) {
    if ($current_mailout == 'test') {
        $stmt = $db->prepare("UPDATE test_mailing_list SET last_sent = ? WHERE email_id = ? AND email = ?");
        $stmt->execute([0, $row['email_id'], $row['email']]);
        return "\n--TEST-- :: Message sent: ".htmlspecialchars($row['email']);}
    try {
        $stmt = $db->prepare("UPDATE ut_mailing_list SET last_sent = ? WHERE email_id = ? AND email = ?");
        $stmt->execute([$current_mailout, $row['email_id'], $row['email']]);
        return 'Message sent: '.htmlspecialchars($row['email']);
    }
    catch(PDOException $e) {
        return  "mark_as_sent Database Error: " . $e->getMessage();
    }
}

function mark_as_error($db, $row, $current_mailout) {
    if ($current_mailout == 'test') {
        $stmt = $db->prepare("UPDATE test_mailing_list SET error = 1 WHERE email_id = ? AND email = ?");
        $stmt->execute([$row['email_id'], $row['email']]);
        return "--TEST-- :: ERROR SENDING: ".$row['email'];}
    try {
        $stmt = $db->prepare("UPDATE ut_mailing_list SET error = 1 WHERE email_id = ? AND email = ?");
        $stmt->execute([$row['email_id'], $row['email']]);
        return 'ERROR SENDING: '.$row['email'];
    }
    catch(PDOException $e) {
        return "mark_as_error Database Error: " . $e->getMessage();
    }

}

/**
 * This example shows how to send a message to a whole list of recipients efficiently.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('../../../../secure/scripts/ut_m_connect.php');
require_once("../../../../secure/mailauth/ut.php");

error_reporting(E_ERROR | E_PARSE);

date_default_timezone_set('Etc/UTC');

require 'vendor/autoload.php';

// paths to email data
$html_email_path = "../assets/mailout_bodies/html/";
$text_email_path = "../assets/mailout_bodies/text/";
$subject_path = "../assets/mailout_bodies/subject/";
// set the current email
$current_mailout = file_get_contents('./current_mailout.txt');
if ($current_mailout == '') exit();
// create log
$log_dir = './logs/';
makeLogDir($log_dir);
$log_fp = fopen("$log_dir/mailout_log_$current_mailout.txt", 'a');

// set up PHP Mailer
//Passing `true` enables PHPMailer exceptions
$mail = new PHPMailer(true);

// mail auth
$mail->isSMTP();
$mail->Host = $mail_auth['host'];
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = false; //SMTP connection will not close after each email sent, reduces SMTP overhead
$mail->Port = 25;
$mail->Username = $mail_auth['username'];
$mail->Password = $mail_auth['password'];
$mail->setFrom($mail_auth['from']['address'], $mail_auth['from']['name']);
$mail->addReplyTo($mail_auth['reply']['address'], $mail_auth['reply']['name']);

// $mail->isSMTP();
// $mail->Host = 'sandbox.smtp.mailtrap.io';
// $mail->SMTPAuth = true;
// $mail->Port = 2525;
// $mail->Username = '2be25e29cd2991';
// $mail->Password = 'aa9d83d9080798';
// $mail->setFrom('info@thesadsongco.com', 'The Sad Song Co. mailing list');
// $mail->addReplyTo('info@thesadsongco.com', 'The Sad Song Co. mailing list');

// set up emails
try {
    try {$body_template = file_get_contents($html_email_path.$current_mailout.'.html');} catch (Exception $e) {throw new Exception("html");}
    try {$text_template = file_get_contents($text_email_path.$current_mailout.'.txt');} catch (Exception $e) {throw new Exception("text");}
    try {$subject = file_get_contents($subject_path.$current_mailout.'.txt');} catch (Exception $e) {throw new Exception ("subject");}
}
catch (Exception $e) {
    write_to_log($log_fp, "\nFATAL: missing email body file: ".$e->getMessage());
    delete_current_mailout();
    email_admin($mail, "FATAL: missing email body file: ".$e->getMessage()." - messages stopped");
    exit();
}



$mail->Subject = $subject;

$result = get_email_addresses($db, $current_mailout, $log_fp);
if (sizeof($result) == 0) {
    write_to_log($log_fp, "\n\n--------COMPLETE--------");
    delete_current_mailout();
    email_admin($mail, "<h2>ALL EMAILS SENT. Check $log_dir/mailout_log_".$current_mailout.".txt for details<h2>");
    exit();
}

$output = "";
$host = 'https://unbelievabletruth.co.uk';

$remove_path = $host.'/email_management/unsubscribe.php?email=<!--{{email}}-->&check=<!--{{secure_id}}-->';
foreach ($result as $row) {
    try {
        $body = replace_tags($body_template, $row);
        $mail->msgHTML($body);
        $text_body = replace_tags($text_template, $row);
        $mail->AltBody = $text_body;
        $mail->addAddress($row['email'], $row['name']);
    } catch (Exception $e) {
        $output .= "\n".mark_as_error($db, $row, $current_mailout);
        $output .=  "\nInvalid address ".$row['email']." skipped";
        $output .= "\nREMOVE: " . replace_tags($remove_path, $row);
        continue;
    }
    
    try {
        $mail->send();
        //Mark it as sent in the DB
        $output .=  "\n".mark_as_sent($db, $current_mailout, $row);
    } catch (Exception $e) {
        $output .= "\n".mark_as_error($db, $row, $current_mailout);
        $output .= "\nPHPMailer Error :: ".$mail->ErrorInfo;
        $output .= "\nREMOVE: " . replace_tags($remove_path, $row);
        //Reset the connection to abort sending this message
        //The loop will continue trying to send to the rest of the list
        $mail->getSMTPInstance()->reset();
    }
    //Clear all addresses and attachments for the next iteration
    $mail->clearAddresses();
    $mail->clearAttachments();
}

// create log
write_to_log($log_fp, $output);

include_once('../../../../secure/scripts/ut_disconnect.php');

?>