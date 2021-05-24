<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/db_connect.php");


/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = 'RWD_STRADE';
// Table's primary key
$primaryKey = 'strada_id';
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(

    array( 'db' => 'v.codice_via', 'dt' => 0,'field' => 'codice_via'),
    array( 'db' => 'v.strada_nome', 'dt' => 1,'field' => 'strada_nome'),
    array( 'db' => 'c.comune_nome', 'dt' => 2,'field' => 'comune_nome'),
    array( 'db' => 'v.strada_id', 'dt' => 3,'field' => 'strada_id', 'formatter' => function ($d,$row) {
        return ("<button class='btn-info btn-edit' data-elem='$d'><i class='fa fa-pencil-square-o' aria-hidden='true'></i> EDIT</button>&nbsp;<button class='btn-danger btn-delete' data-elem='$d'><i class='fa fa-trash-o' aria-hidden='true'></i> DELETE</button>");
    })
);
$sql_details = array(
		'user' => $usernam,
		'pass' => $passwor,
		'db'   => $database,
		'host' => $server
	);
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
// require( 'ssp.class.php' );
require($_SERVER['DOCUMENT_ROOT'].'/admin/assets/ssp.customized.class.php' );

$joinQuery = "FROM `RWD_STRADE` AS `v` 
JOIN `RWD_COMUNI` AS `c` ON c.comune_id=v.comune_id 
";
$extraWhere = "";
$groupBy = "";
$having = "";

if (count($where)>0) {
    $extraWhere=join(" AND ",$where);
}



echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy, $having )
);






?>