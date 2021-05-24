<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");
 
$idazienda=$_REQUEST['idazienda'];
$meseanno=$_REQUEST['meseanno'];
$notestudio=$_REQUEST['notestudio'];

$query="UPDATE pcs_analisi_costi SET noteStudio=? WHERE meseanno=? and id_azienda=?";
$stmt=$dbh->prepare($query);
$stmt->execute(array($notestudio,$meseanno,$idazienda));

	if ($stmt) { ?>
<div class="space6"></div>
<div class="registrazionesuccess alert alert-success" role="alert">
		<div class="center">
  		<strong><?php echo _("Congratulazioni!");?></strong> <?php echo _("Note aggiornate!");?>
  		</div>
    <script>
        setTimeout(function(){$(".registrazionesuccess").hide();}, 2000);
    </script>
</div>
<?php
	} else {
?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<strong><?php echo _("Attenzione!");?></strong><?php echo _("Problema aggiornamento note!");?>
  		</div>
</div>
    <script>
        setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
<?php
	}
?>