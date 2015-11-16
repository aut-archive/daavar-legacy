<?php
/**
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require('init.php');
$title = 'Submit Solution';

require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');

// Don't use HTTP meta refresh, but javascript: otherwise we cannot
// cancel it when the user starts editing the submit form. This also
// provides graceful degradation without javascript present.
$refreshtime = 30;

$langdata = $DB->q('KEYTABLE SELECT langid AS ARRAYKEY, name, extensions
		    FROM language WHERE allow_submit = 1');

$probdata = $DB->q('TABLE SELECT probid, shortname, name FROM problem
	                    WHERE cid = %i AND allow_submit = 1
	                    ORDER BY shortname', $cid);

$fdata = calcFreezeData($cdata);

$id = @intval(@$_GET['id']);

$maxfiles = 1; //dbconfig_get('sourcefiles_limit', 100);

echo "<script type=\"text/javascript\">";
putgetMainExtension($langdata);
echo "initReload(" . $refreshtime . ");\n";

echo "</script>";

echo addForm('upload.php', 'post', null, 'multipart/form-data', null,
        ' onreset="resetUploadForm(' . $refreshtime . ', ' . $maxfiles . ');" class="form-horizontal"') .
    "<p id=\"submitform\">\n\n";

$probs = array();

if ($fdata['cstarted'])
    foreach ($probdata as $probinfo)
        if (!$id || $id == $probinfo['probid'])
            $probs[$probinfo['probid']] = $probinfo['shortname'] . ' : ' . $probinfo['name'];
if (!$id)
    $probs[''] = '<select>'; //'problem';

foreach ($langdata as $langid => $langdata)
    $langs[$langid] = $langdata['name'];
$langs[''] = '<select>'; //'language';

?>

<div class="container main-container">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Submit Solution</h4>
            </div>

            <div class="modal-body">
                <div class="control-group">
                    <label class="control-label" for="probid">Problem :</label>

                    <div class="controls">
                        <?php echo addSelect('probid', $probs, $id, true, ''); ?>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="langid">Language : </label>

                    <div class="controls">
                        <?php echo addSelect('langid', $langs, '', true);; ?>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="langid">File : </label>

                    <div class="controls">
                        <input type=file name='code[]' id='maincode' class='input-file uniform_on' required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">

                <?php if (isset($id) && $id): ?>
                    <a class="pull-left" href="submissions?id=<?php echo $id ?>">View submissions</a>
                <?php endif ?>

                <?php
                echo addSubmit('Submit', 'submit',
                    "return checkUploadForm();", true, 'class="btn  btn-primary"');
                echo "</p>\n</form>\n\n";
                ?>
                <script>
                    initFileUploads(<?php echo $maxfiles?>);
                </script>
            </div>
        </div>
    </div>
</div>


</div>


<?php
require(LIBWWWDIR . '/footer.php');
?>
