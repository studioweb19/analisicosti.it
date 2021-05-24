<?php
require_once("config.php");

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
$table = 'pcs_sedi_clienti';
// Table's primary key
$primaryKey = 'id';
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 's.id', 'dt' => 0,'field' => 'id', 'formatter' => function ($d,$row) {
        return('
<div class="dropdown">
    <div class="btn-group">
        <div class="btn-group" style="text-align:center;">
            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"> <i class=" glyphicon glyphicon-wrench "></i> <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li class="dropdown-header">Sedi Clienti</li>
                <li><a href="get_element.php?debug=0&amp;idmod=10&amp;idele='.$d.'" data-toggle="tooltip" title="" class="green" data-original-title="Edit"><i class="glyphicon glyphicon-edit"></i> MODIFICA</a></li>
                <li class="red"><a data-toggle="tooltip" title="Delete" class="red delete-elemento" idmodalmod="10" idmodalele="'.$d.'" href="#" ><i class="glyphicon glyphicon-trash"></i> CANCELLA</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Aree</li>
                <li><a href="get_element.php?idele=-1&amp;debug=0&amp;idmod=47&amp;k=id_sede-'.$d.'" data-toggle="tooltip" title="Altra area" data-original-title="Altra area">NUOVO </a></li>
                <li><a data-toggle="tooltip" title="Elenco" href="module.php?modname=Aree&amp;p[id_sede]='.$d.'" data-original-title="Sedi clienti">ELENCO <span class="badge">'.$row["totAree"].'</span></a></li>   
                <li class="divider"></li>
                <li class="dropdown-header">Postazioni</li>
                <li><a href="get_element.php?idele=-1&amp;debug=0&amp;idmod=11&amp;k=id_area-'.$d.'" data-toggle="tooltip" title="Altra Postazione" data-original-title="Altra Postazione">NUOVO </a></li>
                <li><a data-toggle="tooltip" title="Elenco" href="module.php?modname=Aree&amp;p[id_sede]='.$d.'" data-original-title="Sedi clienti">ELENCO <span class="badge">'.$row["totPostazioni"].'</span></a></li>   
            </ul>
        </div>
    </div>
</div>
');
    }),
    array( 'db' => 'CONCAT(c.Cognome," ",c.Nome)', 'AS'=>'id_cliente','dt' => 1,'field' => 'id_cliente','formatter'=>function($d,$row){
        return('<a href="module.php?modname=Clienti&amp;p[id]='.$d.'">'.$d.'</a>');
    }),
    array( 'db' => 's.indirizzo', 'dt' => 2,'field' => 'indirizzo'),
    array( 'db' => 's.CAP', 'dt' => 3,'field' => 'CAP'),
    array( 'db' => 's.email', 'dt' => 4,'field' => 'email'),
    array( 'db' => '(SELECT COUNT(pcs_aree.id) FROM pcs_aree WHERE id_sede = s.id)', 'AS'=>'totAree', 'dt' => 5,'field' => 'totAree'),

    );

$sql_details = array(
		'user' => $user,
		'pass' => $pass,
		'db'   => $database,
		'host' => $host
	);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
// require( 'ssp.class.php' );
require('ssp.customized.class.php' );

$joinQuery = "FROM `pcs_sedi_clienti` AS `s` JOIN `pcs_clienti` AS `c` ON `c`.`id`=`s`.`id_cliente`";
$extraWhere = "";
$groupBy = "";
$having = "";

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


if (count($where)>0) {
    $extraWhere=join(" AND ",$where);
}



echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy, $having )
);






?>