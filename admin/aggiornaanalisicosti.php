<?php 
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

$idazienda=$_POST['idazienda'];
$anno=$_POST['anno'];
$mese=$_POST['mese'];
$nomefile=$_POST['nomefile'];
$idCaricamento=$_POST['idCaricamento'];

$ret['post']=$_POST;

//estraggo i parametri del progetto di bilancio
    $query1="SELECT * FROM pcs_progetto_bilancio WHERE anno=? AND id_azienda=? ";
    $stmt1=$dbh->prepare($query1);
    $stmt1->execute(array($anno,$idazienda));
    $PROGETTO=$stmt1->fetch(PDO::FETCH_ASSOC);

$nomefile=str_replace("/admin/","",$nomefile);

$row = 1;
$ret['righetotali']=0;
$ret['inserimenti_andati_a_buon_fine']=0;
$ret['inserimenti_non_andati_a_buon_fine']=0;

$query="SELECT * FROM ".$GLOBAL_tb['clienti']." WHERE id=? LIMIT 0,1";
$stmt = $dbh->prepare($query);
$stmt->execute(array($idazienda));
$CLIENTE=$stmt->fetch(PDO::FETCH_ASSOC);


//----------------------- (i) VECCHIO SISTEMA OVVERO STANDARD
if ($CLIENTE['FormatoPianoConti']==1) {
    if (($handle = fopen($nomefile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data);
            //setNotificheCRUD("admWeb","INFO","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));
            //echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;

            $codiceconto = $data[0];
            $livelli = [];
            $livelli = explode(".", $codiceconto);

            $descrizione = $data[1];
            $dare = str_replace(",", ".", $data[2]);
            $avere = str_replace(",", ".", $data[3]);

            if ($dare==null) { $dare=0; }
            if ($avere==null) { $avere=0; }


            $id = trim($idazienda . "|" . $codiceconto);

            //prima verifico se c'è già il codice conto, altrimenti rischio di sovrascrivere la categoria...

            $queryCheck = "SELECT * FROM pcs_dati_consuntivi WHERE id_azienda=? AND anno=? AND mese=? AND codiceconto=?";
            $stmtCheck = $dbh->prepare($queryCheck);
            $stmtCheck->execute(array($idazienda, $anno, $mese, $codiceconto));
            $ret['righetotali']++;

            if ($rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                //allora esiste
                $id = $rowCheck['id'];
                $query = "REPLACE INTO pcs_dati_consuntivi (id_azienda,codiceconto,anno,mese,dare,avere) VALUES (?,?,?,?,?,?) WHERE id=?";
                $stmt = $dbh->prepare($query);
                if ($stmt->execute(array($idazienda, $codiceconto, $anno, $mese, $dare, $avere, $id))) {

                    $ret['inserimenti_andati_a_buon_fine']++;

                    setNotificheCRUD("admWeb", "SUCCESS", "aggiornaanalisicosti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                } else {

                    $ret['inserimenti_non_andati_a_buon_fine']++;
                    setNotificheCRUD("admWeb", "ERROR", "aggiornapianoconti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                }

            } else {
                //allora non esiste
                $query = "INSERT INTO pcs_dati_consuntivi (id_azienda,codiceconto,anno,mese,dare,avere) VALUES (?,?,?,?,?,?) ";
                $stmt = $dbh->prepare($query);
                if ($stmt->execute(array($idazienda, $codiceconto, $anno, $mese, $dare, $avere))) {

                    $ret['inserimenti_andati_a_buon_fine']++;

                    setNotificheCRUD("admWeb", "SUCCESS", "aggiornaanalisicosti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                } else {

                    $ret['inserimenti_non_andati_a_buon_fine']++;
                    setNotificheCRUD("admWeb", "ERROR", "aggiornapianoconti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                }

            }
        }
        fclose($handle);

        //ORA devo fare il calcolo del prospetto di bilancio
        $totaleRicavi = 0.00;
        $totaleCostiAcquisti = 0.00;
        $totaleCostiGenerali = 0.00;


        //prima di tutto controllo se il mese precedente è già stato caricato, altrimenti segnalo errore!
        if ($mese != "01") {
            $meseprec = sprintf("%02d", intval($mese) - 1);
            $queryCheck1 = "SELECT * FROM pcs_analisi_costi WHERE id_azienda=$idazienda AND meseanno='" . $anno . "-" . $meseprec . "'";
            $stmtCheck1 = $dbh->query($queryCheck1);
            $ret['queryCheck1'] = $queryCheck1;
            //$stmtCheck1->execute(array($idazienda,$anno."-".$meseprec));
            if ($datianalisimeseprecedente = $stmtCheck1->fetch(PDO::FETCH_ASSOC)) {
                //ok, possiamo procedere
            } else {

                //segnaliamo che il mese precedente non è stato inserito a meno che non si tratti di dicembre!! oppure del primo mese in assoluto per questa ditta
                if ($mese == '12' or $mese == $PROGETTO['mese_iniziale']) {

                } else {
                    $ret['result'] = false;
                    $ret['error'] = "Non è possibile aggiornare il prospetto di bilancio per il mese $mese. Prima occorre inserire i dati del mese precedente!";
                    echo json_encode($ret);
                    exit;
                }
            }

        }

        //estraggo i "CAPI CONTO" dei ricavi (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=1";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ricavi[] = $row['codiceconto'];
        }

        //estraggo i "CAPI CONTO" dei costi degli acquisti (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=2";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiacquisti[] = $row['codiceconto'];
        }

        //estraggo i "CAPI CONTO" dei costi generali (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=3";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costigenerali[] = $row['codiceconto'];
        }

        //calcolo ricavi
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $ricavi) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ricaviDare += $row['dare'];
            $ricaviAvere += $row['avere'];
        }
        $totaleRicavi = $ricaviAvere - $ricaviDare;
        $ret['totaleRicavi'] = $totaleRicavi;

        //calcolo costi acquisti
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $costiacquisti) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiAcquistiDare += $row['dare'];
            $costiAcquistiAvere += $row['avere'];
        }
        $totaleCostiAcquisti = $costiAcquistiDare - $costiAcquistiAvere;
        $ret['totaleCostiAcquisti'] = $totaleCostiAcquisti;

        //calcolo costi generali
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $costigenerali) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiGeneraliDare += $row['dare'];
            $costiGeneraliAvere += $row['avere'];
        }
        $totaleCostiGenerali = $costiGeneraliDare - $costiGeneraliAvere;
        $ret['totaleCostiGenerali'] = $totaleCostiGenerali;

        //recupero i dati dal progetto di bilancio e me li porto dietro
        $querydatiprogetto = "SELECT * FROM pcs_progetto_bilancio WHERE id_azienda=? AND anno=?";
        $stmt = $dbh->prepare($querydatiprogetto);
        $stmt->execute(array($idazienda, $anno));
        $datiprogetto = $stmt->fetch(PDO::FETCH_ASSOC);

        $ricavipresunti = $datiprogetto['ricavi_presunti'] / 12;
        $ricavipersonalizzati = $datiprogetto['ricavi_personalizzati_' . $mese];
        $ricavieffettivitotali = $totaleRicavi;

        if ($mese == "01") {
            $ricavidelmese = $totaleRicavi;
            $acquistidelmese = $totaleCostiAcquisti;
            $costigeneralidelmese = $totaleCostiGenerali;

        } else {
            //recupero dati della riga precedente
            $ricavidelmese = $totaleRicavi - $datianalisimeseprecedente['ricavi_totali'];
            $acquistidelmese = $totaleCostiAcquisti - $datianalisimeseprecedente['acquisti_totali'];
            $costigeneralidelmese = $totaleCostiGenerali - $datianalisimeseprecedente['costigenerali_totali'];
        }

        //ora inserisco la riga
        //prima la rimuovo
        $queryDel = "DELETE FROM pcs_analisi_costi WHERE meseanno=? AND id_azienda=?";
        $stmtDel = $dbh->prepare($queryDel);
        $stmtDel->execute(array($anno . "-" . $mese, $idazienda));
        $query = "INSERT INTO pcs_analisi_costi 
    (meseanno,id_azienda,anno,mese,ricavi_presunti,ricavi_personalizzati,ricavi_totali,ricavi_mese,acquisti_totali,acquisti_mese,costigenerali_totali,costigenerali_mese) VALUES 
    (?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($anno . "-" . $mese, $idazienda, $anno, $mese, $ricavipresunti, $ricavipersonalizzati, $ricavieffettivitotali, $ricavidelmese, $totaleCostiAcquisti, $acquistidelmese, $totaleCostiGenerali, $costigeneralidelmese));

        $ret['result'] = true;
        $query = "UPDATE pcs_caricamento_dati SET analisicosti_aggiornata='si' WHERE id=?";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idCaricamento));

        $ret['msg'] .= $ret['righetotali'] . " righe analizzate! Analisi aggiornata al $mese / $anno";
        echo json_encode($ret);
        exit();
    } else {
        $ret['result'] = false;
        $ret['error'] = "Errore sul file";
        echo json_encode($ret);
        exit();
    }
}
//----------------------- (f) VECCHIO SISTEMA OVVERO STANDARD


