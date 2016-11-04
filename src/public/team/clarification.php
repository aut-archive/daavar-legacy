<?php

/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require_once('init.php');

$title = 'Clarifications';

require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');

$id = getRequestID();


// insert a request (if posted)
if (isset($_POST['submit']) && !empty($_POST['bodytext'])) {
    // Disallow problems that are not submittable or
    // before contest start.
    if (!problemVisible($_POST['problem'])) $_POST['problem'] = 'general';

    $newid = $DB->q('RETURNID INSERT INTO clarification
	                 (cid, submittime, sender, probid, body)
	                 VALUES (%i, %s, %i, %i, %s)',
        $cid, now(), $teamid,
        ($_POST['problem'] == 'general' ? NULL : $_POST['problem']),
        $_POST['bodytext']);

    auditlog('clarification', $newid, 'added');

    // redirect back to the original location
    //header('Location: ./');
    //exit;

    $clar_sent = true;
}


$requests = $DB->q('SELECT c.*, p.shortname, t.name AS toname, f.name AS fromname
                    FROM clarification c
                    LEFT JOIN problem p USING(probid)
                    LEFT JOIN team t ON (t.teamid = c.recipient)
                    LEFT JOIN team f ON (f.teamid = c.sender)
                    WHERE c.cid = %i AND c.sender = %i
                    ORDER BY submittime DESC, clarid DESC', $cid, $teamid);

$clarifications = $DB->q('SELECT c.*, p.shortname, t.name AS toname, f.name AS fromname,u.teamid!=0 AS unread
                          FROM clarification c
                          LEFT JOIN problem p USING (probid)
                          LEFT JOIN team t ON (t.teamid = c.recipient)
                          LEFT JOIN team f ON (f.teamid = c.sender)
                          LEFT JOIN team_unread u ON (c.clarid=u.mesgid AND u.teamid = %i)
                          WHERE c.cid = %i AND c.sender IS NULL
                          AND ( c.recipient IS NULL OR c.recipient = %i )
                          ORDER BY c.submittime DESC, c.clarid DESC',
    $teamid, $cid, $teamid);

?>

<div class="container main-container">

    <h1>Clarifications</h1>

    <div class="row">
        <div class="pull-right">
            <?php $clar_action = isset($id) ? "Reply" : "New Clarification"; ?>
            <?php if (isset($id)) { ?>
                <div class="btn btn-info" onclick="document.location='clarification'">
                    Back to clarifications list
                </div>
            <?php } ?>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#sendClarModal">
                <?php echo $clar_action ?>
            </button>
        </div>
    </div>
    <br>

    <?php if (!isset($id)) { ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Clarification Requests</h3>
            </div>
            <div class="panel-body">
                <?php
                if ($requests->count() == 0)
                    echo "<p class=\"nodata\">No clarification requests.</p>\n\n";
                else
                    putClarificationList($requests, $teamid);
                ?>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Clarifications</h3>
            </div>
            <div class="panel-body">
                <?php
                if ($clarifications->count() == 0)
                    echo '<p class=\"nodata\">No clarifications.</p>';
                else
                    putClarificationList($clarifications, $teamid);
                ?>
            </div>
        </div>


        <?php
    } else {
        $req = $DB->q('MAYBETUPLE SELECT * FROM clarification
	               WHERE cid = %i AND clarid = %i', $cid, $id);
        if (!$req) error("clarification $id not found");
        if (!canViewClarification($teamid, $req)) {
            error("Permission denied");
        }
        $myrequest = ($req['sender'] == $teamid);
        $respid = empty($req['respid']) ? $id : $req['respid'];
        ?>

        <?php putClarification($respid, $teamid); ?>

    <?php } ?>

</div>

<?php require(LIBWWWDIR . '/footer.php'); ?>

<?php if (isset($clar_sent)) : ?>
    <script>
        $.jGrowl("Your clarification has been sent !", {corners: 120});
    </script>
<?php endif ?>


<div class="modal fade" id="sendClarModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo $clar_action ?></h4>
            </div>
            <div class="modal-body">
                <?php putClarificationForm("clarification", $cid, $id); ?>
            </div>
        </div>
    </div>
</div>
