<?php

include_once("./includes/html_head.php");

require_once("../../secure/scripts/ut_m_connect.php");

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../private/mailout/API/vendor/autoload.php';

include_once('../private/mailout/includes/replace_tags.php');

function getLatestMailout() {
    $latest_mailout = 0;
    if ($handle = opendir('../private/mailout/assets/mailout_bodies/html')) {
        while (false !== ($entry = readdir($handle))) {
            if (substr($entry, 0, 1) != ".") {
                $mailout_id = explode('.', $entry)[0];
                if ($mailout_id == 'test') continue;
                if ((int)$mailout_id > $latest_mailout) $latest_mailout = (int)$mailout_id;
            }
        }
        closedir($handle);
    }
    return $latest_mailout;
}

function sendLastMailout($row) {

    $last_mailout = getLatestMailout();
    if ($last_mailout == 0) return null;
    $bodies_path = '../private/mailout/assets/mailout_bodies/';
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {

        $body_template = file_get_contents($bodies_path."html/".$last_mailout.".html");
        $text_template = file_get_contents($bodies_path."text/".$last_mailout.".txt");
        $subject = file_get_contents($bodies_path."subject/".$last_mailout.".txt");

        $body = replace_tags($body_template, $row);
        $text_body = replace_tags($text_template, $row);

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
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

$message = 'There was an error. Make sure you only access this page from your email link';

if (isset($_GET) && isset($_GET['email'])) {
    try {
        $stmt = $db->prepare('SELECT email_id, email, name FROM ut_mailing_list WHERE email=?');
        $stmt->execute([$_GET['email']]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $email_id = $result[0]['email_id'];
        $secure_id = hash('ripemd128', $_GET['email'].$email_id.'AndyJasNigel');
        if ($_GET['check'] != $secure_id) throw new PDOException('Bad check code', 1176);
        $row = $result[0];
        $stmt = $db->prepare('UPDATE ut_mailing_list SET confirmed = 1 WHERE email_id = ?');
        $stmt->execute([$email_id]);
        $message = 'Your email is confirmed, welcome to the email list!';
        sendLastMailout($row);
    }
    catch (PDOException $e) {
        if ($e->getCode() ==1176) {
            $message = 'Bad check code';
        }
        error_log($e->getMessage());
    }
}


require_once("../../secure/scripts/ut_disconnect.php");

echo $message;

include_once("./includes/html_foot.php");
?>
