<?php

function replace_tags($body_template, $row) {
    $secure_id = hash('ripemd128', $row['email'].$row['email_id'].'AndyJasNigel');
    $row['secure_id'] = $secure_id;
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $protocol .= 's';
    if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] == '') $row['host'] = "https://unbelievabletruth.co.uk";
    else $row['host'] = "$protocol://".$_SERVER['HTTP_HOST'];
    foreach ($row as $tag_name=>$tag_content) {
        if ($tag_name == 'name' && $tag_content == '') $tag_content = 'Music Friend';
        $body_template = str_replace("<!--{{".$tag_name."}}-->", $tag_content, $body_template);
    }
    return $body_template;
}
?>