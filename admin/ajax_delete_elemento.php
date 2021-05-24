<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idmod=$_REQUEST['idmod'];
$idele=$_REQUEST['idele'];

$modulo=getModulo($idmod);

if (!($idmod>0 and $idele>0)) {
    setNotificheCRUD("admWeb","ERROR","ajax_delete_elemento.php",$modulo['nome_modulo']." e idmod=".$idmod." e idele=".$idele);
    ?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<strong>Attenzione!</strong>Problema cancellazione elemento!!
  		</div>
</div>
    <script>
	setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
<?php 
	exit();
}

$permessi=permessi($idmod,$utente['id_ruolo']);

if (!($permessi['Can_delete']=='si')) {
    setNotificheCRUD("admWeb","ERROR","ajax_delete_elemento.php",$modulo['nome_modulo']." niente permessi");
    ?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<strong>Attenzione!</strong>Non hai i permessi per cancellare questo elemento!
  		</div>
</div>
    <script>
	setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
<?php 
	exit();
}

//veririchiamo anche il pre process delete
$pre_process_delete=json_decode($modulo['pre_process_delete'],true);

setNotificheCRUD("admWeb","INFO","ajax_delete_elemento.php - pre process delete:",$modulo['pre_process_delete']);

if (count($pre_process_delete)>0) {
    foreach ($pre_process_delete as $ppuquery) {
        $ppuquery=str_replace("%chiaveprimaria%",$idele,$ppuquery);
        //echo "<pre>";
        //echo $ppuquery;
        //echo "</pre>";
        if (!($dbh->query($ppuquery))) {
            setNotificheCRUD("admWeb","ERROR","ajax_delete_elemento.php - pre process delete:",$ppuquery);
            $erroreTransazione=true;
        } else {
            setNotificheCRUD("admWeb","SUCCESS","ajax_delete_elemento.php - pre process delete:",$ppuquery);
        }
    }
}

if ($modulo['nome_modulo']=='Files') {
    //allora controllo se sto cancellando un file di Caricamento Dati
    $queryCHECK="SELECT * FROM pcs_file WHERE id_file=$idele";
    //echo $queryCHECK;
    $stmtCHECK=$dbh->query($queryCHECK);
    $rowCHECK=$stmtCHECK->fetch(PDO::FETCH_ASSOC);
    if ($rowCHECK['tb']=='pcs_caricamento_dati') {
        //devo recuperare mese e anno
        $queryCHECK2="SELECT * FROM pcs_caricamento_dati WHERE id=".$rowCHECK['id_elem'];
        //echo $queryCHECK2;
        $stmtCHECK2=$dbh->query($queryCHECK2);
        $rowCHECK2=$stmtCHECK2->fetch(PDO::FETCH_ASSOC);
        $mese=$rowCHECK2['mese'];
        $anno=$rowCHECK2['anno'];
        $azienda=$rowCHECK2['id_azienda'];
    }
}
if ($modulo['nome_modulo']=='CaricamentoDati') {
        //devo recuperare mese e anno
        $queryCHECK2="SELECT * FROM pcs_caricamento_dati WHERE id=$idele";
        //echo $queryCHECK2;
        $stmtCHECK2=$dbh->query($queryCHECK2);
        $rowCHECK2=$stmtCHECK2->fetch(PDO::FETCH_ASSOC);
        $mese=$rowCHECK2['mese'];
        $anno=$rowCHECK2['anno'];
        $azienda=$rowCHECK2['id_azienda'];
}

if ($mese>0 && $anno>0 && $azienda>0) {

    $ret['anno']=$anno;
    $ret['mese']=$mese;
    $ret['id_azienda']=$azienda;
    $ret['url']="/admin/module.php?modname=CaricamentoDati&p[anno]=".$anno."&p[id_azienda]=".$azienda;

    //cancellazione di pcs_analisi_costi
    $queryDEL1="DELETE FROM pcs_analisi_costi WHERE id_azienda=$azienda AND anno=$anno AND mese>=$mese";
    $stmtDEL1=$dbh->query($queryDEL1);

//cancellazione di pcs_caricamento_dati
    $queryDEL2="DELETE FROM pcs_caricamento_dati WHERE id_azienda=$azienda AND anno=$anno AND mese>=$mese";
    $stmtDEL2=$dbh->query($queryDEL2);

//cancellazione di pcs_dati_consuntivi
    $queryDEL3="DELETE FROM pcs_dati_consuntivi WHERE id_azienda=$azienda AND anno=$anno AND mese>=$mese";
    $stmtDEL3=$dbh->query($queryDEL3);

//cancellazione di pcs_piano_conti
    $queryDEL4="DELETE FROM pcs_piano_conti WHERE id_azienda=$azienda AND annoinserimento=$anno AND meseinserimento>=$mese";
    $stmtDEL4=$dbh->query($queryDEL3);

}


$query="DELETE FROM ".$modulo['nome_tabella']." WHERE ".$modulo['chiaveprimaria']."='".$idele."'";
$stmt=$dbh->query($query);

$query2="DELETE FROM ".$GLOBAL_tb['testi']." WHERE id_ext=$idele AND table_ext='".$modulo['nome_tabella']."'";
$stmt2=$dbh->query($query2);

$query3="DELETE FROM ".$GLOBAL_tb['files']." WHERE id_elem=$idele AND tb='".$modulo['nome_tabella']."'";
$stmt3=$dbh->query($query3);

$query4="DELETE FROM ".$GLOBAL_tb['note']." WHERE id_ext=$idele AND table_ext='".$modulo['nome_tabella']."'";
$stmt4=$dbh->query($query4);

//veririchiamo anche il pre process delete
$post_process_delete=json_decode($modulo['post_process_delete'],true);
setNotificheCRUD("admWeb","INFO","ajax_delete_elemento.php - pre process delete:",$modulo['post_process_delete']);

if (count($post_process_delete)>0) {
    foreach ($post_process_delete as $ppuquery) {
        $ppuquery=str_replace("%chiaveprimaria%",$idele,$ppuquery);
        if (!($dbh->query($ppuquery))) {
            setNotificheCRUD("admWeb","ERROR","ajax_delete_elemento.php - post process delete:",$ppuquery);
            $erroreTransazione=true;
        } else {
            setNotificheCRUD("admWeb","SUCCESS","ajax_delete_elemento.php - post process delete:",$ppuquery);
        }
    }
}

//veririchiamo anche il post process update
$post_process_update=json_decode($modulo['post_process_update'],true);
setNotificheCRUD("admWeb","INFO","ajax_delete_elemento.php - post process update:",$modulo['post_process_update']);

if (count($post_process_update)>0) {
    foreach ($post_process_update as $ppuquery) {
        $ppuquery=str_replace("%chiaveprimaria%",$idele,$ppuquery);
        if (!($dbh->query($ppuquery))) {
            setNotificheCRUD("admWeb","ERROR","ajax_delete_elemento.php - post process update:",$ppuquery);
            $erroreTransazione=true;
        } else {
            setNotificheCRUD("admWeb","SUCCESS","ajax_delete_elemento.php - post process update:",$ppuquery);
        }
    }
}

	if ($stmt) {
        $ret['result']=true;
    } else {
        $ret['result']=false;
        $ret['error']="Errore cancellazione file";
	}
	echo json_encode($ret);
?>