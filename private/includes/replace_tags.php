<?php

function replace_tags($body_template, $row) {
    $row['secure_id'] = $row['check'];
    $row['host'] = $_SERVER['HTTP_HOST'];
    foreach ($row as $tag_name=>$tag_content) {
        if ($tag_name == 'name' && $tag_content == '') $tag_content = 'Music Friend';
        $body_template = str_replace("<!--{{".$tag_name."}}-->", $tag_content, $body_template);
    }
    return $body_template;
}

?>