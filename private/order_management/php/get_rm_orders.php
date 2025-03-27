<?php

include_once(__DIR__."/includes/order_includes.php");
require(base_path("../secure/env/config.php"));
//Load Composer's autoloader
require base_path('private/mailout/api/vendor/autoload.php');

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$unsent_orders = getUnsentOrders($db);

$unsent_orders_string = "";
foreach ($unsent_orders as $unsent_order) {
    $unsent_orders_string .= '"orderIdentifier";' . urlencode($unsent_order["rm_order_identifier"]) . ';';
}

$url = $path = RM_BASE_URL."/orders/" . $unsent_orders_string;
$headers = [
    "Authorization: " . RM_API_KEY,
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $path);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$responseObj = json_decode($response);
// p_2($responseObj);
ob_start();
foreach ($responseObj as $order) {
    if (!isset($order->trackingNumber)) {
        continue;
    }
    try {
        $query = "UPDATE Orders
        SET
        `dispatched` = ?,
        `rm_order_identifier` = ?,
        `rm_created` = ?,
        `rm_tracking_number` = ?
        WHERE `order_id` = ?";
        $stmt = $db->prepare($query);
        $shippedOn = isset($order->shippedOn) ? $order->shippedOn : NULL;
        $params = [
            $shippedOn,
            (int)$order->orderIdentifier,
            $order->createdOn,
            $order->trackingNumber,
            (int)$order->orderReference
        ];
        // $stmt->execute($params);
        sendCustomerShippedEmail($order->orderReference, $db, $m);
        echo "Updated order " . $order->orderReference . "<br>";
    } catch (Exception $e) {
        echo "Couldn't update order " . $order->orderReference . ": " . $e->getMessage();
    }
    ob_flush();
    flush();
    exit();
}

header ('HX-Trigger:updateOrderList');
echo "<p>Orders Updated from Royal Mail</p>";
ob_end_flush();

function getUnsentOrders($db) {
    $query = "SELECT rm_order_identifier FROM Orders WHERE rm_tracking_number IS NULL AND rm_order_identifier IS NOT NULL";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sendCustomerShippedEmail($order_id, $db, $m) {
    require_once(base_path("../secure/mailauth/ut.php"));
    try {
        $query = "SELECT Orders.*, Customers.*
        FROM Orders
        JOIN Customers ON Orders.customer_id = Customers.customer_id
        WHERE Orders.order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $order = $result[0];
        $query = "SELECT
        Items.name,
        Order_items.amount,
        Order_items.order_price
        FROM `Order_items`
        JOIN `Items` ON `Order_items`.`item_id` = `Items`.`item_id`
        WHERE `Order_items`.`order_id` = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items_to_send = [];
        foreach ($items as $item) {
            $order["items"][] = [
                "name" => $item["name"],
                "amount" => $item["amount"]
            ];
        }
        p_2($order);
    } catch (PDOException $e) {
        throw new Exception($e);
    }
    $email = $m->render("customerShippedEmail", ["order"=>$order]);

    // mail auth
    $from_name = "Unbelievable Truth shop";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mail_auth['host'];
    $mail->SMTPAuth = true;
    $mail->SMTPKeepAlive = false; //SMTP connection will not close after each email sent, reduces SMTP overhead
    $mail->Port = 25;
    $mail->Username = $mail_auth['username'];
    $mail->Password = $mail_auth['password'];
    $mail->setFrom($mail_auth['from']['address'], $from_name);
    $mail->addReplyTo($mail_auth['reply']['address'], $from_name);

    $mail->msgHTML($email);
    // $mail->addAddress($order['email']);
    $mail->addAddress("nigel@thesadsongco.com", "Nigel");
    $mail->send();
    sleep(2);
}