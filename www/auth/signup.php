<?php
/**
 * Provide login functionality.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */


require_once('init.php');
session_start();
require(LIBWWWDIR . '/header.php');

if (!AllowSignup) {
    echo "Signup is not allowed !";
    require(LIBWWWDIR . '/footer.php');
    exit;
}


define('ID_REGEX', '/^' . IDENTIFIER_CHARS . '+$/');


if (isset($_REQUEST['dosignup'])) {

    $msg = false;

    $teamname = @$_REQUEST['teamname'];
    $email = @$_REQUEST['email'];
    $username = @$_REQUEST['username'];
    $password = @$_REQUEST['password'];
    $members = @$_REQUEST['members'];
    $captcha = @$_REQUEST['captcha'];

    $IP = $_SERVER['REMOTE_ADDR'];

    $enabled = SignupAutoEnable ? '1' : '0';

    $description = @$_REQUEST['description'];
    if (!isset($description))
        $description = '';


    if (!isset($teamname) || !isset($username) || !isset($email) || !isset($password) || !isset($members) || !isset($captcha)) {
        $msg = 'Please fill all required fields';
        goto showmessage;
    }

    if (!isset($_SESSION['captcha']) || $captcha != $_SESSION['captcha']) {
        $msg = 'Invalid answer to security question!';
//        $msg.=$captcha.'/'.$_SESSION['captcha'];
        goto showmessage;
    }

    if ($DB->q("SELECT * FROM team WHERE name=%s", $teamname)->count() != 0) {
        $msg = 'Another team with that name already exists !';
        goto showmessage;
    }

    if (!preg_match(ID_REGEX, $username)) {
        $msg = "Username may only contain characters " . IDENTIFIER_CHARS . ".";
        goto showmessage;
    }

    if ($DB->q("SELECT * FROM user WHERE username=%s", $username)->count() != 0) {
        $msg = 'Username already used !';
        goto showmessage;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Invalid email !';
        goto showmessage;
    }

    if ($DB->q("SELECT * FROM user WHERE email=%s", $email)->count() != 0) {
        $msg = 'Email already used !';
        goto showmessage;
    }

    createUserAndTeam($username, $teamname, $password, $email, SignupDefaultCategory, SignupDefaultUserRole, $enabled, $members, $description, $IP);

    showmessage:
    unset($_SESSION['captcha']);
    ?>

    <div id="login">
        <div class="container">
            <div class="form-signin">
                <?php if (!$msg): ?>
                    Successfully signed up !<br><br>
                    <?php if (!SignupAutoEnable): ?>
                        Your account is pending for approval<br><br>
                    <?php endif ?>
                    <div style="text-align: center;">
                        <button class="btn btn-success" onclick="document.location='login'">Sign in</button>
                    </div>
                <?php else: ?>
                    An error occurred during signup :<br><br>
                    <?php echo $msg ?>
                    <br><br><br>
                    <button class="btn btn-primary" onclick="history.back()"> Try again
                    </button>
                <?php endif ?>
            </div>
        </div>
    </div>

    <?php
    require(LIBWWWDIR . '/footer.php');
    exit;
}

?>

<div id="login">
    <div class="container">
        <form class="form-signin" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
            <input type="hidden" name="dosignup"/>

            <h3 class="form-signin-heading">Signup</h3>
            <br>

            <?php if ($err_message) echo "<div class='alert alert-error'>$err_message</div>" ?>
            <br>

            <div class="control-group">
                <label class="control-label" for="email">*Username:</label>

                <div class="controls">
                    <input class="form-control input-xlarge" id="username" name="username" type="text"
                           required="required"
                           placeholder="only letters,numbers and _" pattern="^[a-zA-Z0-9_-]+$">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="password">*Password :</label>

                <div class="controls">
                    <input class="form-control input-xlarge" id="password" name="password" type="password"
                           required="required"
                           placeholder="choose a strong one">
                </div>
            </div>


            <div class="control-group">
                <label class="control-label" for="email">*Email:</label>

                <div class="controls">
                    <input class="form-control input-xlarge" id="email" name="email" type="email" required="required"
                           placeholder="Your Email">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="teamname">*Team name :</label>

                <div class="controls">
                    <input class="form-control input-xlarge" id="teamname" name="teamname" type="text"
                           required="required"
                           placeholder="something cool">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="members">*Members :</label>

                <div class="controls">
                    <textarea class="form-control input-xlarge" id="members" name="members" type="text" rows="3"
                              required="required" placeholder="who are you?!"></textarea>
                </div>
            </div>


            <div class="control-group">
                <label class="control-label" for="description">Description:</label>

                <div class="controls">
                    <input class="form-control input-xlarge" id="description" name="description" type="text"
                           placeholder="affiliation or ...">
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
                    <input class="form-control input-xlarge" id="captcha" name="captcha" type="text" placeholder=""
                           required="required" pattern="^\d+$">
                </div>
            </div>

            <br>

            <div style="text-align: center;">
                <button class="btn btn-success" type="submit">Signup</button>
            </div>
            <br><a href="login">Already signed up ?</a>
        </form>


    </div>

</div>

<?php
require(LIBWWWDIR . '/footer.php');
?>

