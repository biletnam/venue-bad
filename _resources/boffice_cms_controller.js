//==================
// Shared Functions 
//==================
function call_and_response(query_array, response) {
    $.ajax({
        url: path_to_current_api,
        complete: complete_handle,
        type: 'POST',
        data: query_array,
        cache: false
    });

    function complete_handle(e) {
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

// Dialog Functions
//==================
var window_count = 0;
var windows = [];

function new_dialog(title, body, w, h) {
    if (w < 1 && w > 0) {
        w = $(document).width() * w;
    }
    if (h < 1 && h > 0) {
        h = $(document).height() * h;
    }
    if (w === undefined) {
        w = $(document).width() * .3;
    }
    if (h === undefined) {
        h = $(document).height() * .7;
    }
    w = Math.max(w, 470); //min size for forms

    $('body').append("<div id='window_" + (window_count) + "' class='dialog-window'>" + body + "</div>");
    var options = {
        title: title, width: w, height: h, closeOnEscape: true,
        show: {effect: "slide", duration: 300},
        hide: {effect: 'slide', duration: 200},
        modal: true,
        close: function (e, ui) {
            if ($(e.currentTarget).hasClass('ui-button')) {
                windows.splice(windows.indexOf('#' + $(e.currentTarget).closest('div.ui-dialog').find('.dialog-window').attr('id')), 1);
            } else {
                windows.splice(windows.indexOf('#' + $(e.currentTarget).find('.dialog-window').attr('id')), 1);
            }
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

function dialog_might_have_ckeditor() {
    window.setTimeout(function () {
        $('.wysiwyg').ckeditor();
    }, 500);
}

function post_to_ajax(form_selector, api_command, onComplete, update_cls) {
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
            url: path_to_current_api,
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
                    console.log(f.responseText);
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
    $('#content').append("<div class='toast ui-widget-content' id='toast_" + toasters + "'>" + str + "</div>");
    $('#toast_' + toasters).position({my: 'center bottom', at: 'center bottom', of: '#content'});
    $('#toast_' + toasters).css('position', 'absolute').css('z-index', toasters + 1).css('top', ($('#toast_' + toasters).position()['top'] - 40) + "px");
    $('#toast_' + toasters).hide().show('clip', 300).delay(3000).hide('clip', 150);

    toasters++;
}

