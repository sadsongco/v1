<?php

require_once('includes/mailout_includes.php');

try {
    $query = "SELECT id, DATE_FORMAT(date, '%Y%m%d') AS `date` FROM mailouts ORDER BY date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $mailouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit("Couldn't retrieve mailouts: ".$e->getMessage());
}

echo $m->render("selectMailoutOptions", ["options"=>$mailouts]);