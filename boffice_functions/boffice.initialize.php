<?php


$global_conn;
function boffice_initialize()
{
    global $global_conn;
    $global_conn = db_get_connection_by_name('boffice');

    if (session_id() === "") {
        session_start();
    }
    if (!isset($_SESSION['boffice'])) {
        $_SESSION['boffice'] = Array(); //Create an array within the session array to store seesion data. Using the session name (must be defined before this is run such as in config.php) to force a unqiue sesssion
    }

    if (!sses_running()) {
        sses_start();
    }
}

