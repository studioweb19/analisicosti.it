<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idazienda=$_REQUEST['idazienda'];
$idmod=getModuloFrom_nome_modulo("PianoDeiConti");
$modulo=getModulo($idmod);


if (!($idazienda>0)) {
    setNotificheCRUD("admWeb","ERROR","ajax_delete_elemento.php",$modulo['nome_modulo']." e idmod=".$idmod." e idele=".$idele);
    ?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<strong>Attenzione!</strong>Problema cancellazione!!
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
    setNotificheCRUD("admWeb","ERROR","ajax_delete_piano_dei_conti.php",$modulo['nome_modulo']." niente permessi");
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

$query="DELETE FROM ".$modulo['nome_tabella']." WHERE id_azienda=$idazienda";
$stmt=$dbh->query($query);

	if ($stmt) { ?>
<div class="space6"></div>
<div class="registrazionesuccess alert alert-success" role="alert">
		<div class="center">
  		<strong>Congratulazioni!</strong> Elemento cancellato!
    <script>
	setTimeout(function(){
		$(".registrazionesuccess").hide();
	}, 2000);
	</script>
  		</div>
</div>
<?php
	} else {
?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<strong>Attenzione!</strong>Problema cancellazione elemento!
  		</div>
</div>
    <script>
	setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
<?php
	}
?>