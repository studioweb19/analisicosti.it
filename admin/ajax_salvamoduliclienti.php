<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

if ($_POST['idmodulo']>0) {

    $dati=json_encode($_POST);

    $now=date("Y-m-d H:i:s");

    $query="INSERT INTO pcs_moduli_clienti_storia (id_modulo_cliente,dati,data_aggiornamento) values (?,?,?)";
    $stmt=$dbh->prepare($query);
    if ($stmt->execute(array($_POST['idmodulo'],$dati,$now))) {
        $ret['result']=true;
        echo json_encode($ret);
    } else {
        $ret['result']=false;
        $ret['error']="Query non corretta!";
        echo json_encode($ret);
    }

} else {
    $ret['result']=false;
    $ret['error']="Parametri non validi";
    echo json_encode($ret);
}



?>