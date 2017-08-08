//used throughout
var loading_results_html = "<div class='results'><div style='text-align:center'><img src='../icons/loading_blocks.gif' style='margin:30px auto;'></div></div>";
var poo;

$(document).ready(function () {
    $('button').button();
    $('.buttonset').buttonset();
    //navigational things
    update_show_picker(false);
    register_user_results_listeners();
    user_search_handle();
    new_show_listner();
    new_class_listener();
    webadmin_listeners();
    $('#chart-refresh button').button({icons: {primary: "ui.icon.refresh"}});
    $('#user-new button').click(user_new);
    $('#clear-windows').click(clear_windows);
    $('#edu-admin').click(interface_edu);
    $('#office-admin').click(interface_show);
    $('#web-admin').click(interface_webadmin);
    $('#purse-admin').click(interface_purse);

    $('#sell-package').click(start_sale_package);
    $('#sell-giftcard').click(start_sale_giftcard);

    //layout things
    update_layouts();
    $(window).resize(update_layouts);
    $('document, body').resize(update_layouts);
    window.setTimeout(update_layouts, 200);
    clear_interface_area(true);
    interface_show_can_button();
    notices_initialize();

    if (location.protocol !== 'https:') {
        //$('body').css('border','5px red solid');
        toast("This page is not secure");
    }
    ;
});

// USER 
//=============
function user_listeners() {
    $('.user').click(function (e) {
        user_pull($(e.currentTarget).attr('user_id'));
    });
}

function user_new() {
    var selector = new_dialog('New USer', loading_results_html);
    call_and_response({'command': 'new_user_dialog'}, response1);

    function response1(e) {
        $(selector).html(e.responseText);
        $(selector).find('#ulogin_id').hide();
        dialog_might_have_ckeditor();
        post_to_ajax(selector + ' form', 'update_standard', 'response2', 'user');
    }
}

function user_search_handle() {
    $("#user_picker input").keyup(function (event) {
        if (event.keyCode === 13) {
            user_search_request();
        }
    });
    $('#user_picker input').autocomplete({
        source: "user_search.php",
        minLength: 2,
        select: function (event, ui) {
            user_pull(ui.item.value);
        },
        close: function () {
            $('#user_picker input').val("");
        }
    });
}

function user_search_request() {
    var selector = new_dialog('User Search', loading_results_html);
    call_and_response({'command': 'users_search', 'query': $('#user_picker input').val()}, user_search_response);

    function user_search_response(e) {
        $(selector).find('.results').html(e.responseText);
        user_listeners();
    }
}

function user_pull(id) {
    var selector = new_dialog('User #' + id, loading_results_html, 600, 550);
    call_and_response({'command': 'user_data', 'user_id': id}, answer);

    function answer(e) {
        $(selector + " .results").html(e.responseText);
        dialog_might_have_ckeditor();
        $(".tabs").tabs();
        post_to_ajax(selector + " .results .user_data form", 'update_standard', '', 'user');
        register_user_results_listeners();
        $('.user-purchases .purchasable_seat_instance').on('click', function () {
            reservation_pull_from_purchasable_instance_id($(this).attr('purchasable_seat_instance'));
        });
        $('.user-purchases .purchasable_registration_instance').on('click', function () {
            registration_pull_from_purchasable_instance_id($(this).attr('purchasable_registration_instance'));
        });
        $('.user_packages  .package').on('click', function () {
            package_pull($(this).attr('package_id'));
        });
        if (webadmin_show_id > 0) {
            $(selector).find('form').before("<fieldset><legend>Cast/Crew</legend><div class='form-row'>\n\
		    <label for='role-type'>Role Type</label><select class='role-type' id='role-type'>\n\
			<option value='PRIMARY_CAST'>Primary Cast</option>\n\
			<option value='PRIMARY_CREW'>Primary Crew</option>\n\
			<option value='CAST'>Cast</option>\n\
			<option value='CREW'>Crew</option>\n\
		    </select></div>\n\
		    <div class='form-row'><label for='role'>Role</label><input type='text' class='role' id='role' /></div>\n\
		    <input type='hidden' class='user-id' value='" + id + "'> \n\
		    <div class='form-row'><label>&nbsp;</label><button class='add-to-show-people'>Add to current show</button></div></fieldset>");
            $('.add-to-show-people').button({icons: {primary: "ui-icon-arrowthick-1-e"}}).click(webadmin_add_person_to_show);
        } else {
            console.log("no show-id");
        }
        users_on_stage_for_select('#new-reservation .user-id');
        users_on_stage_for_select('#new-registration .user-id');
        users_on_stage_for_select('#seating-chart .general-seating-chart-form .user-id');
        users_on_stage_for_select('#packages-list .user-id');
        users_on_stage_for_select('#worker_user_id');
    }
}

function register_user_results_listeners() {
    transactions_listeners();
}

// Reservations
//======
function reservation_pull_from_purchasable_instance_id(id) {
    var selector = new_dialog('Reservation', loading_results_html);
    call_and_response({
        'command': 'reservation_by_purchasable_instance_id',
        'purchasable_seat_instance_id': id
    }, answer);

    function answer(e) {
        $(selector + " .results").html(e.responseText);
        user_listeners();
        transactions_listeners();
        dialog_might_have_ckeditor();
        reservation_listeners();
        post_to_ajax(selector + " .edit_form form", 'update_standard', '', 'reservation');
    }
}

function reservation_pull(id) {
    var selector = new_dialog('Reservation ID:' + id, loading_results_html);
    call_and_response({'command': 'reservation_pull', 'reservation_id': id}, response);

    function response(e) {
        $(selector + " .results").html(e.responseText);
        user_listeners();
        transactions_listeners();
        dialog_might_have_ckeditor();
        reservation_listeners();
        post_to_ajax(selector + " .edit_form form", 'update_standard', '', 'reservation');
    }
}

