<?php

//ricordarsi di installare image magick come estensione php sul server altrimenti non può fare
//le anteprime dei pdf!!!
//vedere test.php per provare
//https://help.dreamhost.com/hc/en-us/articles/215075007-GraphicsMagick-and-gmagick-PHP-module-on-Shared-hosting


date_default_timezone_set('Europe/Rome');
session_start();
//$superuserOverride=1;

/*----------------------- Prendo i parametri dalla sessione di login generale ---------*/
/*

		$_SESSION['BSMAIN']['id_utente']=$row['id_cliente'];
		$_SESSION['BSMAIN']['nome']=$row['nome'];
		$_SESSION['BSMAIN']['versione']=$row['versione'];
		$_SESSION['BSMAIN']['DBhost']=$row['dbhost'];
		$_SESSION['BSMAIN']['DBname']=$row['dbname'];
		$_SESSION['BSMAIN']['DBuser']=$row['dbuser'];
		$_SESSION['BSMAIN']['DBpass']=$row['dbpassword'];
		$_SESSION['BSMAIN']['stato']=$row['stato'];
		$_SESSION['BSMAIN']['data_scadenza']=$row['data_scadenza'];
		$_SESSION['BSMAIN']['data_inizio']=$row['data_inizio'];
		$_SESSION['BSMAIN']['username']=$row['username'];
		$_SESSION['BSMAIN']['email']=$row['email'];

*/
//----------- (i) nomi delle tabelle ---------------------------------------------------------------------------------
$GLOBAL_tb['moduli']                    ="pcs_moduli";
$GLOBAL_tb['permessi']                  ="pcs_permessi";
$GLOBAL_tb['users']                     ="pcs_users";
$GLOBAL_tb['ruoli']                     ="pcs_ruoli";
$GLOBAL_tb['lingue']                    ="pcs_lingue";
$GLOBAL_tb['lingue_interfaccia']        ="pcs_lingue_interfaccia";
$GLOBAL_tb['lingue_users']              ="pcs_lingue_users";
$GLOBAL_tb['notificheCRUD']             ="pcs_notificheCRUD";
$GLOBAL_tb['files']                     ="pcs_file";
$GLOBAL_tb['testi']                     ="pcs_testi";
$GLOBAL_tb['campi_traducibili']         ="pcs_campi_traducibili";
$GLOBAL_tb['clienti']                   ="pcs_clienti";
$GLOBAL_tb['moduliclienti']              ="pcs_moduli_clienti";
$GLOBAL_tb['sedi_clienti']              ="pcs_sedi_clienti";
$GLOBAL_tb['postazioni']                ="pcs_postazioni";
$GLOBAL_tb['interventi']                ="pcs_visite";
$GLOBAL_tb['visite']                    ="pcs_visite";
$GLOBAL_tb['ispezioni']                 ="pcs_ispezioni";
$GLOBAL_tb['tipi_servizio']             ="pcs_tipi_servizio";
$GLOBAL_tb['aree']                      ="pcs_aree";
$GLOBAL_tb['infestanti']                ="pcs_infestanti";
$GLOBAL_tb['pianodeiconti']             ="pcs_piano_conti";
$GLOBAL_tb['pacchetti']                 ="pcs_pacchetti";
$GLOBAL_tb['elementi_pacchetto']        ="pcs_pacchetti_righe";
$GLOBAL_tb['caricamento_dati']          ="pcs_caricamento_dati";
$GLOBAL_tb['prospetto_previsionale']    ="pcs_progetto_bilancio";
$GLOBAL_tb['elementi_moduli_generati']  ="pcs_moduli_generati_righe";
$GLOBAL_tb['moduli_generati']           ="pcs_moduli_generati";
$GLOBAL_tb['pacchetti_attivati']                 ="pcs_pacchetti_attivati";
$GLOBAL_tb['elementi_pacchetto_attivato']        ="pcs_pacchetti_attivati_righe";
$GLOBAL_tb['moduliclientistoria']       ="pcs_moduli_clienti_storia";


