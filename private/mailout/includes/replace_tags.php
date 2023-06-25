<?php

function replace_tags($body_template, $row) {
    $secure_id = hash('ripemd128', $row['email'].$row['email_id'].'AndyJasNigel');
    $row['secure_id'] = $secure_id;
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $protocol .= 's';
    $row['host'] = "$protocol://".$_SERVER['HTTP_HOST'];
    foreach ($row as $tag_name=>$tag_content) {
        if ($tag_name == 'name' && $tag_content == '') $tag_content = 'Music Friend';

        echo "tag name: $tag_name<br>";
        echo "tag content: $tag_content<br>";
        echo "tag to replace: ";
        echo htmlspecialchars("<!--{{".$tag_name."}}-->")."<br>";

        $body_template = str_replace("<!--{{".$tag_name."}}-->", $tag_content, $body_template);
    }
    return $body_template;
}
?>