//----------------------- (i) SISTEMI
if ($CLIENTE['FormatoPianoConti']==2) {
    if (($handle = fopen($nomefile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data);
            //setNotificheCRUD("admWeb","INFO","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));
            //echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;


            //prima colonna codice conto nella forma 47.01.12 (con i . come separatori)
            //seconda colonna vuota
            //terza colonna DESCRIZIONE
            //quarta colonna vuota
            //quinta colonna non ci interessa
            //sesta colonna Saldo OK
            //settima colonna è D o A per cui se è A lo metto nei RICAVI

            //Conto;COLONNA2;Descrizione;COLONNA4;% reddito deducibile;Saldo finale;COLONNA11;COLONNA12;Saldo iniziale;COLONNA14;Importo Dare;Importo Avere;Rettifiche;COLONNA18



            $codiceconto = $data[0];
            $livelli = [];
            $livelli = explode(".", $codiceconto);


            $descrizione = $data[1];

            if ($data[6]=='D') {
                $avere=0;
                $dare = str_replace(",", ".", $data[5]);
            }
            if ($data[6]=='A') {
                $dare=0;
                $avere = str_replace(",", ".", $data[5]);
                $avere=$avere*(-1); //avere è sempre in negativo in questo tipo di file
            }


            if ($dare==null) { $dare=0; }
            if ($avere==null) { $avere=0; }

            $id = trim($idazienda . "|" . $codiceconto);

            //prima verifico se c'è già il codice conto, altrimenti rischio di sovrascrivere la categoria...

            $queryCheck = "SELECT * FROM pcs_dati_consuntivi WHERE id_azienda=? AND anno=? AND mese=? AND codiceconto=?";
            $stmtCheck = $dbh->prepare($queryCheck);
            $stmtCheck->execute(array($idazienda, $anno, $mese, $codiceconto));
            $ret['righetotali']++;

            if ($rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                //allora esiste
                $id = $rowCheck['id'];
                $query = "REPLACE INTO pcs_dati_consuntivi (id_azienda,codiceconto,anno,mese,dare,avere) VALUES (?,?,?,?,?,?) WHERE id=?";
                $stmt = $dbh->prepare($query);
                if ($stmt->execute(array($idazienda, $codiceconto, $anno, $mese, $dare, $avere, $id))) {

                    $ret['inserimenti_andati_a_buon_fine']++;

                    setNotificheCRUD("admWeb", "SUCCESS", "aggiornaanalisicosti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                } else {

                    $ret['inserimenti_non_andati_a_buon_fine']++;
                    setNotificheCRUD("admWeb", "ERROR", "aggiornapianoconti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                }

            } else {
                //allora non esiste
                $query = "INSERT INTO pcs_dati_consuntivi (id_azienda,codiceconto,anno,mese,dare,avere) VALUES (?,?,?,?,?,?) ";
                $stmt = $dbh->prepare($query);
                if ($stmt->execute(array($idazienda, $codiceconto, $anno, $mese, $dare, $avere))) {

                    $ret['inserimenti_andati_a_buon_fine']++;

                    setNotificheCRUD("admWeb", "SUCCESS", "aggiornaanalisicosti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                } else {

                    $ret['inserimenti_non_andati_a_buon_fine']++;
                    setNotificheCRUD("admWeb", "ERROR", "aggiornaanalisicosti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                }

            }
        }
        fclose($handle);

        //ORA devo fare il calcolo del prospetto di bilancio
        $totaleRicavi = 0.00;
        $totaleCostiAcquisti = 0.00;
        $totaleCostiGenerali = 0.00;


        //prima di tutto controllo se il mese precedente è già stato caricato, altrimenti segnalo errore!
        if ($mese != "01") {
            $meseprec = sprintf("%02d", intval($mese) - 1);
            $queryCheck1 = "SELECT * FROM pcs_analisi_costi WHERE id_azienda=$idazienda AND meseanno='" . $anno . "-" . $meseprec . "'";
            $stmtCheck1 = $dbh->query($queryCheck1);
            $ret['queryCheck1'] = $queryCheck1;
            //$stmtCheck1->execute(array($idazienda,$anno."-".$meseprec));
            if ($datianalisimeseprecedente = $stmtCheck1->fetch(PDO::FETCH_ASSOC)) {
                //ok, possiamo procedere
            } else {

                //segnaliamo che il mese precedente non è stato inserito a meno che non si tratti di dicembre!! oppure del primo mese in assoluto per questa ditta
                if ($mese == '12' or $mese == $PROGETTO['mese_iniziale']) {

                } else {
                    $ret['result'] = false;
                    $ret['error'] = "Non è possibile aggiornare il prospetto di bilancio per il mese $mese. Prima occorre inserire i dati del mese precedente!";
                    echo json_encode($ret);
                    exit;
                }
            }

        }

        //estraggo i "CAPI CONTO" dei ricavi (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=1";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ricavi[] = $row['codiceconto'];
        }

        //estraggo i "CAPI CONTO" dei costi degli acquisti (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=2";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiacquisti[] = $row['codiceconto'];
        }

        //estraggo i "CAPI CONTO" dei costi generali (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=3";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costigenerali[] = $row['codiceconto'];
        }

        //calcolo ricavi
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $ricavi) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ricaviDare += $row['dare'];
            $ricaviAvere += $row['avere'];
        }
        $totaleRicavi = $ricaviAvere - $ricaviDare;
        $ret['totaleRicavi'] = $totaleRicavi;

        //calcolo costi acquisti
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $costiacquisti) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiAcquistiDare += $row['dare'];
            $costiAcquistiAvere += $row['avere'];
        }
        $totaleCostiAcquisti = $costiAcquistiDare - $costiAcquistiAvere;
        $ret['totaleCostiAcquisti'] = $totaleCostiAcquisti;

        //calcolo costi generali
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $costigenerali) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiGeneraliDare += $row['dare'];
            $costiGeneraliAvere += $row['avere'];
        }
        $totaleCostiGenerali = $costiGeneraliDare - $costiGeneraliAvere;
        $ret['totaleCostiGenerali'] = $totaleCostiGenerali;

        //recupero i dati dal progetto di bilancio e me li porto dietro
        $querydatiprogetto = "SELECT * FROM pcs_progetto_bilancio WHERE id_azienda=? AND anno=?";
        $stmt = $dbh->prepare($querydatiprogetto);
        $stmt->execute(array($idazienda, $anno));
        $datiprogetto = $stmt->fetch(PDO::FETCH_ASSOC);

        $ricavipresunti = $datiprogetto['ricavi_presunti'] / 12;
        $ricavipersonalizzati = $datiprogetto['ricavi_personalizzati_' . $mese];
        $ricavieffettivitotali = $totaleRicavi;

        if ($mese == "01") {
            $ricavidelmese = $totaleRicavi;
            $acquistidelmese = $totaleCostiAcquisti;
            $costigeneralidelmese = $totaleCostiGenerali;

        } else {
            //recupero dati della riga precedente
            $ricavidelmese = $totaleRicavi - $datianalisimeseprecedente['ricavi_totali'];
            $acquistidelmese = $totaleCostiAcquisti - $datianalisimeseprecedente['acquisti_totali'];
            $costigeneralidelmese = $totaleCostiGenerali - $datianalisimeseprecedente['costigenerali_totali'];
        }

        //ora inserisco la riga
        //prima la rimuovo
        $queryDel = "DELETE FROM pcs_analisi_costi WHERE meseanno=? AND id_azienda=?";
        $stmtDel = $dbh->prepare($queryDel);
        $stmtDel->execute(array($anno . "-" . $mese, $idazienda));
        $query = "INSERT INTO pcs_analisi_costi 
    (meseanno,id_azienda,anno,mese,ricavi_presunti,ricavi_personalizzati,ricavi_totali,ricavi_mese,acquisti_totali,acquisti_mese,costigenerali_totali,costigenerali_mese) VALUES 
    (?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($anno . "-" . $mese, $idazienda, $anno, $mese, $ricavipresunti, $ricavipersonalizzati, $ricavieffettivitotali, $ricavidelmese, $totaleCostiAcquisti, $acquistidelmese, $totaleCostiGenerali, $costigeneralidelmese));

        $ret['result'] = true;
        $query = "UPDATE pcs_caricamento_dati SET analisicosti_aggiornata='si' WHERE id=?";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idCaricamento));

        $ret['msg'] .= $ret['righetotali'] . " righe analizzate! Analisi aggiornata al $mese / $anno";
        echo json_encode($ret);
        exit();
    } else {
        $ret['result'] = false;
        $ret['error'] = "Errore sul file";
        echo json_encode($ret);
        exit();
    }
}
//----------------------- (f) SISTEMI