function load_reservation_by_seat_instance_id(purchasable_seat_instance_id) {
    var selector = new_dialog('Reservation', loading_results_html);
    call_and_response({
        'command': 'reservation_by_seat_instance_id',
        'purchasable_seat_instance_id': purchasable_seat_instance_id
    }, response);

    function response(e) {
        $(selector + " .results").html(e.responseText);
        user_listeners();
        transactions_listeners();
        dialog_might_have_ckeditor();
        reservation_listeners();
        post_to_ajax(selector + " .edit_form form", 'update_standard', '', 'reservation');
    }
}

function reservation_listeners() {
    $('.change-seats').off();
    var listener_waiting = true;
    $('.change-seats').click(function (evt) {
        if (listener_waiting) {
            var reservation_id = $(evt.currentTarget).closest('.dialog-window').find('.reservation').attr('reservation_id');
            $(evt.currentTarget).closest('.dialog-window').dialog('close');
            $('#seating-chart').html(loading_results_html);
            call_and_response({'command': 'reservation_cancel', 'reservation_id': reservation_id}, response);

            function response(e) {
                toast(e.responseText);
                load_show(current_show_id, '1');
            }

            listener_waiting = false;
        }
    });

    $('.reservation-actions .cancel').click(function (evt) {
        if (listener_waiting) {
            var reservation_id = $(evt.currentTarget).closest('.dialog-window').find('.reservation').attr('reservation_id');
            $(evt.currentTarget).closest('.dialog-window').dialog('close');
            $('#seating-chart').html(loading_results_html);
            call_and_response({'command': 'reservation_cancel', 'reservation_id': reservation_id}, response);

            function response(e) {
                toast(e.responseText);
                load_show(current_show_id, '1');
            }

            listener_waiting = false;
        }
    });
}

function add_seat_to_reservation(seat_instance_id) {
    if ($('#new-reservation').length === 0) {
        $('#content-body').append("<div id='new-reservation' class='results'><ul></ul><label for='user_id'>User</label><select name='user_id' class='user-id'></select></div>");
        users_on_stage_for_select('#new-reservation .user-id');
        $('#new-reservation').dialog({
            title: 'New Reservation',
            width: 400,
            height: 400,
            buttons: [
                {text: 'Create', click: create_reservation, disabled: true, id: 'create-reservation-btn'}
            ],
            close: empty_reservation_cart
        });

    }
    call_and_response({'command': 'new_seat_cart_item', 'seat_instance_id': seat_instance_id}, resp);

    function resp(e) {
        if ($('#cart-item-' + e.responseText).length === 0) {
            $('#new-reservation ul').append("<li class='cart-item' id='cart-item-" + e.responseText + "'> <img src='../icons/loading_blocks.gif' style='width:13px; height: 13px;'> ... getting name for seat-instance-id:" + seat_instance_id + " </li>");
            seat_readible_name(seat_instance_id, '#cart-item-' + e.responseText);
        } else {
            console.log('seat already in cart');
        }

    }
}

function create_reservation(evt) {
    call_and_response({
        'command': 'cart_set_user_id',
        'user_id': $('#new-reservation select.user-id').val()
    }, '#dev-null');
    cash_register_make();
    $('#new-reservation').remove();
}

function empty_reservation_cart() {
    $('#new-reservation').remove();
    call_and_response({'command': 'empty_cart'}, '#dev-null');
    cash_register_ready = false;
    unprocessed_giftcard_ids = [];
}

function validate_new_reservation() {
    if ($('#new-reservation select.user-id').val() > 0) {
        $('#create-reservation-btn').button('option', 'disabled', false);
    }
}

function users_on_stage_for_select(select_element_selector) {
    var count = 0;
    $('.user_data').each(function (i) {
        count++;
        if ($(this).attr('user_id') > 0 && $(this).attr('user_id') > 0) {
            call_and_response({'command': 'user_data_as_select_option', 'user_id': $(this).attr('user_id')}, response);
        }
    });

    function response(e) {
        $(select_element_selector).append(e.responseText);
        validate_new_reservation();
        validate_new_registration();
        validate_new_general_reservation();
        validate_new_package_sale();
    }

    return count;
}

function seat_readible_name(seat_instance_id, jquery_target) {
    call_and_response({'command': 'seat_readible_name', 'seat_instance_id': seat_instance_id}, response);

    function response(e) {
        $(jquery_target).html(e.responseText);
    }
}


// Packages 
//==========
function package_pull(id) {
    var selector = new_dialog('Package Information. PackageID:' + id, loading_results_html);
    call_and_response({'command': 'package_pull', 'package_id': id}, response);

    function response(e) {
        $(selector).html(e.responseText);
        transactions_listeners();
    }
}


// Transactions
//==============
function transactions_listeners() {
    $('.transaction').on('click', function (e) {
        transaction_pull($(this).attr('transaction_id'));
    });
}

function transaction_pull(id) {
    var selector = new_dialog('Transaction #' + id, loading_results_html);
    call_and_response({'command': 'transaction', 'transaction_id': id}, answer);

    function answer(e) {
        $(selector + " .results").html(e.responseText);
        $(selector + " .results button.refundable").button().click(function (f) {
            call_and_response({
                'command': 'transaction_refund',
                'transaction_id': $(f.currentTarget).attr('transaction_id')
            }, response2);

            function response2(g) {
                if (g.responseText === "1") {
                    toast("Refund Approved");
                    $(selector).dialog("close");
                } else {
                    toast(g.responseText);
                }
            }
        });
    }
}


// Cash Register 
//===============
var cash_register_ready = false

