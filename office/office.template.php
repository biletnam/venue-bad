<?php

function boffice_template_admin($body = "", $notices = array())
{
    global $site_path;
    $string = "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
    <title>Boxoffice - Admin</title>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
    <script src='" . $site_path . "_resources/external/jquery/jquery-1.11.1.min.js'></script>
    <script src='" . $site_path . "_resources/external/jquery-ui/js/jquery-ui-1.10.4.custom.min.js'></script>
    <script src='" . $site_path . "_resources/external/datetimepicker-master/jquery.datetimepicker.js'></script>
    <script src='" . $site_path . "admin/seating_chart_editor.js'></script>
    <script src='" . $site_path . "_resources/external/ckeditor/ckeditor.js'></script>
    <script src='" . $site_path . "_resources/external/ckeditor/adapters/jquery.js'></script>
    <script src='" . $site_path . "_resources/external/jquery.columnizer.js'></script>
    <script src='" . $site_path . "_resources/external/jquery.cardswipe.js'></script>
    <script src='https://www.google.com/jsapi' type='text/javascript'></script>
    <script type='text/javascript' src='office.js'></script>
    <script type='text/javascript' src='office.business.js'></script>
    <script type='text/javascript' src='office.notices.js'></script>
    <link rel='stylesheet' href='" . $site_path . "_resources/boffice_master.css' >	
    <link rel='stylesheet' href='" . $site_path . "_resources/external/jquery-ui/css/overcast/jquery-ui-1.10.4.custom.min.css' >    
    <link rel='stylesheet' href='" . $site_path . "_resources/external/datetimepicker-master/jquery.datetimepicker.css' >
    <link rel='stylesheet' href='office.css' >
</head>
<body>
    <nagivation id='navigation'>
	<div id='logout' class='butt_wrapper'><button>Logout</button></div>
	<div id='clear-windows' class='butt_wrapper'><button>Clear</button></div>
	<span class='buttonset'>
	    <div id='edu-admin' class='butt_wrapper'><button class='edu'>Education</button></div>
	    <div id='purse-admin' class='butt_wrapper'><button class='purse'>Business</button></div>
	    <div id='office-admin' class='butt_wrapper'><button class='.jpce'>Box Office</button></div>
	    <div id='web-admin' class='butt_wrapper'><button class='webadmin'>Web</button></div>
	</span>
	<span class='seperator'></span>
	<div id='user_picker'><input class='office' type='text' placeholder='user search' /></div>
	<div id='user-new' class='butt_wrapper'><button>New User</button></div>
	<br />
	<span class='row_2'>
	    <span id='office-buttons'>
		<div id='show-new' class='butt_wrapper'><button class='office'>New Show</button></div>
		<span class='seperator'></span>
		<div id='showtime-new' class='butt_wrapper'><button class='office'>New Showtime</button></div>
		<div id='show-edit' class='butt_wrapper'><button class='office'>Edit Show</button></div>
		<div id='showtime-edit' class='butt_wrapper'><button class='office'>Edit Showtime</button></div>
		<div id='show_picker' class='office'></div>
		<div id='chart-refresh' class='butt_wrapper'><button class='office'>Chart</button></div>
		<span class='seperator'></span>
		<div id='sell-package' class='butt_wrapper'><button class='office'>Sell Package</button></div>
		<div id='sell-giftcard' class='butt_wrapper'><button class='office'>Sell Giftcard</button></div>
	    </span>
	    <span id='edu-buttons'>
		<div id='class-category-new' class='butt_wrapper'><button class='edu'>New Series/Category</button></div>
		<div id='class-category-picker' class='edu'></div>
		<span class='seperator'></span>
		<div id='class-new' class='butt_wrapper'><button class='edu'>New Class</button></div>
		<div id='class-edit' class='butt_wrapper'><button class='edu'>Edit Class</button></div>
		<div id='class-picker' class='edu'></div>
	    </span>
	    <span id='purse-buttons'>
		<div id='purse-shows'><button class='purse'>Nightlies</button></div>
		<div id='purse-dailies'><button class='purse'>Settlements</button></div>
		<div id='purse-shows'><button class='purse'>Shows</button></div>
	    </span>
	    <span id='webadmin-buttons'>
		<div id='webadmin-shows'><button class='webadmin'>Shows</button></div>
		<div id='webadmin-properties'><button class='webadmin'>Settings</button></div>
		
	    </span>
	</span>
	
    </nagivation>
    <section id='content'>
	<div id='content-body'>
	   $body	
	    <div id='seating-chart'></div>
	    <div id='edu'></div>
	    <div id='purse'></div>
	    <div id='web'></div>
	    
	</div>
	<section id='willcall-area' class='list-right'>
	    <h3>Will Call</h3><div id='willcall-list'></div>
	</section>
	
	<div id='content-cash-register'></div>
	<div id='notices'>";
    foreach ($notices as $n) {
        $string .= "<div class='item'>$n</div>";
    }
    $string .= "
	</div>
    </section>
    
</body>
</html>";

    return $string;
}
