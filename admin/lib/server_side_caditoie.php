<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/db_connect.php");
$ruolo=$_GET['ruolo'];

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
$table = 'RWD_CADITOIE';
// Table's primary key
$primaryKey = 'caditoie_id';
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(

    array( 'db' => 'c.comune_id', 'dt' => 0,'field' => 'comune_id'),
    array( 'db' => 'v.strada_nome', 'dt' => 1,'field' => 'strada_nome'),
    array( 'db' => 's.stato_nome', 'dt' => 2,'field' => 'stato_nome'),
    array( 'db' => 'p.pozzetto_nome', 'dt' => 3,'field' => 'pozzetto_nome'),
    array( 'db' => 'c.caditoie_ubicazione', 'dt' => 4,'field' => 'caditoie_ubicazione'),
    array( 'db' => 'c.caditoie_timestamp', 'dt' => 5, 'field' => 'caditoie_timestamp', 'formatter' => function( $d, $row ) {
        return ("<span style='display:none;'>$d</span>".date( 'd M Y', strtotime($d))."<br/>".date( 'H:i', strtotime($d)));
    }),
    array( 'db' => 'c.caditoie_timestamp', 'dt' => 6, 'field' => 'caditoie_timestamp', 'formatter' => function( $d, $row ) {
        return (date( 'd M Y H:i', strtotime($d)));
    }),
    array( 'db' => 'u.user_nome', 'dt' => 7,'field' => 'user_nome'),
    array( 'db' => 'c.stato_custom', 'dt' => 8,'field' => 'stato_custom'),
    array( 'db' => 'c.pozzetto_custom', 'dt' => 9,'field' => 'pozzetto_custom'),
    array( 'db' => 'c.caditoie_note', 'dt' => 10,'field' => 'caditoie_note'),
    array( 'db' => 'c.id', 'dt' => 11,'field' => 'id', 'formatter' => function ($d,$row) {
        global $ruolo;
            if ($ruolo==1) {
                return ("<button class='btn-success btn-view' id='view$d' data-elem='$d'><i class='fa fa-search' aria-hidden='true'></i> VIEW</button>&nbsp;<button class='btn-info btn-edit' id='edit$d' data-elem='$d'><i class='fa fa-pencil-square-o' aria-hidden='true'></i> EDIT</button>&nbsp;<button class='btn-danger btn-delete' data-elem='$d'><i class='fa fa-trash-o' aria-hidden='true'></i> DELETE</button>");
            } else {
                return ("<button class='btn-success btn-view' id='view$d' data-elem='$d'><i class='fa fa-search' aria-hidden='true'></i> VIEW</button>");
            }
    }),
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

$joinQuery = "FROM `RWD_CADITOIE` AS `c` 
JOIN `RWD_STRADE` AS `v` ON v.codice_via=c.codice_via 
JOIN `RWD_STATI` AS `s` ON s.stato_id=c.stato_id 
JOIN `RWD_TIPI_POZZETTO` AS `p` ON p.tipopozzetto_id=c.tipo_pozzetto_id 
JOIN `RWD_FOTO` AS `f` ON f.foto_id=c.foto_id 
JOIN `RWD_USERS` AS `u` ON u.user_id=c.user_id 
";
$extraWhere = "";
$groupBy = "";
$having = "";

if ($_GET['f']) {
    foreach ($_GET['f'] as $key=>$value) {
        list($alias,$campo)=explode("-",$key);
        $valori=str_replace("|","','",$value);
        $where[]="`".$alias."`.`".$campo."` IN ('".$valori."')";
    }
}

if ($_GET['fd']) {
    foreach ($_GET['fd'] as $key=>$value) {
        list($alias,$campo)=explode("-",$key);

        list($da,$a)=explode("|",$value);
        list($d1,$m1,$y1)=explode("/",$da);
        $datada=$y1."-".$m1."-".$d1." 00:00";
        list($d2,$m2,$y2)=explode("/",$a);
        $dataa=$y2."-".$m2."-".$d2." 23:59";

        $where[]="(`".$alias."`.`".$campo."` BETWEEN '".$datada."' AND '".$dataa."')";
    }
}

if (count($where)>0) {
    $extraWhere=join(" AND ",$where);
}



echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy, $having )
);






?>