<?php
$error="";
if ($_POST['emailcontatto']!='') {

} else {
    $error.="Email richiesta!<br/>";
}
if ($_POST['nomecontatto']!='') {

} else {
    $error.="Nome richiesto!<br/>";
}
if ($_POST['cellularecontatto']!='') {

} else {
    $error.="Cellulare richiesto!<br/>";
}
if ($error!='') {
	$ret['result']=false;
	$ret['error']=$error;
	echo json_encode($ret);
	exit;
} else {
    $messaggio ="Richiesta prova gratuita\r\n";
    $messaggio.="\r\n".$_POST['nomecontatto'];
    $messaggio.="\r\n".$_POST['cellularecontatto'];
    $messaggio.="\r\n".$_POST['emailcontatto'];

    $msgheader = "From: no-reply@analisicosti.it\r\n" .
        "Reply-To: ".$_POST['emailcontatto'] .
        "X-Mailer: PHP/" . phpversion();
    mail('info@analisicosti.it','[Easy Cost 1.0] Richiesta prova gratuita', $messaggio, $msgheader);

    include("admin/config.php");
    $query="INSERT INTO registrazioni_dal_sito (ordine,nome,cellulare,email,data) VALUES (0,?,?,?,?)";
    $stmt=$dbh->prepare($query);
    $stmt->execute(array($_POST['nomecontatto'],$_POST['cellularecontatto'],$_POST['emailcontatto'],date("Y-m-d")));


    $ret['result']=true;
    echo json_encode($ret);
    exit;
}


?>

