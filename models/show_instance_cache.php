<?php

/**
 * Description of show_instance_cache
 *
 * @author lepercon
 */
class show_instance_cache
{
    public $show_instance_id;
    public $show_instance_cache_time;
    public $show_instance_cache_total;
    public $show_instance_cache_reserved;
    public $show_instance_cache_available;

    static private $cache_memory;

    public function __construct()
    {
    }

    /**
     *
     * @global PDO $global_conn
     * @param int $show_instance_id
     * @return \show_instance_cache
     */
    static public function get($show_instance_id)
    {
        global $global_conn;
        if (isset(show_instance_cache::$cache_memory[$show_instance_id])) {
            return show_instance_cache::$cache_memory[$show_instance_id];
        }

        $results = db_query($global_conn, "SELECT * FROM show_instance_cache WHERE show_instance_id = " . db_escape($show_instance_id));
        if (count($results) === 0) {
            return show_instance_cache::create($show_instance_id);
        }
        if (count($results) === 1) {
            $cache = new show_instance_cache();
            $cache->show_instance_id = $results[0]['show_instance_id'];
            $cache->show_instance_cache_time = $results[0]['show_instance_cache_time'];
            $cache->show_instance_cache_total = $results[0]['show_instance_cache_total'];
            $cache->show_instance_cache_reserved = $results[0]['show_instance_cache_reserved'];
            $cache->show_instance_cache_available = $results[0]['show_instance_cache_available'];
            show_instance_cache::$cache_memory[$show_instance_id] = $cache;
            return $cache;
        }
        show_instance_cache::clear($show_instance_id);
        return show_instance_cache::create($show_instance_id);
    }


    static private function create($show_instance_id)
    {
        global $global_conn;
        $show_instance = new show_instance($show_instance_id);
        $cache = new show_instance_cache();
        $cache->show_instance_id = $show_instance_id;
        $cache->show_instance_cache_total = $show_instance->get_quantity_total();
        $cache->show_instance_cache_available = $show_instance->get_quantity_available();
        $cache->show_instance_cache_reserved = $show_instance->get_quantity_reserved();
        $cache->show_instance_cache_time = date("Y-m-d H:i:s");
        db_exec($global_conn, build_insert_query($global_conn, "show_instance_cache", array(
            'show_instance_id' => $cache->show_instance_id,
            'show_instance_cache_total' => $cache->show_instance_cache_total,
            'show_instance_cache_available' => $cache->show_instance_cache_available,
            'show_instance_cache_reserved' => $cache->show_instance_cache_reserved,
            'show_instance_cache_time' => $cache->show_instance_cache_time,
        )));
        show_instance_cache::$cache_memory[$show_instance_id] = $cache;
        return $cache;
    }


    static private function clear($show_instance_id)
    {
        global $global_conn;
        db_exec($global_conn, "DELETE FROM show_instance_cache WHERE show_instance_id = " . db_escape($show_instance_id));
        if (isset(show_instance_cache::$cache_memory[$show_instance_id])) {
            unset(show_instance_cache::$cache_memory[$show_instance_id]);
        }
    }


    static public function update_if_neccissary($show_instance_id)
    {
        $last_transaction_datetime = show_instance::get_datetime_of_last_transaction($show_instance_id);
        if ($last_transaction_datetime) {
            $cache = show_instance_cache::get($show_instance_id);
            if (strtotime($last_transaction_datetime) > strtotime($cache->show_instance_cache_time)) {
                show_instance_cache::update($show_instance_id);
            }
        }
    }


    static public function update($show_instance_id)
    {
        show_instance_cache::clear($show_instance_id);
        show_instance_cache::create($show_instance_id);
    }
}
