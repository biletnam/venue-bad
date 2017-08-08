var cntrlIsPressed = false;
var loading_results_html = "<div class='results'><div style='text-align:center'><img src='../icons/loading_blocks.gif' style='margin:30px auto;'></div></div>";

$(function () {
    $(document).keydown(function (event) {
        if (event.which === 17) {
            cntrlIsPressed = true;
        }
    });
    $(document).keyup(function () {
        cntrlIsPressed = false;
    });
    setInterval(window_has_focus_check, 200);

    $('.boffice_html_static').click(function (evt) {
        if (cntrlIsPressed) {
            evt.preventDefault();
            edit_static(evt.currentTarget);
        }
    });
    $('.boffice_html_dynamic').click(function (evt) {
        if (cntrlIsPressed) {
            evt.preventDefault();
            edit_dynamic(evt.currentTarget);
        }
    });
});

function window_has_focus_check() {
    if (document.hasFocus()) {
        $('#click-message .status').html("Ready");
    } else {
        $('#click-message .status').html("Off");
    }
}

var poo;

function edit_static(jQuery_selector) {
    var id = $(jQuery_selector).attr('boffice_html_static_id');
    var selector = new_dialog('Text Editor', loading_results_html, .7, .6);
    call_and_response({'command': 'edit_static_prepare', 'boffice_html_static_id': id}, response);

    function response(e) {
        $(selector + " .results").html(e.responseText);
        dialog_might_have_ckeditor();
        post_to_ajax(selector + " form", 'update_standard', '', 'boffice_html_static');
    }
}

function edit_dynamic(jQuery_selector) {
    var id = $(jQuery_selector).attr('boffice_html_dynamic_id');
    var selector = new_dialog('Gizmo Editor', loading_results_html);
    call_and_response({'command': 'edit_dynamic_prepare', 'boffice_html_dynamic_id': id}, response);

    function response(e) {
        $(selector + " .results").html(e.responseText);
        dialog_might_have_ckeditor();
        post_to_ajax(selector + " form", 'update_standard', '', 'boffice_html_dynamic');
    }
}
