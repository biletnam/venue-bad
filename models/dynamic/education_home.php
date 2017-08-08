<?php

/**
 * Description of education_home
 *
 * @author lepercon
 */
class education_home implements boffice_html_dynamic_base
{
    public $parent_css_class;
    private $show;

    public function __construct($parameters = "")
    {
        //parameters don't do anything right now
        unset($parameters);
    }

    public function say()
    {
        global $global_conn;
        $string = "";
        foreach (db_query($global_conn, "SELECT * FROM purchasable_registration LEFT JOIN purchasable_registration_category USING (purchasable_registration_category_id) ORDER BY purchasable_registration_category_name, reg_name ASC ") as $item) {
            $reggie = new purchasable_registration($item['purchasable_registration_id']);
            $string .= new boe("div", $reggie->display(2, false));
            //$string .= new boffice_html_region($reggie->display(2, false), boffice_html_region::BOFFICE_HTML_REGION_TYPE_TEASER);
        }
        return $string;
    }


}
