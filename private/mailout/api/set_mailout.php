<?php

include_once("includes/mailout_includes.php");

$test = isset($_POST['test_mailout']); 

$current_mailout_file = "current_mailout.txt";
$body = $_POST['mailout'];
if ($test) $body = "test:" .  $body;

try {
    $fp = fopen($current_mailout_file, 'w');
    fwrite($fp, $body);
    fclose($fp);
}
catch (Exception $e) {
    echo "ERROR";
    exit();
}

echo "Mailout <span class='underline'>".$_POST['mailout']."</span> set to send";
if ($test) echo " to test mailing list";