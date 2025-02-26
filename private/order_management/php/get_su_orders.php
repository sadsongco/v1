<?php

require(__DIR__ . "/classes/EmailParser.php");

use Parser\EmailParser;

ini_set('display_errors', 'on');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('error_log', "./debug.log");

include(__DIR__ . "/includes/order_includes.php");

$authhost="{unbelievabletruth.co.uk:993/imap/ssl/novalidate-cert}";
$user="orders";

$user="orders@unbelievabletruth.co.uk";
$pass="HackMy0rders!";
$dom = new DOMDocument();
if ($mbox=imap_open( $authhost, $user, $pass )) {
    $headers = imap_headers($mbox);
    foreach ($headers as $id=>$email) {
        if ($id < 1) continue;
        $message = imap_fetchbody($mbox, $id + 1, '2');
        try {
                $orderDetailObj = new EmailParser($message, $id);
                $orderDetailObj->parse();
                $order_details = $orderDetailObj->get();
                p_2($order_details);
        }
        catch (Exception $e) {
                error_log($e);
                echo $e->getMessage() . "<br>";
        }
    }
    imap_close($mbox);
} else {
        for ($id = 1; $id < 1000; $id++) {
                $message = file_get_contents((base_path("../order_email_$id.html")));
                if (!$message) break;
                $orderDetailObj = new EmailParser($message, $id);
                $orderDetailObj->parse();
                $order_details = $orderDetailObj->get();
                p_2($order_details);
        }
}

        