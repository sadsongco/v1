<?php

include_once(__DIR__."/../../../../secure/scripts/ut_m_connect.php");
include_once(__DIR__."/includes/make_unique_token.php");

$email = $_POST['email'];
try {
    $query = "SELECT `email_id` FROM ut_mailing_list WHERE `email` = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

if (sizeof($row) == 0) {
    echo "Email not found";
    exit();
}

$token = makeUniqueToken($row['email_id'], $_POST['email']);
try {
    $query = "INSERT INTO download_tokens VALUES (NULL, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$row['email_id'], $token]);
}
catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "TOKEN ALREADY EXISTS";
        exit();
    }
    echo "Error inserting tokens: " . $e->getMessage();
    exit();
}

echo "Token Inserted";