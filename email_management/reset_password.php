<?php


// database
require_once("../../secure/scripts/ut_a_connect.php");

// templating system
require '../lib/mustache.php-main/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../php/templates'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../php/templates/partials')
));

// auth
require __DIR__ . '/../php/vendor/autoload.php';
try {
    $auth = new \Delight\Auth\Auth($db);
}
catch (Exception $e) {
    die($e->getMessage());
}

if (isset($_POST["reset_password"]) && $_POST['password'] == $_POST['password_conf']) {
    try {
        $auth->resetPassword($_POST['selector'], $_POST['token'], $_POST['password']);
    
        echo 'Password has been reset';
    }
    catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
        die('Invalid token');
    }
    catch (\Delight\Auth\TokenExpiredException $e) {
        die('Token expired');
    }
    catch (\Delight\Auth\ResetDisabledException $e) {
        die('Password reset is disabled');
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
        die('Invalid password');
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
        die('Too many requests');
    }
    exit();
}
try {
    $auth->canResetPasswordOrThrow($_GET['selector'], $_GET['token']);

    echo $m->render("resetPWForm", ["selector"=>$_GET['selector'], "token"=>$_GET['token']]);
}
catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
    die('Invalid token');
}
catch (\Delight\Auth\TokenExpiredException $e) {
    die('Token expired');
}
catch (\Delight\Auth\ResetDisabledException $e) {
    die('Password reset is disabled');
}
catch (\Delight\Auth\TooManyRequestsException $e) {
    die('Too many requests');
}
catch (Exception $e) {
    die($e->getMessage());
}
require_once("../../secure/scripts/ut_disconnect.php");

?>