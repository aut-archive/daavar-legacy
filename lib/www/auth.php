<?php
/**
 * This file provides all functionality for authenticating teams. The
 * authentication method used is configured with the AUTH_METHOD
 * variable. When a team is succesfully authenticated, $username is set
 * to the team ID and $teamdata contains the corresponding row from
 * the database. $ip is set to the remote IP address used.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

$ip = $_SERVER['REMOTE_ADDR'];

$teamid = NULL;
$username = NULL;
$teamdata = NULL;
$userdata = NULL;

// Check if current user has given role, or has superset of this role's
// privileges
function checkrole($rolename, $check_superset = TRUE)
{
    global $userdata;
    if (empty($userdata) || !array_key_exists('roles', $userdata)) {
        return false;
    }
    if (in_array($rolename, $userdata['roles'])) return true;
    if ($check_superset) {
        if (in_array('admin', $userdata['roles']) &&
            ($rolename != 'team' || $userdata['teamid'] != NULL)
        ) {
            return true;
        }
    }
    return false;
}

// Returns whether the connected user is logged in, sets $username, $teamdata
function logged_in()
{
    global $DB, $ip, $username, $teamid, $teamdata, $userdata;

    if (!empty($username) && !empty($userdata) && !empty($teamdata)) return TRUE;

    // Retrieve userdata for given AUTH_METHOD, assume not logged in
    // when userdata is empty:
    switch (AUTH_METHOD) {
        case 'FIXED':
            $username = FIXED_USER;
            $userdata = $DB->q('MAYBETUPLE SELECT * FROM user WHERE username = %s AND enabled = 1', $username);
            break;
        case 'EXTERNAL':
            if (empty($_SERVER['REMOTE_USER'])) {
                $username = $userdata = null;
            } else {
                $username = $_SERVER['REMOTE_USER'];
                $userdata = $DB->q('MAYBETUPLE SELECT * FROM user WHERE username = %s AND enabled = 1', $username);
            }
            break;

        case 'IPADDRESS':
            $userdata = $DB->q('MAYBETUPLE SELECT * FROM user WHERE ip_address = %s AND enabled = 1', $ip);
            break;

        case 'PHP_SESSIONS':
        case 'LDAP':
            if (session_id() == "") session_start();
            if (isset($_SESSION['username'])) {
                $userdata = $DB->q('MAYBETUPLE SELECT * FROM user WHERE username = %s AND enabled = 1',
                    $_SESSION['username']);
            }
            break;

        default:
            error("Unknown authentication method '" . AUTH_METHOD . "' requested.");
    }

    if (!empty($userdata)) {
        $username = $userdata['username'];
        $teamdata = $DB->q('MAYBETUPLE SELECT * FROM team WHERE teamid = %i AND enabled = 1', $userdata['teamid']);

        // Pull the list of roles that a user has
        $userdata['roles'] = get_user_roles($userdata['userid']);
    }

    if (!empty($teamdata)) {
        $teamid = $teamdata['teamid'];
        // Is this the first visit? Record that in the team table.
        if (empty($teamdata['teampage_first_visited'])) {
            $hostname = gethostbyaddr($ip);
            $DB->q('UPDATE team SET teampage_first_visited = %s, hostname = %s
			        WHERE teamid = %i',
                now(), $hostname, $teamid);
        }
    }

    return $username !== NULL;
}

// Returns whether the active authentication method has logout functionality.
function have_logout()
{
    switch (AUTH_METHOD) {
        case 'FIXED':
            return FALSE;
        case 'EXTERNAL':
            return FALSE;
        case 'IPADDRESS':
            return FALSE;
        case 'PHP_SESSIONS':
            return TRUE;
        case 'LDAP':
            return TRUE;
    }
    return FALSE;
}

// Generate a page stating that login has failed with $msg and exit.
function show_failed_login($msg)
{
    show_loginpage($msg);
    exit;
}

// This function presents some kind of login page, which should e.g.
// have a POST form to supply login credentials. This function does
// not return.
function show_loginpage($err_message = null)
{
    global $ip, $pagename;

    switch (AUTH_METHOD) {
        case 'EXTERNAL':
            if (empty($_SERVER['REMOTE_USER'])) {
                show_failed_login("No authentication information provided by Apache.");
            } else {
                show_failed_login("User '" . htmlspecialchars($_SERVER['REMOTE_USER']) . "' not authorized.");
            }
        case 'IPADDRESS':
        case 'PHP_SESSIONS':
        case 'LDAP':
            include(LIBWWWDIR . '/header.php');
            ?>


                <div class="container">

                    <form class="form-signin" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                        <h3 class="form-signin-heading">Welcome to <span>AUTJudge</span></h3>
                        <br>

                        <?php if ($err_message) echo "<div class='alert alert-error'>$err_message</div>" ?>
                        <br>

                        <input type="hidden" name="cmd" value="login"/>

                        <input type="text" class="input-block-level" placeholder="Username" id="login" name="login"
                               value="" size="15" maxlength="15" accesskey="l"
                               autofocus>

                        <input type="password" class="input-block-level" type="password" id="passwd" name="passwd"
                               value="" size="15" maxlength="255"
                               accesskey="p" placeholder="Password">

                        <div style="text-align: center;">
                            <button class="btn btn-success" type="submit">Sign in</button>
                        </div>

                        <?php if (AllowSignup): ?>
                            <br><a href="signup">New user?</a>
                        <?php endif ?>
                        <?php if (AllowForget): ?>
                            <br><a href="forget">Forget your password?</a>
                        <?php endif ?>

                    </form>


                </div>




            <?php
            include(LIBWWWDIR . '/footer.php');
            break;

        default:
            error("Unknown authentication method '" . AUTH_METHOD .
                "' requested, or login not supported.");
    }

    exit;
}

// Check LDAP user and password credentials by trying to login to
// the LDAP server(s).
function ldap_check_credentials($user, $pass)
{
    foreach (explode(' ', LDAP_SERVERS) as $server) {

        // The connection may only be really established when needed,
        // so execute a dummy query to test if the server is available:
        $conn = @ldap_connect($server);
        if (!$conn || !ldap_get_option($conn, LDAP_OPT_PROTOCOL_VERSION, $dummy)) {
            continue;
        }

        /*
                // The following options are necessary to be able to talk
                // to an Active Directory:
                if ( !ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3) ) {
                    error("Failed to set protocol version to 3.");
                }

                if ( !ldap_set_option($conn, LDAP_OPT_REFERRALS, 0) ) {
                    error("Failed to set LDAP_OPT_REFERRALS.");
                }

                if ( !ldap_set_option($conn, LDAP_OPT_DEREF, 0) ) {
                    error("Failed to set LDAP_OPT_DEREF.");
                }
        */

        // Create the dn
        $ldap_dn = str_replace('&', $user, LDAP_DNQUERY);

        // Try to login to test credentials
        if (@ldap_bind($conn, $ldap_dn, $pass)) {
            @ldap_unbind($conn);
            return TRUE;
        }
    }
    return FALSE;
}

