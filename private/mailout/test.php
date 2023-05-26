<?php

function get_email_addresses($db, $mailout_id) {
    try {
        if ($mailout_id == 'test') {$mailout_id = 1; $mailing_table = "test_mailing_list";}
        else $mailing_table = "test_mailing_list";
        $query = "SELECT email, name, email_id FROM $mailing_table WHERE last_sent < ? ORDER BY domain";
        $stmt = $db->prepare($query);
        $stmt->execute([$mailout_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Database Error: " . $e->getMessage();
        exit();
    }
}

function replace_tags($body_template, $row) {
    $secure_id = hash('ripemd128', $row['email'].$row['email_id'].'AndyJasNigel');
    $row['secure_id'] = $secure_id;
    foreach ($row as $tag_name=>$tag_content) {
        if ($tag_name == 'name' && $tag_content == '') $tag_content = 'Music Friend';
        $body_template = str_replace("<!--{{".$tag_name."}}-->", $tag_content, $body_template);
    }
    return $body_template;
}

function mark_as_sent($db, $current_mailout, $row) {
    // $current_mailout = 1;
    if ($current_mailout == 'test') {echo "JUST A TEST<br />"; return 'Message sent to :' . htmlspecialchars($row['name']) . ' (' .
        htmlspecialchars($row['email']) . ')';}
    try {
        $stmt = $db->prepare("UPDATE test_mailing_list SET last_sent = ? WHERE email_id = ? AND email = ?");
        $stmt->execute([$current_mailout, $row['email_id'], $row['email']]);
        return 'Message sent to :' . htmlspecialchars($row['name']) . ' (' .
        htmlspecialchars($row['email']) . ')';
    }
    catch(PDOException $e) {
        echo "Database Error: " . $e->getMessage() . "<br />";
    }
}

/**
 * This example shows how to send a message to a whole list of recipients efficiently.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('../../../secure/scripts/ut_m_connect.php');
include_once('../includes/html_header.php');

error_reporting(E_STRICT | E_ALL);

date_default_timezone_set('Etc/UTC');

require 'vendor/autoload.php';

// paths to email data
$html_email_path = "./mailout_bodies/html/";
$text_email_path = "./mailout_bodies/text/";
$subject_path = "./mailout_bodies/subject/";
// set the current email
$current_mailout = "test";

//Passing `true` enables PHPMailer exceptions
$mail = new PHPMailer(true);
$body_template = file_get_contents($html_email_path.$current_mailout.'.html');
$text_template = file_get_contents($text_email_path.$current_mailout.'.txt');
$subject = file_get_contents($subject_path.$current_mailout.'.txt');

$mail->isSMTP();
$mail->Host = 'thesadsongco.com';
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = true; //SMTP connection will not close after each email sent, reduces SMTP overhead
$mail->Port = 25;
$mail->Username = 'info@thesadsongco.com';
$mail->Password = "0RosamundE####";
$mail->setFrom('info@thesadsongco.com', 'The Sad Song Co. mailing list');
$mail->addReplyTo('info@thesadsongco.com', 'The Sad Song Co. mailing list');

$mail->Subject = $subject;

$result = get_email_addresses($db, $current_mailout);

foreach ($result as $row) {
    try {
        $body = replace_tags($body_template, $row);
        $mail->msgHTML($body);
        $text_body = replace_tags($text_template, $row);
        $mail->AltBody = $text_body;
        $mail->addAddress($row['email'], $row['name']);
    } catch (Exception $e) {
        echo 'Invalid address skipped: ' . htmlspecialchars($row['email']) . '<br>';
        continue;
    }

    try {
        $mail->send();
        //Mark it as sent in the DB
        echo "<p>".mark_as_sent($db, $current_mailout, $row)."</p>\n\t\t";
    } catch (Exception $e) {
        echo 'Mailer Error (' . htmlspecialchars($row['email']) . ') ' . $mail->ErrorInfo . '<br>';
        //Reset the connection to abort sending this message
        //The loop will continue trying to send to the rest of the list
        $mail->getSMTPInstance()->reset();
    }
    //Clear all addresses and attachments for the next iteration
    $mail->clearAddresses();
    $mail->clearAttachments();
}

include_once('../includes/html_footer.php');
include_once('../../../secure/scripts/ut_disconnect.php');