function cash_register_make() {
    $('#content-cash-register').html(loading_results_html);
    $('#content-cash-register').dialog({
        width: 600,
        height: $('#content-body').height() - 100,
        buttons: [
            {text: 'Cash', click: cash_register_process},
            {text: 'Check', click: cash_register_process},
            {text: 'GiftCard', click: giftcard_process},
            {text: 'Comp', click: cash_register_process}
        ],
        draggable: true,
        resizable: false,
        title: 'Transaction Register',
        close: empty_reservation_cart,
        position: {my: "right top", at: "right top", of: '#content-body'}
    });
    call_and_response({'command': 'get_current_cart'}, function (e) {
        $('#content-cash-register').html(e.responseText);
        if (get_register_total() === '0') {
            $('#content-cash-register').dialog({buttons: [{text: 'Confirm', click: cash_register_process}]});
        }
        cash_register_ready = true;
        cash_register_buttons_enable();
    });
}

function get_register_total() {
    return $('.register-summary .total').attr('total');
}

function get_register_subtotal() {
    return $('.register-summary .subtotal').attr('subtotal');
}

function giftcard_process() {
    var string = "<div id='giftcard_prompt'><input type='text' class='id' placehoder='id'><br /><input type='text' class='key' placehoder='key'><br /><button class='validate'>Validate</button></div>";
    var selector = new_dialog('Giftcard Redemption', string, 300, 300);
    $(selector).dialog({
        close: function () {
            $(selector).remove();
        }
    });
    $('#giftcard_prompt .validate').click(function () {
        call_and_response({
            'command': 'giftcard_validate',
            'id': $('#giftcard_prompt .id').val(),
            'key': $('#giftcard_prompt .key').val()
        }, response);
    });

    function response(e) {
        id = e.responseText;
        call_and_response({'command': 'giftcard_value', 'instance_id': id}, value_response);

        function value_response(f) {
            value = f.responseText;
            if (value > 0) {
                $('#giftcard_prompt .validate').remove();
                $('#giftcard_prompt').append("<input type='text' placeholder='amount' value='" + value + "' class='amount' /><button class='add'>Add</button>");
                var display_max = Math.min(get_register_total(), value);
                $('#giftcard_prompt .amount').spinner({min: 0, max: display_max});
                $('#giftcard_prompt .add').click(function () {
                    call_and_response({
                        'command': 'giftcard_add',
                        'instance_id': id,
                        'amount': $('#giftcard_prompt .amount').val()
                    }, adding_response);

                    function adding_response(g) {
                        console.log(g);
                        if (g.responseText === '1') {
                            $(selector).dialog("close");
                            cash_register_make();
                        }
                    }
                });
            }
        }
    }

}

function cash_register_process(e) {
    var transaction_type = $(e.currentTarget).find('span').html();
    $('#content-cash-register').html(loading_results_html);
    cash_register_buttons_disable();
    call_and_response({'command': 'process_current_cart_manually', 'transaction_type': transaction_type}, response);

    function response(e) {
        cash_register_exit();
        toast("Transaction Complete. TransactionId:" + e.responseText);
    }
}

function cash_register_exit() {
    clear_windows();
    for (var i = 0; i < unprocessed_giftcard_ids.length; i++) {
        giftcard_pull(unprocessed_giftcard_ids[i]);
    }
    unprocessed_giftcard_ids = [];

    if (current_show_id > 0) {
        load_show(current_show_id);
        $('#new-reservation').remove();
        empty_reservation_cart();
    }
    if (active_class_id > 0) {
        pull_class(active_class_id);
        empty_registration_cart();
    }
    $('#content-cash-register').dialog('close');


}

function cash_register_buttons_disable() {
    $('#content-cash-register').next('.ui-dialog-buttonpane').find('button').button('disable');
    cash_register_ready = false;
}

function cash_register_buttons_enable() {
    $('#content-cash-register').next('.ui-dialog-buttonpane').find('button').button('enable');
    cash_register_ready = true;
}

// SHOW 
//======
var current_show_id = 0; //show_instance_id
function update_show_picker(autoload_first_show) {
    call_and_response({'command': 'shows_active'}, response);
    $('#chart-refresh button').button({disabled: true});
    current_show_id = 0;
    interface_show_can_button();

    function response(e) {
        $('#show_picker').html(e.responseText);
        $('#navigation select').height($('#navigation #logout').height() * .8);
        $('#navigation select').prepend("<option value='' selected='selected' disabled='disabled'>Seating Chart</select>");
        $('#show_picker select').change(function () {
            load_show($('#show_picker select').val());
        });
        if (autoload_first_show === true) {
            load_show($('#show_picker select').val());
        }
        $('#chart-refresh button').button({disabled: false});
        $('#chart-refresh').click(function () {
            load_show($('#show_picker select').val(), '1');
        });
    }
}

