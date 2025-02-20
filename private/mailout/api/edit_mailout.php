<?php

require_once('includes/mailout_includes.php');

// paths to email data
$content_path = "../assets/content/";
$remove_path = '/email_management/unsubscribe.php';

$current_mailout = $_GET['id'];

if ($current_mailout == '') exit("Select a mailout to preview...");

$raw_content = file($content_path.$current_mailout.".txt");

[$subject, $heading, $body] = parseContent($raw_content);

$filename = $_GET['id'] ?? date("ymd");

echo $m->render("createMailout", ["filename"=>$filename, "id"=>$_GET['id'], "subject"=> $subject, "heading"=>$heading, "body"=>$body]);

function parseContent($content) {
    $subject = array_shift($content);
    $heading = array_shift($content);
    return [
        $subject,
        $heading,
        implode("", $content)
    ];
}