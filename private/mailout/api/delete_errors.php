<?php

require_once('includes/mailout_includes.php');

$mailing_list = $_GET['mailing_list'];
if ($mailing_list == "") exit('Mailing list error');

try {
    $query = "DELETE FROM $mailing_list WHERE error = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([1]);
    $rows_affected = $stmt->rowCount();
}
catch (PDOException $e) {
    exit ("Database error: ".$e->getMessage());
}

$plural = $rows_affected > 1 ? "s" : "";

header("HX-Trigger: errorsDeleted");
echo $m->render('message', ["msg"=>"$rows_affected error email$plural deleted"]);