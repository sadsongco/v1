<?php

$current_mailout_file = "current_mailout.txt";

try {
    $fp = fopen($current_mailout_file, 'w');
    fwrite($fp, $_POST['mailout']);
    fclose($fp);
}
catch (Exception $e) {
    echo "ERROR";
    exit();
}

echo "Mailout <span class='underline'>".$_POST['mailout']."</span> set to send";