//----------- (f) nomi delle tabelle ---------------------------------------------------------------------------------

$mese['01']="Gen";
$mese['02']="Feb";
$mese['03']="Mar";
$mese['04']="Apr";
$mese['05']="Mag";
$mese['06']="Giu";
$mese['07']="Lug";
$mese['08']="Ago";
$mese['09']="Set";
$mese['10']="Ott";
$mese['11']="Nov";
$mese['12']="Dic";

$meselong['01']="Gennaio";
$meselong['02']="Febbraio";
$meselong['03']="Marzo";
$meselong['04']="Aprile";
$meselong['05']="Maggio";
$meselong['06']="Giugno";
$meselong['07']="Luglio";
$meselong['08']="Agosto";
$meselong['09']="Settembre";
$meselong['10']="Ottobre";
$meselong['11']="Novembre";
$meselong['12']="Dicembre";


//----------- (i) parametri di connessione ---------------------------------------------------------------------------
$user="c6acuser";
$pass="B3nv3nut0";
$database="c6analisicostidb";
$host="localhost";

//----------- (f) parametri di connessione ---------------------------------------------------------------------------

//----------- (i) connessione ---------------------------------------------------------------------------
try {
    $dbh = new PDO('mysql:host='.$host.';dbname='.$database, $user, $pass);
    $dbh->exec("set names utf8");

    $query="SELECT * FROM ".$GLOBAL_tb['users']."  WHERE (username=? OR email=?) LIMIT 0,1";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_SESSION['BSMAIN']['username'], $_SESSION['BSMAIN']['email']));
    if ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $trovato=true;
        $_SESSION['BSid_user']=$row['id_user'];
    }

    //echo "Dentro il db";
//    foreach($dbh->query('SELECT * from FOO') as $row) {
//        print_r($row);
//    }
//    $dbh = null;
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
//----------- (f) connessione ---------------------------------------------------------------------------

/*--------------------------- Directory di questa versione --------- */
$sitedir="/admin/";
$basedir=$sitedir;
$root_server = $_SERVER['DOCUMENT_ROOT'];

$projecttitle="ANALISI COSTI";

//----------- (f) variabili predefinite ------------------------------------------------------------------------------

/* (i) --------------------------------------------------- parametri specifici  --------------------------------------------------- */


$ftp_host = "analisicosti.it";
$ftp_id = "studioweb19enzoacuser";
$ftp_pw = "B3nv3nut0";

$FTPUPLOADDIR="/web";

$sitoweb="https://analisicosti.it";

$directoryfiles="/admin/file";

//widthmax e heightmax servono a plupload, sono le dimensioni massime delle immagini che vengono uploadate sul server

$widthmax=1200;
$heightmax=800;

//con l'array resizes invece vengono eseguiti i thumbnails, nella modalità crop, oppure landscape oppure portrait
//il resizes 0 è quello di default, non andrebbe cambiato mai!

//$resizes è definito a livello di file _parametri.php
//il primo è quello di default

$resizes[0]['prefisso']="crop";
$resizes[0]['crop']="crop";
$resizes[0]['width']=150;
$resizes[0]['height']=150;

$resizes[1]['prefisso']="crop";
$resizes[1]['crop']="crop";
$resizes[1]['width']=80;
$resizes[1]['height']=80;

$resizes[2]['prefisso']="thumb";
$resizes[2]['crop']="landscape"; //forzo il resize su width 400
$resizes[2]['width']=400;
$resizes[2]['height']=400;

$resizes[3]['prefisso']="small";
$resizes[3]['crop']="landscape"; //forzo il resize su width 300
$resizes[3]['width']=300;
$resizes[3]['height']=300;

//inoltre è obbligatorio mettere come campo traducibile della tabella tb_file il campo "nome", perché serve per l'interfaccia di upload, se gli allegati sono abilitati come modulo

