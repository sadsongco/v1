<?php

include (__DIR__."/../../php/includes/p_2.php");

$filename = "progress.txt";

if (!file_exists($filename)) $fp = fopen($filename, "w+");
else $fp = fopen($filename, "r+");

if (filesize($filename) == 0 && !isset($_SESSION)) {
    fclose($fp);
    exit("NO PROGRESS");
}

if (filesize($filename) == 0 && isset($_SESSION)) {
    fwrite($fp, p_2($_SESSION));
}

$output = fread($fp, filesize($filenname));
fclose($fp);

echo $output;

?>