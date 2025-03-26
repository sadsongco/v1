<?php

require("../../../../secure/env/config.php");

require(__DIR__ . "/classes/EmailParser.php");
include(__DIR__ . "/includes/order_includes.php");

use Parser\EmailParser;

$dom = new DOMDocument();
if ($mbox=imap_open( IMAP_CONFIG::AUTHHOST, IMAP_CONFIG::USERNAME, IMAP_CONFIG::PASSWORD )) {
        $output = "Processing orders...<br>";
    $headers = imap_headers($mbox);
    $msgs = imap_check($mbox);
    if ($msgs->Nmsgs == 0) {
            exit("No new orders");
    }
    $headers = imap_fetch_overview($mbox,"1:{$msgs->Nmsgs}",0);
    foreach ($headers as $id=>$header) {
            $subject = imap_mime_header_decode($header->subject);
            if (strtolower($subject[0]->text) != "new order") continue;
        $message = imap_fetchbody($mbox, $id + 1, '2');
        $message = imap_utf8(imap_qprint($message));
        try {
                $orderDetailObj = new EmailParser($message, $id);
                $orderDetailObj->parse();
                $orderDetailObj->rearrayItems();
                $order_details = $orderDetailObj->get();
                try {
                        insertOrderIntoDatabase($order_details, $db);
                } catch (Exception $e) {
                        error_log($e);
                        $output .= "Couldn't insert order " . $order_details['order_no'] . " into database: " . $e->getMessage() . "<br>";
                }
                $output .= "Order " . $order_details['order_no'] . " inserted into database.<br>";
                // imap_delete($mbox, $id + 1);
                $output .= "Email for order " . $order_details['order_no'] . " deleted.<br>";
        }
        catch (Exception $e) {
                error_log($e);
                $output .= $e->getMessage() . "<br>";
        }
    }
    imap_close($mbox, CL_EXPUNGE);
    header ('HX-Trigger:updateOrderList');
    echo $output;
} else {
        for ($id = 1; $id < 1000; $id++) {
                if (!$message) break;
                $orderDetailObj = new EmailParser($message, $id);
                $orderDetailObj->parse();
                $order_details = $orderDetailObj->get();        }
}

function insertOrderIntoDatabase($order_details, $db) {
        try {
                if ($order_details['country'] == "United States") {
                        $zip = getZipFromPostcode($order_details['postcode'], 'us');
                        $order_details['country'] = "USA";
                        $order_details['postcode'] = $zip['places'][0]['state abbreviation'] . " " . $zip['post code'];
                        $order_details['town'] = $zip['places'][0]['place name'];
                }

                if ($order_details['city'] == "") {
                        $query = "SELECT country_code FROM countries WHERE name = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$order_details['country']]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);

                        $country_code = $result['country_code'];
                        if ($country_code != "") {
                                $zip = getZipFromPostcode($order_details['postcode'], $country_code);
                                $order_details['postcode'] = $zip['post code'];
                                $order_details['town'] = $zip['places'][0]['place name'];
                        }
                }
                $customer_id = checkIfCustomerExists($order_details['email'], $db); 
                if (!$customer_id) $customer_id = insertNewCustomer($order_details, $db);
                $order_details['customer_id'] = $customer_id;
                foreach ($order_details['items'] as &$item) {
                    $item_id = checkIfItemExists($item['item'], $db);
                    if (!$item_id) {
                        $item_id = insertNewItem($item['item'], $item['price'], $db);
                        $item['item_id'] = $item_id;
                    }
                    else $item['item_id'] = $item_id;
                }
        }
        catch (Exception $e) {
                throw new Exception($e);
        }
        $db->beginTransaction();
        try {
                $order_details['order_id'] = insertOrderIntoOrderTable($order_details, $db);
                foreach ($order_details['items'] as $order_item) {
                        insertItemIntoOrderTable($order_details, $order_item, $db);
                }

        } catch (Exception $e) {
                $db->rollback();
                error_log($e);
                exit("Database update failed: " . $e->getMessage());
        }
        // $db->rollback();
        $db->commit();
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
                return $db->lastInsertId();
        } catch (Exception $e) {
                throw new Exception($e);
        }
}

function checkIfItemExists($item_name, $db) {
    try {
        $query = "SELECT item_id FROM Items WHERE name LIKE ?";
        $stmt = $db->prepare($query);
        $stmt->execute(['%'. trim($item_name) . '%']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) return false;
        return $result['item_id'];
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
        return $db->lastInsertId();
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

function insertOrderIntoOrderTable($order_details, $db) {
        try {
        $query = "INSERT INTO Orders VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, 0, NULL, ?, 0, NULL, NULL, NULL)";
        $params = [
                $order_details['order_no'],
                $order_details['customer_id'],
                $order_details['postage_method'],
                $order_details['totals']['Subtotal'],
                $order_details['totals']['Shipping'],
                $order_details['totals']['vat'],
                $order_details['totals']['total'],
                $order_details['order_date']
        ];
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $db->lastInsertId();
        } catch (Exception $e) {
                error_log($e);
                throw new Exception($e);
        }
}

function insertItemIntoOrderTable($order_details, $item, $db) {
        try {
                $query = "INSERT INTO Order_items VALUES (
                NULL,
                ?,
                ?,
                ?,
                ?);";
                $params = [
                        $order_details['order_id'],
                        $item['item_id'],
                        $item['amount'],
                        $item['price']
                ];
                $stmt = $db->prepare($query);
                $stmt->execute($params);
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

function getZipFromPostcode($postcode, $country_code='us') {
        preg_match('/\d{5}(-\d{4})?\b/', $postcode, $zip);
        $endpoint = "https://api.zippopotam.us/$country_code/";
        $ch = curl_init($endpoint . $zip[0]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $place_details = json_decode($response, true);
        // $postcode = $json->places[0]->place_name;
        return $place_details;
}

function to_utf8( $string ) {

        // From http://w3.org/International/questions/qa-forms-utf-8.html
        
            if ( preg_match('%^(?:
        
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
        
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
        
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
        
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        
        )*$%xs', $string) ) {
        
                return $string;
        
            } else {
        
                return iconv( 'CP1252', 'UTF-8', $string);
        
            }
        
        }