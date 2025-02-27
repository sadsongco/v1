<?php

require(__DIR__ . "/classes/EmailParser.php");
include(__DIR__ . "/includes/order_includes.php");

use Parser\EmailParser;

ini_set('display_errors', 'on');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('error_log', "./debug.log");

// PUT this info outside the web route before deploying
$authhost="{unbelievabletruth.co.uk:993/imap/ssl/novalidate-cert}";
$user="orders";

$user="orders@unbelievabletruth.co.uk";
$pass="HackMy0rders!";
$dom = new DOMDocument();
if ($mbox=imap_open( $authhost, $user, $pass )) {
        echo "LIVE SERVER";
    $headers = imap_headers($mbox);
    foreach ($headers as $id=>$email) {
        if (!isOrder($email)) continue;
        $message = imap_fetchbody($mbox, $id + 1, '2');
        try {
                $orderDetailObj = new EmailParser($message, $id);
                $orderDetailObj->parse();
                $order_details = $orderDetailObj->get();
                // try {
                //         insertOrderIntoDatabase($order_details, $db);
                // } catch (Exception $e) {
                //         error_log($e);
                //         echo "Couldn't insert order " . $order_details['order_no'] . " into database: " . $e->getMessage() . "<br>";
                // }
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

function isOrder($email) {
        $tmp_arr = explode(" ", $email);
        $header_arr = [];
        foreach($tmp_arr as $el) {
                if (trim($el) == "" || trim($el) == "U") continue;
                $header_arr[] = $el;
        }
        if ($header_arr[2]== "New" && $header_arr[3] == "order") return true;
        return false;
}

function insertOrderIntoDatabase($order_details, $db) {
        try {
                if (!checkIfCustomerExists($order_details['email'], $db)) insertNewCustomer($order_details, $db);
                else echo "Customer exists<br>";
        }
        catch (Exception $e) {
                throw new Exception($e);
        }
}

function checkIfCustomerExists($email, $db) {
        try {
                $query = "SELECT customer_id FROM Customers WHERE email = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$email]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) return $result['customer_id'];
                return false;
        } catch (Exception $e) {
                throw new Exception($e);
        }
}

function insertNewCustomer($order_details, $db) {
        try {
                $query = "INSERT INTO Customers VALUES (NULL, ?, ?, NULL, ?, ?, ?, ?);";
                $stmt = $db->prepare($query);
                $stmt->execute([
                        ucwords($order_details['name']),
                        ucwords($order_details['address']),
                        ucwords($order_details['town']),
                        $order_details['postcode'],
                        ucwords($order_details['country']),
                        $order_details['email']
                ]);
        } catch (Exception $e) {
                throw new Exception($e);
        }
}