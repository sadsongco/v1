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

function sendLastMailout($row, $secure_id) {

    if (!isset($row['name'])) $row['name'] = '';
    
    require("../private/mailout/api/includes/mailout_create.php");
    
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader('../private/mailout/assets/templates'),
        'partials_loader' => new Mustache_Loader_FilesystemLoader('../private/mailout/assets/templates/partials')
    ));
    $last_mailout = getLatestMailout();
    if ($last_mailout == 0) throw new Exception("Test exception");
    $content_path = "../private/mailout/assets/content/";
    $remove_path = '/email_management/unsubscribe.php';
    $subject_id = "[UNBELIEVABLE TRUTH]";
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    
    try {
        $content = file($content_path.$last_mailout.'.txt');
        $subject = $subject_id.array_shift($content);
        $heading = array_shift($content);
        $text_template = createTextBody($content);
        $html_template = createHTMLBody($content);
        $host = getHost();
        
        $mail->Subject = $subject;
        $text_body = $m->render("textTemplate", ["heading"=>$heading, "content"=>$text_template, "host"=>$host, "remove_path"=>$remove_path, "name"=>$row['name'], "email"=>$row['email'], "secure_id"=>$secure_id]);
        $html_body = $m->render("htmlTemplate", ["heading"=>$heading, "content"=>$html_template, "host"=>$host, "remove_path"=>$remove_path, "name"=>$row['name'], "email"=>$row['email'], "secure_id"=>$secure_id]);
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
        $mail->msgHTML($html_body);
        $mail->AltBody = $text_body;
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