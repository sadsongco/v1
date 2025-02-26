<?php

require_once('includes/mailout_includes.php');
require_once('includes/mailout_create.php');
p_2($_GET);
die();
$subject_id = "[UNBELIEVABLE TRUTH]";
if (isset($_GET['preview_mailout'])) {
    $subject = $_GET['subject'];
    $heading = $_GET['heading'];
    $raw_content = "$subject\n$heading\n".$_GET['content'];
    $content = explode("\n", $raw_content);
    $remove_path = null;
} else {
    // paths to email data
    $content_path = "../assets/content/";
    $remove_path = '/email_management/unsubscribe.php';
    
    $current_mailout = $_GET['mailout'];
    
    if ($current_mailout == '') exit("Select a mailout to preview...");
    
    $content = file($content_path.$current_mailout.".txt");
}

$email = "previewemail@preview.com";
$id = 1;
require(__DIR__."/../../../../secure/secure_id/secure_id_ut.php");
include_once(__DIR__."/includes/generate_mailout_content.php");
include_once(__DIR__."/includes/generate_mailout_email_content.php");

$secure_id = generateSecureId($email, $id);
$replacements = generateMailoutContent($content, $subject_id);
$replacements['host'] = getHost();
$replacements['remove_path'] = $remove_path;

$data = ["name"=>"Preview Name", "email"=>$email, "email_id"=>$id];
$bodies = generateMailoutEmailContent($replacements, $data);

echo $m->render('mailoutPreview', $bodies);