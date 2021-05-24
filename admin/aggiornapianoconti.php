<?php 
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

$idazienda=$_POST['idazienda'];
$anno=$_POST['anno'];
$mese=$_POST['mese'];
$nomefile=$_POST['nomefile'];

$rigenera=$_POST['rigenera'];

$nomefile=str_replace("/admin/","",$nomefile);


$row = 1;
$ret['righetotali']=0;
$ret['nuoviconti']=0;
$ret['inserimenti_andati_a_buon_fine']=0;
$ret['inserimenti_non_andati_a_buon_fine']=0;

//prima facciamo il backup dell'intero piano dei conti aggiornato a mese e anno del momento

$queryDel="DELETE FROM pcs_piano_conti_backup WHERE id_azienda=? AND mese=? AND anno=?";
$stmtDel=$dbh->prepare($queryDel);
$stmtDel->execute(array($idazienda,$mese,$anno));

$queryBAC="INSERT INTO pcs_piano_conti_backup SELECT * FROM pcs_piano_conti WHERE id_azienda=?";
$stmtBAC=$dbh->prepare($queryBAC);
$stmtBAC->execute(array($idazienda));
while ($row = $stmtBAC->fetch(PDO::FETCH_ASSOC)) {
    $righe[]=$row;
}



if ($rigenera==1) {
    //elimino il piano dei conti per questa azienda
    $queryDel="DELETE FROM pcs_piano_conti WHERE id_azienda=?";
    $stmtDel=$dbh->prepare($queryDel);
    $stmtDel->execute(array($idazienda));
    $ret['msg']="Piano dei conti azzerato! ";
}

$nuovoconto=array();

$query="SELECT * FROM ".$GLOBAL_tb['clienti']." WHERE id=? LIMIT 0,1";
$stmt = $dbh->prepare($query);
$stmt->execute(array($idazienda));
$CLIENTE=$stmt->fetch(PDO::FETCH_ASSOC);

