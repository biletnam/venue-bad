<?php

function db_get_connection_by_name($name = null)
{
    global $global_connections,
           $boffice_database_connection_host_address, $boffice_database_connection_password, $boffice_database_connection_type, $boffice_database_connection_username, $boffice_database_name,
           $ulogin_database_connection_host_address, $ulogin_database_connection_password, $ulogin_database_connection_type, $ulogin_database_connection_username, $ulogin_database_name;

    if ($name === null) {
        $name = "boffice"; //default?
    }

    if (!isset($global_connections)) {
        $global_connections = array();
    }

    if (isset($global_connections[$name])) {
        return $global_connections[$name];
    } else {
        $db_type_var = $name . "_database_connection_type";
        $db_type = $$db_type_var;
        $db_pass_var = $name . "_database_connection_password";
        $db_pass = $$db_pass_var;
        $db_user_var = $name . "_database_connection_username";
        $db_user = $$db_user_var;
        $db_host_var = $name . "_database_connection_host_address";
        $db_host = $$db_host_var;
        $db_name_var = $name . "_database_name";
        $db_name = $$db_name_var;
        try {
            $pdo = new PDO($db_type . ':host=' . $db_host . ';dbname=' . $db_name . ';', $db_user, $db_pass); //Establishes a new connection to the server
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $global_connections[$name] = $pdo;
            return $pdo;
        } catch (PDOException $error) { //If any errors occurred while attempting to connect, they will be displayed
            echo "<div id='mysql_error' class='error_msg'>Error connecting to Server: " . $error->getMessage(), "</div>";
            exit();
        }
    }
}

function db_null_date()
{
    return "0000-00-00 00:00:00";
}

function put_web_file_in_sql($url)
{
    global $global_conn;
    $contents = file_get_contents($url);
    $file_info = new finfo(FILEINFO_MIME_TYPE);

    $parts = explode('/', $url);
    $name = $parts[count($parts) - 1];
    $results = db_exec($global_conn, build_insert_query($global_conn, "page_files", array(
        'page_file_name' => $name,
        'page_file_title' => $name,
        'page_file_type' => $file_info->buffer($contents),
        'page_file_contents' => $contents,
        'page_file_contents_date' => date("Y-m-d H:i:s"),
        'updated' => date("Y-m-d H:i:s"),
        'page_file_access_restricted' => '0',
        'page_file_size' => strlen($contents),
        'user' => 'legacy'
    )));
    if ($results['rows_changed'] === 1) {
        return $global_conn->lastInsertId();
    } else {
        return false;
    }
}