function load_show(id, refresh_chart) {
    interface_show();
    current_show_id = 0;
    interface_show_can_button();
    if ($('#seating-chart').length === 0) {
        $('#content-body').append("<div id='seating-chart'></div>");
    }
    $('#seating-chart').html(loading_results_html);
    $('#show-edit button').button({disabled: true});
    call_and_response({
        'command': 'seating_chart',
        'show_instance_id': id,
        'refresh_chart': refresh_chart
    }, show_response);

    function show_response(e) {
        current_show_id = id;
        interface_show_can_button();
        $('#edit-show button, #showtime-edit button, #chart-refresh button').button({'disabled': false});
        $('#seating-chart').html(e.responseText);
        $('#seating-chart img.object.SEAT_STATUS_RESERVED').click(function (evt) {
            load_reservation_by_seat_instance_id($(evt.currentTarget).attr('purchasable_seat_instance_id'));
        });
        $('#seating-chart img.object.SEAT_STATUS_AVAILABLE').click(function (evt) {
            add_seat_to_reservation($(evt.currentTarget).attr('purchasable_seat_instance_id'));
        });
        $('#seating-chart img.object.SEAT_STATUS_RESERVED').mouseenter(highlight_hovered_reseration).mouseleave(unhighlight_hovered_reseration);
        if (current_show_id > 0) {
            $('#show-edit button').button({disabled: false});
        }
        if ($('#seating-chart .general-seating-chart-form').length) {
            //General Seating
            users_on_stage_for_select('#seating-chart .general-seating-chart-form .user-id');
            $('#seating-chart .general-seating-chart-form input#count').spinner({
                min: 1,
                max: $('#seating-chart .available').attr('available')
            });
            $('.general-seating-chart-form button').button().click(function () {
                call_and_response({
                    'command': 'new_general_seat_cart_item',
                    'show_instance_id': id,
                    'quantity': $('#seating-chart .general-seating-chart-form input#count').val(),
                    'user_id': $('#seating-chart .general-seating-chart-form .user-id').val()
                }, create_general_reservation_response);
            });
            validate_new_general_reservation();
            var data = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Capacity', Math.round(100 * $('#seating-chart .reserved').attr('reserved') / $('#seating-chart .total').attr('total'))]
            ]);

            var options = {
                width: 400,
                height: 120,
                redFrom: 90,
                redTo: 100,
                yellowFrom: 75,
                yellowTo: 90,
                minorTicks: 5
            };
            var chart = new google.visualization.Gauge(document.getElementById('show_guage'));
            chart.draw(data, options);
        }

        function create_general_reservation_response(f) {
            cash_register_make();
        }
    }

    $('#willcall-list').html(loading_results_html);
    call_and_response({'command': 'willcall', 'show_instance_id': id}, willcall_response);

    function willcall_response(e) {
        if ($('#wicall-list').length === 0) {
            $('#willcall-area').append("<div id='willcall-list'></div>");
        }
        $('#willcall-list').html(e.responseText);
        $('#willcall-list .tabs').tabs();
        $('#willcall-area li.reservation').button().mouseenter(highlight_hovered_reseration).mouseleave(unhighlight_hovered_reseration);
        $('#willcall-area li.reservation').click(function (evt) {
            reservation_pull($(evt.currentTarget).attr('reservation_id'));
        });
        willcall_workers_update(id);
    }
}

function willcall_workers_update(id) {
    if (id === undefined) {
        id = current_show_id;
    }
    $('#willcall-list #tabs-workers').html(loading_results_html);
    call_and_response({'command': 'show_worker_list', 'show_instance_id': id}, function (e) {
        $('#willcall-list #tabs-workers').html(e.responseText);
        willcall_workers();
    });
}

function willcall_workers() {
    $('#willcall-list #tabs-workers button.add').button({icons: {primary: 'ui-icon-plusthick'}}).click(function (e) {
        var selector = new_dialog('Create new show job', loading_results_html, 450, 250);
        call_and_response({'command': 'show_worker_new'}, function (f) {
            $(selector).html(f.responseText);
            $(selector + " form").append("<input type='hidden' name='show_instance_id' value='" + current_show_id + "' />");
            post_to_ajax(selector + " form", 'show_worker_new_submit', function (g) {
                toast(g.responseText);
                willcall_workers_update();
            });
        });
    });
    $('#willcall-list #tabs-workers li.worker.filled button').button({
        icons: {primary: 'ui-icon-triangle-1-w'}
    }).click(function (e) {
        var show_instance_worker_id = $(e.currentTarget).attr('show_instance_worker_id');
        call_and_response({
            'command': 'show_worker_unfill',
            'show_instance_worker_id': show_instance_worker_id
        }, function (f) {
            toast(f.responseText);
            willcall_workers_update();
        });
    });
    $('#willcall-list #tabs-workers li.worker.unfilled button').button({
        icons: {primary: 'ui-icon-triangle-1-e'}
    }).click(function (e) {
        var show_instance_worker_id = $(e.currentTarget).attr('show_instance_worker_id');
        var selector = new_dialog('Assign job to user.', loading_results_html, 430, 250);
        call_and_response({
            'command': 'show_worker_fill',
            'show_instance_worker_id': show_instance_worker_id
        }, function (f) {
            $(selector).html(f.responseText);
            users_on_stage_for_select('#worker_user_id');
            post_to_ajax(selector + " form", 'show_worker_fill_submit', function (g) {
                toast(g.responseText);
                willcall_workers_update();
            });
        });
    });
}

function validate_new_general_reservation() {
    if ($('#seating-chart .general-seating-chart-form .user-id option').length === 0) {
        $('.general-seating-chart-form button').button({disabled: true});
    } else {
        $('.general-seating-chart-form button').button({disabled: false});
    }
}

function highlight_hovered_reseration(evt) {
    $('.rid' + $(evt.currentTarget).attr('reservation_id')).addClass('highlight');
}

function unhighlight_hovered_reseration(evt) {
    $('.rid' + $(evt.currentTarget).attr('reservation_id')).removeClass('highlight');
}

function new_show_listner() {
    $('#show-new').click(function () {
        var selector = new_dialog('New Show', loading_results_html);
        call_and_response({'command': 'show_new_dialog'}, selector);
    });
    $('#show-edit').click(function () {
        var selector = new_dialog('Edit Show', loading_results_html);
        call_and_response({'command': 'show_edit_dialog', 'show_instance_id': current_show_id}, selector);
    });
    $('#showtime-new').click(function () {
        var selector = new_dialog('New Showtime', loading_results_html);
        call_and_response({'command': 'showtime_new'}, showtime_new_response);

        function showtime_new_response(e) {
            $(selector).html(e.responseText);
            datetime();
        }
    });
    $('#showtime-edit').click(function () {
        var selector = new_dialog('Edit Showtime', loading_results_html);
        call_and_response({'command': 'showtime_edit_dialog', 'show_instance_id': current_show_id}, selector);
    });
}


//=========================================================
//                   Education Functions                   
//=========================================================
function new_class_listener() {
    $('#class-new').click(class_new);
    $('#class-category-new').click(class_category_new);
}

