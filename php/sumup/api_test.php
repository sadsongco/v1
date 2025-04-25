<?php

include("../includes/base_path.php");
include("../includes/p_2.php");

require(base_path("../secure/env/config.php"));


$headers = [
    "Authorization: Bearer " . SU_API_KEY,
    "Content-Type: application/json"
];

$trans_url = "https://api.sumup.com/v0.1/checkouts";
// $trans_url = "https://api.sumup.com/v0.1/me";

$post_body = json_encode([
    'checkout_reference' => 'AAA123',
    'merchant_code' => 'MDKGXUMF',
    'currency' => 'GBP',
    'amount' => 4
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_URL, $trans_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
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