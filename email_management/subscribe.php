<?php

include_once("./includes/html_head.php");

require_once("../../secure/scripts/ut_m_connect.php");

$message = "<p>Mailing list subscription page. Please access this through the link in your email.</p>";

if (isset ($_POST['add_name']) && $_POST['add_name'] == "Add Your Name") {
    try {
        $stmt = $db->prepare("SELECT email_id FROM ut_mailing_list WHERE email=?;");
        $stmt->execute([$_POST['email']]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db_id = $result[0]['email_id'];
        $secure_id = hash('ripemd128', $_POST['email'].$db_id.'AndyJasNigel');
        if ($secure_id != $_POST['check']) {
            throw new PDOException('Bad Check Code', 1176);
        }
        $stmt = $db->prepare("UPDATE ut_mailing_list SET name=? WHERE email_id=? and email=?");
        $stmt->execute([$_POST['name'], $db_id, $_POST['email']]);
        $message = "<h2>Name updated!</h2>";
        $_GET['email'] = $_POST['email'];
        $_GET['check'] = $_POST['check'];
    }
    catch(PDOException $e) {
        if ($e->getCode() != 1176) {
            error_log($e->getMessage());
            $message = "<p>Sorry, there was a background error</p>";
        }
        else {
            $message =  '<h2>'.$e->getMessage().' - please make sure you have accessed this page through the add name form below</h2>';
        }
    }
}

elseif (isset($_GET['email']) && $_GET['email'] != '' && isset($_GET['check']) && $_GET['check'] != '') {
    try {
        $stmt = $db->prepare("INSERT INTO ut_mailing_list (email, name, domain, subscribed, date_added) VALUES (?, ?, SUBSTRING_INDEX(?, '@', -1, NOW()), ?);");
        $stmt->execute([$_GET['email'], '', $_GET['email'], 1]);
        $_GET['check'] = hash('ripemd128', $_GET['email'].$db->lastInsertId().'AndyJasNigel');
        $message = '<p>The email <span class = "email">'.$_GET['email'].'</span> has been added to the Unbelievable Truth mailing list.<br />';
    }
    catch(PDOException $e) {
        if ($e->getCode() != 23000) {
            error_log($e->getMessage());
            echo $e->getMessage();
            $message = "<p>Sorry, there was a background error</p>";}
        else {
            $stmt = $db->prepare("UPDATE ut_mailing_list SET subscribed=1 WHERE email=?");
            $stmt->execute([$_GET['email']]);
            $stmt = $db->prepare("SELECT email_id FROM test_mailing_list WHERE email=?");
            $stmt->execute([$_GET['email']]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $id = $result[0]['email_id'];
            $_GET['check'] = hash('ripemd128', $_GET['email'].$id.'AndyJasNigel');
            $message = '<p>That email is already on our list, thank you!</p>';
        }
    }
    $message .= 'If you would like to add your name to your email on the Unbeleivable Truth mailing list so we can be more polite when we contact you, feel free to do so here:<br />
        <form action = "'.$_SERVER['PHP_SELF'].'" method = "post">
            <input type = "text" name = "name" size = "30" placeholder = "your name" />
            <input type="submit" name="add_name" value="Add Your Name" />
            <input type="hidden" name="check" value = "'.$_GET['check'].'" />
            <input type="hidden" name="email" value = "'.$_GET['email'].'" />
        </form>
        <footer>
            If you want to unsubscribe click &nbsp;<a href="http://unbelievabletruth.co.uk/email_management/unsubscribe.php?email='.$_GET['email'].'&check='.$_GET['check'].'">HERE</a><br />
        </footer>
';
}

require_once("../../secure/scripts/ut_disconnect.php");

echo $message;

include_once("./includes/html_foot.php");
?>
