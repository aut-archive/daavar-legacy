<?php
global $refresh,$notify_flag;

$notify_flag = isset($_COOKIE["domjudge_notify"]) && (bool)$_COOKIE["domjudge_notify"];
$refresh_flag = !isset($_COOKIE["domjudge_refresh"]) || (bool)$_COOKIE["domjudge_refresh"];

if (isset($refresh)) {
    echo addForm('toggle_refresh.php', 'get') .
        addHidden('enable', ($refresh_flag ? 0 : 1)) .
        addSubmit(($refresh_flag ? 'Dis' : 'En') . 'able refresh', 'toggle_refresh', null, true, 'class=""') .
        addEndForm();
}
// Default hide this from view, only show when javascript and
// notifications are available:
echo '<div id="notify" style="display: none">' .
    addForm('toggle_notify.php', 'get') .
    addHidden('enable', ($notify_flag ? 0 : 1)) .
    addSubmit(($notify_flag ? 'Dis' : 'En') . 'able notifications', 'toggle_notify',
        'return toggleNotifications(' . ($notify_flag ? 'false' : 'true') . ')', true, 'class=""') .
    addEndForm() . "</div>";
?>

<div style="clear: both"></div>
<script type="text/javascript">
    if ('Notification' in window)
        document.getElementById('notify').style.display = 'inline';
</script>