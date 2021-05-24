<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idservizio=$_POST['idservizio'];

if ($idservizio>0) {
    $query="SELECT * FROM pcs_infestanti WHERE id_servizio='".$idservizio."'";
    if ($stmt=$dbh->query($query)) {
        $infestanti=Array();
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $infestanti[]=$row;
        }
        $ret['result']=true;
        $ret['infestanti']=$infestanti;
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