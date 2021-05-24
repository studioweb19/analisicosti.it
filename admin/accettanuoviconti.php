<?php 
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

$idazienda=$_POST['idazienda'];


//se i conti new sono i sottoconti, occorre che tutti i sottoconti prendano la stessa categoria del padre di livello 1

    //metto il flag new=0 a tutti i conti con new=1  per questa azienda
    $queryDel="UPDATE pcs_piano_conti SET new=0 WHERE id_azienda=? AND new=1";
    $stmtDel=$dbh->prepare($queryDel);
    $stmtDel->execute(array($idazienda));


    $queryDel="SELECT * FROM pcs_piano_conti WHERE id_azienda=? AND livello2 is null"; //prendo tutti i macroconti
    $stmtDel=$dbh->prepare($queryDel);
    $stmtDel->execute(array($idazienda));

    //ora ciclo e faccio update per tutti i sottoconti mantenendo la stessa categoria del padre
while($row = $stmtDel->fetch(PDO::FETCH_ASSOC)) {
    $query1="UPDATE pcs_piano_conti SET categoria=? WHERE id_azienda=? and livello1=?";
    $stmt1=$dbh->prepare($query1);
    $stmt1->execute(array($row['categoria'],$idazienda,$row['livello1']));
}


$ret['msg']="Nuovi conti accettati! ";
$ret['result']=true;
    echo json_encode($ret);
    exit();

