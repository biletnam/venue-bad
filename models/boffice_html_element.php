<?php

class boffice_html_element
{
    public $class;
    public $id;
    public $content;
    public $element_type;
    public $attributes;

    public function __construct($element_type, $content)
    {
        $this->element_type = $element_type;
        $this->content = $content;
        $this->attributes = array();
    }

    public function __toString()
    {
        $string = "\n\t\t<" . $this->element_type . " id=\"" . $this->id . "\" class=\"" . $this->class . "\"";
        foreach ($this->attributes as $attr => $value) {
            $string .= " $attr=\"$value\"";
        }
        $string .= ">";
        if (is_array($this->content)) {
            foreach ($this->content as $element) {
                $string .= $element;
            }
        } else {
            $string .= $this->content;
        }
        $string .= "\n\t\t</" . $this->element_type . ">";
        return $string;
    }

    public function say()
    {
        return "" . (string)$this;
    }
}

/**
 * Alias of boffice_html_element
 */
class boe extends boffice_html_element
{
}

;
