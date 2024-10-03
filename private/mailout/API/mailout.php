<?php

$current_mailout_file = './current_mailout.txt';
$current_mailout = file_get_contents($current_mailout_file);
if ($current_mailout == '') exit();

// current mailout it set, carry on

// paths to email data
$content_path = "../assets/content/";
$remove_path = '/email_management/unsubscribe.php';
$subject_id = "[UNBELIEVABLE TRUTH]";
$mailing_list_table = $current_mailout == "test" ? "test_mailing_list" : "ut_mailing_list";
$log_dir =  $current_mailout == "test" ? './logs/test/' : './logs/';

// email variables
$from_name = "Unbelievable Truth mailing list";

/* *** INCLUDES *** */

require_once('includes/mailout_includes.php');
require_once('includes/do_mailout.php');