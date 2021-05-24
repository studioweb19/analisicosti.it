<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");




$idazienda=$_REQUEST['idazienda'];
$mese=$_REQUEST['mese'];
$anno=$_REQUEST['anno'];
$notestudio=$_REQUEST['notestudio'];
$url=$_REQUEST['url'];
$idclientehash=substr(md5($idazienda*($idazienda+1)), 0, 128);

$query1="SELECT * FROM pcs_clienti WHERE id=? ";
$stmt1=$dbh->prepare($query1);
$stmt1->execute(array($idazienda));
$CLIENTE=$stmt1->fetch(PDO::FETCH_ASSOC);


require 'class.phpmailer.php';


//questi due devono essere il nome dello studio e l'email

$EMAILADMIN['email']="fabio.franci@gmail.com";
$EMAILADMIN['name']="Studio La Rosa";


$EMAILADMIN['email']=$utente['email'];
$EMAILADMIN['name']=$utente['Nome']." ".$utente['Cognome'];


$testo=$notestudio;
$testo.="<br/>Consulta direttamente il report che ti abbiamo preparato: ";
$testo.="https://".$_SERVER['SERVER_NAME'].$url."&idclientehash=$idclientehash";
$testo.="<br/><br/>Cordiali saluti<br/><br/>Studio La Rosa";

$mail = new PHPMailer;

$mail->IsSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp-relay.sendinblue.com';                 // Specify main and backup server
$mail->Port = 587;                                    // Set the SMTP port
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'info@analisicosti.it';                  // SMTP username
$mail->Password = '96WBqRCm8GvdTJsS';                      // SMTP password info@analisicosti.it
$mail->SMTPSecure = 'SSL';                            // Enable encryption, 'ssl' also accepted

$mail->AddReplyTo($EMAILADMIN['email'], $EMAILADMIN['name']);

$mail->addAddress($CLIENTE['Mail']);            // Name is optional
$mail->SetFrom('info@analisicosti.it', "Easy Cost 1.0");

$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = "[Easy Cost 1.0] - Analisi Bilancio ".$anno."-".$mese;
$mail->Body    = $testo;

if ($mail->send()) {

$query="UPDATE pcs_analisi_costi SET email_inviata='si',data_invio_email=CONVERT_TZ( NOW( ) ,  '%TIMEZONE%',  'Europe/Rome' ) WHERE meseanno=? and id_azienda=?";
$query=str_replace("%TIMEZONE%",$TIMEZONE,$query);

$stmt=$dbh->prepare($query);
$stmt->execute(array($anno."-".$mese,$idazienda));

	if ($stmt) { ?>
<div class="space6"></div>
<div class="registrazionesuccess alert alert-success" role="alert">
		<div class="center">
  		<strong><?php echo _("Congratulazioni!");?></strong> <?php echo _("Email inviata al cliente!");?>
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
  		<strong><?php echo _("Attenzione!");?></strong><?php echo _("Problema invio email al cliente!");?>
  		</div>
</div>
    <script>
        setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
<?php
	}

} else {
echo 'Mailer Error: ' . $mail->ErrorInfo;
setNotificheCRUD("APP","ERROR","controlloPdf.php","Mail non inviata: $codicevisita");
}
?>