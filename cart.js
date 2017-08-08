var card_valid;
$(document).ready(function () {
    $('#cart_add_giftcard').hide();
    $('#cart_add_donation').hide();
    $('.giftcard-show').click(function () {
        $('#cart_add_giftcard').slideDown();
        $('#cart_checkout_form').slideUp();
        $('#cart_add_donation').slideUp();
    });
    $('.creditcard-show').click(function () {
        $('#cart_add_giftcard').slideUp();
        $('#cart_add_donation').slideUp();
        $('#cart_checkout_form').slideDown();
    });
    $('.donation-prompt').click(function () {
        $('#cart_add_giftcard').slideUp();
        $('#cart_add_donation').slideDown();
        $('#cart_checkout_form').slideUp();
    });
    $('.card-image').html("<img src='../_resources/images/credit_card_glass_visa_up.png' /><img src='../_resources/images/credit_card_glass_mastercard_up.png' /><img src='../_resources/images/credit_card_glass_amex_up.png' />");
    $('#credit_card_number').validateCreditCard(function (data) {
        card_valid = false;
        if (data.card_type !== null) {
            $('.card-image').html("<img src='../_resources/images/credit_card_glass_" + data.card_type.name + "_down.png' />");
        } else {
            $('.card-image').html("<img src='../_resources/images/credit_card_glass_visa_up.png' /><img src='../_resources/images/credit_card_glass_mastercard_up.png' /><img src='../_resources/images/credit_card_glass_amex_up.png' />");
        }
        if (data.luhn_valid === true && data.length_valid === true) {
            $('.card-image').append("<img src='../_resources/images/mark_check_green_50.png' />");
            card_valid = true;
        }
        form_validation();
    });

    $('#user_name_first, #user_name_last, #user_address_line1, #user_city, #user_zip').on('input', form_validation);
    $('#expiry_month, #expiry_year').change(form_validation);

    $('#cart_checkout_form').submit(function (e) {
        if (form_validation()) {
            $('#credit_card_number').attr('type', 'password');
            $('#submit_button').button('disable');
            overlay_block("Processing...");
            return true;
        } else {
            e.preventDefault();
        }
    });

    $('#cart_add_donation').submit(function () {
        overlay_block("Processing Donation Request...")
    });
    post_to_ajax_public('#cart_add_donation', 'donation_add', function (e) {
        overlay_block_remove();
        location.reload();
    });

    $('.remove-item').button({icons: {primary: 'ui-icon-closethick'}});
});


function form_validation() {
    var valid = true;
    var fields = ['user_name_first', 'user_name_last', 'user_address_line1', 'user_city', 'user_zip'];
    for (var i = 0; i < fields.length; i++) {
        if ($('#' + fields[i]).val().length === 0) {
            valid = false;
        }
    }
    if (!card_valid) {
        valid = false;
    }
    test_day = new Date($('#expiry_year').val(), Math.round($('#expiry_month').val()), -1, 01, 1, 59);
    if (test_day < new Date()) {
        valid = false;
    }
    $('#submit_button').button({disabled: !valid});
    return valid;
}

