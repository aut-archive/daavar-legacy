<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');

$title = 'Edit Profile';

require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');

if(isset($_REQUEST['save'])){

    $p1=@$_REQUEST['password'];
    $p2=@$_REQUEST['password2'];
    $email=@$_REQUEST['email'];

    if(isset($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Invalid email!';
            goto end_of_save;//Error
        }
        $DB->q('UPDATE user SET email=%s WHERE userid=%i',$email,$userdata['userid']);
        $userdata['email']=$email;
    }

    if(isset($p1) && isset($p2)) {
        if ($p1 != $p2) {
            $msg = 'Passwords does not match!';
            goto end_of_save;//Error
        }
        $hashed_passsword = md5($username . '#' . $p1);
        $DB->q('UPDATE user SET password=%s WHERE userid=%i',$hashed_passsword,$userdata['userid']);
    }

    $msg='Everything saved !';

   end_of_save:
}

?>

    <div class="container main-container">
        <div class="block-content collapse in">
            <h2>Edit profile</h2>
            <br><br>

            <div>

                <script>
                    var password_input = function () {
                        var p = document.getElementById('password').value;
                        if (p.length > 0)
                            $('#password2').fadeIn();
                        else
                            $('#password2').fadeOut();
                    };
                </script>

                <form class="form-horizontal" method="post">
                    <input type="hidden" name="save">

                    <div class="control-group">
                        <label class="control-label" for="username">
                            Username
                        </label>

                        <div class="controls">
                            <input type="text" name="" disabled="disabled" id="username" value="<?php echo $username?>">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="email">
                            Email
                        </label>

                        <div class="controls">
                            <input type="email" name="email" id="email" value="<?php echo $userdata['email']?>">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="password">
                            Password
                        </label>

                        <div class="controls">
                            <input type="password" name="password" id="password" placeholder="unchenged"
                                   onkeyup="password_input()">
                            <input type="password" name="password2" id="password2" placeholder="repeat"
                                   style="display: none;">
                        </div>

                    </div>

                    <div class="form-actions">
                        <input type="submit" class="btn btn-success" value="Save">
                    </div>

                </form>

            </div>
        </div>
    </div>


<?php require(LIBWWWDIR . '/footer.php');?>

<?php if(isset($msg)) :?>
    <script>
        $.jGrowl('<?php echo $msg?>');
    </script>
<?php endif?>
