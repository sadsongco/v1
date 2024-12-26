<?php

include_once(__DIR__."/make_unique_token.php");
include_once(__DIR__."/../../../../email_management/includes/get_host.php");

define("DOWNLOAD_URL", getHost()."/API/download.php");

function generateDownloadLink($email, $email_id) {
    $u_token = makeUniqueToken($email_id, $email);
    return DOWNLOAD_URL."?email=".$email."&u_token=".$u_token;
}