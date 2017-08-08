$(function () {
    $('#purse-admin').click(function () {
        clear_windows();
        $('#purse-shows').click(bus_shows);
        bus_overview();
    });
});

function bus_shows() {

}

function bus_overview() {
    $('#purse').html(loading_results_html);
    call_and_response({command: 'bus_overview'}, response);

    function response(e) {
        $('#purse').html(e.responseText);
        $('#purse .guage_array').click(function (evt) {
            bus_show($(evt.currentTarget).attr("show_id"));
        });
    }
}

var current_bus_show_id = 0;

function bus_show(show_id) {
    current_bus_show_id = show_id;
    $('#purse').html(loading_results_html);
    call_and_response({'command': 'bus_show', 'show_id': show_id}, response);

    function response(e) {
        $('#purse').html(e.responseText);
    }

    call_and_response({'command': 'bus_instances', 'show_id': show_id}, response_sidebar);

    function response_sidebar(e) {
        $('#willcall-list').html(e.responseText);
        $('#willcall-area').show('slide');
        $('#willcall-area h3').html("Showtime");
    }
}