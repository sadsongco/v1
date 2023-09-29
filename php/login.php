<?php

// database
require_once("../../secure/scripts/ut_a_connect.php");

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
    'loader' => new Mustache_Loader_FilesystemLoader('../templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('../templates/partials')
));

try {
    $auth->login($_POST['email'], $_POST['password']);
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