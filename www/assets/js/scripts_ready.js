var lastUnread = -1;
var first_unread_reload = true;
function reloadUnread() {
    try {
        $.ajax('menu?ajax=unread').success(function (data) {
            data *= 1;
            if (data > 0) {
                $('#unread').html(data);
                $('#unread').fadeIn();
                if (lastUnread != data) {
                    lastUnread = data;
                    if (!first_unread_reload) {
                        $.jGrowl('New Clarification!');
                    } else {
                        first_unread_reload = false;
                    }
                }
            } else {
                $('#unread').fadeOut();
            }
        });
    }
    catch (e) {
    }
    setTimeout(reloadUnread, 3000);
}


function reloadSubmissions() {
    try {
        var uri = '?ajax=submissions&id=';
        var id = query['id'] * 1;
        if (id > 0)
            uri += '&id=' + id;
        if (query.hasOwnProperty('all'))
            uri += '&all';
        $.ajax(uri).done(function (data) {
            $('#submissions_block').html(data);
            $('#last_submissions_update').html('Last update:' + new Date().toTimeString());
        });
    } catch (e) {
    }
    CheckReloadSubmissions();
}

function CheckReloadSubmissions() {
    if (typeof reload_submissions_enabled !== 'undefined' && reload_submissions_enabled) {
        setTimeout(reloadSubmissions, 5000);
        $('#last_submissions_update').fadeIn();
    } else {
        $('#last_submissions_update').fadeOut();
    }
}

CheckReloadSubmissions();

//Parse uri
var query = {};
location.search.substr(1).split("&").forEach(function (item) {
    query[item.split("=")[0]] = item.split("=")[1]
});


//$('#clock').countdown({
//    until: remainingTime,
//    expiryUrl: document.URL,
//    layout: '{hn}:{mn}:{sn}'
//});
//$('#clock_container').prop('title','Remaining to '+remaining_msg+' contest');
//$('#clock_container').addClass('badge-'+remaining_style);


if (typeof(doReloadUnread) != 'undefined')
    reloadUnread();
