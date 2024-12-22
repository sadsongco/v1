<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../private/mailout/api/vendor/autoload.php';

// Load Mustache
require('../lib/mustache.php-main/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();

require("./includes/get_host.php");
include_once(__DIR__.'/get_latest_mailout.php');

function sendLastMailout($row, $last_sent) {

    if (!isset($row['name'])) $row['name'] = '';
    
    require("../private/mailout/api/includes/mailout_create.php");
    include_once(__DIR__."/../../private/mailout/api/includes/generate_mailout_content.php");
    include_once(__DIR__."/../../private/mailout/api/includes/generate_mailout_email_content.php");
    
    $last_mailout = getLatestMailout();
    if ($last_mailout == $last_sent) return ["success"=>true, "last_mailout"=>$last_mailout];
    if ($last_mailout == 0) throw new Exception("Test exception");
    $content_path = "../private/mailout/assets/content/";
    $remove_path = '/email_management/unsubscribe.php';
    $subject_id = "[UNBELIEVABLE TRUTH]";
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    
    try {
        $content = file($content_path.$last_mailout.'.txt');
        $replacements = generateMailoutContent($content, $subject_id);
        $replacements['host'] = getHost();
        $replacements['remove_path'] = $remove_path;
        
        $mail->Subject = $replacements["subject"];
        $bodies = generateMailoutEmailContent($replacements, $row);
        $mail->msgHTML($bodies["html_body"]);
        $mail->AltBody = $bodies["text_body"];

        require_once("../../secure/mailauth/ut.php");

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
        //Recipients
        $mail->addAddress($row['email'], $row['name']);     //Add a recipient


        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->addAddress($row['email'], $row['name']);

        $mail->send();
        return ["success"=>true, "last_mailout"=>$last_mailout];
    } catch (Exception $e) {
        error_log($mail->ErrorInfo);
        error_log($e);
        return ["success"=>false, "status"=>"email_error"];
    }
    return $last_mailout;
}