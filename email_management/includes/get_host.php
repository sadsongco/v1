<?php

function getHost() {
    /**r eturn complete URL of server */
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
    $server = !isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] == '' ? "theexactopposite.uk" : $_SERVER['HTTP_HOST'];
    return "$protocol://$server";
}

?>