<?php

function bus_overview()
{
    $string = "<div id='income'>";

    $gross_income_for_month = boffice_stat_gross_income_date_range("2015-01-01", "2015-01-30");
    $string .= bus_google_timeline($gross_income_for_month, "Gross Income", "Day", "Gross Income");

    $string .= "</div><div id='guages_overview'>";
    $shows = show::get_upcoming_shows(10);
    foreach ($shows as $show) {
        $instances = $show->get_instances($show->show_id, true);
        $string .= "<div class='guage_array' show_id='" . $show->show_id . "'><h2>" . $show->title . "</h2>";
        foreach ($instances as $show_instance) {
            $cache = show_instance::get_cache($show_instance->show_instance_id);
            $reserved = $cache->show_instance_cache_reserved;
            $total = $cache->show_instance_cache_total + .00001;
            if ($reserved / $total >= .9) {
                $p = "90";
            } else if ($reserved / $total >= .75) {
                $p = "75";
            } else {
                $p = "00";
            }
            $past = "future";

            if (time() > strtotime($show_instance->datetime)) {
                $past = "past";
            }
            $string .= "
		<div class='percent-$p $past'>
		    <div>" . date("M jS", strtotime($show_instance->datetime)) . "</div>
		    <div id='show_instance_gauge_" . $show_instance->show_instance_id . "' show_instance_id='" . $show_instance->show_instance_id . "'></div> 
		    <div>$reserved of " . round($total) . "</div>
		</div>
		<script>
		$(function() {
		    var data = google.visualization.arrayToDataTable([
			['Label', 'Value'], ['Capacity', " . round(100 * ($reserved / $total)) . " ]
			]
		    );
		    var options = { width: 80, height: 80, redFrom: 90, redTo: 100, yellowFrom:75, yellowTo: 90, minorTicks: 5};
		    var chart = new google.visualization.Gauge(document.getElementById('show_instance_gauge_" . $show_instance->show_instance_id . "'));
		    chart.draw(data, options);
		});
		</script>
		  ";
        }
        $string .= "</div>";
    }
    $string .= "</div>";
    return $string;
}

function bus_show($show_id)
{
    $round_to_days = true;
    $data = boffice_stat_preorder_time_by_show($show_id, $round_to_days);
    return bus_google_timeline(bus_google_timeline_prepare_data($data), "Time before show tickets are preordered", ($round_to_days ? "Days" : "Hours"), "Tickets");
}

function bus_instances($show_id)
{
    $string = "<ul class='bus_instances'>";
    foreach (show::get_instances($show_id, true) as $instance) {
        $string .= "<li class='bus_instance' show_instance_id='" . $instance->show_instance_id . "'>" . date("M jS, g:ia", strtotime($instance->datetime)) . "</li>";
    }
    $string .= "</ul>";
    return $string;
}


function bus_google_timeline_prepare_data($data, $window = 0.05)
{
    $first_time = array_keys($data)[0];
    $last_time = array_keys($data)[count($data) - 1];
    $count = max(abs(0 - $first_time), $last_time - $first_time);
    $window_value = $count * $window;
    $filler = boffice_array_fill($first_time - $window_value, $count + (2 * $window_value), 0);
    $full_data_set = boffice_array_sub_sum(array($data, $filler));
    ksort($full_data_set);
    return $full_data_set;
}


function bus_google_timeline($array, $title, $key_column_label, $values_column_label)
{
    $rand_id = random_string(10);
    $string = "
	<div class='google_timeline_chart' id='$rand_id'>
	
	<script type='text/javascript'>
	var data$rand_id = google.visualization.arrayToDataTable([
	    ['$key_column_label', '$values_column_label'],
	    ";
    foreach ($array as $key => $value) {
        $string .= "[$key, $value],";
    }
    $string = substr($string, 0, -1);
    $string .= " 
        ]);

        var options$rand_id = {
          title: \"$title\",
          curveType: 'function',
          legend: { position: 'bottom' },
	  animation : {duration: 3, startup: true, easing: 'out'},
	  pointSize: 5
        };

        var chart = new google.visualization.LineChart(document.getElementById('$rand_id'));
	chart.draw(data$rand_id, options$rand_id);
	</script>
	";
    return $string;
}