<?php

require_once('includes/mailout_includes.php');

$current_mailout_file = './current_mailout.txt';
$current_mailout_contents = file_get_contents($current_mailout_file);
if ($current_mailout_contents == '') exit();
date_default_timezone_set('Europe/London');

// current mailout is set, carry on

// get mailout data
$test = false;
$mailout_arr = explode(":", $current_mailout_contents);
if ($mailout_arr[0] == "test") {
    $test = true;
    $current_mailout_id = $mailout_arr[1];
} else {
    $current_mailout_id = $mailout_arr[0];
}

$current_mailout = getCurrentMailout($db, $current_mailout_id);
// set other mailout variables
$remove_path = '/email_management/unsubscribe.php';
$subject_id = "[UNBELIEVABLE TRUTH]";
$mailing_list_table = $test ? "test_mailing_list" : "ut_mailing_list";
$log_dir =  $test ? './logs/test/' : './logs/';

// email variables
$from_name = "Unbelievable Truth mailing list";

/* *** INCLUDES *** */

require_once('includes/do_mailout.php');