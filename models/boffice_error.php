<?php

/**
 * Description of boffice_error
 *
 * @author lepercon
 */
class boffice_error
{
    public $string;
    public $continuable;
    public $form_element;

    public static $count = 0;
    public static $form_count = 0;
    public static $errors = array();

    public function __construct($string, $continuable = true, $form_element = null)
    {
        $this->string = $string;
        $this->continuable = $continuable;
        $this->form_element = $form_element;
        boffice_error::$errors[] = $this;
        boffice_error::$count++;
        if ($form_element !== null) {
            boffice_error::$form_count++;
        }
        if (!$continuable) {
            $this->say();
            boffice_html::$html_body_regions = array();
            echo boffice_template_simple("Error");
            die();
        } else {
            $this->say(true);
        }
    }

    public function __toString()
    {
        return $this->string;
    }

    static public function any_errors()
    {
        return boffice_error::$count > 0;
    }

    static public function has_form_errors()
    {
        return boffice_error::$form_count > 0;
    }

    /**
     * Get the boffice error for an element
     * @param string $element_name
     * @return boffice_error
     */
    static public function form_error_by_element($element_name)
    {
        foreach (boffice_error::$errors as $err) {
            if ($err->form_element === $element_name) {
                return $err;
            }
        }
    }

    public function say($html_prepend = true)
    {
        $string = "<div class='error'>" . $this->string . "</div>";
        if ($html_prepend) {
            boffice_html::$html_body_prepend .= $string;
        } else {
            return $string;
        }
    }

    static public function error_element($element)
    {
        foreach (boffice_error::$errors as $err) {
            if ($err->form_element === $element) {
                return "<span class='field-error'>" . $err->string . "</span>";
            }
        }
        return "";
    }
}
