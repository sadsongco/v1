<?php

include_once(__DIR__."/../../../../secure/env/config.php");
include_once("includes/p_2.php");

$headers = [
    "Authorization: Bearer " . SU_API_KEY
];

$trans_url = "https://api.sumup.com/v2.1/merchants/MCCHLZ27/transactions/history?limit=1&order=descending&changes_since=2025-01-01";
$trans_url = "https://api.sumup.com/v0.1/me";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $trans_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$res = curl_exec($ch);
curl_close($ch);

$transactions = json_decode($res);

// foreach ($transactions->items as &$transaction) {
//     if ($transaction->status != "SUCCESSFUL") continue;
//     $cust_url = "https://api.sumup.com/v0.1/checkouts/{$transaction->transaction_id}";
    
//     $ch = curl_init();$ch = curl_init();
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_URL, $cust_url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
//     $res = curl_exec($ch);
//     curl_close($ch);
    
//     $transaction->checkout = json_decode($res);
// }

p_2($transactions);