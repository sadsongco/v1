<?php

try {
    $fp = fopen('current_mailout_tssc.txt', 'w');
    fwrite($fp, $_GET['mailout']);
    fclose($fp);
}
catch (Exception $e) {
    echo "ERROR";
    exit();
}

echo "SUCCESS";

?>