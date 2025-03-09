<?php

include_once(__DIR__."/includes/order_includes.php");

function readCSV($csvFile){
    $file_handle = fopen($csvFile, 'r');
    $header_row = true;
    $headers = [];
    $data = [];
    while (!feof($file_handle) ) {
        if ($header_row) {
            $headers = fgetcsv($file_handle, 1024);
            $header_row = false;
        }
        $row = fgetcsv($file_handle, 1024);
        if (!$row) break;
        if ($row[0] ==  "Citizens Band - Limited Edition Vinyl EP") {
            $next_row = fgetcsv($file_handle, 1024);
            // p_2($next_row);
            $row = array_merge($row, $next_row);
            unset($row[34]);
        }
        if (sizeof($row) == sizeof($headers))
            $data[] = array_combine($headers, $row);
    }
    fclose($file_handle);
    return [$headers, $data];
}


// Set path to CSV file
$csvFile = base_path('private/order_management/assets/csv/items.csv');

[$headers, $csv] = readCSV($csvFile);

foreach ($csv as $row) {
    if ($row['Item name']) $current_item = $row['Item name'];
    else $row['Item name'] = $current_item . " - " . strtolower($row['Variations']);
    if ($id = itemExists($row['Item name'], $db)) {
        updateItem($row, $id, $db);
        continue;
    }
    addItem($row, $db);
}


function itemExists($name, $db) {
    $query = "SELECT item_id FROM Items WHERE name = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$name]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (sizeof($result) > 0) {
        return $result[0]['item_id'];
    }
    return false;
}

function updateItem($row, $id, $db) {
    p_2("UPDATE ITEM - " . $row['Item name']);
    try {
        $query = "UPDATE Items SET
            `description` = ?,
            `category` = ?,
            `image` = ?,
            `weight` = ?,
            `packaging_weight` = NULL
        WHERE item_id = ?";
        $stmt = $db->prepare($query);
        $params = [
            $row['Description (Online Store and Invoices only)'],
            $row['Category'],
            $row['Image 1'],
            (float)$row['Shipping weight [kg] (Online Store only)'],
            $id
        ];
        $stmt->execute($params);
    }
    catch (PDOException $e) {
        p_2("Error: " . $e->getMessage());
        p_2($query);
        p_2($params);
        exit();
    }
}

function addItem($row, $db) {
    p_2("ADD ITEM - " . $row['Item name']);
    $query = "INSERT INTO Items VALUES (
    NULL,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    NULL,
    NULL,
    NULL)";
    $params = [
        $row['Item name'],
        (float)$row['Price'],
        $row['Description (Online Store and Invoices only)'],
        $row['Category'],
        $row['Image 1'],
        (float)$row['Shipping weight [kg] (Online Store only)']
    ];
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
    }
    catch (PDOException $e) {
        p_2("Error: " . $e->getMessage());
        p_2($query);
        p_2($params);
        exit();
    }
}