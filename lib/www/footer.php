<script src="../assets/vendors/jquery.min.js"></script>

<script src="../assets/vendors/bootstrap/js/bootstrap.min.js"></script>

<script src="../assets/vendors/jquery.plugin.min.js"></script>

<script src="../assets/vendors/jquery.countdown.min.js"></script>
<script src="../assets/vendors/jGrowl/jquery.jgrowl.js"></script>

<script type="text/javascript" src="../assets/js/scripts_ready.js"></script>

</div>
</div>

<!--Notify-->
<?php if (IS_JURY) { ?>
    <div class="pull-right">
        <?php
        if (isset($refresh)) {
            echo addForm('toggle_refresh.php', 'get') .
                addHidden('enable', ($refresh_flag ? 0 : 1)) .
                addSubmit(($refresh_flag ? 'Dis' : 'En') . 'able refresh', 'toggle_refresh', null, true, 'class="btn btn-small"') .
                addEndForm();
        }
        // Default hide this from view, only show when javascript and
        // notifications are available:
        echo '<div id="notify" style="display: none">' .
            addForm('toggle_notify.php', 'get') .
            addHidden('enable', ($notify_flag ? 0 : 1)) .
            addSubmit(($notify_flag ? 'Dis' : 'En') . 'able notifications', 'toggle_notify',
                'return toggleNotifications(' . ($notify_flag ? 'false' : 'true') . ')', true, 'class="btn btn-small"') .
            addEndForm() . "</div>";
        ?>
    </div>
    <div style="clear: both"></div>
    <script type="text/javascript">
        if ('Notification' in window)
            document.getElementById('notify').style.display = 'inline';
    </script>
<?php } ?>
<!--/Notify-->

<footer class="footer">
    Powered by <a href="#"><?php echo productName ?> v<?php echo version ?></a> | Developed by <a
        href="mailto:pyapar@gmail.com">pooya parsa</a>
</footer>


</body>
</html>


<?php

if (file_exists('footer.custom.php'))
    require_once 'footer.custom.php';

?>