<?php

include_once(__DIR__."/../../../secure/scripts/ut_m_connect.php");
include_once(__DIR__."/api/includes/make_unique_token.php");

try {
    $query = "SELECT * FROM ut_mailing_list WHERE email LIKE '%debbiejclare@gmail.com%';";
    $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

var_dump($result);

$token = makeUniqueToken($result[0]['email_id'], $result[0]['email']);

$query = "INSERT INTO download_tokens VALUES (NULL, ?, ?)";
$stmt = $db->prepare($query);
$stmt->execute([$result[0]['email_id'], $token]);

echo $token;