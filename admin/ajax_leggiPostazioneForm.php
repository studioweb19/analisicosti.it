<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idele=$_POST['idele'];

if ($idele>0) {
    $query="SELECT * FROM pcs_aree WHERE id='".$idele."'";
    if ($stmt=$dbh->query($query)) {
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        $ret['result']=true;
        $ret['campi']=$row;
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