$(document).ready(function () {
    var seats = [];
    var seat_icons = [];
    $('#add-to-cart').button({'disabled': true});
    $('#seating-chart img.seat.SEAT_STATUS_AVAILABLE').click(function (e) {
        if (seats.indexOf(e.target) === -1) {
            $('#seating-chart-selections').append("<li purchasable_seat_instance_id='" + $(e.target).attr('purchasable_seat_instance_id') + "'>" + $(e.target).attr('display_name') + ". $" + $(e.target).attr('display_price') + "</li>");
            $('#seating-chart-selections li').last().hide();
            $('#seating-chart-selections').animate({'height': $('#seating-chart-selections').height() + $('#seating-chart-selections li').last().outerHeight() + 0}, 100, 'swing', function () {
                $('#seating-chart-selections li').last().show('slide', 250, 'swing');
            });
            seats.push(e.target);
            seat_icons.push($(e.target).attr('src'));
            $(e.target).attr('src', directory + '/icons/check.png');
        } else {
            $(e.target).attr('src', seat_icons[seats.indexOf(e.target)]);
            $('#seating-chart-selections').animate({'height': $('#seating-chart-selections').height() - $('#seating-chart-selections li').last().outerHeight() + 0}, 100, 'swing', function () {
                $('#seating-chart-selections li')[seats.indexOf(e.target)].remove();
                seat_icons.splice(seats.indexOf(e.target), 1);
                seats.splice(seats.indexOf(e.target), 1);
            });
        }
        if (seats.length > 0) {
            $('#add-to-cart').button({'disabled': false});
        } else {
            $('#add-to-cart').button({'disabled': true});
        }
    });

    $('#add-to-cart').click(function () {
        $('#seating-chart-selections li').each(function (i, ui) {
            $('#seat-form').append("<input type='hidden' name='seat[]' value='" + $(ui).attr('purchasable_seat_instance_id') + "' />");
        });
        $('#seat-form').submit();
    });

});