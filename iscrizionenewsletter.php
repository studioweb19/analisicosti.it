<?php
$error="";
if ($_POST['email']!='') {

} else {
    $error.="Email richiesta!<br/>";
}
if ($error!='') {
	$ret['result']=false;
	$ret['error']=$error;
	echo json_encode($ret);
	exit;
} else {

    include("admin/config.php");
    $query="INSERT INTO iscrizioni_newsletter (email,data) VALUES (?,?)";
    $stmt=$dbh->prepare($query);
    if ($stmt->execute(array($_POST['email'],date("Y-m-d")))) {
        $ret['result']=true;
        echo json_encode($ret);
        exit;
    } else {
        $ret['result']=false;
        $ret['error']="Problemi registrazione db!";
        echo json_encode($ret);
        exit;
    }
}


?>

