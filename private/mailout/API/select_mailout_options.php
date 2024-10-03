<?php

require_once('includes/mailout_includes.php');

$content_dir = "../assets/content";
$mailoutOptions = [];

if ($handle = opendir($content_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if (substr($entry, 0, 1) != ".")
        array_push($mailoutOptions, str_replace(".txt", "", $entry));
    }

    closedir($handle);
}

rsort($mailoutOptions);

echo $m->render("selectMailoutOptions", ["options"=>$mailoutOptions]);