function class_new() {
    var selector = new_dialog('New Class', loading_results_html);
    call_and_response({'command': 'new_class_dialog'}, response1);

    function response1(e) {
        $(selector).html(e.responseText);
        $(selector + " ul").hide();
        dialog_might_have_ckeditor();
        post_to_ajax(selector + ' form', 'update_standard', function (e) {
            $(selector).dialog('close');
            toast("New Class Created");
            update_classes_list();
        }, 'purchasable_registration');
    }
}

function update_classes_list() {
    $('#class-picker').html();
    $('#class-edit button').button('disable');
    call_and_response({'command': 'update_classes_list'}, response);

    function response(e) {
        $('#class-picker').html(e.responseText);
        $('#class-picker select').prepend("<option value='' selected='selected' disabled='disabled'>Class Editor</select>");
        $('#class-picker select').height($('#navigation #logout').height());
        $('#classes_list').change(function () {
            pull_class($('#classes_list').val());
        });
    }
}

function pull_class(purchasable_registration_id) {
    call_and_response({'command': 'pull_class', 'purchasable_registration_id': purchasable_registration_id}, response);
    $('#class-edit button').button('disable');

    function response(e) {
        active_class_id = purchasable_registration_id;
        $('#class-edit button').button('enable');
        $('#class-edit button').off();
        $('#class-edit button').click(edit_active_class);
        $('#edu').html(e.responseText);
        $('#edu .accordion ul').columnize({columns: 2});
        $('#edu .accordion').accordion({collapsible: true, active: 'none'});
        $('#edu .user').click(function (f) {
            user_pull($(f.currentTarget).attr('user_id'));
        });
        $('#edu .transaction-details').click(function (f) {
            transaction_pull($(f.currentTarget).attr('transaction_id'));
        });
        $('#edu .new').click(new_class_instance);
        $('#edu .new-registration').off();
        $('#edu .new-registration').click(new_class_registration_instance);
    }
}

function new_class_registration_instance(e) {
    if ($('#new-registration').length === 0) {
        $('#content-body').append("<div id='new-registration' class='results'><ul></ul><label for='user_id'>User</label><select name='user_id' class='user-id'></select></div>");
        users_on_stage_for_select('#new-registration .user-id');
        $('#new-registration').dialog({
            title: 'New Registration',
            width: 400,
            height: 400,
            buttons: [
                {text: 'Create', click: create_registration, disabled: true, id: 'create-registration-btn'}
            ],
            close: empty_registration_cart
        });
    }

    var instance_id = $(e.currentTarget).attr('purchasable_registration_instance_id');
    call_and_response({
        'command': 'add_registration_to_cart',
        'purchasable_registration_instance_id': instance_id
    }, response);

    function response(e) {
        $('#new-registration ul').append("<li class='cart-item' id='cart-item-" + e.responseText + "'> <img src='../icons/loading_blocks.gif' style='width:13px; height: 13px;'> ... getting name </li>");
        readible_class_name(active_class_id, '#cart-item-' + e.responseText);
        validate_new_registration();
    }
}

function create_registration(evt) {
    call_and_response({'command': 'cart_set_user_id', 'user_id': $('#new-registration .user-id').val()}, '#dev-null');
    console.log($('#new-registration .user-id').val());

    cash_register_make();
    $('#new-registration').remove();
}

function empty_registration_cart() {
    $('#new-registration').remove();
    call_and_response({'command': 'empty_cart'}, '#dev-null');
}

function validate_new_registration() {
    if ($('#new-registration select.user-id').val() > 0) {
        $('#create-registration-btn').button('option', 'disabled', false);
    }
}

function readible_class_name(class_id, jQueryTarget) {
    call_and_response({'command': 'readible_class_name', 'purchasable_registration_id': class_id}, response);

    function response(e) {
        $(jQueryTarget).html(e.responseText);
    }
}

function new_class_instance() {
    if (active_class_id > 0) {
        var selector = new_dialog("New class meeting time", loading_results_html);
        call_and_response({
            'command': 'new_purchasable_registration_instance',
            'purchasable_registration_id': active_class_id
        }, response);

        function response(e) {
            $(selector).html(e.responseText);
            post_to_ajax(selector + ' form', 'update_standard', function (e) {
                $(selector).dialog('close');
                toast("New meeting time added!");
                pull_class(active_class_id);
            }, 'purchasable_registration_instance');
        }
    } else {
        toast("cannot create a new class time without a class selected");
    }
}

var active_class_id;

function edit_active_class() {
    if (active_class_id > 0) {
        var selector = new_dialog('Edit Class', loading_results_html);
        call_and_response({'command': 'edit_class_dialog', 'purchasable_registration_id': active_class_id}, response);

        function response(e) {
            $(selector).html(e.responseText);
            $(selector + " ul").hide();
            dialog_might_have_ckeditor();
            post_to_ajax(selector + ' form', 'update_standard', function (e) {
                $(selector).dialog('close');
                toast("All changes saved. Class Updated.");
                update_classes_list();
            }, 'purchasable_registration');
        }
    } else {
        toast("No class is active in the view panel. Select a class, then mash edit.");
    }
}

function class_category_new() {
    var selector = new_dialog('New Category/Series for classes', loading_results_html);
    call_and_response({'command': 'new_class_category_dialog'}, response1);

    function response1(e) {
        $(selector).html(e.responseText);
        $(selector + " ul").hide();
        dialog_might_have_ckeditor();
        post_to_ajax(selector + ' form', 'update_standard', function (e) {
            $(selector).dialog('close');
            toast("New Class Category");
            update_class_categories_list();
        }, 'purchasable_registration_category');
    }
}

function class_category_edit(id) {
    var selector = new_dialog('Edit Class Category/Series', loading_results_html);
    call_and_response({'command': 'edit_class_category_dialog', 'purchasable_registration_category_id': id}, response);

    function response(e) {
        $(selector).html(e.responseText);
        $(selector + " ul").hide();
        post_to_ajax(selector + ' form', 'update_standard', function (e) {
            $(selector).dialog('close');
            toast("All changes saved. Class Cateogry Updated.");
            update_class_categories_list();
        }, 'purchasable_registration_category');
    }
}

