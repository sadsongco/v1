<?php

error_reporting(E_ALL); // Error/Exception engine, always use E_ALL

ini_set('ignore_repeated_errors', TRUE); // always use TRUE

ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment

ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', './debug_log'); // Logging file path

require_once(__DIR__."/../../../secure/scripts/ut_m_connect.php");
require_once(__DIR__."/send_confirmation_email.php");
require_once(__DIR__."/add_email_to_db.php");

include_once(__DIR__.'/../../../secure/secure_id/secure_id_ut.php');
include_once(__DIR__.'/../../email_management/includes/get_host.php');
include_once(__DIR__.'/../../private/mailout/api/includes/replace_tags.php');

