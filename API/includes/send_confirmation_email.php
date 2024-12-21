<?php

//Load Composer's autoloader
require '../private/mailout/api/vendor/autoload.php';

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Mustache
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();


function sendConfirmationEmail($row) {
    $mail = new PHPMailer(true);
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader('templates'),
        'partials_loader' => new Mustache_Loader_FilesystemLoader('templates/partials')
    ));
    try {
        $row['host'] = getHost();
        $row['secure_id'] = generateSecureId($row['email'], $row['email_id']);
        $body = $m->render('confirmationEmailHtml', $row);
        $text_body = $m->render('confirmationEmailText', $row);
        $subject = 'Unbelievable Truth - confirm your email';
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
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $text_body;

        $mail->send();
        return ['success'=>true];
    } catch (Exception $e) {
        error_log($e);
        error_log("Message could not be sent. Mailer Error: " . $mail->ErrorInfo);
        return ["success"=>false, "status"=>"email_error"];
    }
}
