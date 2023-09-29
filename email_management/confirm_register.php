<?php

// database
require_once("../../secure/scripts/ut_a_connect.php");

// templating system
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader('../php/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader('../php/templates/partials')
));

// auth
require __DIR__ . '/../php/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

function addEmailToList($email) {
    require_once("../../secure/scripts/ut_m_connect.php");
    echo "Add Email $email To List";

    try {
        $stmt = $db->prepare("INSERT INTO ut_mailing_list (email, name, domain, subscribed, confirmed, date_added) VALUES (?, ?, SUBSTRING_INDEX(?, '@', -1), ?, ?, NOW());");
        $stmt->execute([$email, '', $email, 1, 1]);
        $_GET['check'] = hash('ripemd128', $email.$db->lastInsertId().'AndyJasNigel');
    }
    catch(PDOException $e) {
        if ($e->getCode() == 23000) return;
        error_log($e->getMessage());
    }

    require_once("../../secure/scripts/ut_disconnect.php");
}

try {
    // $auth->confirmEmailAndSignIn($_GET['selector'], $_GET['token']);
    addEmailToList($auth->getEmail());
    echo $m->render('emailConfirmed');
}
catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
    die('Invalid token');
}
catch (\Delight\Auth\TokenExpiredException $e) {
    die('Token expired');
}
catch (\Delight\Auth\UserAlreadyExistsException $e) {
    die('Email address already exists');
}
catch (\Delight\Auth\TooManyRequestsException $e) {
    die('Too many requests');
}

require_once("../../secure/scripts/ut_disconnect.php");

?>