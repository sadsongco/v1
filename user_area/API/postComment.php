<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

// define("RELATIVE_ROOT", "/../../../");

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../../private/mailout/API/vendor/autoload.php';

function getCommentNotify($db, $reply) {
    try {
        $query = "SELECT notify, user_id FROM comments WHERE comment_id = ?;";
        $stmt = $db->prepare($query);
        $stmt->execute([$reply]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result[0];
    }
    catch (Exception $e) {
        return 0;
    }
}

function sendNotification($db, $m, $user_id, $article_id, $tab_id) {
    $email = "info@unbelievabletruth.co.uk";
    if ($user_id != "admin") {
        try {
            $query = "SELECT email FROM users WHERE id = ?;";
            $stmt = $db->prepare($query);
            $stmt->execute([$user_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $email = $result[0]['email'];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return;
        }
    }

    include("../../../secure/mailauth/ut.php");
    $host = getHost();
    $email_html = $m->render('replyNotificationEmailHTML', ["host"=>$host, "article_id"=>$article_id, "tab_id"=>$tab_id]);
    $email_txt = $m->render('replyNotificationEmailTxt', ["host"=>$host, "article_id"=>$article_id, "tab_id"=>$tab_id]);
    $subject = "Unbelievable Truth - there's a reply to your comment";

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
    $mail->setFrom($mail_auth['from']['address'], "Unbelievable Truth - website");
    $mail->addReplyTo($mail_auth['reply']['address'], "Unbelievable Truth - website");
    $mail->addAddress($email);
    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $email_html;
    $mail->AltBody = $email_txt;

    $mail->send();
}

$reply = null;
$notify = 0;

if (isset($_POST['notify'])) $notify = true; // doesn't need sanitisation? if it exists then it's true, otherwise false

if (isset($_POST['comment_reply_id']) && intval($_POST['comment_reply_id']) != 0) {
    $reply = intval($_POST['comment_reply_id']); // is this a valid comment id?
    $email_notification = getCommentNotify($db, $reply);
    if ($email_notification['notify'] == 1) sendNotification($db, $m, $email_notification['user_id'], $_POST['article_id'], $_POST['tab_id']);
}

sendNotification($db, $m, 'admin', $_POST['article_id'], $_POST['tab_id']);


$params = [
    "user_id"=>$auth->getUserId(),
    "article_id"=>intval($_POST['article_id']), // is this a valid article id?
    "reply"=>$reply,
    "reply_to"=>null,
    "notify"=>$notify,
    "comment"=>strip_tags($_POST['comment'])
];

try {
    $query = "INSERT INTO comments VALUES (0, :user_id, :article_id, NOW(), :reply, :reply_to, 0, :notify, 0, :comment);";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
}
catch (Exception $e) {
    echo "Error inserting comment:";
    die($e->getMessage());
}

header ('HX-Trigger:refreshComments');
echo $m->render("commentFormSolo", ["article_id"=>$params['article_id']]);
?>