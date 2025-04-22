<?php

require_once('includes/mailout_includes.php');

function getCompletedEmails($db, $table, $current_mailout) {
    $all = null;
    $sent = null;
    try {
        $query = "SELECT COUNT(*) AS `total` FROM `$table`";
        $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $all = $result[0]['total'];
        $query = "SELECT COUNT(*) AS `sent` FROM `$table` WHERE `last_sent` = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$current_mailout]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sent = $result[0]['sent'];
        $query = "SELECT COUNT(*) AS `errors`, IF(COUNT(*) > 0, 1, NULL) AS `error_flag` FROM `$table` WHERE `error`=?";
        $stmt = $db->prepare($query);
        $stmt->execute([1]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $errors = $result[0];
    }
    catch (PDO_EXCEPTION $e) {
        echo "Error retrieving completed emails: ".$e->getMessage();
        return;
    }
    return ["total"=>$all, "sent"=>$sent, "errors"=>$errors];
}


$current_mailout_contents = file_get_contents("current_mailout.txt");

$sent = null;
$dd_sent = null;
$current_mailout = false;
$test = false;

if ($current_mailout_contents != "") {
    $mailout_arr = explode(":", $current_mailout_contents);
    if ($mailout_arr[0] == "test") {
        $test = true;
        $current_mailout_id = $mailout_arr[1];
    } else {
        $current_mailout_id = $mailout_arr[0];
    }
    $mailing_list = $test ? "test_mailing_list" : "ut_mailing_list";
    $current_mailout = getCurrentMailout($db, $current_mailout_id);
    $sent = getCompletedEmails($db, $mailing_list, $current_mailout);
    $sent['mailing_list'] = $mailing_list;
}

echo $m->render("selectMailout", ["current_mailout"=>$current_mailout, "test"=>$test, "sent"=>$sent]);