//----------------------- (i) VECCHIO SISTEMA
if ($CLIENTE['FormatoPianoConti']==1) {
    if (($handle = fopen($nomefile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data);
            //setNotificheCRUD("admWeb","INFO","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));
            //echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;

            $codiceconto=$data[0];
            $livelli=[];
            $livelli=explode(".",$codiceconto);


            $descrizione=str_replace("/","-",$data[1]);
            $descrizione=str_replace(","," ",$descrizione);
            $descrizione=str_replace("."," ",$descrizione);
            $dare=str_replace(",",".",$data[2]);
            $avere=str_replace(",",".",$data[3]);

            $id=trim($idazienda."|".$codiceconto);

            $categoria=3;

            //prima verifico se c'è già il codice conto, altrimenti rischio di sovrascrivere la categoria...

            $queryCheck="SELECT * FROM pcs_piano_conti WHERE id=?";
            $stmtCheck=$dbh->prepare($queryCheck);
            $stmtCheck->execute(array($id));
            if ($rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                //allora esiste
                $categoria=$rowCheck['categoria'];
                $new=0;
                $annoinserimento=$rowCheck['annoinserimento'];
                $meseinserimento=$rowCheck['meseinserimento'];
            } else {
                //allora non esiste
                $nuovoconto[]=$codiceconto;
                $ret['nuoviconti']++;
                $categoria=3; //costi generali
                $new=1;
                $annoinserimento=$anno;
                $meseinserimento=$mese;
            }

            $query="REPLACE INTO pcs_piano_conti (id,id_azienda,codiceconto,nome,livello1,livello2,livello3,livello4,descrizione,categoria,new,annoinserimento,meseinserimento) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) " ;

            $stmt=$dbh->prepare($query);

            $ret['righetotali']++;

            if ($stmt->execute(array(
                $id,$idazienda,$codiceconto,$descrizione,
                $livelli[0],
                '5' => $livelli[1]!='' ? $livelli[1] : null,
                '6' => $livelli[2]!='' ? $livelli[2] : null,
                '7' => $livelli[3]!='' ? $livelli[3] : null,
                $nome,$categoria,$new,$annoinserimento,$meseinserimento))) {

                $ret['inserimenti_andati_a_buon_fine']++;

                setNotificheCRUD("admWeb","SUCCESS","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));

            } else {

                $ret['inserimenti_non_andati_a_buon_fine']++;
                setNotificheCRUD("admWeb","ERROR","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));

            }

            //for ($c=0; $c < $num; $c++) {
            //    echo $data[$c] . "<br />\n";
            //}
        }
        fclose($handle);

        $queryupdate="UPDATE pcs_dati_consuntivi set codiceconto = TRIM(codiceconto) WHERE id_azienda=?";
        $stmt2=$dbh->prepare($queryupdate);
        $stmt2->execute(array($idazienda));

        $query2="UPDATE pcs_caricamento_dati SET pianoconti_aggiornato='si' WHERE id_azienda=? AND anno=? AND mese=?";
        $stmt2=$dbh->prepare($query2);
        $stmt2->execute(array($idazienda,$anno,$mese));

        $ret['result']=true;
        $ret['nuovoconto']=$nuovoconto;
        $ret['msg'].=$ret['righetotali']." righe analizzate, ".$ret['nuoviconti']." nuovi conti trovati!";

        procedurafinale($idazienda);

        echo json_encode($ret);
        exit();
    } else {
        $ret['result']=false;
        $ret['error']="Errore sul file";
        echo json_encode($ret);
        exit();
    }
}
//----------------------- (f) VECCHIO SISTEMA

//----------------------- (i) SISTEMI
if ($CLIENTE['FormatoPianoConti']==2) {

    //prima colonna codice conto nella forma 47.01.12 (con i . come separatori)
    //seconda colonna vuota
    //terza colonna DESCRIZIONE
    //quarta colonna vuota
    //quinta colonna non ci interessa
    //sesta colonna Saldo OK
    //settima colonna è D o A per cui se è A lo metto nei RICAVI

    //Conto;COLONNA2;Descrizione;COLONNA4;% reddito deducibile;Saldo finale;COLONNA11;COLONNA12;Saldo iniziale;COLONNA14;Importo Dare;Importo Avere;Rettifiche;COLONNA18



    if (($handle = fopen($nomefile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data);
            setNotificheCRUD("admWeb","INFO","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));
            //echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;

            $codiceconto=trim($data[0]);
            $livelli=[];
            $livelli=explode(".",$codiceconto);

            $categoria=3; //costi generali

            if ($data[6]=='A') {
                $categoria=1; //è un ricavo
            }

            $descrizione=str_replace("/","-",$data[2]);
            $descrizione=str_replace(","," ",$descrizione);
            $descrizione=str_replace("."," ",$descrizione);

            $id=trim($idazienda."|".$codiceconto);

            //prima verifico se c'è già il codice conto, altrimenti rischio di sovrascrivere la categoria...

            $queryCheck="SELECT * FROM pcs_piano_conti WHERE id=?";
            $stmtCheck=$dbh->prepare($queryCheck);
            $stmtCheck->execute(array($id));
            if ($rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                //allora esiste
                $categoria=$rowCheck['categoria'];
                $new=0;
                $annoinserimento=$rowCheck['annoinserimento'];
                $meseinserimento=$rowCheck['meseinserimento'];
            } else {
                //allora non esiste
                $nuovoconto[]=$codiceconto;
                $ret['nuoviconti']++;
                $categoria=3; //costi generali
                $annoinserimento=$anno;
                $meseinserimento=$mese;

                if ($data[6]=='A') {
                    $categoria=1; //è un ricavo
                }

                $new=1;

            }


            $query="REPLACE INTO pcs_piano_conti (id,id_azienda,codiceconto,nome,livello1,livello2,livello3,livello4,descrizione,categoria,new,annoinserimento,meseinserimento) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) " ;

            $stmt=$dbh->prepare($query);

            $ret['righetotali']++;

            if ($stmt->execute(array(
                $id,$idazienda,$codiceconto,$descrizione,
                $livelli[0],
                '5' => $livelli[1]!='' ? $livelli[1] : null,
                '6' => $livelli[2]!='' ? $livelli[2] : null,
                '7' => $livelli[3]!='' ? $livelli[3] : null,
                $nome,$categoria,$new,$annoinserimento,$meseinserimento))) {

                $ret['inserimenti_andati_a_buon_fine']++;

                setNotificheCRUD("admWeb","SUCCESS","aggiornapianoconti.php sistema:".$CLIENTE['FormatoPianoConti']." $idazienda, $anno, $mese ".$nomefile,json_encode($data));

            } else {

                $ret['inserimenti_non_andati_a_buon_fine']++;
                setNotificheCRUD("admWeb","ERROR","aggiornapianoconti.php sistema:".$CLIENTE['FormatoPianoConti']." $idazienda, $anno, $mese ".$nomefile,$query);
                setNotificheCRUD("admWeb","ERROR","aggiornapianoconti.php sistema:".$CLIENTE['FormatoPianoConti']." $idazienda, $anno, $mese ".$nomefile,json_encode(array(
                    $id,$idazienda,$codiceconto,$descrizione,
                    $livelli[0],
                    '5' => $livelli[1]!='' ? $livelli[1] : null,
                    '6' => $livelli[2]!='' ? $livelli[2] : null,
                    '7' => $livelli[3]!='' ? $livelli[3] : null,
                    $nome,$categoria,$new,$annoinserimento,$meseinserimento)));
            }

            //for ($c=0; $c < $num; $c++) {
            //    echo $data[$c] . "<br />\n";
            //}
        }
        fclose($handle);

        $query2="UPDATE pcs_caricamento_dati SET pianoconti_aggiornato='si' WHERE id_azienda=? AND anno=? AND mese=?";
        $stmt2=$dbh->prepare($query2);
        $stmt2->execute(array($idazienda,$anno,$mese));

        $ret['result']=true;
        $ret['nuovoconto']=$nuovoconto;
        $ret['msg'].=$ret['righetotali']." righe analizzate, ".$ret['nuoviconti']." nuovi conti trovati!";
        procedurafinale($idazienda);
        echo json_encode($ret);
        exit();
    } else {
        $ret['result']=false;
        $ret['error']="Errore sul file";
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

//            $codiceconto=$data[0];

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

            $id=trim($idazienda."|".$codiceconto);

            $categoria=3;

            //prima verifico se c'è già il codice conto, altrimenti rischio di sovrascrivere la categoria...

            $queryCheck="SELECT * FROM pcs_piano_conti WHERE id=?";
            $stmtCheck=$dbh->prepare($queryCheck);
            $stmtCheck->execute(array($id));
            if ($rowCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                //allora esiste
                $categoria=$rowCheck['categoria'];
                $new=0;
                $annoinserimento=$rowCheck['annoinserimento'];
                $meseinserimento=$rowCheck['meseinserimento'];
            } else {
                //allora non esiste
                $nuovoconto[]=$codiceconto;
                $ret['nuoviconti']++;
                $categoria=3; //costi generali
                $new=1;
                $annoinserimento=$anno;
                $meseinserimento=$mese;
            }

            $query="REPLACE INTO pcs_piano_conti (id,id_azienda,codiceconto,nome,livello1,livello2,livello3,livello4,descrizione,categoria,new,annoinserimento,meseinserimento) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) " ;

            $stmt=$dbh->prepare($query);

            $ret['righetotali']++;

            if ($stmt->execute(array(
                $id,$idazienda,$codiceconto,$descrizione,
                $livelli[0],
                '5' => $livelli[1]!='' ? $livelli[1] : null,
                '6' => $livelli[2]!='' ? $livelli[2] : null,
                '7' => $livelli[3]!='' ? $livelli[3] : null,
                $nome,$categoria,$new,$annoinserimento,$meseinserimento))) {

                $ret['inserimenti_andati_a_buon_fine']++;

                setNotificheCRUD("admWeb","SUCCESS","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));

            } else {

                $ret['inserimenti_non_andati_a_buon_fine']++;
                setNotificheCRUD("admWeb","ERROR","aggiornapianoconti.php $idazienda, $anno, $mese ".$nomefile,json_encode($data));

            }

            //for ($c=0; $c < $num; $c++) {
            //    echo $data[$c] . "<br />\n";
            //}
        }
        fclose($handle);

        $query2="UPDATE pcs_caricamento_dati SET pianoconti_aggiornato='si' WHERE id_azienda=? AND anno=? AND mese=?";
        $stmt2=$dbh->prepare($query2);
        $stmt2->execute(array($idazienda,$anno,$mese));

        $ret['result']=true;
        $ret['nuovoconto']=$nuovoconto;
        $ret['msg'].=$ret['righetotali']." righe analizzate, ".$ret['nuoviconti']." nuovi conti trovati!";
        procedurafinale($idazienda);
        echo json_encode($ret);
        exit();
    } else {
        $ret['result']=false;
        $ret['error']="Errore sul file";
        echo json_encode($ret);
        exit();
    }
}
//----------------------- (f) BUFFETTI DALY
function procedurafinale($idazienda) {
    global $dbh;

    $queryDel="SELECT * FROM pcs_piano_conti WHERE id_azienda=? AND livello2 is null"; //prendo tutti i macroconti
    $stmtDel=$dbh->prepare($queryDel);
    $stmtDel->execute(array($idazienda));

    //ora ciclo e faccio update per tutti i sottoconti mantenendo la stessa categoria del padre
    while($row = $stmtDel->fetch(PDO::FETCH_ASSOC)) {
        $query1="UPDATE pcs_piano_conti SET categoria=? WHERE id_azienda=? and livello1=?";
        $stmt1=$dbh->prepare($query1);
        $stmt1->execute(array($row['categoria'],$idazienda,$row['livello1']));
    }
    return;
}
