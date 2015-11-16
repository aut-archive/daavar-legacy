<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');

$title = 'Import / Export';
require(LIBWWWDIR . '/header.php');

requireAdmin();


$list_tag = @$_REQUEST['list_tag'];
$list_file = @$_FILES['list_file']['tmp_name'];

?>

    <h1>CSV import</h1>
    <h2 class="alert-danger">WARNING: BETA AND OBSOLETE FEATURE!</h2>
    <br><br>
<?php if (isset($_REQUEST['removal']) && isset($list_tag)): ?>

    <div>
        <?php
        //====================================Tag removal====================================

        global $DB;
        $DB->q('DELETE FROM `team` WHERE `comments` LIKE %s', "%[$list_tag]%");

        ?>

        <br><a class="btn btn-success" href="autoimport.php">Done !</a>
    </div>

<?php else : if (isset($list_tag) && isset($list_file)) : ?>

    <div>
        <?php
        //====================================Import====================================
        $list = file_get_contents($list_file);

        $l_teamname = NULL;
        $l_team_users = array();
        $l_team_mambers = '';
        $IP = $_SERVER['REMOTE_ADDR'];

        $all_usernames = array('admin', 'judgehost', 'judger', 'judge', 'test');


        foreach (explode("\n", $list) as &$user_str) {
            $user = str_getcsv($user_str);
            if ($user[0][0] == '#') continue;

            $affilid = NULL; //$user[0];
            $teamname = $user[1];
            $full_name = $user[2];
            $email = $user[3];
            $comments = $user[4];

            //Gen!
            $username = explode('@', $email)[0];
            while (in_array($username, $all_usernames))
                $username = $username . rand(0, 9);


            array_push($all_usernames, $username);

            $password = substr(base64_encode(md5(time() . $username . 'DUMMYYY')), 0, 6);


            if ($l_teamname != NULL && $teamname != $l_teamname) {
                echo "Adding team $teamname<br><br>\r\n";
                $team_id = createTeam($l_teamname, SignupDefaultCategory, false, $l_team_mambers, "[$list_tag] $comments", $IP, $affilid);

                while ($u = array_pop($l_team_users)) {
                    echo "Adding $full_name from $teamname@$affilid , username : $username<br>\r\n";
                    createUser($u[0], $u[1], $u[2], $team_id, true, $u[3], $IP, SignupDefaultUserRole, true /*send email*/);
                }

                $l_team_mambers = '';
                $l_team_users = array();
            }

            $l_team_mambers .= "$full_name\r\n";
            array_push($l_team_users, array($username, $password, $email, $full_name));
            $l_teamname = $teamname;
        }

        ?>


        <br><a class="btn btn-success" href="csvimport">Done !</a>


    </div>
    <?php
else:
    //====================================Show form====================================
    ?>

    <a href="../assets/templates/list.csv">Download Sample CSV File</a><br><br>

    <form class='form-horizontal' method="post" enctype="multipart/form-data">
        <input type="text" placeholder="TAG" id="tag" name="list_tag" required="required">
        <input type="file" class="" name="list_file" id="file" required="required">
        <input type="submit" class="btn btn-success" value="Import from CSV">
    </form>

    <br>

    <form class='form-horizontal' method="post" enctype="multipart/form-data">
        <input type="hidden" name="removal" value="true">
        <input type="text" placeholder="TAG" id="tag" name="list_tag" required="required">
        <input type="submit" class="btn btn-danger" value="REMOVE ALL">
    </form>

<?php endif; endif; ?>


<?php
require(LIBWWWDIR . '/footer.php');