// Try to login a team with e.g. authentication data POST-ed. Function
// does not return and should generate e.g. a redirect back to the
// referring page.
function do_login($dont_redirect = false)
{
    global $DB, $ip, $username, $userdata;

    switch (AUTH_METHOD) {
        // Generic authentication code for IPADDRESS and PHP_SESSIONS;
        // some specializations are handled by if-statements.
        case 'IPADDRESS':
        case 'PHP_SESSIONS':
            $user = trim($_POST['login']);
            $pass = trim($_POST['passwd']);

            $title = 'Authenticate user';
            $menu = false;

            if (empty($user) || empty($pass)) {
                show_failed_login("Please supply a username and password.");
            }
            do_login_native($user, $pass);

            if (AUTH_METHOD == 'IPADDRESS') {
                $cnt = $DB->q('RETURNAFFECTED UPDATE user SET ip_address = %s
				       WHERE username = %s', $ip, $username);
                if ($cnt != 1) error("cannot set IP for '$username'");
            }
            if (AUTH_METHOD == 'PHP_SESSIONS') {
                session_start();
                $_SESSION['username'] = $username;
                auditlog('user', $userdata['userid'], 'logged in', $ip);
            }
            break;

        case 'LDAP':
            $user = trim($_POST['login']);
            $pass = trim($_POST['passwd']);

            $title = 'Authenticate user';
            $menu = false;

            if (empty($user) || empty($pass)) {
                show_failed_login("Please supply a username and password.");
            }

            $userdata = $DB->q('MAYBETUPLE SELECT * FROM user
		                    WHERE username = %s AND enabled = 1', $user);

            if (!$userdata ||
                !ldap_check_credentials($userdata['username'], $pass)
            ) {
                sleep(1);
                show_failed_login("Invalid username or password supplied. " .
                    "Please try again or contact a staff member.");
            }


            $username = $userdata['username'];

            session_start();
            $_SESSION['username'] = $username;
            auditlog('user', $userdata['userid'], 'logged in', $ip);
            break;
        case 'EXTERNAL':
            if (empty($_SERVER['REMOTE_USER'])) {
                show_failed_login("No authentication data provided by Apache.");
            }
            break;

        default:
            error("Unknown authentication method '" . AUTH_METHOD .
                "' requested, or login not supported.");
    }

    // Authentication success. We could just return here, but we do a
    // redirect to clear the POST data from the browser.
    $DB->q('UPDATE user SET last_login = %s, last_ip_address = %s
	                    WHERE username = %s',
        now(), $ip, $username);


    if (!$dont_redirect) {

        require_once 'scoreboard.php';

        logged_in(); // fill userdata

        $cdata = getCurContest(TRUE);
        if(checkrole('team') && $cdata['enabled'] && !calcFreezeData($cdata)['cstarted']) {
            header('Location: ../public/countdown');
            exit;
        }

        if (preg_match('/\/public\/login.*$/', $_SERVER['PHP_SELF'])) {
            if (checkrole('jury') || checkrole('balloon')) {
                header("Location: ../admin/");
                exit;
            } else if (checkrole('team')) {
                header("Location: ../team/");
                exit;
            }
        }
        header("Location: ./");
        exit;
    }
}

function do_login_native($user, $pass)
{
    global $DB, $userdata, $username, $teamid;

    $userdata = $DB->q('MAYBETUPLE SELECT * FROM user
			    WHERE username = %s AND password = %s AND enabled = 1',
        $user, md5($user . "#" . $pass));

    if (!$userdata) {
        sleep(1);
        show_failed_login("Invalid username or password supplied. " .
            "Please try again or contact a staff member.");
    } else if (empty($teamdata)) {

        $iteamdata = $DB->q('MAYBETUPLE SELECT * FROM team WHERE teamid = %i', $userdata['teamid']);

        if ($iteamdata && !$iteamdata['enabled'])
            show_failed_login("Your account is pending for approval");
    }

    $username = $userdata['username'];
}

// Logout a team. Function does not return and should generate a page
// showing logout and optionally refer to a login page.
function do_logout()
{
    global $DB, $ip, $username, $userdata;

    switch (AUTH_METHOD) {
        case 'PHP_SESSIONS':
        case 'LDAP':

            // Check that a session exists:
            if (session_id() == "") session_start();

            // Unset all of the session variables.
            $_SESSION = array();

            // Also delete the session cookie.
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]);
            }

            // Finally, destroy the session.
            if (!session_destroy()) error("PHP session not successfully destroyed.");
            break;

        default:
            error("Unknown authentication method '" . AUTH_METHOD .
                "' requested, or logout not supported.");
    }

    auditlog('user', @$userdata['userid'], 'logged out', $ip);

    header('HTTP/1.1 302 Please see this page');
    header('Location: ..');

    exit;
}

function get_user_roles($userid)
{
    global $DB;
    return $DB->q('COLUMN SELECT role.role FROM userrole LEFT JOIN role ON userrole.roleid = role.roleid WHERE userrole.userid = %s', $userid);
}
