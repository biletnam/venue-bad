function notices_initialize() {
    $('#navigation br').before("<div id='notices-callout'></div>");
    window.setInterval(notices_update_count, 1500);
}

var notices_count = -1;

function notices_update_count() {
    call_and_response({'command': 'notice_list_unacknowledged_count'}, response);

    function response(e) {
        if (notices_count * 1 !== e.responseText * 1) {
            notices_count = e.responseText * 1;
            notices_draw_update();
        }
    }
}

function notices_draw_update() {
    $('#navigation #notices-callout').html("<button>" + notices_count + "</button>");
    if (notices_count > 0) {
        $('#notices-callout button').button({icons: {primary: 'ui-icon-mail-open'}});
    } else {
        $('#notices-callout button').button({icons: {primary: 'ui-icon-mail-closed'}});
    }
    $('#notices-callout button').click(notices_list);
}

function notices_list() {
    if ($('.notice-list-unacknowledged').length) {
        $('.notice-list-unacknowledged').closest('.dialog-window').dialog('close').remove();
    }
    var selector = new_dialog("Notices", loading_results_html, 650, 400);
    call_and_response({'command': 'notice_list_unacknowledged'}, response);

    function response(e) {
        if (e.responseText === 'no_notices') {
            $(selector).dialog('close');
            toast("No new notices.");
        } else {
            $(selector).html(e.responseText);
            $(".notice-list-unacknowledged button.acknowledge").button({icons: {primary: 'ui-icon-check'}}).click(function (f) {
                var boffice_notice_id = $(f.currentTarget).attr('boffice_notice_id');
                $(f.currentTarget).button('disable');
                call_and_response({
                    'command': 'notice_acknowledge',
                    'boffice_notice_id': boffice_notice_id
                }, function (g) {
                    if (g.responseText === '1') {
                        $(f.currentTarget).closest('li').slideUp();
                    }
                    $(f.currentTarget).button('enable');
                });
            });
        }
    }
}
