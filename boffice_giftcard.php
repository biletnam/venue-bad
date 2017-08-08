<?php
require_once('boffice_config.php');
boffice_initialize();

global $site_domain, $site_path;

$p = filter_input(INPUT_GET, 'p');
if (substr($p, -1) === "/") {
    $p = substr($p, 0, -1);
}
$p_parts = explode('/', $p);

$USA_STATES = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'Dist of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming');

user::login_required($site_path . "giftcard/");
$user = user::current_user();

$giftcard_js_string = "
    $(function() {
	$('#delivery_method_ship').hide();
	$('#delivery_method_selection input[name=giftcard_delivery_method]').click(function() {
	    $('#delivery_method_email, #delivery_method_ship').slideUp();

	    var method = $('input[name=giftcard_delivery_method]:checked', '#giftcard_creation').val();
	    if(method === 'email') {
		$('#delivery_method_email').slideDown();
	    }
	    if(method === 'ship') {
		$('#delivery_method_ship').slideDown();
	    }
	});

	$('#giftcard_value').spinner({
	    min:5,
	    max:1000,
	    step:1
	});

	$( document ).tooltip();
	$('#giftcard_creation button').button({'disabled':true});
	$('#giftcard_creation input').on('input', function () {validate_giftcard_form(false)});
	$('#giftcard_creation input').change(function () {validate_giftcard_form(false)});

	$('#giftcard_creation').submit(function (e) {
	    var success = validate_giftcard_form(true);
	    if(!success) {
		e.preventDefault();
		$('#giftcard_creation').append(\"<div id='form_error_message'>There was a problem submitting your form. If you are unsure of your recipients information, choose the 'you print' option and hand deliver it!</div>\");
		$('#form_error_message').dialog({modal:true, title:'A Problem.', width:300, height:300, close:function() { $('#form_error_message').remove(); }});
	    }
	});
    });

    function validate_giftcard_form(addClasses) {
	var success = true;
	var method_test = $('input[name=giftcard_delivery_method]:checked', '#giftcard_creation').val();
	if(method_test === 'email') {
	    if(!validateEmail( $('#recipient_email_address').val() )) {
		success = false;
		if(addClasses) {
		    $('#recipient_email_address').addClass('invalid');
		}
	    }
	}
	if(method_test === 'ship') {
	    arr = ['recipient_address_name','recipient_address_line_1','recipient_address_city','recipient_address_zip'];
	    for(var i=0; i<arr.length; i++) {
		if($('#'+arr[i]).val().length === 0) {
		    if(addClasses) {
			$('#'+arr[i]).addClass('invalid');
		    }
		    success=false;
		} else {
		    $('#'+arr[i]).removeClass('invalid');
		}
	    }
	}

	$('#giftcard_creation button').button({'disabled':!success});

	return success;
    }
    ";

$giftcard_html_string = "
	<form action='//" . $site_domain . $site_path . "giftcard/new' method='POST' id='giftcard_creation'>
		
	    <fieldset id='delivery_method_print'><legend>Your giftcard</legend>
		<div class='form-row'>
		    <label for='recipient_print_to'>To: </label>
		    <input type='text' name='recipient_print_to' id='recipient_print_to' class='optional' title='Optional. Appears on the giftcard.' />
		</div>
		<div class='form-row'>
		    <label for='recipient_print_from'>From: </label>
		    <input type='text' name='recipient_print_from' id='recipient_print_from' class='optional' title='Optional. Appears on the giftcard.' />
		</div>
		<div class='form-row'>
		    <label for='giftcard_value'>Value</label>
		    <input type='text' name='giftcard_value' id='giftcard_value' value='20' title='Giftcards can range from $5 to $500' />
		</div>
	    </fieldset>
	    
	    <fieldset id='delivery_method_selection' class='long-labels'><legend>Delivery Method</legend>
		<div class='form-row'>
		    <input type='radio' name='giftcard_delivery_method' id='method_email' value='email' checked='checked'>
		    <label for ='method_email'>They print - We'll immediately email your gift for your recipient to print.</label>
		</div>
		<div class='form-row'>
		    <input type='radio' name='giftcard_delivery_method' id='method_print' value='print'>
		    <label for ='method_print'>You print - We'll immeditely send you a printable giftcard that you can deliver in person!</label>
		</div>
		<div class='form-row'>
		    <input type='radio' name='giftcard_delivery_method' id='method_ship' value='ship'>
		    <label for='method_ship'>We print - printed and mailed to any address in the US within 2 to 3 Busness Days</label>
		</div>
	    </fieldset>
	    
	    <fieldset id='delivery_method_email'><legend>Recipient Email</legend>
		<div class='form-row'>
		    <label for='recipient_email_name'>Recipient Name</label>
		    <input type='text' name='recipient_email_name' id='recipient_email_name' />
		</div>
		<div class='form-row'>
		    <label for='recipient_email_address'>Recipient Email</label>
		    <input type='email' name='recipient_email_address' id='recipient_email_address' title='Required. Only used to send your gift.' />
		</div>
	    </fieldset>
	    	    
	    <fieldset id='delivery_method_ship' class='medium-labels'><legend>Shipping Information</legend>
		<div class='form-row'>
		    <label for='recipient_address_name'>Name, as it should appear on the envelope</label>
		    <input type='text' name='recipient_address_name' id='recipient_address_name'/>
		</div>
		<div class='form-row'>
		    <label for='recipient_address_line_1'>Recipient address</label>
		    <input type='text' name='recipient_address_line_1' id='recipient_address_line_1' />
		</div>
		<div class='form-row'>
		    <label for='recipient_address_line_2'>Recipient address secondary line</label>
		    <input type='text' name='recipient_address_line_2' id='recipient_address_line_2' />
		</div>
		<div class='form-row'>
		    <label for='recipient_address_city'>Recipient city</label>
		    <input type='text' name='recipient_address_city' id='recipient_address_city' />
		</div>
		<div class='form-row'>
		    <label for='recipient_address_state'>Recipient state</label>
		    <select id='recipient_address_state' name='recipient_address_state'>";
foreach ($USA_STATES as $abbr => $name) {
    $giftcard_html_string .= "<option value='$abbr' " . ($abbr === "SC" ? "SELECTED='SELECTED'" : "") . " >$name</option>";
}
$giftcard_html_string .= " 
		    </select>
		</div>
		<div class='form-row'>
		    <label for='recipient_address_zip'>Recipient zip</label>
		    <input type='text' name='recipient_address_zip' id='recipient_address_zip' />
		</div>
	    </fieldset>
	    
	    <div class='form-row'>
		<button type='SUBMIT' value='Add to cart'>Add to cart</button>
	    </div>
	</form>
	";


if ((!isset($p_parts[0]) OR $p_parts[0] !== "new") AND count($p_parts) < 2) {
    boffice_html::$js_internal = $giftcard_js_string;
    boffice_html::$html_body_regions[] = new boe("div", $giftcard_html_string);


} else if ($p_parts[0] === "new") {
    if (filter_input(INPUT_POST, 'giftcard_delivery_method') === null) {
        new boffice_error("Your giftcard request had some issues.", true, 'giftcard_value');
    }
    if (filter_input(INPUT_POST, 'giftcard_value') < 5 OR filter_input(INPUT_POST, 'giftcard_value') > 999) {
        new boffice_error("Giftcards must be more than $5.00 and less than $500.00", true, 'giftcard_value');
    }

    $method = filter_input(INPUT_POST, 'giftcard_delivery_method');
    if ($method === 'email') {
        if (!valid_email(filter_input(INPUT_POST, 'recipient_email_address'))) {
            new boffice_error("Recipient email address does not appear valid", true, 'recipient_email_address');
        } else if (filter_input(INPUT_POST, 'recipient_email_name') !== null) {
            $address = filter_input(INPUT_POST, 'recipient_email_name') . " <" . filter_input(INPUT_POST, 'recipient_email_address') . ">";
        } else {
            $address = filter_input(INPUT_POST, 'recipient_email_address');
        }
    } else if ($method === 'ship') {
        foreach (array('recipient_address_name', 'recipient_address_line_1', 'recipient_address_city', 'recipient_address_zip') as $field) {
            if (strlen(filter_input(INPUT_POST, $field)) < 4) {
                new boffice_error("Incomplete shipping information", true, $field);
            }
        }
        $address = filter_input(INPUT_POST, 'recipient_address_name') . "\n" . filter_input(INPUT_POST, 'recipient_address_line_1') . "\n" . filter_input(INPUT_POST, 'recipient_address_line_2') . "\n" . filter_input(INPUT_POST, 'recipient_address_city') . "\n" . filter_input(INPUT_POST, 'recipient_address_state') . "\n" . filter_input(INPUT_POST, 'recipient_address_zip');
    } else if ($method === 'print') {
        $address = "";
    }

    if (boffice_error::has_form_errors()) {
        boffice_html::$js_internal = $giftcard_js_string;
        boffice_html::$html_body_regions[] = new boe("div", $giftcard_html_string);
    } else {
        $new_giftcard = new purchasable_giftcard_instance();
        $new_giftcard->purchasable_giftcard_instance_from = filter_input(INPUT_POST, 'recipient_print_from');
        $new_giftcard->purchasable_giftcard_instance_to = filter_input(INPUT_POST, 'recipient_print_to');
        $new_giftcard->purchasable_giftcard_instance_send_method = filter_input(INPUT_POST, 'giftcard_delivery_method');
        $new_giftcard->purchasable_giftcard_instance_starting_value = filter_input(INPUT_POST, 'giftcard_value');
        $new_giftcard->purchasable_giftcard_instance_send_data = $address;
        $new_giftcard->purchasable_giftcard_instance_activated = '0';
        $new_giftcard->purchasable_giftcard_instance_created = date("Y-m-d H:i:s");
        $new_giftcard->set();

        $cart = cart::cart_from_user_id($user->user_id);
        $cart->cart_item_new("purchasable_giftcard_instance", $new_giftcard->purchasable_giftcard_instance_id, 1, true);
        boffice_html::$html_body_regions[] = new boe("div", "Your giftcard has been added to your cart. <a href='//" . $site_domain . $site_path . "checkout'>Checkout</a> or <a href='//" . $site_domain . $site_path . "'>Keep Looking</a>");
    }
} else {
    if (count($p_parts) === 2 AND substr($p_parts[1], -4) === ".jpg") {
        $test_card = purchasable_giftcard_instance::get_card_by_id(substr($p_parts[1], 0, -4));
        if ($test_card === null) {
            header("//" . $site_domain . $site_path);
            die();
        }
        if ($test_card->purchasable_giftcard_instance_robot_url === $p_parts[0]) {
            $test_card->image_data();
            die();
        }
    } else {
        header("//" . $site_domain . $site_path);
        die();
    }
}

echo boffice_template_simple("Some title");