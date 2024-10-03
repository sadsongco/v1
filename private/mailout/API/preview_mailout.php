<?php

require_once('includes/mailout_includes.php');
require_once('includes/mailout_create.php');

if (isset($_GET['preview_mailout'])) {
    $content = explode("\n", $_GET['content']);
    $subject = $_GET['subject'];
    $heading = $_GET['heading'];
    $remove_path = null;
} else {
    // paths to email data
    $content_path = "../assets/content/";
    $remove_path = '/email_management/unsubscribe.php';
    $subject_id = "[UNBELIEVABLE TRUTH]";
    
    $current_mailout = $_GET['mailout'];
    
    if ($current_mailout == '') exit("Select a mailout to preview...");
    
    $content = file($content_path.$current_mailout.".txt");
    
    $subject = $subject_id.array_shift($content);
    $heading = array_shift($content);
}

$email = "previewemail@preview.com";
$id = 1;
require(__DIR__."/../../../../secure/secure_id/secure_id_ut.php");
$secure_id = generateSecureId($email, $id);
$text_template = createTextBody($content);
$html_template = createHTMLBody($content);
$host = getHost();

$text_body = $m->render("textTemplate", ["heading"=>$heading, "content"=>$text_template, "host"=>$host, "remove_path"=>$remove_path, "name"=>"Preview Name", "email"=>$email, "secure_id"=>$secure_id]);
$html_body = $m->render("htmlTemplate", ["heading"=>$heading, "content"=>$html_template, "host"=>$host, "remove_path"=>$remove_path, "name"=>"Preview Name", "email"=>$email, "secure_id"=>$secure_id]);

echo $m->render('mailoutPreview', ["text_body"=>$text_body, "html_body"=>$html_body, "subject"=>$subject]);