function update_class_categories_list() {
    $('#class-category-picker').html();
    call_and_response({'command': 'class_category_list'}, response);

    function response(e) {
        $('#class-category-picker').html(e.responseText);
        $('#class-category-picker select').prepend("<option value='' selected='selected' disabled='disabled'>Series Editor</option>");
        $('#class-category-picker select').height($('#navigation #logout').height());
        $('#class-category-picker select').change(function () {
            class_category_edit($('#class-category-picker select').val());
        });
    }
}

function registration_pull_from_purchasable_instance_id(id) {
    var selector = new_dialog('Registration', loading_results_html);
    call_and_response({
        'command': 'registration_by_purchasable_instance',
        'purchasable_registration_instance_id': id
    }, answer);

    function answer(e) {
        $(selector + " .results").html(e.responseText);
        user_listeners();
        transactions_listeners();
    }
}


//=========================================================
//		    Package Retail			   
//=========================================================
function start_sale_package() {
    var selector = new_dialog("New Package", loading_results_html, 400, 300);
    call_and_response({'command': 'packages_available'}, response);

    function response(e) {
        $(selector + " .results").html(e.responseText);
        $("#packages-list button.add").button({'disabled': true});
        $("#packages-list button.add").click(function (f) {
            call_and_response({
                'command': 'package_add_to_cart',
                'purchasable_package_model_id': $(f.currentTarget).attr('purchasable_package_model_id'),
                'user_id': $('#packages-list .user-id').val()
            }, response2);

            function response2(g) {
                cash_register_make();
                $(selector).dialog('close');
            }

        });
        users_on_stage_for_select('#packages-list .user-id');
    }
}

function validate_new_package_sale() {
    if ($('#packages-list .user-id option').length === 0) {
        $('#packages-list button.add').button({disabled: true});
    } else {
        $('#packages-list button.add').button({disabled: false});
    }
}


//=========================================================
//		    Giftcard				   
//=========================================================
//
// Retail
//========
var unprocessed_giftcard_ids = [];

function start_sale_giftcard() {
    var selector = new_dialog("New Giftcard", loading_results_html, 450, 320);
    call_and_response({'command': 'giftcard_creation_form'}, response);

    function response(e) {
        $(selector + " .results").html(e.responseText);
        $('#giftcard-creation-from #method').change(function () {
            if ($('#giftcard-creation-from #method').val() === 'print') {
                $('#giftcard-creation-from .method-data').hide();
            } else {
                $('#giftcard-creation-from .method-data').show();
            }
        }).change();

        $("#giftcard-creation-from button.add").click(function () {
            call_and_response({
                'command': 'giftcard_create',
                'amount': $('#giftcard-creation-from #amount').val(),
                'method': $('#giftcard-creation-from #method').val(),
                'method_data': $('#giftcard-creation-from #method_data').val()
            }, response2);
        });

        function response2(f) {
            unprocessed_giftcard_ids.push(f.responseText);
            cash_register_make();
            $(selector).dialog('close');
        }

        $(selector).dialog({
            close: function () {
                $(selector).dialog('destroy').remove();
            }
        });
    }
}


function giftcard_pull(id) {
    var selector = new_dialog("Giftcard", loading_results_html, 260, 400);
    call_and_response({'command': 'giftcard_pull', 'giftcard_id': id}, response);

    function response(e) {
        $(selector).html(e.responseText);
    }
}


//=========================================================
//                   WebAdmin Functions                    
//=========================================================
function webadmin_listeners() {
    $('#webadmin-shows').click(webadmin_shows_list);
    $('#webadmin-properties').click(webadmin_properties_list);
}

function webadmin_shows_list() {
    $('#web').html(loading_results_html);
    webadmin_show_id = 0;
    call_and_response({'command': 'web_shows_list'}, response);

    function response(e) {
        $('#web').html(e.responseText);
        $('.web-shows-list a').click(function (f) {
            f.preventDefault();
            webadmin_pull_show($(f.currentTarget).attr('show_id'));
        });
    }
}

function webadmin_pull_show(id) {
    $('#web').html("<div class='instances'></div><div class='show-editor'>" + loading_results_html + "<div class='show-images'>" + loading_results_html + "</div></div>");
    $('#willcall-area').show('slide', 600);
    $('#willcall-area').html(loading_results_html);
    call_and_response({'command': 'web_show_editor', 'show_id': id}, editor_response);

    function editor_response(e) {
        $('#web .show-editor').html(e.responseText + "<div class='show-images'>" + loading_results_html + "</div>");
        dialog_might_have_ckeditor();
        if ($('#seating_chart_id').val() !== '0') {
            $('#seating_chart_general_count').attr('disabled', 'disabled');
        }
        $('#seating_chart_id').change(function () {
            if ($('#seating_chart_id').val() !== '0') {
                $('#seating_chart_general_count').attr('disabled', 'disabled');
            } else {
                $('#seating_chart_general_count').removeAttr('disabled');
                if ($('#seating_chart_general_count').val() < 1) {
                    toast("You MUST set 'Maximum Seats' before saving!");
                } else {
                    toast("Maximum seating will be " + $('#seating_chart_general_count').val() + "seats.");
                }
            }
        });
        post_to_ajax('form#f_data_shows', 'update_standard', function (f) {
            if (f.responseText === '1') {
                toast("Show Updated");
            } else {
                toast(f.responseText);
            }
            webadmin_shows_list();
        }, 'show');
        webadmin_refresh_willcall_area(id);
        webadmin_refresh_show_images(id);
        webadmin_refresh_instances(id);

    }

}

