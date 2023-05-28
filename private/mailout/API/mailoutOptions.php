<?php

$mailoutOptions = [];

if ($handle = opendir('./mailout_bodies/html')) {
    while (false !== ($entry = readdir($handle))) {
        if (substr($entry, 0, 1) != ".")
        array_push($mailoutOptions, $entry);
    }

    closedir($handle);
}

echo json_encode($mailoutOptions);

?>