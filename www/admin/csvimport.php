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
<!--    <h2 class="alert-danger">WARNING: BETA AND OBSOLETE FEATURE!</h2>-->
    <br><br>
<?php if (isset($_REQUEST['removal']) && isset($list_tag)): ?>

    <div>
        <?php
        //====================================Tag removal====================================

        global $DB;
        $DB->q('DELETE FROM `team` WHERE `comments` LIKE %s', "%[$list_tag]%");

        ?>

        <br><a class="btn btn-success" href="autoimport">Done !</a>
    </div>

<?php else : if (isset($list_tag) && isset($list_file)) : ?>

    <div class="container">
        <br>
        <?php
        //====================================Import====================================
        $list = file_get_contents($list_file);

        $IP = $_SERVER['REMOTE_ADDR'];
        $all_usernames = array('admin', 'judgehost', 'judger', 'judge', 'test');

        foreach (explode("\n", $list) as &$user_str) {
            $user = str_getcsv($user_str);
            if ($user[0][0] == '#') continue;

            $comments = "";
            $username = $user[0];
            while (in_array($username, $all_usernames))
                $username = $username . rand(0, 9);
            array_push($all_usernames, $username);
            $full_name = $user[1];
            $teamname = $user[1];
            $affilid = intval($user[2]);
            $password = substr(base64_encode(md5(time() . $username . 'DUMMYYY')), 0, 6);

            echo "Adding team $teamname<br>";

            $team_id = createTeam($full_name, SignupDefaultCategory, true, "", "[$list_tag] $comments", $IP, $affilid);

            echo "Adding user for team $team_id<br>";

            createUser($username, $password, "", $team_id, true, $full_name, $IP, SignupDefaultUserRole, false);
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
