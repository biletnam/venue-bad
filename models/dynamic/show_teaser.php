<?php

/**
 * Description of show_teaser
 *
 * @author lepercon
 */
class show_teaser implements boffice_html_dynamic_base
{
    public $parent_css_class;
    private $show;

    public function __construct($parameters = "")
    {
        $parts = explode(';', $parameters);
        if (count($parts) === 0) {
            $this->show = show::get_current_show();
        } else if (count($parts) === 1) {
            if (is_numeric($parts[0])) {
                $this->show = new show($parts[0]);
            } else {
                $this->show = show::get_current_show();
                $this->parent_css = $parts[0];
            }
        } else if (count($parts) === 2) {
            if (is_numeric($parts[0])) {
                $this->show = new show($parts[0]);
                $this->parent_css = $parts[1];
            } else {
                $this->show = new show($parts[1]);
                $this->parent_css = $parts[0];
            }
        }
    }

    public function say()
    {
        global $site_domain, $site_path;
        $string = "<a href='//" . $site_domain . $site_path . "show/" . $this->show->url_name . "'><img src='" . $this->show->cover_image_url . "' />" . htmlspecialchars($this->show->title) . "</a>";
        return $string;
    }
}
