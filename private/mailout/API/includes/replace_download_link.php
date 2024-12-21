<?php

include(__DIR__."/generate_download_link.php");

function replaceDownloadLink($replacements, $data) {
    $text_array = explode("/n", $replacements["text_content"]);
    $html_array = explode("/n", $replacements["html_content"]);
    $download_url = generateDownloadLink($data['email'], $data['email_id']);
    $html_link = '<a href="'.$download_url.'" target="_blank">Click Here!</a>';
    foreach ($text_array as &$line) {
        $line = preg_replace('/{{download}}([^}]*){{\/download}}/', $download_url, $line);
    }
    foreach ($html_array as &$line) {
        $line = preg_replace('/{{download}}([^}]*){{\/download}}/', $html_link, $line);
    }
    $replacements["text_content"] = implode("\n", $text_array);
    $replacements["html_content"] = implode("\n", $html_array);
    return $replacements;
}