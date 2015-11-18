<?php

require_once('init.php');

if (!AllowForget) {
    echo "Action is not allowed !";
    require(LIBWWWDIR . '/footer.php');
    exit;
}

if (isset($_REQUEST['reset'])) {
    sleep(1);

    $ok = false;
    $req = @$_REQUEST['request'];

    $i = 1;

    if (!isset($req))
        goto showmessage2;

    $req = base64_decode($req);
    if (!isset($req))
        goto showmessage2;

    $req = explode('-', $req);
    if (count($req) != 2)
        goto showmessage2;

    session_id($req[0]);
    if (!session_start())
        goto showmessage2;

    if (strlen($req[1]) != 32)
        goto showmessage2;

    if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_id']) || !isset($_SESSION['reset_username'])
        || $_SESSION['reset_code'] != $req[1]
    )
        goto showmessage2;

    //It seems every thing is OK :D
    $reset_username = $_SESSION['reset_username'];
    $newpassword = substr(md5('JUNKJUNK' . rand(500, 5000) . $username), 3, 9);
    $newPasswordHashed = md5($_SESSION['reset_username'] . '#' . $newpassword);

    $DB->q('UPDATE user SET password=%s WHERE userid=%i', $newPasswordHashed, intval($_SESSION['reset_id']));

    unset($_SESSION['reset_code']);
    unset($_SESSION['reset_id']);
    unset($_SESSION['reset_username']);
    session_destroy();
    $ok = true;

    //Try to login
    session_start();
    $_POST['login'] = $reset_username;
    $_POST['passwd'] = $newpassword;
    do_login(true);

    showmessage2:
    require(LIBWWWDIR . '/header.php');
    ?>

    <div class="container">
        <div class="modal modal-visible">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Reset password
                        </h4>
                    </div>
                    <div class="modal-body">

                        <?php if (!$ok) : ?>
                            <br>Oops !<br>
                            <br><br><br>
                            <button class="btn btn-primary" onclick="document.location='forget'">Reset my password
                            </button>
                        <?php else : ?>
                            <h3>Your password has been successfully changed !</h3>
                            <br><br>
                            Username : <b><?php echo $reset_username ?></b><br>
                            Your new password is : <b><?php echo $newpassword ?></b><br>
                            <br><br>

                            <div style="text-align: center;">
                                <a href="../team/edit_profile">Go to my profile</a>
                            </div>
                            <br>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require(LIBWWWDIR . '/footer.php');
    exit;
}

session_start();
require(LIBWWWDIR . '/header.php');
$pagename = $currPage;
define('IS_JURY', false);
define('IS_PUBLIC', true);

if (isset($_REQUEST['sendCode'])) {
    sleep(1);
    $msg = false;

    $email = @$_REQUEST['email'];
    $captcha = @$_REQUEST['captcha'];

    if (!isset($email) || !isset($captcha)) {
        $msg = 'Please fill all required fields';
        goto showmessage;
    }

    if (!isset($_SESSION['captcha']) || $captcha != $_SESSION['captcha']) {
        $msg = 'Invalid answer to security question!';
        goto showmessage;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Invalid email !';
        goto showmessage;
    }

    $user = $DB->q("SELECT * FROM user WHERE email=%s", $email)->next();

    if (!$user) {
        $msg = 'No user found with that email address ...';
        goto showmessage;
    }

    //Generate random code and send it to user
    $_SESSION['reset_code'] = md5('MAGIC#' . rand(10000, 100000));
    $_SESSION['reset_id'] = $user['userid'];
    $_SESSION['reset_username'] = $user['username'];

    //Generate password reset link
    $magic_code = session_id() . '-' . $_SESSION['reset_code'];
    $url = site_url . 'auth/forget?reset&request=' . base64_encode($magic_code);

    $full_name =& $user['name'];
    $email_content = "
        <br>Hello $full_name,
        <br>Someone requested to reset your password
        <br>If it wasn't you just ignore this email and don't do anything.
        <br>But if it were you, click on link below to reset your password :
        <br><br><div style='text-align: center;'><a href='$url'>Reset my password</a></div>
    ";

    JsendEmail($email_content, 'Reset password', $user['email']);

    showmessage:
    unset($_SESSION['captcha']);
    ?>


    <div class="container">
        <div class="container">
            <div class="modal modal-visible">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                Reset password
                            </h4>
                        </div>
                        <div class="modal-body">
                            <?php if (!$msg): ?>
                                Instructions to reset your password has been sent to your email address.<br><br>
                                Check your inbox (this email may be moved to spam folder)<br><br>
                                <br>
                            <?php else: ?>
                                An error occurred :<br><br>
                                <?php echo $msg ?>
                                <br><br><br>
                                <button class="btn btn-primary" onclick="history.back()"> Try again
                                </button>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require(LIBWWWDIR . '/footer.php');
    exit;
} //End send code

?>

<div class="container">

    <div class="modal modal-visible">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        Reset password
                    </h4>
                </div>
                <div class="modal-body">
                    <form class="form-signin" action="forget" method="post">
                        <input type="hidden" name="sendCode"/>
                        <br>
                        <?php if ($err_message) echo "<div class='alert alert-error'>$err_message</div>" ?>
                        <br>

                        <div class="control-group">
                            <label class="control-label" for="email">* Registered email:</label>

                            <div class="controls">
                                <input class="form-control" id="email" name="email" type="email" required="required"
                                       placeholder="Your Email">
                            </div>
                        </div>

                        <?php
                        //Security question
                        $a = rand(10, 50);
                        $b = rand(15, 40);
                        $c = $a + $b;
                        $_SESSION['captcha'] = $c;
                        $question = "$a + $b = ?";
                        ?>
                        <div class="control-group">
                            <label class="control-label noselect" for="captcha"><?php echo $question ?></label>

                            <div class="controls">
                                <input class="form-control" id="captcha" name="captcha" type="text" required="required"
                                       pattern="^\d+$">
                            </div>
                        </div>

                        <br>

                        <div style="text-align: center;">
                            <button class="btn btn-danger btn-block" type="submit">Reset my password</button>
                        </div>

                        <br>
                        <?php if (AllowSignup): ?>
                            <br><a href="signup">New user?</a>
                        <?php endif ?>
                        <br><a href="login">Did you remember your password ?</a>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

<?php
require(LIBWWWDIR . '/footer.php');
?>

