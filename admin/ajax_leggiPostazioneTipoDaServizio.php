<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idservizio=$_POST['idservizio'];

if ($idservizio>0) {
    $query="SELECT * FROM pcs_tipo_postazione WHERE id_servizio='".$idservizio."'";
    if ($stmt=$dbh->query($query)) {
        $tipi=Array();
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $tipi[]=$row;
        }
        $ret['result']=true;
        $ret['tipi']=$tipi;
        echo json_encode($ret);
    } else {
        $ret['result']=false;
        $ret['error']="Errore accesso al db";
        echo json_encode($ret);
    }
} else {
    $ret['result']=false;
    $ret['error']="Parametri non validi";
    echo json_encode($ret);
}
    

?>