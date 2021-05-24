<?php
session_start();
$_SESSION['annoscelto']=$_GET['anno'];
//poi devo rimandare indietro l'url, conservando p['id_azienda']

$url=$_GET['backlist']."&p[id_azienda]=".$_GET['idazienda']."&p[anno]=".$_GET['anno']."&sceglianno=".$_GET['anno'];
header("Location: $url");
//echo $url;
exit;
?>
