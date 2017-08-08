<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of seating_chart
 *
 * @author lepercon
 */
class seating_chart
{
    public $id;
    public $name_internal;
    public $seats;
    public $extras;
    public $seating_chart_row_names;

    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->get($id);
        }

    }

    public function get($id)
    {
        global $global_conn;
        $properties_result = db_query($global_conn, "SELECT * FROM seating_chart WHERE seating_chart_id = " . db_escape($id, $global_conn));
        if (count($properties_result)) {
            $this->id = $id;
            $this->name_internal = $properties_result[0]['seating_chart_name_internal'];
            $this->seating_chart_row_names = $properties_result[0]['seating_chart_row_names'];
            $this->seats = array();
            foreach (db_query($global_conn, "SELECT purchasable_seat_id FROM purchasable_seat WHERE show_seating_chart_id = " . db_escape($id)) as $seat) {
                $new_seat = new purchasable_seat();
                $new_seat->get($seat['purchasable_seat_id'], false);
                $new_seat->show_seating_chart_id = $this->id;
                //$new_seat->show_seating_chart = $this;
                $this->seats[] = $new_seat;
            }
            $this->extras = array();
            foreach (db_query($global_conn, "SELECT seating_chart_extras_instance_id FROM seating_chart_extras_instance WHERE seating_chart_id = " . db_escape($id)) as $extra) {
                $this->extras[] = new seating_chart_extras_instance($extra['seating_chart_extras_instance_id']);
            }
            return true;
        } else {
            return false;
        }
    }

    public function admin_edit_form()
    {
        global $global_conn;

        $elements = array(
            new f_data_element('Stage Name', 'seating_chart_name_internal', 'text'),
            new f_data_element('Row Names', 'seating_chart_row_names', 'textarea'),
        );

        if ($this->id !== null AND $this->id > 0) {
            $f = new f_data($global_conn, 'seating_chart', 'seating_chart_id', $elements, $this->id);
        } else {
            $f = new f_data($global_conn, 'seating_chart', 'seating_chart_id', $elements, false);
        }
        return $f->start();
    }

    public function admin_editor($icon_size = 32, $action = '')
    {
        if (filter_input(INPUT_POST, 'seating_chart_editor_submission')) {
            return $this->admin_editor_process_submission(json_decode(filter_input(INPUT_POST, 'chart_data')));
        } else {
            return $this->admin_editor_build_form($icon_size, $action);
        }
    }

    private function admin_editor_build_form($icon_size = 32, $action = '')
    {
        global $site_domain, $site_path, $global_conn;
        $string = "<div id='chart'><div id='chart-header'>";
        foreach (db_query($global_conn, "SELECT * FROM purchasable_seat_abstract ORDER BY purchasable_seat_abstract_name ASC;") AS $seat) {
            $string .= "<img class='option seat' src='" . $seat['purchasable_seat_abstract_icon_available_url'] . "' alt='" . $seat['purchasable_seat_abstract_name'] . "' width='32' height'32' purchasable_seat_abstract_id='" . $seat['purchasable_seat_abstract_id'] . "' />";
        }
        foreach (db_query($global_conn, "SELECT * FROM seating_chart_extras ORDER BY seating_chart_extra_name ASC") as $extras_option) {
            $string .= "<img class='option extra' alt='" . $extras_option['seating_chart_extra_name'] . "' src='" . $extras_option['seating_chart_extra_icon_url'] . "'  seating_chart_extra_id='" . $extras_option['seating_chart_extra_id'] . "' />";
        }

        $string .= "</div><form action='$action' id='chart-body' method='POST' ><input type='hidden' name='seating_chart_editor_submission' value='1' />";

        if ($this->extras) {
            foreach ($this->extras as $extra) {
                $string .= "<img class='object extra' position_x='" . $extra->seating_chart_extras_instance_x . "' position_y='" . $extra->seating_chart_extras_instance_y . "' src='" . $extra->seating_chart_extra_icon_url . "' width='$icon_size' height='$icon_size' seating_chart_extras_instance_id='" . $extra->seating_chart_extras_instance_id . "' />";
            }
        }
        if ($this->seats) {
            foreach ($this->seats as $seat) {
                $string .= "<img class='object seat'  position_x='" . $seat->position_x . "' position_y='" . $seat->position_y . "'src='" . $seat->purchasable_seat_abstract_icon_available_url . "' width='$icon_size' height='$icon_size.' purchasable_seat_id='" . $seat->purchasable_seat_id . "' />";
            }
        }
        boffice_html::$css_internal .= "
	    #chart-body { background-color:#dedede; min-width:400px; min-height:400px; }
	    #chart img.object, #chart img.option, #chart img.new, #chart img.extra { width:" . $icon_size . "px; height:" . $icon_size . "px; }";
        boffice_html::$js_external_src[] = "//" . $site_domain . $site_path . "admin/seating_chart_editor.js";
        return $string .= "</form><button id='chart-submit'>Save Chart</button>
	    <p>This chart is currently used " . $this->uses_count() . " times. The more frequently it is used, the longer it will take to update.</p>
	    </div>";
    }

    private function admin_editor_process_submission($json)
    {
        if (filter_input(INPUT_GET, 'row_id')) {
            $this->id = filter_input(INPUT_GET, 'row_id');
        }
        if (filter_input(INPUT_POST, 'row_id')) {
            $this->id = filter_input(INPUT_POST, 'row_id');
        }
        $has_changes = false;
        foreach ($json as $item) {
            if (isset($item->isNew, $item->purchasable_seat_abstract_id)) {
                $seat = new purchasable_seat();
                $seat->position_x = $item->x;
                $seat->position_y = $item->y;
                $seat->purchasable_seat_abstract_id = $item->purchasable_seat_abstract_id;
                $seat->show_seating_chart_id = $this->id;
                $seat->set();
                $has_changes = true;
                boffice_html::$html_body_prepend .= "<div class='notice'>new seat from seat_type_id:" . $item->purchasable_seat_abstract_id . "</div>";
            }
            if (isset($item->isNew, $item->seating_chart_extra_id)) {
                $extra = new seating_chart_extras_instance();
                $extra->seating_chart_id = $this->id;
                $extra->seating_chart_extra_id = $item->seating_chart_extra_id;
                $extra->seating_chart_extras_instance_x = $item->x;
                $extra->seating_chart_extras_instance_y = $item->y;
                $extra->set();
                $has_changes = true;
                boffice_html::$html_body_prepend .= "<div class='notice'>new chart extra from chart_extra_id:" . $item->seating_chart_extra_id . "</div>";
            }
            if (!isset($item->isNew) AND !isset($item->remove) AND isset($item->purchasable_seat_id)) {
                $original_seat = new purchasable_seat($item->purchasable_seat_id);
                if ($original_seat->position_x != $item->x OR $original_seat->position_y != $item->y) {
                    $original_seat->position_x = $item->x;
                    $original_seat->position_y = $item->y;
                    $original_seat->set();
                    $has_changes = true;
                    boffice_html::$html_body_prepend .= "<div class='notice'>seat moved:" . $original_seat->purchasable_seat_id . "</div>";
                }
            }
            if (!isset($item->isNew) AND !isset($item->remove) AND isset($item->seating_chart_extras_instance_id)) {
                $original_extra = new seating_chart_extras_instance($item->seating_chart_extras_instance_id);
                if ($original_extra->seating_chart_extras_instance_x != $item->x OR $original_extra->seating_chart_extras_instance_y != $item->y) {
                    $original_extra->seating_chart_extras_instance_x = $item->x;
                    $original_extra->seating_chart_extras_instance_y = $item->y;
                    $original_extra->set();
                    $has_changes = true;
                    boffice_html::$html_body_prepend .= "<div class='notice'>chart extra moved:" . $original_seat->purchasable_seat_id . "</div>";
                }
            }
            if (isset($item->remove)) {
                if (isset($item->purchasable_seat_id)) {
                    $original_seat = new purchasable_seat($item->purchasable_seat_id);
                    $name = $original_seat->get_readible_name();
                    if ($original_seat->delete() === '-1') {
                        new boffice_error("Could not delete seat ($name) because it has at least one reservation. Cancel those reservations first before deleting that seat or make a new seating chart.");
                    } else {
                        $has_changes = true;
                        boffice_html::$html_body_prepend .= "<div class='notice'>seat ($name) deleted.</div>";
                    }
                } else if (isset($item->seating_chart_extras_instance_id)) {
                    $original_extra = new seating_chart_extras_instance($item->seating_chart_extras_instance_id);
                    $name = $original_extra->seating_chart_extra_name;
                    $original_extra->delete();
                    $has_changes = true;
                    boffice_html::$html_body_prepend .= "<div class='notice'>Removed seating a chart extra - $name.</div>";
                } else {
                    new boffice_error("Could not remove a requested deletion. Please check the seating chart and try again. (Neither a seat nor an extra)");
                }
            }
        }

        if ($has_changes) {
            $i = $this->update_seating_charts();
            boffice_html::$html_body_prepend .= "<div class='notice'>Updated $i seating charts.</div>";
        }

    }

    static public function admin_list($href = '')
    {
        global $global_conn;
        $string = "<ul class='admin-list'><li><a href='$href'>New seating chart</a></li>";
        foreach (db_query($global_conn, "SELECT * FROM seating_chart ORDER BY seating_chart_name_internal ASC") as $item) {
            $chart = new seating_chart($item['seating_chart_id']);
            $string .= "<li><a href='" . $href . "?row_id=" . $item['seating_chart_id'] . "'>" . $item['seating_chart_name_internal'] . "</a> - " . $chart->uses_count() . "uses</li>";
        }
        return $string . "</ul>";
    }

    public function readible_seat_name($seat, $short = false)
    {
        $row_names = explode(',', $this->seating_chart_row_names);
        $position = $this->get_seat_rows_and_columns($seat);
        if ($short) {
            return "" . $row_names[$position[0]] . " " . ($position[1] + 1);
        } else {
            return " Row:" . $row_names[$position[0]] . " Seat:" . ($position[1] + 1);
        }
    }

    public function get_seat_rows_and_columns($target_seat)
    {
        $arr = $this->array_of_seats_in_rows();
        foreach ($arr as $row_name => $row) {
            $x = 0;
            foreach ($row as $seat) {
                if ($target_seat->position_x === $seat->position_x AND $target_seat->position_y === $seat->position_y) {
                    return array($row_name, $x);
                }
                $x++;
            }
        }
        return null;
    }

    public function array_of_seats_in_rows()
    {
        global $site_count_seats_right_to_left;
        $array = array();
        foreach ($this->seats as $s) {
            $array[$s->position_y][$s->position_x] = $s;
        }
        if ($site_count_seats_right_to_left) {
            $new = array();
            foreach ($array as $row) {
                $new[] = array_reverse($row);
            }
            $array = $new;
        }
        return $array;
    }

    public function update_seating_charts()
    {
        global $global_conn;
        $i = 0;
        foreach (db_query($global_conn, "SELECT * FROM shows WHERE seating_chart_id = " . db_escape($this->id)) as $row) {
            foreach (show::get_instances($row['show_id'], true) as $instance) {
                $instance->seating_chart_html_update();
                $i++;
            }
        }
        return $i;
    }

    public function uses_count()
    {
        global $global_conn;
        $i = 0;
        foreach (db_query($global_conn, "SELECT * FROM shows WHERE seating_chart_id = " . db_escape($this->id)) as $row) {
            $i += count(show::get_instances($row['show_id'], true));
        }
        return $i;
    }
}
