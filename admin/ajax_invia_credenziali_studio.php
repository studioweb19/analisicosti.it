<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

$iduser=$_REQUEST['iduser'];

$query1="SELECT * FROM pcs_users WHERE id_user=? ";
$stmt1=$dbh->prepare($query1);
$stmt1->execute(array($iduser));
$UTENTE=$stmt1->fetch(PDO::FETCH_ASSOC);

if ($UTENTE['email']=='') {
    $ret['result']=false;
    $ret['error']="Non è stata indicata nessuna email!";
    echo json_encode($ret);
    exit;
}
if ($UTENTE['password']=='') {
    $ret['result']=false;
    $ret['error']="Non è stata indicata nessuna password!";
    echo json_encode($ret);
    exit;
}


require 'class.phpmailer.php';

$EMAILADMIN['email']=$UTENTE['email'];
$EMAILADMIN['name']=$UTENTE['Nome']." ".$UTENTE['Cognome'];

$testo='Gentile Studio, ';
$testo.="<br/>le inviamo le credenziali per accedere alla sua area riservata per Easy Cost 1.0";
$testo.="<br/>Accesso: https://".$_SERVER['SERVER_NAME']."/admin/login.php";
$testo.="<br/>Username: ".$UTENTE['username'];
$testo.="<br/>Password: ".$UTENTE['password'];
$testo.="<br/><br/>Saluti";

$mail = new PHPMailer;

$mail->IsSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp-relay.sendinblue.com';                 // Specify main and backup server
$mail->Port = 587;                                    // Set the SMTP port
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'info@analisicosti.it';                  // SMTP username
$mail->Password = '96WBqRCm8GvdTJsS';                      // SMTP password info@analisicosti.it
$mail->SMTPSecure = 'SSL';                            // Enable encryption, 'ssl' also accepted

$mail->AddReplyTo($EMAILADMIN['email'], $EMAILADMIN['name']);

$mail->addAddress($UTENTE['email']);            // Name is optional
$mail->SetFrom('info@analisicosti.it', "Easy Cost 1.0");

$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = "[Easy Cost 1.0] - Credenziali di accesso ";
$mail->Body    = $testo;

if ($mail->send()) {

$query="UPDATE pcs_users SET inviate_credenziali='si' WHERE id_user=?";

$stmt=$dbh->prepare($query);
$stmt->execute(array($iduser));

	if ($stmt) {

	    $ret['result']=true;
	    $ret['msg']="Email inviata allo studio!";

	} else {
        $ret['result']=false;
        $ret['error']="Problema invio email allo studio!";
	}

} else {
    $ret['result']=false;
    $ret['error']=$mail->ErrorInfo;

}
echo json_encode($ret);
exit;

?>