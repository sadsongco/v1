<?php

include_once(__DIR__."/../../../../../secure/secure_id/secure_id_ut.php");
include_once(__DIR__."/replace_download_link.php");

function generateMailoutEmailContent($replacements, $data) {
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../../assets/templates'),
        'partials_loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../../assets/templates/partials')
    ));

    $secure_id = generateSecureId($data['email'], $data['email_id']);
    $replacements['name'] = $data['name'];
    $replacements['email'] = $data['email'];
    $replacements['secure_id'] = $secure_id;
    $replacements = replaceDownloadLink($replacements, $data);
    $text_body = $m->render("textTemplate", $replacements);
    $html_body = $m->render("htmlTemplate", $replacements);


    
    return [
        "text_body"=>$text_body,
        "html_body"=>$html_body,
        "subject"=>$replacements["subject"]
    ];        

}