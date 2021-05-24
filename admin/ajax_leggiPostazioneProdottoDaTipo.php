<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idtipopostazione=$_POST['idtipopostazione'];

if ($idtipopostazione>0) {
    $query="SELECT * FROM pcs_prodotto_postazione WHERE id_tipo_postazione='".$idtipopostazione."'";
    if ($stmt=$dbh->query($query)) {
        $prodotti=Array();
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $prodotti[]=$row;
        }
        $ret['result']=true;
        $ret['prodotti']=$prodotti;
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