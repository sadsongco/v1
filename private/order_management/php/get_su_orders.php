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
                try {
                        insertOrderIntoDatabase($order_details, $db);
                } catch (Exception $e) {
                        error_log($e);
                        echo "Couldn't insert order " . $order_details['order_no'] . " into database: " . $e->getMessage() . "<br>";
                }
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
                $customer_id = checkIfCustomerExists($order_details['email'], $db); 
                if (!$customer_id) $customer_id = insertNewCustomer($order_details, $db);
                $item_ids = [];
                foreach ($order_details['items'] as $idx=>$item) {
                    $item_details = checkIfItemExists($item['item'], $db);
                    if (!$item_details['item_id']) {
                        $item_id = insertNewItem($item['item'], $order_details['item_prices'][$idx]['price'], $db);
                    }
                    elseif ($item_details['item_id'] && $item_details['price'] != $order_details['item_prices'][$idx]['price']) {
                        updateItem($item_details['item_id'], $order_details['item_prices'][$idx]['price'], $db);
                    }
                    $item_ids[] = $item_id;
                }
        }
        catch (Exception $e) {
                throw new Exception($e);
        }
}

function checkIfCustomerExists($email, $db) : int {
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
                return $db->last_insert_id;
        } catch (Exception $e) {
                throw new Exception($e);
        }
}

function checkIfItemExists($item_name, $db) {
    try {
        $query = "SELECT item_id, price FROM Items WHERE name LIKE ?";
        $stmt = $db->prepare($query);
        $stmt->execute(['%'. $item_name . '%']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) return false;
        $result['price'] = get_numeric($result['price']);
        return $result;
    }
    catch (Exception $e) {
        throw new Exception($e);
    }
}

function insertNewItem($item_name, $item_price, $db) {
    try {
        $query = "INSERT INTO Items VALUES (NULL, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$item_name, $item_price]);
        return $db->last_insert_id;
    } catch (Exception $e) {
        throw new Exception($e);
    }
}

function updateItem($item_id, $item_price, $db) {
    try {
        $query = "UPDATE Items SET price = ? WHERE item_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$item_price, $item_id]);
        return true;
    } catch (Exception $e) {
        throw new Exception($e);
    }
}

function get_numeric($val) {
        if (is_numeric($val)) {
          return $val + 0;
        }
        return 0;
      }