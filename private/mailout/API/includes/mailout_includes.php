<?php

require_once('../../../../secure/scripts/ut_m_connect.php');

require '../../../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader('../assets/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('../assets/templates/partials')
));

include_once("../../../email_management/includes/get_host.php");

function getMailoutData($id, $db) {
    try {
        $query = "SELECT * FROM mailouts WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
        throw new Exception("Problem retrieving mailout data: ".$e->getMessage());
    }
    return $result;
}

function getCurrentMailout($db, $id)
{
    try {
        $query = "SELECT DATE_FORMAT(date, '%Y%m%d') AS `date` FROM mailouts WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['date'];
    } catch (PDOException $e) {
        exit("Couldn't retrieve current mailout: ".$e->getMessage());
    }
}

function p_2($input) {
    echo "<pre>"; print_r($input); echo "</pre>";
}