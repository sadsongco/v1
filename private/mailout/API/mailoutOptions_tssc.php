<?php

$mailoutOptions = [];

if ($handle = opendir('../assets/mailout_bodies/tssc/html')) {
    while (false !== ($entry = readdir($handle))) {
        if (substr($entry, 0, 1) != ".")
        array_push($mailoutOptions, $entry);
    }

    closedir($handle);
}

echo json_encode($mailoutOptions);

?>