//----------------------- (i) BUFFETTI DALY
if ($CLIENTE['FormatoPianoConti']==3) {
    if (($handle = fopen($nomefile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data);
            //setNotificheCRUD("admWeb","INFO","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));
            //echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;


            $codiceconto=str_replace(" ","",$data[0]);
            $codiceconto=str_replace("/",".",$codiceconto);
//            29 / 5 / 19;      Energia elettrica uso civile;105,86;
            $livelli=[];
            $livelli=explode(".",$codiceconto);

            $descrizione=str_replace("/","-",$data[1]);
            $descrizione=str_replace(","," ",$descrizione);
            $descrizione=str_replace("."," ",$descrizione);


            //7.262,37 deve diventare 7267.37

            $dare=str_replace(".","",$data[2]);
            $avere=str_replace(".","",$data[3]);

            $dare=str_replace(",",".",$dare);
            $avere=str_replace(",",".",$avere);

            if ($dare==null) { $dare=0; }
            if ($avere==null) { $avere=0; }

            $id=trim($idazienda."|".$codiceconto);


            //prima verifico se c'è già il codice conto, altrimenti rischio di sovrascrivere la categoria...

            $queryCheck = "SELECT * FROM pcs_dati_consuntivi WHERE id_azienda=? AND anno=? AND mese=? AND codiceconto=?";
            $stmtCheck = $dbh->prepare($queryCheck);
            $stmtCheck->execute(array($idazienda, $anno, $mese, $codiceconto));
            $ret['righetotali']++;

            if ($rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                //allora esiste
                $id = $rowCheck['id'];
                $query = "REPLACE INTO pcs_dati_consuntivi (id_azienda,codiceconto,anno,mese,dare,avere) VALUES (?,?,?,?,?,?) WHERE id=?";
                $stmt = $dbh->prepare($query);
                if ($stmt->execute(array($idazienda, $codiceconto, $anno, $mese, $dare, $avere, $id))) {

                    $ret['inserimenti_andati_a_buon_fine']++;

                    setNotificheCRUD("admWeb", "SUCCESS", "aggiornaanalisicosti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                } else {

                    $ret['inserimenti_non_andati_a_buon_fine']++;
                    setNotificheCRUD("admWeb", "ERROR", "aggiornapianoconti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                }

            } else {
                //allora non esiste
                $query = "INSERT INTO pcs_dati_consuntivi (id_azienda,codiceconto,anno,mese,dare,avere) VALUES (?,?,?,?,?,?) ";
                $stmt = $dbh->prepare($query);
                if ($stmt->execute(array($idazienda, $codiceconto, $anno, $mese, $dare, $avere))) {

                    $ret['inserimenti_andati_a_buon_fine']++;

                    setNotificheCRUD("admWeb", "SUCCESS", "aggiornaanalisicosti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                } else {

                    $ret['inserimenti_non_andati_a_buon_fine']++;
                    setNotificheCRUD("admWeb", "ERROR", "aggiornapianoconti.php $idazienda, $anno, $mese, $codiceconto, $dare, $avere " . $nomefile, json_encode($data));

                }

            }
        }
        fclose($handle);

        //ORA devo fare il calcolo del prospetto di bilancio
        $totaleRicavi = 0.00;
        $totaleCostiAcquisti = 0.00;
        $totaleCostiGenerali = 0.00;


        //prima di tutto controllo se il mese precedente è già stato caricato, altrimenti segnalo errore!
        if ($mese != "01") {
            $meseprec = sprintf("%02d", intval($mese) - 1);
            $queryCheck1 = "SELECT * FROM pcs_analisi_costi WHERE id_azienda=$idazienda AND meseanno='" . $anno . "-" . $meseprec . "'";
            $stmtCheck1 = $dbh->query($queryCheck1);
            $ret['queryCheck1'] = $queryCheck1;
            //$stmtCheck1->execute(array($idazienda,$anno."-".$meseprec));
            if ($datianalisimeseprecedente = $stmtCheck1->fetch(PDO::FETCH_ASSOC)) {
                //ok, possiamo procedere
            } else {

                //segnaliamo che il mese precedente non è stato inserito a meno che non si tratti di dicembre!! oppure del primo mese in assoluto per questa ditta
                if ($mese == '12' or $mese == $PROGETTO['mese_iniziale']) {

                } else {
                    $ret['result'] = false;
                    $ret['error'] = "Non è possibile aggiornare il prospetto di bilancio per il mese $mese. Prima occorre inserire i dati del mese precedente!";
                    echo json_encode($ret);
                    exit;
                }
            }

        }

        //estraggo i "CAPI CONTO" dei ricavi (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=1";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ricavi[] = $row['codiceconto'];
        }

        //estraggo i "CAPI CONTO" dei costi degli acquisti (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=2";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiacquisti[] = $row['codiceconto'];
        }

        //estraggo i "CAPI CONTO" dei costi generali (quelli di livello1)
        $query = "SELECT codiceconto FROM pcs_piano_conti WHERE livello2 IS NULL AND id_azienda=? AND categoria=3";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idazienda));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costigenerali[] = $row['codiceconto'];
        }

        //calcolo ricavi
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $ricavi) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ricaviDare += $row['dare'];
            $ricaviAvere += $row['avere'];
        }
        $totaleRicavi = $ricaviAvere - $ricaviDare;
        $ret['totaleRicavi'] = $totaleRicavi;

        //calcolo costi acquisti
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $costiacquisti) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiAcquistiDare += $row['dare'];
            $costiAcquistiAvere += $row['avere'];
        }
        $totaleCostiAcquisti = $costiAcquistiDare - $costiAcquistiAvere;
        $ret['totaleCostiAcquisti'] = $totaleCostiAcquisti;

        //calcolo costi generali
        $query = "SELECT dare,avere FROM pcs_dati_consuntivi WHERE anno=$anno AND mese=$mese AND id_azienda=$idazienda AND codiceconto IN ('" . join("','", $costigenerali) . "') ";
        $stmt = $dbh->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $costiGeneraliDare += $row['dare'];
            $costiGeneraliAvere += $row['avere'];
        }
        $totaleCostiGenerali = $costiGeneraliDare - $costiGeneraliAvere;
        $ret['totaleCostiGenerali'] = $totaleCostiGenerali;

        //recupero i dati dal progetto di bilancio e me li porto dietro
        $querydatiprogetto = "SELECT * FROM pcs_progetto_bilancio WHERE id_azienda=? AND anno=?";
        $stmt = $dbh->prepare($querydatiprogetto);
        $stmt->execute(array($idazienda, $anno));
        $datiprogetto = $stmt->fetch(PDO::FETCH_ASSOC);

        $ricavipresunti = $datiprogetto['ricavi_presunti'] / 12;
        $ricavipersonalizzati = $datiprogetto['ricavi_personalizzati_' . $mese];
        $ricavieffettivitotali = $totaleRicavi;

        if ($mese == "01") {
            $ricavidelmese = $totaleRicavi;
            $acquistidelmese = $totaleCostiAcquisti;
            $costigeneralidelmese = $totaleCostiGenerali;

        } else {
            //recupero dati della riga precedente
            $ricavidelmese = $totaleRicavi - $datianalisimeseprecedente['ricavi_totali'];
            $acquistidelmese = $totaleCostiAcquisti - $datianalisimeseprecedente['acquisti_totali'];
            $costigeneralidelmese = $totaleCostiGenerali - $datianalisimeseprecedente['costigenerali_totali'];
        }

        //ora inserisco la riga
        //prima la rimuovo
        $queryDel = "DELETE FROM pcs_analisi_costi WHERE meseanno=? AND id_azienda=?";
        $stmtDel = $dbh->prepare($queryDel);
        $stmtDel->execute(array($anno . "-" . $mese, $idazienda));
        $query = "INSERT INTO pcs_analisi_costi 
    (meseanno,id_azienda,anno,mese,ricavi_presunti,ricavi_personalizzati,ricavi_totali,ricavi_mese,acquisti_totali,acquisti_mese,costigenerali_totali,costigenerali_mese) VALUES 
    (?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($anno . "-" . $mese, $idazienda, $anno, $mese, $ricavipresunti, $ricavipersonalizzati, $ricavieffettivitotali, $ricavidelmese, $totaleCostiAcquisti, $acquistidelmese, $totaleCostiGenerali, $costigeneralidelmese));

        $ret['result'] = true;
        $query = "UPDATE pcs_caricamento_dati SET analisicosti_aggiornata='si' WHERE id=?";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idCaricamento));

        $ret['msg'] .= $ret['righetotali'] . " righe analizzate! Analisi aggiornata al $mese / $anno";
        echo json_encode($ret);
        exit();
    } else {
        $ret['result'] = false;
        $ret['error'] = "Errore sul file";
        echo json_encode($ret);
        exit();
    }
}
//----------------------- (f) BUFFETTI DALY

