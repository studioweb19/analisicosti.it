<?php
require_once("config.php");
$backlist=base64_encode("/admin/clienti.php");


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


$modulo=getModuloFrom_nome_modulo("Clienti");
$permessi_modulo=permessi($modulo,$utente['id_ruolo'],$superuserOverride);

$can_read_all=0;
if ($permessi_modulo['Can_read_all']=='si') {
    $can_read_all=1;
}

// DB table to use
$table = 'pcs_clienti';
// Table's primary key
$primaryKey = 'id';
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'c.id', 'dt' => 0,'field' => 'id', 'formatter' => function ($d,$row) {
        global $permessi_modulo,$backlist;

        if ($permessi_modulo['Can_update']=='si' and $permessi_modulo['Can_delete']=='si') {
            return('
<div class="dropdown">
    <div class="btn-group">
        <div class="btn-group" style="text-align:center;">
            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"> <i class=" glyphicon glyphicon-wrench "></i> <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li class="dropdown-header">Clienti</li>
                <li><a href="/admin/?ida='.$d.'" data-toggle="tooltip" title="" data-original-title="Choose"><i class="glyphicon glyphicon-search"></i> SCEGLI</a></li>
                <li><a href="get_element.php?debug=0&amp;idmod=9&amp;idele='.$d.'&backlist='.$backlist.'" data-toggle="tooltip" title="" class="green" data-original-title="Edit"><i class="glyphicon glyphicon-edit"></i> MODIFICA</a></li>
                <li class="red"><a data-toggle="tooltip" title="Delete" class="red delete-elemento" idmodalmod="9" idmodalele="'.$d.'" href="#" ><i class="glyphicon glyphicon-trash"></i> CANCELLA</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Utilità</li>
                <li><a data-toggle="tooltip" title="Caricamento Dati" href="module.php?modname=CaricamentoDati&amp;p[id_azienda]='.$d.'" data-original-title="Caricamento Dati">Caricamento Dati</a></li>   
                <li><a data-toggle="tooltip" title="Prospetto Previsionale" href="module.php?modname=ProspettoPrevisionale&amp;p[id_azienda]='.$d.'" data-original-title="Prospetto Previsionale">Prospetto Previsionale</a></li>   
                <li><a data-toggle="tooltip" title="Piano Dei Conti" href="module.php?modname=PianoDeiConti&amp;p[id_azienda]='.$d.'" data-original-title="Piano Dei Conti">Piano Dei Conti</a></li>   
                <!--<li class="divider"></li>-->
                <!--<li class="dropdown-header">Moduli Clienti</li>
                <li><a data-toggle="tooltip" title="Moduli Clienti" href="module.php?modname=ModuliClienti&amp;p[id_cliente]='.$d.'" data-original-title="Moduli Clienti">Moduli Clienti</a></li>   -->
            </ul>
        </div>
    </div>
</div>
');
        }

        if ($permessi_modulo['Can_update']=='si' and $permessi_modulo['Can_delete']=='no') {
            return('
<div class="dropdown">
    <div class="btn-group">
        <div class="btn-group" style="text-align:center;">
            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"> <i class=" glyphicon glyphicon-wrench "></i> <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li class="dropdown-header">Clienti</li>
                <li><a href="/admin/?ida='.$d.'" data-toggle="tooltip" title="" data-original-title="Choose"><i class="glyphicon glyphicon-search"></i> SCEGLI</a></li>
                <li><a href="get_element.php?debug=0&amp;idmod=9&amp;idele='.$d.'&backlist='.$backlist.'" data-toggle="tooltip" title="" class="green" data-original-title="Edit"><i class="glyphicon glyphicon-edit"></i> MODIFICA</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Utilità</li>
                <li><a data-toggle="tooltip" title="Caricamento Dati" href="module.php?modname=CaricamentoDati&amp;p[id_azienda]='.$d.'" data-original-title="Caricamento Dati">Caricamento Dati</a></li>   
                <li><a data-toggle="tooltip" title="Prospetto Previsionale" href="module.php?modname=ProspettoPrevisionale&amp;p[id_azienda]='.$d.'" data-original-title="Prospetto Previsionale">Prospetto Previsionale</a></li>   
                <li><a data-toggle="tooltip" title="Piano Dei Conti" href="module.php?modname=PianoDeiConti&amp;p[id_azienda]='.$d.'" data-original-title="Piano Dei Conti">Piano Dei Conti</a></li>   
                <!--<li class="divider"></li>-->
                <!--<li class="dropdown-header">Moduli Clienti</li>
                <li><a data-toggle="tooltip" title="Moduli Clienti" href="module.php?modname=ModuliClienti&amp;p[id_cliente]='.$d.'" data-original-title="Moduli Clienti">Moduli Clienti</a></li>   -->
            </ul>
        </div>
    </div>
</div>
');
        }

        if ($permessi_modulo['Can_update']=='no' and $permessi_modulo['Can_delete']=='si') {
            return('
<div class="dropdown">
    <div class="btn-group">
        <div class="btn-group" style="text-align:center;">
            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"> <i class=" glyphicon glyphicon-wrench "></i> <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li class="dropdown-header">Clienti</li>
                <li><a href="/admin/?ida='.$d.'" data-toggle="tooltip" title="" data-original-title="Choose"><i class="glyphicon glyphicon-search"></i> SCEGLI</a></li>
                <li class="red"><a data-toggle="tooltip" title="Delete" class="red delete-elemento" idmodalmod="9" idmodalele="'.$d.'" href="#" ><i class="glyphicon glyphicon-trash"></i> CANCELLA</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Utilità</li>
                <li><a data-toggle="tooltip" title="Caricamento Dati" href="module.php?modname=CaricamentoDati&amp;p[id_azienda]='.$d.'" data-original-title="Caricamento Dati">Caricamento Dati</a></li>   
                <li><a data-toggle="tooltip" title="Prospetto Previsionale" href="module.php?modname=ProspettoPrevisionale&amp;p[id_azienda]='.$d.'" data-original-title="Prospetto Previsionale">Prospetto Previsionale</a></li>   
                <li><a data-toggle="tooltip" title="Piano Dei Conti" href="module.php?modname=PianoDeiConti&amp;p[id_azienda]='.$d.'" data-original-title="Piano Dei Conti">Piano Dei Conti</a></li>   
                <!--<li class="divider"></li>-->
<!--                <li class="dropdown-header">Moduli Clienti</li>
                <li><a data-toggle="tooltip" title="Moduli Clienti" href="module.php?modname=ModuliClienti&amp;p[id_cliente]='.$d.'" data-original-title="Moduli Clienti">Moduli Clienti</a></li>  --> 
            </ul>
        </div>
    </div>
</div>
');

        }

        if ($permessi_modulo['Can_update']=='no' and $permessi_modulo['Can_delete']=='no') {
            return('
<div class="dropdown">
    <div class="btn-group">
        <div class="btn-group" style="text-align:center;">
            <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"> <i class=" glyphicon glyphicon-wrench "></i> <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li class="dropdown-header">Utilità</li>
                <li><a data-toggle="tooltip" title="Prospetto Previsionale" href="module.php?modname=ProspettoPrevisionale&amp;p[id_azienda]='.$d.'" data-original-title="Prospetto Previsionale">Prospetto Previsionale</a></li>   
                <!--<li class="divider"></li>-->
                <!--<li class="dropdown-header">Moduli Clienti</li>
                <li><a data-toggle="tooltip" title="Moduli Clienti" href="module.php?modname=ModuliClienti&amp;p[id_cliente]='.$d.'" data-original-title="Moduli Clienti">Moduli Clienti</a></li>   -->
            </ul>
        </div>
    </div>
</div>
');

        }
    }),
    array( 'db' => 'RagioneSociale', 'dt' => 1,'field' => 'RagioneSociale'),
    array( 'db' => 'Cognome', 'dt' => 2,'field' => 'Cognome'),
    array( 'db' => 'Nome', 'dt' =>3,'field' => 'Nome'),
    array( 'db' => 'Indirizzo', 'dt' => 4,'field' => 'Indirizzo'),
    array( 'db' => 'c.Mail', 'dt' => 5,'field' => 'Mail'),
    array( 'db' => 'c.Telefono', 'dt' => 6,'field' => 'Telefono'),
    array( 'db' => 'c.Cell', 'dt' => 7,'field' => 'Cell'),
    array( 'db' => 'inviate_credenziali', 'dt' => 8,'field' => 'inviate_credenziali'),
    array( 'db' => 'c.id', 'dt' => 9,'field' => 'id', 'formatter' => function ($d,$row) {return ('<a class="btn btn-xs btn-danger inviacredenzialicliente" idcliente="'.$d.'">INVIA</a>');}),
    array( 'db' => 'c.Referenti', 'dt' => 10,'field' => 'Referenti'),

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

$joinQuery = "FROM `pcs_clienti` AS `c` ";
$extraWhere = "";
$groupBy = "";
$having = "";

$extraWhere = "";
$groupBy = "";
$having = "";

//$where[]="(".$can_read_all."=1 OR id=".$utente['id_cliente']." OR idStudio=".$utente['id_user'].")";

if ($_GET['f']) {
    foreach ($_GET['f'] as $key=>$value) {
        if ($value=='') continue;
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

if ($utente['id_ruolo']==3) {
    $where[]="c.id=".$utente['id_cliente'];
}

if ($utente['id_ruolo']==4) {
    $where[]="c.idStudio=".$utente['id_user'];
}



if (count($where)>0) {
    $extraWhere=join(" AND ",$where);
}

echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy, $having )
);






?>