/* (f) --------------------------------------------------- parametri specifici del nuovo cms --------------------------------------------------- */


// include le funzioni generali
include($root_server.$sitedir."functions.php");

//default english
$locale = "it_IT";

putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain("messages", "./locale");
textdomain("messages");

$utente=array();
$lingue=array();

$defaultLang='it';
$lang='it';

$prefissoqrcode="imp_";

//$_SESSION['BSid_user']=1;

//verifico se c'è il "passpartout"
$trovato=false;
if ($_GET['idclientehash']!='') {
    $query="SELECT * FROM ".$GLOBAL_tb['clienti']." WHERE (idclientehash=?) LIMIT 0,1";
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
}


$aliasnames=array();
$aliasnames['ricavi_personalizzati_01']="Ricavi Gen";
$aliasnames['ricavi_personalizzati_02']="Ricavi Feb";
$aliasnames['ricavi_personalizzati_03']="Ricavi Mar";
$aliasnames['ricavi_personalizzati_04']="Ricavi Apr";
$aliasnames['ricavi_personalizzati_05']="Ricavi Mag";
$aliasnames['ricavi_personalizzati_06']="Ricavi Giu";
$aliasnames['ricavi_personalizzati_07']="Ricavi Lug";
$aliasnames['ricavi_personalizzati_08']="Ricavi Ago";
$aliasnames['ricavi_personalizzati_09']="Ricavi Set";
$aliasnames['ricavi_personalizzati_10']="Ricavi Ott";
$aliasnames['ricavi_personalizzati_11']="Ricavi Nov";
$aliasnames['ricavi_personalizzati_12']="Ricavi Dic";

if ($_SESSION['pcs_id_user']>0) {
    $utente = getUtente($_SESSION['pcs_id_user']);
    if ($_SESSION['pcs_id_cliente']>0) {
        $_SESSION['ClienteSceltoDaUtente']=$_SESSION['pcs_id_cliente'];
        //unset($_SESSION['annoscelto']);
        $utente['id_cliente']=$_SESSION['pcs_id_cliente'];
        $moduliattivatiquery="SELECT mc.*,mg.icona,mg.nome FROM ".$GLOBAL_tb['moduliclienti']." mc JOIN ".$GLOBAL_tb['moduli_generati']." mg ON mg.id=mc.id_modulo_generato  WHERE id_cliente=?";
        $stmt=$dbh->prepare($moduliattivatiquery);
        $stmt->execute(array($utente['id_cliente']));
        $moduliattivati=array();
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $moduliattivati[]=$row;
        }
        $cliente=getCliente($_SESSION['pcs_id_cliente']);

    } else {
        $utente['id_cliente']=0;
    }

    if ($_GET['resetCliente']==1) {
        unset($_SESSION['ClienteSceltoDaUtente']);
        unset($_SESSION['annoscelto']);
    }

    if ($_SESSION['ClienteSceltoDaUtente']>0) {
        $clientescelto=getCliente($_SESSION['ClienteSceltoDaUtente']);
        $annoscelto=$_SESSION['annoscelto'];
    }
//    $lingue = getLingue($_SESSION['id_user']);


    $lingue=explode(",",$utente['languages']);

    if (count($lingue)>0) {
    } else {
        $lingue[0]="it";
    }

    $defaultLang =substr($utente['main_language'],0,2);
    $lang =$defaultLang;

    $_SESSION['pcs_lang'] = $lang;

    $locale =$utente['main_language'];

    $_SESSION['pcs_locale']=$locale;

    putenv("LC_ALL=$locale");
    setlocale(LC_ALL, $locale);
    bindtextdomain("messages", "./locale");
    textdomain("messages");

}
setlocale(LC_MONETARY, 'it_IT');
//echo money_format('%.2n', $number) . "\n";


//TIMEZONE
$TIMEZONE="US/Pacific"; //timezone del server dove c'è mysql
?>
