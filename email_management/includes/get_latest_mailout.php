<?php

function getLatestMailout() {
    $latest_mailout = 0;
    if ($handle = opendir('../private/mailout/assets/content')) {
        while (false !== ($entry = readdir($handle))) {
            if (substr($entry, 0, 1) != ".") {
                $mailout_id = explode('.', $entry)[0];
                if ($mailout_id == 'test') continue;
                if ((int)$mailout_id > $latest_mailout) $latest_mailout = (int)$mailout_id;
            }
        }
        closedir($handle);
    }
    return $latest_mailout;
}