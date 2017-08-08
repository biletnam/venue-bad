var directory = '/boffice/';

var api_path = directory + "office/api.public.php";

$(function () {
    $('navigation>ul li').hover(
        function () { //appearing on hover
            $('ul', this).fadeIn();
        },
        function () { //disappearing on hover
            $('ul', this).fadeOut();
        }
    );
});

function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function overlay_block(message) {
    $('body').append("<div class='overlay-block'><div class='message'>" + message + "</div></div>");
    overlay_block_center();

    function overlay_block_center() {
        $('.overlay-block .message').position({
            my: 'center center',
            at: 'center center',
            of: window
        });
    }

    //$(overlay_block).height($('body').height());
    $(window).scroll(overlay_block_center);
    $(window).resize(overlay_block_center);
}

function overlay_block_remove() {
    $('.overlay-block').fadeOut(1000);
}


//==================================================
//		    SHARED			    
//==================================================
function call_and_response_public(query_array, response) {
    $.ajax({
        url: api_path,
        complete: complete_handle,
        type: 'POST',
        data: query_array,
        cache: false
    });

    function complete_handle(e) {
        if (e.responseText === "no_command") {
            toast("No such command: " + query_array.command);
        }
        if (e.responseText === 'no_session') {
            location.reload();
        }
        if (jQuery.isFunction(response)) {
            response(e);
        } else {
            $(response).html(e.responseText);
            dialog_might_have_ckeditor();
        }
    }
}

var window_count = 0;
var windows = [];

function new_dialog_public(title, body, w, h) {
    if (w === undefined) {
        w = $(document).width() * .3;
        w = Math.max(w, 470); //min size for forms
    }
    if (h === undefined) {
        h = $(document).height() * .7;
    }

    $('#content').append("<div id='window_" + (window_count) + "' class='dialog-window'>" + body + "</div>");
    var options = {
        title: title, width: w, height: h, closeOnEscape: true,
        show: {effect: "slide", duration: 300},
        hide: {effect: 'slide', duration: 200},
        close: function (e, ui) {
            if ($(e.currentTarget).hasClass('ui-button')) {
                id = $(e.currentTarget).closest('div.ui-dialog').find('.dialog-window').attr('id');
            } else {
                id = $(e.currentTarget).find('.dialog-window').attr('id');
            }
            windows.splice(windows.indexOf('#' + id), 1);
            $('#' + id).remove();
        }
    };
    $('#window_' + window_count).dialog(options);
    var id = "#window_" + window_count;
    window_count++;
    windows.push(id);
    return id;
}

function clear_windows() {
    for (var i in windows) {
        $(windows[i]).dialog("close");
    }
    windows = [];
}

function post_to_ajax_public(form_selector, api_command, onComplete, update_cls) {
    $(form_selector).submit(function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        fd.append('command', api_command);
        $(form_selector + " input, " + form_selector + " select").each(function (i, ui) {
            if ($(ui).attr('type') === 'file') {
                var file = $(ui)[0].files[0];
                fd.append($(ui).attr('name'), file);
            }
            fd.append($(ui).attr('name'), $(ui).val());
        });

        $(form_selector + " textarea").each(function (i, ui) {
            var area_name = $(ui).attr('name');
            var area_value = CKEDITOR.instances[area_name].getData();
            fd.append(area_name, area_value);
        });

        if (update_cls !== undefined) {
            fd.append('update_cls', update_cls);
        }

        $.ajax({
            url: api_path,
            type: 'POST',
            processData: false,
            contentType: false,
            data: fd,
            cache: false,
            complete: complete_handler
        });

        function complete_handler(f) {
            if (jQuery.isFunction(onComplete)) {
                onComplete(f);
            } else {
                if (f.responseText === '1') {
                    toast('All changes saved');
                } else {
                    toast('Changes not Saved');
                }
            }
        }
    });
}

// TOAST
//======
var toasters = 0;

function toast(str) {
    $('body').append("<div class='toast ui-widget-content' id='toast_" + toasters + "'>" + str + "</div>");
    $('#toast_' + toasters).position({my: 'center center', at: 'center center', of: '#content-body'});
    $('#toast_' + toasters).css('position', 'absolute').css('z-index', toasters + 100).css('top', ($('#toast_' + toasters).position()['top'] - 40) + "px");
    $('#toast_' + toasters).hide().show('clip', 300).delay(5000).hide('clip', 150);
    toasters++;
}

function toast_persistant(str) {
    $('body').append("<div class='toast ui-widget-content' id='toast_" + toasters + "'>" + str + "</div>");
    $('#toast_' + toasters).position({my: 'center center', at: 'center center', of: '#content-body'});
    $('#toast_' + toasters).css('position', 'absolute').css('z-index', toasters + 100).css('top', ($('#toast_' + toasters).position()['top'] - 40) + "px");
    $('#toast_' + toasters).hide().show('clip', 300);
    var selector = '#toast_' + toasters;
    toasters++;
    return selector;
}

function refresh() {
    window.location.reload();
}