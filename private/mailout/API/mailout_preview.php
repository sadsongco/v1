<?php

include_once('../includes/replace_tags.php');

$row = [];
$row['email'] = 'testemail@thesadsongco.com';
$row['name'] = 'Test Name';
$row['email_id'] = '123';

$preview_file = '../'.$_GET['prev'];

$template = file_get_contents($preview_file);

echo replace_tags($template, $row);

?>