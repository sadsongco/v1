<?php

include_once(__DIR__."/mailout_create.php");

function generateMailoutContent($raw_content, $subject_id) {
    $subject = $subject_id.array_shift($raw_content);
    $heading = array_shift($raw_content);
    $text_content = createTextBody($raw_content);
    $html_content = createHTMLBody($raw_content);
    return [
        "subject"=>$subject,
        "heading"=>$heading,
        "text_content"=>$text_content,
        "html_content"=>$html_content,
        "host"=>"",
        "remove_path"=>"",
        "name"=>"",
        "email"=>"",
        "secure_id"=>"",
        "download_link"=>""
    ];
}