<?php

// database
require_once("../../secure/scripts/ut_a_connect.php");

include("includes/p_2.php");
define("REMEMBER_DURATION", (int) 60 * 60 * 24 * 30);

// auth
require __DIR__ . '/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

// templating
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/templates/partials')
));

try {
    $rememberDuration = null;
    if (isset($_POST["remember"]) && $_POST['remember'] == "on") {
        $rememberDuration = REMEMBER_DURATION;
    } 
    $auth->login($_POST['email'], $_POST['password'], $rememberDuration);
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $protocol .= 's';    $host = "$protocol://".$_SERVER['HTTP_HOST'];
    echo $m->render('userLoggedIn', ["username"=>$auth->getUsername(), "base_dir"=>$host]);
}
catch (\Delight\Auth\InvalidEmailException $e) {
    die('Wrong email address');
}
catch (\Delight\Auth\InvalidPasswordException $e) {
    die('Wrong password');
}
catch (\Delight\Auth\EmailNotVerifiedException $e) {
    die('Email not verified');
}
catch (\Delight\Auth\TooManyRequestsException $e) {
    die('Too many requests');
}

?>