<?php

function boffice_template_simple($title = "", $body = "")
{
    if ($title === "") {
        $title = boffice_html::get_title();
    }
    $string = "<html lang='en'>
    <head>
	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
	<title>$title</title>
	<meta charset='utf-8'>
	<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>"
        . boffice_html::get_js_external()
        . boffice_html::get_js_internal()
        . boffice_html::get_css_external()
        . boffice_html::get_css_internal()
        . "
    </head>
    <body>
	<a href='#content' class='not-visible'>Skip to content</a>
	<div id='outer-wrapper'>
	    <section id='user-navigation'>
		" . boffice_html::get_user_navigation() . " 
	    </section>
	    <navigation>
		" . boffice_html::get_navigation() . "
	    </navigation>
	    <header>
		" . boffice_html::get_header() . "
		" . boffice_html::$html_header_append . "
	    </header>
	    <section id='content'>
		" . boffice_html::$html_body_prepend
        . "<div id='content-body'>
		    " . boffice_html::get_html_body_regions()
        . $body
        . "</div>
		" . boffice_html::$html_body_append
        . "</section>
	    <footer>
		" . boffice_html::$html_footer_prepend
        . "<div>footer content</div>
		" . boffice_html::$html_footer_append
        . "</footer>
	</div>
    </body>
</html>";

    return $string;
}
