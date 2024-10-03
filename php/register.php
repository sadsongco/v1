<?php

// helper
include_once("includes/p_2.php");
require_once(__DIR__."/../../secure/env/ut_reserved_usernames.php");

// database
require_once(__DIR__."/../../secure/scripts/ut_a_connect.php");

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require_once __DIR__.'/../private/mailout/api/vendor/autoload.php';

// templating system
require_once __DIR__.'/../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

// auth
require_once __DIR__ . '/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}


function SendConfirmationEmail ($email, $selector, $token, $m, $mail_auth) {
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $protocol .= 's';
    $host = "$protocol://".$_SERVER['HTTP_HOST'];
    $email_html = $m->render('confirmRegisterEmail', ["host"=>$host, "selector"=>$selector, "token"=>$token]);
    $email_txt = $m->render('confirmRegisterEmailTxt', ["host"=>$host, "selector"=>$selector, "token"=>$token]);
    $subject = "Unbelievable Truth - please confirm your email";

    // set up PHP Mailer
    //Passing `true` enables PHPMailer exceptions
    $mail = new PHPMailer(true);

    // setup email variables
    $mail->isSMTP();
    $mail->Host = $mail_auth['host'];
    $mail->SMTPAuth = true;
    $mail->SMTPKeepAlive = false; //SMTP connection will not close after each email sent, reduces SMTP overhead
    $mail->Port = 25;
    $mail->Username = $mail_auth['username'];
    $mail->Password = $mail_auth['password'];
    $mail->setFrom($mail_auth['from']['address'], "Unbelievable Truth - website registration");
    $mail->addReplyTo($mail_auth['reply']['address'], "Unbelievable Truth - website registration");
    $mail->addAddress($email);
    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $email_html;
    $mail->AltBody = $email_txt;

    $mail->send();
}

try {
    if (in_array($_POST['username'], $reserved_usernames)) throw new \Delight\Auth\UserAlreadyExistsException;
    $userId = $auth->register($_POST['email'], $_POST['password'], $_POST['username'], function ($selector, $token) {
        $m = new Mustache_Engine(array(
            'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates'),
            'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates/partials')
        ));
        require_once("../../secure/mailauth/ut.php");
        try {
            SendConfirmationEmail($_POST['email'], $selector, $token, $m, $mail_auth);
            echo "<p>Confirmation email sent to ".$_POST['email']."</p>";
        }
        catch (Exception $e) {
            echo "Couldn't send confirmation email: ";
            echo $e->getMessage();
        }
    });

    // echo 'We have signed up a new user with the ID ' . $userId;
}
catch (\Delight\Auth\InvalidEmailException $e) {
    die('Invalid email address');
}
catch (\Delight\Auth\InvalidPasswordException $e) {
    exit('Invalid password');
}
catch (\Delight\Auth\UserAlreadyExistsException $e) {
   exit("That email or username is already registered!");
}
catch (\Delight\Auth\TooManyRequestsException $e) {
    error_log('Too many requests: '.$e->getMessage());
    echo "There has been an error. Please try again later.";
    echo $e->getMessage();
}

require_once("../../secure/scripts/ut_m_connect.php");

?>
