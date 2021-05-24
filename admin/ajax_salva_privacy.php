<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idcliente=$_REQUEST['idcliente'];
$iduser=$_REQUEST['iduser'];

if ($idcliente>0 && $iduser==11) {
    //cliente
    $query="UPDATE pcs_clienti SET privacy = NOW() WHERE id=?";
    $stmt=$dbh->prepare($query);
    if ($stmt->execute(array($idcliente))) {


        $ret['result']=true;
        echo json_encode($ret);
        exit;
    } else {
        $ret['result']=false;
        echo json_encode($ret);
        exit;
    }
}

if ($iduser!=11) {
    //user
    $query="UPDATE pcs_users SET privacy = NOW() WHERE id_user=?";
    $stmt=$dbh->prepare($query);
    if ($stmt->execute(array($iduser))) {
        $ret['result']=true;
        echo json_encode($ret);
        exit;
    } else {
        $ret['result']=false;
        echo json_encode($ret);
        exit;
    }
}
$ret['result']=false;
echo json_encode($ret);
exit;
