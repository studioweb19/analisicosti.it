<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

$testifile=array();

sanitate($_POST);

if ($_POST['idConto']!='' and $_POST['nomecampo']!='' and $_POST['valorecampo']!='') {

    if ($_POST['valorecampo']===0) {
        $queryupdate = "UPDATE " . $GLOBAL_tb['pianodeiconti'] . " SET " . $_POST['nomecampo'] . "=NULL WHERE id=?";
        $stmt = $dbh->prepare($queryupdate);
        $stmtOK=$stmt->execute(array($_POST['idConto']));
    } else {
        $queryupdate = "UPDATE " . $GLOBAL_tb['pianodeiconti'] . " SET " . $_POST['nomecampo'] . "=? WHERE id=?";
        $stmt = $dbh->prepare($queryupdate);
        $stmtOK=$stmt->execute(array($_POST['valorecampo'],$_POST['idConto']));
    }

    if ($stmtOK and aggiornaCategorieConti($_POST['idConto'])) {
        setNotificheCRUD("admWeb", "SUCCESS", "ajax_modificaCategoriaContoInline - UPDATE", $queryupdate);
        $ret['res']=true;
    } else {
        setNotificheCRUD("admWeb", "ERROR", "ajax_modificaCategoriaContoInline - UPDATE", $queryupdate);
        $ret['res']=false;
        $ret['msg']="Errore: problema aggiornamento database!";
    }
} else {
    setNotificheCRUD("admWeb", "ERROR", "ajax_modificaCategoriaContoInline - UPDATE", "parametri mancanti: ".json_encode($_POST));
    $ret['res']=false;
    $ret['msg']="Errore: parametri non corretti!";
}
echo json_encode($ret);

?>