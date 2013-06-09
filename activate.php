<?php
/**
 * Register the ElggKHanExercise class for the object/blog subtype
 */

function check_database() {
	//$conf = $db->config->getConnectionConfig();
    //$con = mysql_connect($conf['host'], $conf['user'], $conf['password']);
    //if (!$con)
    //{
    //    die('Could not connect: ' . mysql_error());
    //}
    $db = False; //mysql_select_db("khan_exercises", $con);
    if (!$db) {
        $con = _elgg_services()->db->getLink("readwrite");
        $sql="CREATE DATABASE khan_exercises";
        mysql_query($sql);
        mysql_select_db("khan_exercises", $con);
        error_log("dumping file");
        $sql = run_sql_script(dirname(__file__).'/dumpfile.sql');
        mysql_select_db("elgg", $con);
    }
}
check_database();

if (get_subtype_id('object', 'khan_exercise')) {
	update_subtype('object', 'khan_exercise', 'ElggKhanExercise');
} else {
	add_subtype('object', 'khan_exercise', 'ElggKhanExercise');
}
