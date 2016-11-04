<?php
/**
 * Upload form for documents to be sent to the printer.
 *
 * Part of the DOMjudge Programming Contest Jury System and licenced
 * under the GNU GPL. See README and COPYING for details.
 */

require_once('init.php');

$title = 'Print';
require(LIBWWWDIR . '/header.php');
require(LIBWWWDIR . '/forms.php');

?>

    <div class="container main-container">

        <h1>Print source</h1>


        <?php
        if (!have_printing()) {
            error("Printing disabled.");
        }

        // Seems reasonable to require that there's a contest running
        // before allowing to submit printouts.
        if (is_null($cid) || difftime($cdata['starttime'], now()) > 0) {
            echo "<p class=\"nodata\">Contest has not yet started.</p>\n";
            require(LIBWWWDIR . '/footer.php');
            exit;
        }

        if (isset($_POST['langid'])) {
            handle_print_upload();
        } else {
            put_print_form();
        }
        ?>
        
    </div>

<?php
require(LIBWWWDIR . '/footer.php');
