<?php $f=$_GET['f'];?>
<?php session_start(); ?>
<?php //echo "INC10HEADER inizio inizio<br/>";print_r($_SESSION);?>
<?php //clienti

if ($f['c-id']>0) {
    $_SESSION['ClienteSceltoDaUtente']=$f['c-id'];
} else {
    if ($_SESSION['pcs_id_cliente']>0) {
        $_SERVER['QUERY_STRING']="f[c-id]=".$_SESSION['pcs_id_cliente'];
    } else {
        $_SERVER['QUERY_STRING']="f[c-id]=".$_SESSION['ClienteSceltoDaUtente'];
    }
} //(f) clienti

$token=json_decode(base64_decode($_GET['token']),true);

//se esiste $token, prevale su tutto
if ($token['anno']>0) {
    $_SESSION['annoscelto']=$token['anno'];
} else {
    if ($_SESSION['annoscelto']>0) {
        $token['anno']=$_SESSION['annoscelto'];
    }
}
if ($token['id_azienda']>0) {
    $_SESSION['ClienteSceltoDaUtente']=$token['id_azienda'];
} else {
    if ($_SESSION['ClienteSceltoDaUtente']>0) {
        $token['id_azienda']=$_SESSION['ClienteSceltoDaUtente'];
    }
}

if ($_GET['debug']=="VIACOLDEBUG") {
    echo "PRIMA DI CONFIG----SESSION:<br/>";
    print_r($_SESSION);

    echo "<br/>token:<br/>";
    print_r($token);
    echo "<hr/>";

}

include 'config.php';

if ($_GET['debug']=="VIACOLDEBUG") {
    echo "DOPO CONFIG----SESSION:<br/>";
    print_r($_SESSION);

    echo "<br/>token:<br/>";
    print_r($token);
    echo "<hr/>";

}

//di default il blocco non c'è
$bloccoprivacy=0;


//CONTROLLO PRIVACY PER CLIENTI OPPURE STUDI

if ($_SESSION['pcs_id_cliente']>0 && $_SESSION['pcs_id_user']==11) {
    //print_r($cliente);
    //if ($cliente['privacy']=='' || $cliente['privacy']=='0000-00-00 00:00:00') {
    //    $bloccoprivacy=1;
    //}
} else {

    //controllo per gli studi
    if (($utente['id_ruolo']==4) && ($utente['privacy']=='' || $utente['privacy']=='0000-00-00 00:00:00') || ($utente['termini']=='' || $utente['termini']=='0000-00-00 00:00:00')) {
        $bloccoprivacy=1;
    }
}



if ($token['id_azienda']>0) {
    $queryClientiAnnoMax="SELECT id_azienda,max(anno) as annomax FROM pcs_progetto_bilancio WHERE id_azienda=?";
    $stmtClientiAnnoMax=$dbh->prepare($queryClientiAnnoMax);
    $stmtClientiAnnoMax->execute(array($token['id_azienda']));
    $ClientiAnnoMax=array();
    while($row = $stmtClientiAnnoMax->fetch(PDO::FETCH_ASSOC)) {
        $ClientiAnnoMax[$row['id_azienda']] = $row['annomax'];
    }
}

if ($token['anno']>0) {
} else {
    $_SESSION['annoscelto']=$ClientiAnnoMax[$_SESSION['ClienteSceltoDaUtente']];
}

if ($_SESSION['pcs_id_user']>0) {

    //$utente=getUtente($_SESSION['pcs_id_user']);

} else if ($_GET['idclientehash']) {
//proviamo prima il cliente
    $query="SELECT * FROM ".$GLOBAL_tb['clienti']." WHERE idclientehash=? LIMIT 0,1";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_GET['idclientehash']));
    $nome="";
    if ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $trovato=true;
        $nome=$row['RagioneSociale'];
        $_SESSION['pcs_id_user']=11;
        $_SESSION['pcs_id_cliente']=$row['id'];
        $_SESSION['pcs_nome']=$row['RagioneSociale'];
        $_SESSION['pcs_cognome']='';
    }

} else {

    if ($_SERVER['SCRIPT_NAME'] != $sitedir . "login.php") {
        header('Location:' . $sitedir."login.php");
        exit;
    }
}?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $projecttitle;?> </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            padding-top: 50px;
            padding-bottom: 20px;
        }
    </style>
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">

    <!-- (i) 3rd party components -->
    <link rel="stylesheet" href="3rd-party/chosen/chosen.min.css">
    <!-- (f) 3rd party components -->

    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/hover-min.css" >
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.7/css/select.dataTables.min.css">
    <link rel="stylesheet" href="3rd-party/bootstrap-datepicker/bootstrap-datepicker3.min.css" >

    <link rel="stylesheet" href="jqplot/jquery.jqplot.min.css">
    <link rel="stylesheet/less" href="css/timepicker.less" />

    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

    <script src="https://use.fontawesome.com/00e28d196c.js"></script>
    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>


</head>