function webadmin_refresh_instances(show_id) {
    if ($('#web .instances').length === 0) {
        $('#web').append("<div class='instances'></div>");
    }
    if (show_id < 1) {
        $('#web .instances').html("<p>After you've created a new show, you can create showtimes here.</p>");
    } else {
        if ($('#web .instances').length === 0) {
            $('#web').append("<div class='instances'></div>");
        }
        $('#web .instances').html(loading_results_html);
        call_and_response({'command': 'web_show_instances', 'show_id': show_id}, response);

        function response(e) {
            $('#web .instances').html(e.responseText);
            datetime();
            $('#web .instances button.locked').button({icons: {primary: 'ui-icon-locked'}, disabled: true});
            $('#web .instances button.delete').button({icons: {primary: 'ui-icon-trash'}}).click(function (f) {
                webadmin_remove_show_instance($(f.currentTarget).attr('show_instance_id'));
            });
            $('#web .instances #new-instance button').button({icons: {primary: 'ui-icon-calendar'}}).click(function (f) {
                webadmin_create_show_instance($("#new-instance #new_datetime").val());
            });
        }
    }
}

function webadmin_remove_show_instance(show_instance_id) {
    $('#web .instances').html(loading_results_html);
    call_and_response({'command': 'web_show_instance_delete', 'show_instance_id': show_instance_id}, response);

    function response(e) {
        toast(e.responseText);
        webadmin_refresh_instances(webadmin_show_id);
    }
}

function webadmin_create_show_instance(new_datetime) {
    if (new_datetime.length < 10) {
        toast("Select a new date and time first.");
    } else {
        $('#web .instances').html(loading_results_html);
        call_and_response({
            'command': 'web_show_instance_create',
            'show_id': webadmin_show_id,
            'new_datetime': new_datetime
        }, response);
    }

    function response(e) {
        toast(e.responseText);
        webadmin_refresh_instances(webadmin_show_id);
    }
}

var webadmin_show_id = 0;

function webadmin_refresh_willcall_area(show_id) {
    if (show_id > 0) {
        call_and_response({'command': 'web_show_people', 'show_id': show_id}, people_response);
        webadmin_show_id = show_id;
    } else {
        $('#willcall-area').html("<p>After you've created a new show, you can add cast and crew in this area.</p>");
    }

    function people_response(e) {
        $('#willcall-area').html(e.responseText);
        $('#willcall-area .person-minus-button').button({icons: {primary: 'ui-icon-trash'}}).click(function (f) {
            webadmin_remove_person_from_show(webadmin_show_id, $(f.currentTarget).attr('user_id'));
        });
    }
}

function webadmin_add_person_to_show(mouseEvent) {
    var user_id = $(mouseEvent.currentTarget).closest('fieldset').find("input.user-id").val();
    var role = $(mouseEvent.currentTarget).closest('fieldset').find("input.role").val();
    var role_type = $(mouseEvent.currentTarget).closest('fieldset').find("select.role-type").val();
    $(mouseEvent.currenTarget).closest('fieldset').html(loading_results_html);
    var obj = {
        'command': 'web_show_add_person',
        'show_id': webadmin_show_id,
        'user_id': user_id,
        'show_people_role': role,
        'show_people_role_type': role_type
    };
    call_and_response(obj, response);

    function response(e) {
        console.log(mouseEvent);
        $(mouseEvent.currentTarget).find('.dialog-window').dialog('close');
        webadmin_refresh_willcall_area(webadmin_show_id);
    }
}

function webadmin_remove_person_from_show(show_id, user_id) {
    $('#willcall-area').html(loading_results_html);
    var obj = {'command': 'web_show_remove_person', 'show_id': show_id, 'user_id': user_id};
    call_and_response(obj, response);

    function response(e) {
        webadmin_refresh_willcall_area(webadmin_show_id);
    }
}

function webadmin_refresh_show_images(show_id) {
    $('#web .show-editor .show-images').html(loading_results_html);
    call_and_response({'command': 'web_show_images_list', 'show_id': show_id}, response);

    function response(e) {
        $('#web .show-editor .show-images').html(e.responseText);
        post_to_ajax('#webadmin-show-image-upload form', 'web_show_image_upload', function (evt) {
            if (evt.responseText === '1') {
                toast("Image Uploaded");
                webadmin_refresh_show_images(webadmin_show_id);
            } else {
                toast("There was a problem uploading your image. " + evt.responseText);
            }
        });
        $('#web .show-image button.delete').button({icons: {primary: "ui-icon-trash"}}).click(function (evt) {
            call_and_response({
                'command': 'web_show_image_delete',
                'page_file_id': $(evt.currentTarget).attr('page_file_id')
            }, delete_response);

            function delete_response(g) {
                toast(g.responseText);
                webadmin_refresh_show_images(webadmin_show_id);
            }
        });
        $('#web .show-image button.delete').each(function (i, ui) {
            var par = $(ui).closest('div').find('img');
            //$(ui).position({my:'left top', at:'left top', of:par});
        });
    }
}

function webadmin_upload_image() {
    $('#web #webadmin-show-image-upload button').button('disable');


}


function webadmin_properties_list() {
    $('#web').html(loading_results_html);
    call_and_response({'command': 'web_prop_list'}, response);

    function response(e) {
        $('#web').html(e.responseText);
        $('#web-prop-list button.save').button().click(function (evt) {
            var input = $(evt.currentTarget).closest('div.form-row').find('input.property');
            var obj = {
                'command': 'web_prop_update',
                'boffice_property_name': $(input).attr('boffice_property_name'),
                'boffice_property_value': $(input).val()
            };
            call_and_response(obj, update_response);

            function update_response(g) {
                if (g.responseText === "1") {
                    toast("Settings Updated");
                } else {
                    toast("Update Failed. " + g.responseText);
                }
                webadmin_properties_list();
            }
        });
    }
}


