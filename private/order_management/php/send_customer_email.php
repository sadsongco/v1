<?php

include_once("includes/order_includes.php");

$query = "SELECT item_id FROM Items WHERE name LIKE '%Citizens Band%'";
$stmt = $db->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$item_id = $result[0]['item_id'];

$query = "SELECT
    DISTINCT (Customers.name), (Customers.email)
    FROM Orders
    LEFT JOIN Customers
        ON Orders.customer_id = Customers.customer_id
    JOIN Order_items
        ON Order_items.order_id = Orders.order_id
    WHERE Order_items.item_id = $item_id;";

$stmt = $db->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["test"])) {
    $result = [
        ["email"=>"nigel@thesadsongco.com", "name"=>"Nigel Powell"],
        ["email"=>"info@unbelievabletruth.co.uk", "name"=>"Unbelievable Truth"]
    ];
}

require_once(base_path("../secure/mailauth/ut.php"));
include(base_path("../secure/secure_id/secure_id_ut.php"));

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ERROR | E_PARSE);

date_default_timezone_set('Etc/UTC');

require base_path('private/mailout/api/vendor/autoload.php');

// set up PHP Mailer
//Passing `true` enables PHPMailer exceptions
$mail = new PHPMailer(true);
// p_2($mail_auth);
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

$mail->Subject = $_POST['subject'];

ob_start();
foreach ($result as $row) {
    $output = "";
    try {
        $mail->msgHTML($_POST['message']);
        $mail->AltBody = $_POST['message'];
        $mail->addAddress($row['email'], $row['name']);
    } catch (Exception $e) {
        $output .= "\n".mark_as_error($db, $mailing_list_table, $current_mailout, $row);
        $output .=  "\nInvalid address ".$row['email']." skipped";
        $output .= "\nREMOVE: " . replaceTags($remove_path, $row);
        continue;
    }
    
    try {
        $mail->send();
        sleep(10);
        //Mark it as sent in the DB
        $output .=  "\n<br>Email " . $row['email'] . " sent";
    } catch (Exception $e) {
        $output .= "\nPHPMailer Error :: ".$mail->ErrorInfo;
        $output .= "\n<br>Email send error for " . $row['email'];
        //Reset the connection to abort sending this message
        //The loop will continue trying to send to the rest of the list
        $mail->getSMTPInstance()->reset();
        echo $output;
        ob_flush();
    }
    //Clear all addresses and attachments for the next iteration
    $mail->clearAddresses();
    $mail->clearAttachments();
    echo $output;
    ob_flush();
}

echo "<h1>All finished</h1>";
ob_end_flush();
