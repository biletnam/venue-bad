<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of boffice_html_region
 *
 * @author lepercon
 */
class boffice_html_region
{
    public $inner_html;

    public $region_type;
    const BOFFICE_HTML_REGION_TYPE_FEATURE = '101';
    const BOFFICE_HTML_REGION_TYPE_ARTICLE = '103';
    const BOFFICE_HTML_REGION_TYPE_RELATED = '102';
    const BOFFICE_HTML_REGION_TYPE_TEASER = '104';
    const BOFFICE_HTML_REGION_TYPE_NAV_PRIMARY = '201';
    const BOFFICE_HTML_REGION_TYPE_NAV_SECONDARY = '202';
    const BOFFICE_HTML_REGION_TYPE_NAV_TERIARY = '203';


    public function __construct($inner_html, $region_type = null)
    {
        $this->inner_html = $inner_html;
        if ($region_type === null) {
            $this->region_type = boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE;
        } else {
            $this->region_type = $region_type;
        }
    }

    public function say()
    {
        $string = "
	    <div class='boffice-region boffice-region-" . boffice_html_region::region_class_name($this->region_type) . "'>
		" . $this->inner_html . "
	    </div>";
        return $string;
    }

    public static function region_class_name($region_type)
    {
        $array = array(
            boffice_html_region::BOFFICE_HTML_REGION_TYPE_FEATURE => 'feature',
            boffice_html_region::BOFFICE_HTML_REGION_TYPE_ARTICLE => 'article',
            boffice_html_region::BOFFICE_HTML_REGION_TYPE_RELATED => 'related',
            boffice_html_region::BOFFICE_HTML_REGION_TYPE_TEASER => 'teaser',
            boffice_html_region::BOFFICE_HTML_REGION_TYPE_NAV_PRIMARY => 'nav-primary',
            boffice_html_region::BOFFICE_HTML_REGION_TYPE_NAV_SECONDARY => 'nav-secondary',
            boffice_html_region::BOFFICE_HTML_REGION_TYPE_NAV_TERIARY => 'nav-teriary',
        );
        return $array[$region_type];
    }
}
