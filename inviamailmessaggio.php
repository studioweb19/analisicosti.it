<?php
$error="";
if ($_POST['emailmessaggio']!='') {

} else {
    $error.="Email richiesta!<br/>";
}
if ($_POST['nomemessaggio']!='') {

} else {
    $error.="Nome richiesto!<br/>";
}
if ($_POST['soggettomessaggio']!='') {

} else {
    $error.="Oggetto del messaggio richiesto!<br/>";
}
if ($error!='') {
	$ret['result']=false;
	$ret['error']=$error;
	echo json_encode($ret);
	exit;
} else {
    $messaggio ="Messaggio ricevuto:\r\n";
    $messaggio.="\r\nSOGGETTO: ".$_POST['soggettomessaggio'];
    $messaggio.="\r\nNOME: ".$_POST['nomemessaggio'];
    $messaggio.="\r\nEMAIL: ".$_POST['emailmessaggio'];
    $messaggio.="\r\nMESSAGGIO: ".$_POST['messaggio'];

    $msgheader = "From: no-reply@analisicosti.it\r\n" .
        "Reply-To: ".$_POST['emailcontatto'] .
        "X-Mailer: PHP/" . phpversion();
    mail('info@analisicosti.it','[Easy Cost 1.0] Messaggio ricevuto', $messaggio, $msgheader);

    $ret['result']=true;
    echo json_encode($ret);
    exit;
}


?>

