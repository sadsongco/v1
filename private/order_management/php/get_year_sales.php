<?php

include(__DIR__."/includes/order_includes.php");
include(__DIR__."/includes/make_report_pdf.php");

$date = new DateTime();
$year_ending = $year = (int)$date->format('Y');
$last_year = $year - 1;
$last_last_year = $last_year - 1;

$end_date = "05/04/";
$start_date = "06/04/";

$end_time = "23:59:59";
$start_time = "00:00:00";


$this_tax_year = $date->createFromFormat('d/m/Y H:i:s', $end_date . $year . " $end_time");

if ($date <= $this_tax_year) {
    $tax_year_start = $date->createFromFormat('d/m/Y H:i:s', $start_date . $last_last_year . " $start_time");
    $tax_year_end = $date->createFromFormat('d/m/Y H:i:s', $end_date . $last_year . " $end_time");
    $year_ending = $last_year;
} else {
    $tax_year_start = $date->createFromFormat('d/m/Y H:i:s', $start_date . $last_year . " $start_time");
    $tax_year_end = $date->createFromFormat('d/m/Y H:i:s', $end_date . $year . " $end_time");
    $year_ending = $year;
}

try {
    $query = "SELECT
        IFNULL(SUM(subtotal), 0) AS subtotal,
        IFNULL(SUM(shipping), 0) AS shipping,
        IFNULL(SUM(vat), 0) AS vat,
        IFNULL(SUM(total), 0) AS total
    FROM Orders
    WHERE order_date >= ?
    AND order_date <= ?";
    $params = [
        $tax_year_start->format('Y-m-d H:i:s'),
        $tax_year_end->format('Y-m-d H:i:s')
    ];
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}
$report = [
    "start_date" => $tax_year_start->format('d/m/Y'),
    "end_date" => $tax_year_end->format('d/m/Y'),
    "date" => $date->format('d/m/Y'),
    "year_ending" => $year_ending,
    "totals" => $result
];
makeReportPDF($report);