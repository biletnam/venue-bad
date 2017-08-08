<?php


/**
 * Description of show_people
 *
 * @author lepercon
 */
class show_people
{
    /**
     * @var int
     */
    public $show_id;
    /**
     * @var int
     */
    public $user_id;
    /**
     * @var string
     */
    public $show_people_role;
    /**
     * @var string one of show_people::SHOW_PEOPLE_ROLE_TYPE_... constants
     */
    public $show_people_role_type;
    CONST SHOW_PEOPLE_ROLE_TYPE_CAST = "CAST";
    CONST SHOW_PEOPLE_ROLE_TYPE_PRIMARY_CAST = "PRIMARY_CAST";
    CONST SHOW_PEOPLE_ROLE_TYPE_CREW = "CREW";
    CONST SHOW_PEOPLE_ROLE_TYPE_PRIMARY_CREW = "PRIMARY_CREW";

    /**
     * @var \user
     */
    public $user;

    /**
     * Get an array of all cast/crew/primary-cast/primary-crew for a show
     * @global PDO $global_conn
     * @param int $show_id
     * @param string $role_type_limit one of show_people::SHOW_PEOPLE_ROLE_TYPE_... constants
     * @return array of show_people
     */
    static public function get_for_show_by_type($show_id, $role_type_limit = show_people::SHOW_PEOPLE_ROLE_TYPE_CAST)
    {
        global $global_conn;
        $return = array();
        $results = db_query($global_conn, "SELECT show_people.* FROM show_people LEFT JOIN users USING (user_id) WHERE show_id = " . db_escape($show_id) . " AND show_people_role_type = " . db_escape($role_type_limit) . " ORDER BY user_name_last, user_name_first ");
        foreach ($results as $row) {
            $show_person = new show_people();
            $show_person->user = new user($row['user_id']);
            $show_person->show_id = $show_id;
            $show_person->user_id = $row['user_id'];
            $show_person->show_people_role = $row['show_people_role'];
            $show_person->show_people_role_type = $row['show_people_role_type'];
            $return[] = $show_person;
        }
        return $return;
    }

    /**
     * Iterates over all show_people::get_for_show_by_type for each role_type in proper order.
     * @param int $show_id
     * @return array of show_people
     */
    static public function get_all_for_show($show_id)
    {
        return array_merge(
            show_people::get_for_show_by_type($show_id, show_people::SHOW_PEOPLE_ROLE_TYPE_PRIMARY_CREW),
            show_people::get_for_show_by_type($show_id, show_people::SHOW_PEOPLE_ROLE_TYPE_PRIMARY_CAST),
            show_people::get_for_show_by_type($show_id, show_people::SHOW_PEOPLE_ROLE_TYPE_CAST),
            show_people::get_for_show_by_type($show_id, show_people::SHOW_PEOPLE_ROLE_TYPE_CREW)
        );
    }


    static public function make($show_id, $user_id, $show_people_role, $show_people_role_type)
    {
        global $global_conn;
        db_exec($global_conn, build_insert_query($global_conn, 'show_people', array(
            'show_id' => $show_id,
            'user_id' => $user_id,
            'show_people_role' => $show_people_role,
            'show_people_role_type' => $show_people_role_type
        )));
    }

    static public function remove($show_id, $user_id, $show_people_role = "")
    {
        global $global_conn;
        $where = " WHERE show_id = " . db_escape($show_id) . " AND user_id = " . db_escape($user_id);
        if ($show_people_role !== "") {
            $where .= " AND show_people_role = " . db_escape($show_people_role);
        }
        db_exec($global_conn, "DELETE FROM show_people $where LIMIT 1;");
    }

}
