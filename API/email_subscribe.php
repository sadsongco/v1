<?php

require_once("../../secure/scripts/ut_m_connect.php");

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../private/mailout/API/vendor/autoload.php';
include_once('../private/includes/replace_tags.php');

// function replace_tags($body_template, $row) {
//     $row['secure_id'] = $row['check'];
//     foreach ($row as $tag_name=>$tag_content) {
//         if ($tag_name == 'name' && $tag_content == '') $tag_content = 'Music Friend';
//         $body_template = str_replace("<!--{{".$tag_name."}}-->", $tag_content, $body_template);
//     }
//     return $body_template;
// }

function sendConfirmationEmail($row) {
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        $body_template = file_get_contents('mail_bodies/confirm.html');
        $text_template = file_get_contents('mail_bodies/confirm.txt');
        $subject = 'Unbelievable Truth - confirm your email';

        $body = replace_tags($body_template, $row);
        $text_body = replace_tags($text_template, $row);

        $mail->isSMTP();
        $mail->Host = 'unbelievabletruth.co.uk';
        $mail->SMTPAuth = true;
        $mail->SMTPKeepAlive = false; //SMTP connection will not close after each email sent, reduces SMTP overhead
        $mail->Port = 25;
        $mail->Username = 'info@unbelievabletruth.co.uk';
        $mail->Password = "Wh0'sT0Kn0w?";
        $mail->setFrom('info@unbelievabletruth.co.uk', 'Unbelievable Truth mailing list');
        $mail->addReplyTo('info@unbelievabletruth.co.uk', 'Unbelievable Truth mailing list');
        //Recipients
        $mail->addAddress($row['email'], $row['name']);     //Add a recipient


        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $text_body;

        $mail->send();
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

$output = "404 Not Found";

$post = file_get_contents('php://input');
$post = json_decode($post, true);

// $post['email'] = 'nigel@thesadsongco.com';
// $post['name'] = '';

if (isset($post['email']) && $post['email'] != '') {
    try {
        $stmt = $db->prepare("INSERT INTO ut_mailing_list (email, domain, name, last_sent, subscribed, date_added) VALUES (?, SUBSTRING_INDEX(?, '@', -1), ?, ?, ?, NOW())");
        $stmt->execute([$post['email'], $post['email'], $post['name'], 0, 1]);
        $secure_id = hash('ripemd128', $post['email'].$db->lastInsertID().'AndyJasNigel');
        sendConfirmationEmail(['email'=>$post['email'], 'name'=>$post['name'], 'check'=>$secure_id]);
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