<?php
/**
 * Approve teams
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');

require(LIBWWWDIR . '/header.php');

$id = @$_POST['id'];
if (isset($id)) {
    requireAdmin();

    $id = intval($id);
    $DB->q('UPDATE team SET enabled=TRUE WHERE teamid=%i', $id);

    //find team users
    $team_users = $DB->q('SELECT email,username FROM user WHERE teamid=%i', $id)->gettable();

    foreach ($team_users as &$team_user) {
        $email =& $team_user['email'];
        $username =& $team_user['username'];

        $msg = '
                    Your team has been approved in AUTJudge system :) <br>
                    You can now login :<br>
                    <a href="' . site_url . '">' . site_url . '</a><br><br>' . '
                    User name : ' . $username . '<br>
                    Password : [as you set]<br>
            ';

        JSendEmail($msg, 'ACM Contest - Team approved', $email);
    }

}


$teams = $DB->q('SELECT * FROM team WHERE enabled=FALSE')->gettable();

?>

    <h1>Teams awaiting for approval</h1>
    <br><br>

<?php if (count($teams) == 0): ?>
    <div class="nodata">No teams remaining</div>
<?php else: ?>

    <table class="table">
        <tr>
            <th>Team name</th>
            <th>Members</th>
            <th>Description</th>
            <th></th>
        </tr>
        <?php
        foreach ($teams as &$team) {
            echo "<tr>";
            echo '<td>' . $team['name'] . '</td>';
            echo '<td>' . $team['members'] . '</td>';
            echo '<td>' . $team['comments'] . '</td>';
            ?>

            <td>
                <?php if (IS_ADMIN) { ?>
                    <form action="approve.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $team['teamid'] ?>">
                        <input type="submit" class="btn btn-success" value="Approve">
                    </form>
                <?php } ?>
            </td>

            <?php echo "</tr>";
        }
        ?>

    </table>

<?php endif ?>

    <br>
<?php
require(LIBWWWDIR . '/footer.php');
