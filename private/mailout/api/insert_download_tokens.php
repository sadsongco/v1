<?php

include_once(__DIR__."/../../../../secure/scripts/ut_m_connect.php");
include_once(__DIR__."/includes/make_unique_token.php");

try {
    $query = "SELECT * FROM ut_mailing_list ORDER BY date_added";
    $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
foreach ($result as $row) {
    $token = makeUniqueToken($row['email_id'], $row['email']);
    try {
        $query = "INSERT INTO download_tokens VALUES (NULL, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$row['email_id'], $token]);
    }
    catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            continue;
        }
        echo "Error inserting tokens: " . $e->getMessage();
        exit();
    }

}

echo "Tokens Inserted";