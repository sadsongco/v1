<?php

include(__DIR__."/replace_tags.php");
include(__DIR__."/mailout_create.php");

/* *** FUNCTIONS *** */

function makeLogDir ($path) {
    return is_dir($path) || mkdir($path);
}

function write_to_log ($log_fp, $output) {
    fwrite($log_fp, $output);
    fclose($log_fp);
}

function delete_current_mailout($current_mailout_file) {
    $fp = fopen($current_mailout_file, 'w');
    fwrite($fp, '');
    fclose($fp);
}

function email_admin($mail, $msg) {
    $mail->Subject = 'The Exact Opposite mailout admin email';
    $mail->msgHTML($msg);
    $mail->addAddress('info@thesadsongco.com', 'Info');
    $mail->send();
}

function get_email_addresses($db, $mailout_id, $mailing_list_table, $log_fp) {
    try {
        if ($mailout_id == 'test') $mailout_id = 1;
        else $mailout_id = (int)$mailout_id;
        $query = "SELECT email, name, email_id
        FROM $mailing_list_table
        WHERE last_sent < ?
        AND subscribed = 1
        AND error = 0
        AND email LIKE '%sadsongco%'
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

function mark_as_sent($db, $mailing_list_table, $mailout_id, $row) {
    if ($mailout_id == 'test') $mailout_id = 1;
    else $mailout_id = (int)$mailout_id;
    try {
        $stmt = $db->prepare("UPDATE $mailing_list_table SET last_sent = ? WHERE email_id = ? AND email = ?");
        $stmt->execute([$mailout_id, $row['email_id'], $row['email']]);
        return "$mailout_id\tMessage sent\t".htmlspecialchars($row['email'])."\t".date("Y-m-d H:i:s");
    }
    catch(PDOException $e) {
        return  "mark_as_sent Database Error: " . $e->getMessage();
    }
}

function mark_as_error($db, $mailing_list_table, $mailout_id, $row) {
    if ($mailout_id == 'test') $mailout_id = 1;
    else $mailout_id = (int)$mailout_id;

    try {
        $stmt = $db->prepare("UPDATE `$mailing_list_table` SET `error` = 1 WHERE `email_id` = ? AND `email` = ?");
        $stmt->execute([$row['email_id'], $row['email']]);
        return "$mailout_id\t::ERROR SENDING::\t".$row['email'];}
    catch(PDOException $e) {
        return "mark_as_error Database Error: " . $e->getMessage();
    }

}

/* ************************** */

require_once("../../../../secure/mailauth/ut.php");
include(__DIR__."/../../../../../secure/secure_id/secure_id_ut.php");

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ERROR | E_PARSE);

date_default_timezone_set('Etc/UTC');

require 'vendor/autoload.php';

// set up PHP Mailer
//Passing `true` enables PHPMailer exceptions
$mail = new PHPMailer(true);

// create log
makeLogDir($log_dir);
$log_fp = fopen("$log_dir$current_mailout.txt", 'a');


// mail auth
$mail->isSMTP();
$mail->Host = $mail_auth['host'];
$mail->SMTPAuth = true;
$mail->SMTPKeepAlive = false; //SMTP connection will not close after each email sent, reduces SMTP overhead
$mail->Port = 25;
$mail->Username = $mail_auth['username'];
$mail->Password = $mail_auth['password'];
$mail->setFrom($mail_auth['from']['address'], $from_name);
$mail->addReplyTo($mail_auth['reply']['address'], $from_name);

// set up emails
try {
    $content = file($content_path.$current_mailout.'.txt');
}
catch (Exception $e) {
    write_to_log($log_fp, "\nFATAL: missing email body file: ".$e->getMessage());
    delete_current_mailout($current_mailout_file);
    email_admin($mail, "FATAL: missing email body file: ".$e->getMessage()." - messages stopped");
    exit();
}
$subject = $subject_id.array_shift($content);
$heading = array_shift($content);
$text_template = createTextBody($content);
$html_template = createHTMLBody($content);
$host = getHost();

$mail->Subject = $subject;

$result = get_email_addresses($db, $current_mailout, $mailing_list_table, $log_fp);

if (sizeof($result) == 0) {
    write_to_log($log_fp, "\n\n--------COMPLETE--------");
    delete_current_mailout($current_mailout_file);
    email_admin($mail, "<h2>ALL EMAILS SENT. Check ./logs/mailout_log_".$current_mailout.".txt for details<h2>");
    exit();
}

$output = "";

foreach ($result as $row) {
    try {
        $secure_id = generateSecureId($row['email'], $row['email_id']);
        
        $text_body = $m->render("textTemplate", ["heading"=>$heading, "content"=>$text_template, "host"=>$host, "remove_path"=>$remove_path, "name"=>$row['name'], "email"=>$row['email'], "secure_id"=>$secure_id]);
        $html_body = $m->render("htmlTemplate", ["heading"=>$heading, "content"=>$html_template, "host"=>$host, "remove_path"=>$remove_path, "name"=>$row['name'], "email"=>$row['email'], "secure_id"=>$secure_id]);
        $mail->msgHTML($html_body);
        $mail->AltBody = $text_body;
        $mail->addAddress($row['email'], $row['name']);
    } catch (Exception $e) {
        $output .= "\n".mark_as_error($db, $mailing_list_table, $current_mailout, $row);
        $output .=  "\nInvalid address ".$row['email']." skipped";
        $output .= "\nREMOVE: " . replace_tags($remove_path, $row);
        continue;
    }
    
    try {
        $mail->send();
        //Mark it as sent in the DB
        $output .=  "\n".mark_as_sent($db, $mailing_list_table, $current_mailout, $row);
    } catch (Exception $e) {
        $output .= "\n".mark_as_error($db, $mailing_list_table, $current_mailout, $row);
        $output .= "\nPHPMailer Error :: ".$mail->ErrorInfo;
        $output .= "\nREMOVE: " . replace_tags($remove_path, $row);
        //Reset the connection to abort sending this message
        //The loop will continue trying to send to the rest of the list
        echo ($output);
        $mail->getSMTPInstance()->reset();
    }
    //Clear all addresses and attachments for the next iteration
    $mail->clearAddresses();
    $mail->clearAttachments();
}

// create log
write_to_log($log_fp, $output);