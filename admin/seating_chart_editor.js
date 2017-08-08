var icon_size = 33;
$(document).ready(function () {
    if ($('#chart').length === 1) {
        ready_seating_chart();
    }
});

function ready_seating_chart() {
    $('#chart div.object.seat').draggable({
        grid: [icon_size, icon_size]
    });
    $('#chart-header img').draggable({
        helper: 'clone',
        appendTo: '#chart-body',
        grid: [icon_size, icon_size]
    });
    $('#chart-body img').draggable({
        grid: [icon_size, icon_size]
    });
    $('#chart-body').droppable({
        accept: ":not(.ui-sortable-helper)",
        drop: new_item
    });

    var offset = $('#chart-body').offset();
    $('#chart-body img.object').css('position', 'absolute');
    $('#chart-body img.object').each(function (i, ui) {
        $(ui).offset({
            left: $(ui).attr('position_x') * icon_size + offset.left,
            top: $(ui).attr('position_y') * icon_size + offset.top
        });
    });

    //Trash Can
    $('#chart-body').append("<img class='trash' src='../icons/trashcan_32.png' width='32' height='32' />");
    $('img.trash').position({
        my: 'right bottom',
        at: 'right top',
        of: '#chart-body'
    });
    $('img.trash').droppable({
        accept: ":not(.ui-sortable-helper)",
        drop: delete_img
    });

    $('#chart-submit').button({
        icons: {
            primary: "ui-icon-disk"
        }
    }).click(chart_items);
}

function delete_img(evt, ui) {
    if ($(ui.draggable[0]).hasClass('new')) {
        $(ui.draggable[0]).remove();
    } else {
        $(ui.draggable[0]).addClass('remove').hide();
    }
}


function new_item(evt, ui) {
    var item = $('');
    if ((ui.helper).hasClass('option')) {
        var item = $(ui.helper).clone(false);
        $('#chart-body').append(item);
        $(item).draggable({
            grid: [icon_size, icon_size]
        });
        $(item).removeClass('option');
        $(item).addClass('new');
    } else {
        item = $(ui.helper);
    }
}

function chart_items() {
    var items = [];
    var doing_remove = false;
    $('#chart-body img').each(function (i, ui) {
        var item = {};
        if ($(ui).hasClass('new')) {
            item.isNew = '1';
            if ($(ui).attr('seating_chart_extra_id') !== undefined) {
                item.seating_chart_extra_id = $(ui).attr('seating_chart_extra_id');
            }
            if ($(ui).attr('purchasable_seat_abstract_id') !== undefined) {
                item.purchasable_seat_abstract_id = $(ui).attr('purchasable_seat_abstract_id');
            }
        } else {
            if ($(ui).attr('seating_chart_extras_instance_id') !== undefined) {
                item.seating_chart_extras_instance_id = $(ui).attr('seating_chart_extras_instance_id');
            }
            if ($(ui).attr('purchasable_seat_id') !== undefined) {
                item.purchasable_seat_id = $(ui).attr('purchasable_seat_id');
            }
        }
        if ($(ui).hasClass('remove')) {
            item.remove = '1';
            doing_remove = true;
        }
        item.x = Math.round(($(ui).position().left - $('#chart-body').position().left) / icon_size);
        item.y = Math.round(($(ui).position().top - $('#chart-body').position().top + 1) / icon_size);
        items.push(item);
    });
    if (doing_remove) {
        $('#chart').append("<div class='notice'>Please wait... deleting seats takes some time to update.</div>");
    }
    $('form#chart-body').append("<input type='hidden' name='chart_data' id='chart_data' />");
    $('input#chart_data').val(JSON.stringify(items));
    $('form#chart-body').submit();
}