//=========================================================
//                   Interface Functions                   
//=========================================================
function update_layouts() {
    $('#content-body').height($(window).innerHeight() - $('#navigation').outerHeight());
    $('#content-body').width($(window).innerWidth() - $('.list-right').first().outerWidth());
    $('.list-right').height($('#content-body').innerHeight());
    $('.list-right div').height($('#content-body').innerHeight() - $('.list-right h3').height() - 40);
    $('.list-area li').button();

    $('#edu').height($('#content-body').height());
    $('#purse').height($('#content-body').height());
    $('#web').height($('#content-body').height());
}

function clear_interface_area(fast) {
    if (fast) {
        $('#willcall-area').hide();
        $('#seating-chart').hide();
        $('#purse').hide();
        $('#edu').hide();
        $('#web').hide();
        $('#office-buttons').hide();
        $('#edu-buttons').hide();
        $('#purse-buttons').hide();
        $('#webadmin-buttons').hide();

    } else {
        duration = 600;
        $('#willcall-area').hide('slide', {'direction': 'right'}, duration);
        $('#seating-chart').hide('slide', duration);
        $('#purse').hide('slide', duration);
        $('#edu').hide('slide', duration);
        $('#web').hide('slide', duration);
        $('#office-buttons').hide();
        $('#edu-buttons').hide();
        $('#purse-buttons').hide();
        $('#webadmin-buttons').hide();
    }
    $('#workers').remove();
    $('#willcall-area').html("&nbsp;");
    webadmin_show_id = 0;
    interface_show_can_button();
}

slide_in_duration = 600;

function interface_show() {  //"show" as in a performance
    clear_interface_area(false);
    $('#willcall-area').show('slide', {'direction': 'right'}, slide_in_duration);
    $('#seating-chart').show('slide', slide_in_duration);
    $('#office-buttons').show('slide', slide_in_duration);
    interface_show_can_button();
}

function interface_show_can_button() {
    if (current_show_id > 0) {
        $('#show-edit button, #showtime-edit button, #chart-refresh button, #showtime-new button').button({'disabled': false});
    } else {
        $('#show-edit button, #showtime-edit button, #chart-refresh button, #showtime-new button').button({'disabled': true});
    }
}

function interface_edu() {
    clear_interface_area(false);
    $('#edu').html("");
    $('#edu').show('slide', slide_in_duration);
    $('#edu-buttons').show('slide', slide_in_duration);
    $('#class-edit button').button('disable');
    update_classes_list();
    update_class_categories_list();
}

function interface_purse() {
    clear_interface_area(false);
    $('#purse').show('slide', slide_in_duration);
    $('#purse-buttons').show('slide', slide_in_duration);
}

function interface_webadmin() {
    clear_interface_area(false);
    $('#web').show('slide', slide_in_duration);
    $('#webadmin-buttons').show('slide', slide_in_duration);
}


//====================
// Credit Card Swipe  
//====================
var card_swipe_success = function (data) {
    perform_card_swipe(data);
};
var poo;

function perform_card_swipe(data) {
    poo = data;
    if (cash_register_ready) {
        var line1 = data.line1;
        if (line1.substr(0, 1) === '%') {
            line1 = line1.substring(1, line1.length - 1);
        }
        var line2 = data.line2;
        if (line2.substr(0, 1) === ";") {
            line2 = line2.substring(1, line2.length - 1);
        }
        call_and_response({'command': 'card_swipe', 'line1': line1, 'line2': line2}, response);
        var selector = toast_persistant("Processing Card, Please Wait." + loading_results_html);
        cash_register_buttons_disable();

        function response(e) {
            $(selector).slideUp();
            cash_register_buttons_enable();
            if (e.responseText === '1') {
                cash_register_exit();
            } else {
                toast(e.responseText);
            }
        }
    } else {
        toast('Create a transaction before sliding a card.');
    }
}

var card_swipe_error = function () {
    toast("Card Read Error");
};
$.cardswipe({
    firstLineOnly: false,
    error: card_swipe_error,
    debug: false
});


//==================
// Shared Functions 
//==================
function call_and_response(query_array, response) {
    $.ajax({
        url: 'api.php',
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

function datetime() {
    window.setTimeout(function () {
        $('.datetime').datetimepicker({step: 30, formatTime: 'g:ia'});
    }, 200);
}

// Dialog Functions
//==================
var window_count = 0;
var windows = [];

function new_dialog(title, body, w, h) {
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

function dialog_might_have_ckeditor() {
    $('textarea.wysiwyg').hide();
    window.setTimeout(function () {
        $('.wysiwyg').ckeditor().on('instanceReady.ckeditor', function (event, editor) {
            $(editor.ui.editor.container.$).hide().delay(100).slideDown(300);
        });
    }, 1000);
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
            url: 'api.php',
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
    $('#content-body').append("<div class='toast ui-widget-content' id='toast_" + toasters + "'>" + str + "</div>");
    $('#toast_' + toasters).position({my: 'center center', at: 'center center', of: '#content-body'});
    $('#toast_' + toasters).css('position', 'absolute').css('z-index', toasters + 100).css('top', ($('#toast_' + toasters).position()['top'] - 40) + "px");
    $('#toast_' + toasters).hide().show('clip', 300).delay(5000).hide('clip', 150);
    toasters++;
}

function toast_persistant(str) {
    $('#content-body').append("<div class='toast ui-widget-content' id='toast_" + toasters + "'>" + str + "</div>");
    $('#toast_' + toasters).position({my: 'center center', at: 'center center', of: '#content-body'});
    $('#toast_' + toasters).css('position', 'absolute').css('z-index', toasters + 100).css('top', ($('#toast_' + toasters).position()['top'] - 40) + "px");
    $('#toast_' + toasters).hide().show('clip', 300);
    var selector = '#toast_' + toasters;
    toasters++;
    return selector;
}


google.load("visualization", "1", {packages: ["corechart", "gauge"]});