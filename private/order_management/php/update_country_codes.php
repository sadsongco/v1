<?php

include_once(__DIR__."/includes/order_includes.php");

function readCSV($csvFile){
    $file_handle = fopen($csvFile, 'r');
    $data = [];
    while (!feof($file_handle) ) {
        $row = fgetcsv($file_handle, 1024);
        if (!$row) break;
            $data[] = $row;
    }
    fclose($file_handle);
    return $data;
}


// Set path to CSV file
$csvFile = base_path('private/order_management/assets/csv/country-region-codes.csv');

$csv = readCSV($csvFile);

foreach ($csv as $row) {
    $query = "SELECT country_id FROM Countries WHERE name = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$row[0]]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (sizeof($result) > 0) {
        $country_id = $result[0]['country_id'];
        $query = "UPDATE Countries SET country_code = ?, country_code_3 = ? WHERE country_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$row[1], $row[2], $country_id]);
        echo "UPDATED COUNTRY " . $row[0] . "<br>\n";
        continue;
    }
    $query = "INSERT INTO Countries VALUES (NULL, ?, 1, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$row[0], $row[1], $row[2]]);
    echo "INSERTED COUNTRY " . $row[0] . "<br>\n";
}

// manual cleanup
$query = 'UPDATE Countries SET `country_code` = "PT", `country_code_3` = "PRT" WHERE `name` = "Azores" OR `name` = "Madeira"';
$db->query($query);
$query = 'UPDATE Countries SET `country_code` = "ES", `country_code_3` = "ESP" WHERE `name` = "Balaeric Islands" OR `name` = "Canary Islands"';
$db->query($query);
$query = 'UPDATE Countries SET `country_code` = "FR", `country_code_3` = "FRA" WHERE `name` = "Corsica"';
$db->query($query);

// p_2($csv);
