<?php
include("config.php");

$codicevisita=$_GET['codicevisita'];

$debug=0;
if ($_GET['debug']=='VIACOLDEBUG') {
    $debug=1;
}

if ($codicevisita=='') {
    exit;
}

$checkbox[1]='n';
$checkbox[0]='q';

$tbvisite=$GLOBAL_tb['visite'];
$tbsedi=$GLOBAL_tb['sedi_clienti'];
$tbclienti=$GLOBAL_tb['clienti'];
$tbtipiservizio=$GLOBAL_tb['tipiservizio'];

$query="SELECT *,s.email as email_sede, s.persona_di_riferimento as persona_di_riferimento_sede,s.telefono as telefono_sede ,c.email as email_cliente, c.persona_di_riferimento as persona_di_riferimento_cliente,c.telefono as telefono_cliente FROM $tbvisite v JOIN $tbsedi s ON v.id_sede=s.id JOIN $tbclienti c ON c.id=s.id_cliente WHERE codice_visita=?";

$stmt=$dbh->prepare($query);
$stmt->execute(array($codicevisita));
$VISITA=$stmt->fetch(PDO::FETCH_ASSOC);

$notecliente=$VISITA['azioni_correttive'];

if ($VISITA['stato_visita']!='conclusa') {
    exit;
}

//se è in stato conclusa e il nr_certificato non c'è allora lo genero
if ($VISITA['nr_certificato']==0) {

    $query="SELECT max(nr_certificato) as nr_certificato FROM $tbvisite WHERE 1";
    $stmt=$dbh->query($query);
    $row=$stmt->fetch(PDO::FETCH_ASSOC);
    $VISITA['nr_certificato']=$row['nr_certificato']+1;

    $query="UPDATE $tbvisite SET nr_certificato=? WHERE codice_visita=?";
    $stmt=$dbh->prepare($query);
    $stmt->execute(array($VISITA['nr_certificato'],$codicevisita));
}

//print_r($row);

$nomecliente=$VISITA['nome_o_ragione_sociale']." - SEDE: ".$VISITA['sede'];
$indirizzo=$VISITA['indirizzo']." ".$VISITA['CAP']." ".$VISITA['citta']." (".$VISITA['provincia'].")";
$contatti="Tel: ".$VISITA['telefono_sede']." Email: ".$VISITA['email_sede'];
$dataCertificato=$VISITA['data_fine_visita'];
$NumCertificato=$VISITA['nr_certificato'];
$firmacliente=$VISITA['firma_cliente'];

$schedamonitoraggio=$codicevisita;
$datafinevisita=$VISITA['data_fine_visita'];

$nomefirmacliente=$VISITA['nome_cliente_firma'];

$tecnico=getUtente($VISITA['id_dipendente']);
$nometecnico=$tecnico['Nome']." ".$tecnico['Cognome'];

$immaginefirma=str_replace("data:image/png;base64,","",$firmacliente);
//$immaginefirma=str_replace("data:image/jpeg;base64,","",$firmacliente);

$imgdata=base64_decode($immaginefirma);

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Studio Web 19');
$pdf->SetTitle('Studio Web 19  Certificati Clienti');
$pdf->SetSubject('Studio Web 19  Certificati Clienti');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(1, 5, 1, true);

//margin bottom a 0
$pdf->SetAutoPageBreak(TRUE, 0);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------


$pdf->setFontSubsetting(false);


// set font
$pdf->SetFont('times', '', 14);

//stampa certificato

// add a page
//$pdf->AddPage();
$pdf->AddPage('L', 'A4');

// set JPEG quality
$pdf->setJPEGQuality(75);

// Image method signature:
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)

//$pdf->Image('images/image_demo.jpg', $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, $fitbox, false, false);

// set cell padding
$pdf->setCellPaddings(1, 1, 1, 1);

// set cell margins
$pdf->setCellMargins(1, 1, 1, 1);

// set color for background
$pdf->SetFillColor(255, 255, 255);

// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)


$var=-13;
$varx=-5;

// Image example with resizing
$pdf->Image('studioweb19certificatologo.jpg', 10, 20+$var, 85, 40, 'JPG', 'http://www.tcpdf.org', '', false, 150, '', false, false, 0, false, false, false);

$border=0;
$pdf->MultiCell(150, 15, 'Spettabile '.$nomecliente, $border, 'L', 1, 1, 98, 20+$var, true);
$pdf->MultiCell(150, 15, $indirizzo, $border, 'L', 1, 1, 98, 35+$var, true);
$pdf->MultiCell(150, 15, $contatti, $border, 'L', 1, 1, 98, 50+$var, true);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// -- FINE INTESTAZIONE
$border=0;
$pdf->MultiCell(80, 8, 'Bologna, '.$dataCertificato, $border, 'L', 1, 0, 10, 70+$var, true);

// set font bold
$pdf->SetFont('times', 'B', 14);

$pdf->MultiCell(110, 8, "CERTIFICATO D'INTERVENTO N. ".$NumCertificato, $border, 'L', 1, 1, 98, 70+$var, true);

//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
//$pdf->Cell(15, 5, 'q n',$border);

//------------------------------------------------------------------------------------------------------
// Aree e Normativa
//------------------------------------------------------------------------------------------------------

$border=0;
$x=15;

$normativa['c1a']="Aree Industriali";
$normativa['c2a']="Aree condominiali";
$normativa['c3a']="Comm. pubblici";
$normativa['c4a']="D.lgs 81/08";
$normativa['c5a']="Reg.C.E. 852/04";

$pdf->SetFont('zapfdingbats', '', 12);
$pdf->MultiCell(6, 5, $checkbox[$VISITA['c1a']], $border, 'L', 1, 1, $x+$varx, 80+$var, true);
$pdf->SetFont('times', '', 12);
$pdf->MultiCell(45, 5, $normativa['c1a'], $border, 'L', 1, 1, $x+6+$varx, 80+$var, true);

$pdf->SetFont('zapfdingbats', '', 12);
$pdf->MultiCell(6, 5, $checkbox[$VISITA['c2a']], $border, 'L', 1, 1, $x+$varx, 90+$var, true);
$pdf->SetFont('times', '', 12);
$pdf->MultiCell(45, 5, $normativa['c2a'], $border, 'L', 1, 1, $x+6+$varx, 90+$var, true);

$pdf->SetFont('zapfdingbats', '', 12);
$pdf->MultiCell(6, 5, $checkbox[$VISITA['c3a']], $border, 'L', 1, 1, $x+$varx, 100+$var, true);
$pdf->SetFont('times', '', 12);
$pdf->MultiCell(45, 5, $normativa['c3a'], $border, 'L', 1, 1, $x+6+$varx, 100+$var, true);

$pdf->SetFont('zapfdingbats', '', 12);
$pdf->MultiCell(6, 5, $checkbox[$VISITA['c4a']], $border, 'L', 1, 1, $x+$varx, 115+$var, true);
$pdf->SetFont('times', 'B', 12);
$pdf->MultiCell(45, 5, $normativa['c4a'], $border, 'L', 1, 1, $x+6+$varx, 115+$var, true);

$pdf->SetFont('zapfdingbats', '', 12);
$pdf->MultiCell(6, 5, $checkbox[$VISITA['c5a']], $border, 'L', 1, 1, $x+$varx, 125+$var, true);
$pdf->SetFont('times', 'B', 12);
$pdf->MultiCell(45, 5, $normativa['c5a'], $border, 'L', 1, 1, $x+6+$varx, 125+$var, true);


//------------------------------------------------------------------------------------------------------
// Servizio
//------------------------------------------------------------------------------------------------------

$border=0;

$servizio1['c1b']="Disinfestazione";
$servizio1['c2b']="Derattizzazione";
$servizio1['c3b']="Disinfezione";
$servizio1['c4b']="Tratt. Fitoiatrici";
$servizio1['c5b']="Antipiccioni";
$servizio1['c6b']="Diserbo";

$y=80+$var;
$x=60;
foreach ($servizio1 as $key=>$value ) {
    $pdf->SetFont('zapfdingbats', '', 14);
    $pdf->MultiCell(8, 5, $checkbox[$VISITA[$key]], $border, 'L', 1, 1, $x+$varx, $y, true);
    $pdf->SetFont('times', '', 14);
    $pdf->MultiCell(45, 5, $value, $border, 'L', 1, 1, $x+$varx+8, $y, true);
    $y=$y+10;
}

$servizio2['c7b']="Monitoraggio";
$servizio2['c8b']="Igienizzazione";
$servizio2['c9b']="Altro";
$servizio2['c10b']="Pacch. Igiene e Salubrità";
$servizio2['c11b']="Area Interna";
$servizio2['c12b']="Area Esterna";

$y=80+$var;
$x=105;
foreach ($servizio2 as $key=>$value ) {
    $pdf->SetFont('zapfdingbats', '', 14);
    $pdf->MultiCell(8, 5, $checkbox[$VISITA[$key]], $border, 'L', 1, 1, $x+$varx, $y, true);
    $pdf->SetFont('times', '', 14);
    $pdf->MultiCell(65, 5, $value, $border, 'L', 1, 1, $x+$varx+8, $y, true);
    $y=$y+10;
}

//------------------------------------------------------------------------------------------------------
// Target
//------------------------------------------------------------------------------------------------------

$Target['c13b']="Roditori";
$Target['c14b']="Ins. Striscianti";
$Target['c15b']="Ins. Volanti";
$Target['c16b']="Cimex";
$Target['c17b']="Tarme";
$Target['c18b']="Altro:";

$y=80+$var;
$x=165;
foreach ($Target as $key=>$value ) {
    $pdf->SetFont('zapfdingbats', '', 14);
    $pdf->MultiCell(8, 5, $checkbox[$VISITA[$key]], $border, 'L', 1, 1, $x+$varx, $y, true);
    $pdf->SetFont('times', '', 14);
    $pdf->MultiCell(45, 5, $value, $border, 'L', 1, 1, $x+$varx+8, $y, true);
    $y=$y+8;
}
$pdf->SetFont('times', 'I', 14);
$pdf->MultiCell(45, 5, $VISITA['c18btesto'], $border, 'L', 1, 1, $x+$varx+8, $y-3, true);

//------------------------------------------------------------------------------------------------------
// L/A
//------------------------------------------------------------------------------------------------------
$LA['c1c']="Antilarvale";
$LA['c2c']="Adulticida";

$y=100+$var;
$x=210;
foreach ($LA as $key=>$value ) {
    $pdf->SetFont('zapfdingbats', '', 14);
    $pdf->MultiCell(8, 5, $checkbox[$VISITA[$key]], $border, 'L', 1, 1, $x+$varx, $y, true);
    $pdf->SetFont('times', '', 14);
    $pdf->MultiCell(35, 5, $value, $border, 'L', 1, 1, $x+$varx+8, $y, true);
    $y=$y+8;
}

//------------------------------------------------------------------------------------------------------
// Tipo di Insetto
//------------------------------------------------------------------------------------------------------
$TipoInsetto['c1d']="Blattoidei";
$TipoInsetto['c2d']="Emitteri";
$TipoInsetto['c3d']="Dittere";
$TipoInsetto['c4d']="Muscidi";
$TipoInsetto['c5d']="Imenotteri";
$TipoInsetto['c6d']="Altro:";

$y=80+$var;
$x=250;
foreach ($TipoInsetto as $key=>$value ) {
    $pdf->SetFont('zapfdingbats', '', 14);
    $pdf->MultiCell(8, 5, $checkbox[$VISITA[$key]], $border, 'L', 1, 1, $x+$varx, $y, true);
    $pdf->SetFont('times', '', 14);
    $pdf->MultiCell(65, 5, $value, $border, 'L', 1, 1, $x+$varx+8, $y, true);
    $y=$y+8;
}
$pdf->SetFont('times', 'I', 14);
$pdf->MultiCell(65, 5, $VISITA['c6dtesto'], $border, 'L', 1, 1, $x+$varx+8, $y-3, true);



//------------------------------------------------------------------------------------------------------
// Coformulati
//------------------------------------------------------------------------------------------------------

$Coformulati['c1e']="Rodenticida";
$Coformulati['c2e']="Organofosforici";
$Coformulati['c3e']="Piretroidi";
$Coformulati['c4e']="Carbammati";
$Coformulati['c6e']="Altro:";

$y=140+$var;
$x=30;
$pdf->SetFont('zapfdingbats', '', 13);
$pdf->MultiCell(8, 5, $checkbox[$VISITA['c1e']], $border, 'L', 1, 1, $x+$varx, $y, true);
$pdf->SetFont('times', '', 13);
$pdf->MultiCell(45, 5, $Coformulati['c1e'], $border, 'L', 1, 1, $x+$varx+8, $y, true);
$pdf->SetFont('zapfdingbats', '', 13);
$pdf->MultiCell(8, 5, $checkbox[$VISITA['c2e']], $border, 'L', 1, 1, $x+$varx, $y+7, true);
$pdf->SetFont('times', '', 13);
$pdf->MultiCell(45, 5, $Coformulati['c2e'], $border, 'L', 1, 1, $x+$varx+8, $y+7, true);

$y=140+$var;
$x=80;
$pdf->SetFont('zapfdingbats', '', 13);
$pdf->MultiCell(8, 5, $checkbox[$VISITA['c3e']], $border, 'L', 1, 1, $x+$varx, $y, true);
$pdf->SetFont('times', '', 13);
$pdf->MultiCell(45, 5, $Coformulati['c3e'], $border, 'L', 1, 1, $x+$varx+8, $y, true);
$pdf->SetFont('zapfdingbats', '', 13);
$pdf->MultiCell(8, 5, $checkbox[$VISITA['c6e']], $border, 'L', 1, 1, $x+$varx, $y+7, true);
$pdf->SetFont('times', '', 13);
$pdf->MultiCell(45, 5, $Coformulati['c6e'], $border, 'L', 1, 1, $x+$varx+8, $y+7, true);

$y=140+$var;
$x=120;
$pdf->SetFont('zapfdingbats', '', 13);
$pdf->MultiCell(8, 5, $checkbox[$VISITA['c4e']], $border, 'L', 1, 1, $x+$varx, $y, true);
$pdf->SetFont('times', '', 13);
$pdf->MultiCell(45, 5, $Coformulati['c4e'], $border, 'L', 1, 1, $x+$varx+8, $y, true);

$y=140+$var;
$x=158;
$pdf->SetFont('times', 'I', 13);
$pdf->MultiCell(100, 5, "Principio Attivo: ".$VISITA['c7etesto'], $border, 'L', 1, 1, $x+$varx+8, $y, true);

//------------------------------------------------------------------------------------------------------
// Scheda di monitoraggio
//------------------------------------------------------------------------------------------------------
$y=158+$var;
$x=20;
$pdf->SetFont('times', '', 14);
$pdf->MultiCell(105, 5, "Scheda di monitoraggio Nr. ".$VISITA['nr_certificato'], $border, 'L', 1, 1, $x+$varx, $y, true);
$pdf->MultiCell(65, 5, "Effettuata in data: ".$datafinevisita, $border, 'L', 1, 1, $x+$varx+120, $y, true);
$pdf->MultiCell(220, 5, "Note: ".$notecliente, $border, 'L', 1, 1, $x+$varx, $y+8, true);

//------------------------------------------------------------------------------------------------------
// AVVERTENZA
//------------------------------------------------------------------------------------------------------
$y=175+$var;
$x=20;
$pdf->SetFont('times', '', 14);
$pdf->MultiCell(265, 5, "IL CLIENTE E' STATO ISTRUITO SULLE MODALITA' DI PULIZIA, AEREAZIONE E NORME PRECAUZIONALI", $border, 'L', 1, 1, $x+$varx, $y, true);


//------------------------------------------------------------------------------------------------------
// FIRME
//------------------------------------------------------------------------------------------------------

// The '@' character is used to indicate that follows an image data stream and not an image file name

$y=185+$var;
$x=20;
$pdf->SetFont('times', '', 14);
$pdf->MultiCell(95, 5, "Firma Tecnico: ".$nometecnico, $border, 'L', 1, 1, $x+$varx, $y, true);

$pdf->MultiCell(95, 5, "Per il cliente: ".$nomefirmacliente, $border, 'L', 1, 1, $x+$varx+130, $y, true);

$pdf->Image('@'.$imgdata, $x+$varx+180, $y, 50, 30);






//------------------------------------------------------------------------------------------------------
// FINE PAGINA CERTIFICATO
//------------------------------------------------------------------------------------------------------



//------------------------------------------------------------------------------------------------------
// INIZIO CON LE PAGINE DELLE SCHEDE DI MONITORAGGIO
//------------------------------------------------------------------------------------------------------

//Una scheda per ogni tipo di servizio

$codice['Nuova']="N.";
$codice['Buono']="B.";
$codice['Mancante']="M.";
$codice['Ricollocata per rottura']="R.R.";
$codice['Ricollocata per mancanza']="R.M.";
$codice['Ricollocata per richiesta del cliente']="R.C.";
$codice['Inaccessibile']="I.";
$codice['Non Visitata']="N.V.";

$codice1['integra']="Integra";
$codice1['rovinata']="Rovinata";
$codice1['presenza target']="Presenza Target";

$query="SELECT * FROM pcs_tipi_servizio ";
$stmt=$dbh->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $tiposervizio[$row['id']]=$row['servizio'];
    $descservizio[$row['id']]=$row['descrizione_servizio'];
}

//controlliamo quante trappole per ciascun servizio, se non ci sono postazioni, allora nessuna scheda per quel servizio
$query="SELECT * FROM pcs_ispezioni JOIN pcs_postazioni ON pcs_ispezioni.codice_postazione=pcs_postazioni.codice_postazione
WHERE pcs_ispezioni.codice_visita=? order by id_servizio,CAST(nome as SIGNED INTEGER) ASC ";

$stmt=$dbh->prepare($query);
$stmt->execute(array($codicevisita));
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $schedaservizio[$row[id_servizio]]=$descservizio[$row[id_servizio]];
    $scheda[$row[id_servizio]][]=$row;
}

//Stampo le schede di servizio, una per ogni servizio in cui sono presenti postazioni ispezionate
//Inizio con scheda monitoraggio roditori e insetti striscianti (A1/A2/B)

$ylimit=266;

if ($schedaservizio['1']) {
    $pdf->AddPage('P', 'A4');

    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 8, "Scheda Derattizzazione Interna N. ".$VISITA['nr_certificato'], $border, 'C', 1, 1, 5, 10, true);


    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 15, 'Cliente: '.$nomecliente." ".$indirizzo, $border, 'L', 1, 1, 5, 23, true);


    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $pdf->MultiCell(195, 8, "Scheda Monitoraggio Roditori", $border, 'C', 1, 1, 5, 50, true);

    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $varx=0;
    $vary=10;

    $y=55+$vary;
    $pdf->MultiCell(40, 8, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
    $pdf->MultiCell(15, 8, "Stato", $border, 'L', 1, 1, 45+$varx, $y, true);
    $pdf->MultiCell(50, 8, "Stato esca roditori", $border, 'L', 1, 1, 60+$varx, $y, true);
    $pdf->MultiCell(75, 8, "Collocato Adescante ", $border, 'L', 1, 1, 110+$varx, $y, true);


    $pdf->SetFont('times', '', 14);

    //Derattizzazione Interna
        $ct=0;
        $y+=8;
        foreach ($scheda[1] as $isp):
            $y=$y+8;
            if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
            $pdf->MultiCell(40, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
            $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 45+$varx, $y, true);
            $pdf->MultiCell(50, 8, $isp['stato_esca_roditori'], $border, 'L', 1, 1, 60+$varx, $y, true);
            $pdf->MultiCell(75, 8, $isp['collocato_adescante_roditori'], $border, 'L', 1, 1, 110+$varx, $y, true);
            $ct++;

        endforeach;
            //legenda
            $y+=24;
            if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
            $ylegenda=$y;
            $pdf->SetFont('times', 'B', 12);
            $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
            $y+=8;

            $pdf->SetFont('times', 'I', 12);
            foreach ($codice as $key=>$value) {
                $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
                $y+=8;
            }
}

if ($schedaservizio['2']) {
    $pdf->AddPage('P', 'A4');

    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 8, "Scheda Derattizzazione Esterna N. ".$VISITA['nr_certificato'], $border, 'C', 1, 1, 5, 10, true);


    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 15, 'Cliente: '.$nomecliente." ".$indirizzo, $border, 'L', 1, 1, 5, 23, true);


    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $pdf->MultiCell(195, 8, "Scheda Monitoraggio Roditori", $border, 'C', 1, 1, 5, 50, true);

    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $varx=0;
    $vary=10;

    $y=55+$vary;
    $pdf->MultiCell(40, 8, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
    $pdf->MultiCell(15, 8, "Stato", $border, 'L', 1, 1, 45+$varx, $y, true);
    $pdf->MultiCell(50, 8, "Stato esca roditori", $border, 'L', 1, 1, 60+$varx, $y, true);
    $pdf->MultiCell(75, 8, "Collocato Adescante ", $border, 'L', 1, 1, 110+$varx, $y, true);


    $pdf->SetFont('times', '', 14);

    //Derattizzazione Esterna
    $ct=0;
    $y+=8;
    foreach ($scheda[2] as $isp):
        $y=$y+8;
        if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
        $pdf->MultiCell(40, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
        $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 45+$varx, $y, true);
        $pdf->MultiCell(50, 8, $isp['stato_esca_roditori'], $border, 'L', 1, 1, 60+$varx, $y, true);
        $pdf->MultiCell(75, 8, $isp['collocato_adescante_roditori'], $border, 'L', 1, 1, 110+$varx, $y, true);
        $ct++;

    endforeach;
    //legenda
    $y+=24;
    if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
    $ylegenda=$y;
    $pdf->SetFont('times', 'B', 12);
    $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
    $y+=8;

    $pdf->SetFont('times', 'I', 12);
    foreach ($codice as $key=>$value) {
        $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
    }
}

if ($schedaservizio['3']) {
    $pdf->AddPage('P', 'A4');

    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 8, "Scheda Monitoraggio Insetti striscianti/Roditori N. ".$VISITA['nr_certificato'], $border, 'C', 1, 1, 5, 10, true);


    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 15, 'Cliente: '.$nomecliente." ".$indirizzo, $border, 'L', 1, 1, 5, 23, true);


    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $pdf->MultiCell(195, 8, "Scheda Monitoraggio Roditori", $border, 'C', 1, 1, 5, 50, true);

    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $varx=0;
    $vary=10;

    $y=55+$vary;
    $pdf->MultiCell(40, 8, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
    $pdf->MultiCell(15, 8, "Stato", $border, 'L', 1, 1, 45+$varx, $y, true);
    $pdf->MultiCell(50, 8, "Stato esca roditori", $border, 'L', 1, 1, 60+$varx, $y, true);
    $pdf->MultiCell(75, 8, "Collocato Adescante ", $border, 'L', 1, 1, 110+$varx, $y, true);


    $pdf->SetFont('times', '', 14);

    //Roditori/Blattoidei
    if (count($scheda[3])>0) :

        $ct=0;
        $y+=8;
        foreach ($scheda[3] as $isp):
            $y=$y+8;
            if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
            $pdf->MultiCell(40, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
            $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 45+$varx, $y, true);
            $pdf->MultiCell(50, 8, $isp['stato_esca_roditori'], $border, 'L', 1, 1, 60+$varx, $y, true);
            $pdf->MultiCell(75, 8, $isp['collocato_adescante_roditori'], $border, 'L', 1, 1, 110+$varx, $y, true);
            $ct++;

        endforeach;

        //legenda
        $y+=24;
        if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
        $ylegenda=$y;
        $pdf->SetFont('times', 'B', 12);
        $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;

        $pdf->SetFont('times', 'I', 12);
        foreach ($codice as $key=>$value) {
            $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
            $y+=8;
        }

        //Blatte

        $pdf->AddPage('P', 'A4');

        $pdf->SetFont('times', 'B', 16);
        $pdf->MultiCell(190, 8, "Scheda Monitoraggio Insetti striscianti/Roditori N. ".$VISITA['nr_certificato'], $border, 'C', 1, 1, 5, 10, true);


        $pdf->SetFont('times', 'B', 16);
        $pdf->MultiCell(190, 15, 'Cliente: '.$nomecliente." ".$indirizzo, $border, 'L', 1, 1, 5, 23, true);


        $pdf->SetFont('times', 'B', 14);
        $border=1;
        $pdf->MultiCell(195, 8, "Scheda Monitoraggio Blatte", $border, 'C', 1, 1, 5, 50, true);

        $pdf->SetFont('times', 'B', 14);
        $border=1;
        $varx=0;
        $vary=10;

        $y=55+$vary;

        $pdf->SetFont('times', 'B', 14);
        $border=1;

        $y+=10;
        $pdf->MultiCell(34, 16, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
        $pdf->MultiCell(15, 16, "Stato", $border, 'L', 1, 1, 39+$varx, $y, true);
        $pdf->MultiCell(40, 16, "Stato piastra ", $border, 'L', 1, 1, 54+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.O. ", $border, 'L', 1, 1, 94+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.O. ", $border, 'L', 1, 1, 107+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.G. ", $border, 'L', 1, 1, 120+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.G. ", $border, 'L', 1, 1, 133+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.L. ", $border, 'L', 1, 1, 146+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.L. ", $border, 'L', 1, 1, 159+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.A. ", $border, 'L', 1, 1, 172+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.A. ", $border, 'L', 1, 1, 185+$varx, $y, true);

        $ct=0;
        $y+=16;
        $pdf->SetFont('times', '', 14);
        foreach ($scheda[3] as $isp):
            $y=$y+8;
            if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
            $pdf->MultiCell(34, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
            $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 39+$varx, $y, true);
            $pdf->MultiCell(40, 8, $codice1[$isp['stato_piastra_collante_insetti_striscianti']], $border, 'L', 1, 1, 54+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_orientalis'], $border, 'L', 1, 1, 94+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_orientalis'], $border, 'L', 1, 1, 107+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_germanica'], $border, 'L', 1, 1, 120+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_germanica'], $border, 'L', 1, 1, 133+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_supella_longipalpa'], $border, 'L', 1, 1, 146+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_supella_longipalpa'], $border, 'L', 1, 1, 159+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_periplaneta_americana'], $border, 'L', 1, 1, 172+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_periplaneta_americana'], $border, 'L', 1, 1, 185+$varx, $y, true);
            $ct++;

        endforeach;

        //legenda
        $y+=24;
        if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
        $ylegenda=$y;
        $pdf->SetFont('times', 'B', 12);
        $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;

        $pdf->SetFont('times', 'I', 12);
        foreach ($codice as $key=>$value) {
            $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
            $y+=8;
        }

        $y=$ylegenda;
        $oldvarx=$varx;
        $varx=100;
        //legenda
        $pdf->SetFont('times', 'I', 12);
        $pdf->MultiCell(90, 8, "O.O. Ooteche Blatta orientalis", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.O. Adulti Blatta orientalis", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "O.G. Ooteche Blattella germanica", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.O. Adulti Blattella germanica", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "O.L. Ooteche Supella Longipalpa", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.L. Adulti Supella Longipalpa", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "O.A. Ooteche Periplaneta Americana", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.A. Adulti Periplaneta Americana", $border, 'L', 1, 1, 5+$varx, $y, true);


        $varx=$oldvarx;

    endif;
}


/*
if ($schedaservizio['1'] or $schedaservizio['2'] or $schedaservizio['3']) {
    $pdf->AddPage('P', 'A4');

    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 8, "Scheda Monitoraggio Insetti striscianti/Roditori N. ".$VISITA['nr_certificato'], $border, 'C', 1, 1, 5, 10, true);


    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 15, 'Cliente: '.$nomecliente." ".$indirizzo, $border, 'L', 1, 1, 5, 23, true);


    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $pdf->MultiCell(195, 8, "Scheda Monitoraggio Roditori", $border, 'C', 1, 1, 5, 50, true);

    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $varx=0;
    $vary=10;

    $y=55+$vary;
    $pdf->MultiCell(40, 8, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
    $pdf->MultiCell(15, 8, "Stato", $border, 'L', 1, 1, 45+$varx, $y, true);
    $pdf->MultiCell(50, 8, "Stato esca roditori", $border, 'L', 1, 1, 60+$varx, $y, true);
    $pdf->MultiCell(75, 8, "Collocato Adescante ", $border, 'L', 1, 1, 110+$varx, $y, true);


    $pdf->SetFont('times', '', 14);

    //Derattizzazione Interna
    if (count($scheda[1])>0) :

        $ct=0;
        $y+=8;
        foreach ($scheda[1] as $isp):
            $y=$y+8;
            if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
            $pdf->MultiCell(40, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
            $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 45+$varx, $y, true);
            $pdf->MultiCell(50, 8, $isp['stato_esca_roditori'], $border, 'L', 1, 1, 60+$varx, $y, true);
            $pdf->MultiCell(75, 8, $isp['collocato_adescante_roditori'], $border, 'L', 1, 1, 110+$varx, $y, true);
            $ct++;

        endforeach;

        if (count($scheda[1])>0) {

        } elseif (count($scheda[2])>0) {

        } else {
            //legenda
            $y+=24;
            if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
            $ylegenda=$y;
            $pdf->SetFont('times', 'B', 12);
            $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
            $y+=8;

            $pdf->SetFont('times', 'I', 12);
            foreach ($codice as $key=>$value) {
                $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
                $y+=8;
            }
        }

    endif;


    //Derattizzazione Esterna
    if (count($scheda[2])>0) :

        $ct=0;
        //$y+=8;
        foreach ($scheda[2] as $isp):
            $y=$y+8;
            if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
            $pdf->MultiCell(40, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
            $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 45+$varx, $y, true);
            $pdf->MultiCell(50, 8, $isp['stato_esca_roditori'], $border, 'L', 1, 1, 60+$varx, $y, true);
            $pdf->MultiCell(75, 8, $isp['collocato_adescante_roditori'], $border, 'L', 1, 1, 110+$varx, $y, true);
            $ct++;

        endforeach;

        if (count($scheda[3])>0) {

        } else {
            //legenda
            $y+=24;
            if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
            $ylegenda=$y;
            $pdf->SetFont('times', 'B', 12);
            $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
            $y+=8;

            $pdf->SetFont('times', 'I', 12);
            foreach ($codice as $key=>$value) {
                $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
                $y+=8;
            }
        }

    endif;

    //Roditori/Blattoidei
    if (count($scheda[3])>0) :

        $ct=0;
        $y+=8;
        foreach ($scheda[3] as $isp):
            $y=$y+8;
            if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
            $pdf->MultiCell(40, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
            $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 45+$varx, $y, true);
            $pdf->MultiCell(50, 8, $isp['stato_esca_roditori'], $border, 'L', 1, 1, 60+$varx, $y, true);
            $pdf->MultiCell(75, 8, $isp['collocato_adescante_roditori'], $border, 'L', 1, 1, 110+$varx, $y, true);
            $ct++;

        endforeach;


        $pdf->SetFont('times', 'B', 14);
        $border=1;
        $y+=30;
        if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
        $pdf->MultiCell(195, 8, "Scheda Monitoraggio Blatte", $border, 'C', 1, 1, 5, $y, true);

        $y+=10;
        $pdf->MultiCell(34, 16, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
        $pdf->MultiCell(15, 16, "Stato", $border, 'L', 1, 1, 39+$varx, $y, true);
        $pdf->MultiCell(40, 16, "Stato piastra ", $border, 'L', 1, 1, 54+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.O. ", $border, 'L', 1, 1, 94+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.O. ", $border, 'L', 1, 1, 107+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.G. ", $border, 'L', 1, 1, 120+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.G. ", $border, 'L', 1, 1, 133+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.L. ", $border, 'L', 1, 1, 146+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.L. ", $border, 'L', 1, 1, 159+$varx, $y, true);
        $pdf->MultiCell(13, 16, "O.A. ", $border, 'L', 1, 1, 172+$varx, $y, true);
        $pdf->MultiCell(13, 16, "A.A. ", $border, 'L', 1, 1, 185+$varx, $y, true);

        $ct=0;
        $y+=16;
        $pdf->SetFont('times', '', 14);
        foreach ($scheda[3] as $isp):
            $y=$y+8;
            if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
            $pdf->MultiCell(34, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
            $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 39+$varx, $y, true);
            $pdf->MultiCell(40, 8, $codice1[$isp['stato_piastra_collante_insetti_striscianti']], $border, 'L', 1, 1, 54+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_orientalis'], $border, 'L', 1, 1, 94+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_orientalis'], $border, 'L', 1, 1, 107+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_germanica'], $border, 'L', 1, 1, 120+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_germanica'], $border, 'L', 1, 1, 133+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_supella_longipalpa'], $border, 'L', 1, 1, 146+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_supella_longipalpa'], $border, 'L', 1, 1, 159+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['ooteche_periplaneta_americana'], $border, 'L', 1, 1, 172+$varx, $y, true);
            $pdf->MultiCell(13, 8, $isp['adulti_periplaneta_americana'], $border, 'L', 1, 1, 185+$varx, $y, true);
            $ct++;

        endforeach;

        //legenda
        $y+=24;
        if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
        $ylegenda=$y;
        $pdf->SetFont('times', 'B', 12);
        $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;

        $pdf->SetFont('times', 'I', 12);
        foreach ($codice as $key=>$value) {
            $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
            $y+=8;
        }

        $y=$ylegenda;
        $oldvarx=$varx;
        $varx=100;
        //legenda
        $pdf->SetFont('times', 'I', 12);
        $pdf->MultiCell(90, 8, "O.O. Ooteche Blatta orientalis", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.O. Adulti Blatta orientalis", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "O.G. Ooteche Blattella germanica", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.O. Adulti Blattella germanica", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "O.L. Ooteche Supella Longipalpa", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.L. Adulti Supella Longipalpa", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "O.A. Ooteche Periplaneta Americana", $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
        $pdf->MultiCell(90, 8, "A.A. Adulti Periplaneta Americana", $border, 'L', 1, 1, 5+$varx, $y, true);


        $varx=$oldvarx;

    endif;
}
*/

if ($schedaservizio['4']) {
    $pdf->AddPage('P', 'A4');
    $border=0;

    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 8, "Scheda Monitoraggio Insetti Volanti N. ".$VISITA['nr_certificato'], $border, 'C', 1, 1, 5, 10, true);


    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 15, 'Cliente: '.$nomecliente." ".$indirizzo, $border, 'L', 1, 1, 5, 23, true);

    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $varx=0;
    $vary=10;

    $y=30+$vary;
    $pdf->MultiCell(20, 8, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
    $pdf->MultiCell(15, 8, "Stato", $border, 'L', 1, 1, 25+$varx, $y, true);
    $pdf->MultiCell(30, 8, "Muscidi", $border, 'L', 1, 1, 40+$varx, $y, true);
    $pdf->MultiCell(30, 8, "Vespidi", $border, 'L', 1, 1, 70+$varx, $y, true);
    $pdf->MultiCell(30, 8, "Calabronidi", $border, 'L', 1, 1, 100+$varx, $y, true);
    $pdf->MultiCell(30, 8, "Dittere", $border, 'L', 1, 1, 130+$varx, $y, true);
    $pdf->MultiCell(30, 8, "Altri", $border, 'L', 1, 1, 160+$varx, $y, true);


    $pdf->SetFont('times', '', 14);
    $y+=8;
    $ct=0;

    foreach ($scheda[4] as $isp):
        $y=$y+8;
        if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
        $pdf->MultiCell(20, 8, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
        $pdf->MultiCell(15, 8, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 25+$varx, $y, true);
        $pdf->MultiCell(30, 8, $isp['presenza_muscidi'], $border, 'L', 1, 1, 40+$varx, $y, true);
        $pdf->MultiCell(30, 8, $isp['presenza_imenotteri_vespidi'], $border, 'L', 1, 1, 70+$varx, $y, true);
        $pdf->MultiCell(30, 8, $isp['presenza_imenotteri_calabronidi'], $border, 'L', 1, 1, 100+$varx, $y, true);
        $pdf->MultiCell(30, 8, $isp['presenza_dittere'], $border, 'L', 1, 1, 130+$varx, $y, true);
        $pdf->MultiCell(30, 8, $isp['presenza_altri_tipi_insetti'], $border, 'L', 1, 1, 160+$varx, $y, true);
        $ct++;

    endforeach;

    //legenda
    $y+=16;
    if ($y>$ylimit-64) { $y=16; $pdf->AddPage('P', 'A4');}
    $pdf->SetFont('times', 'B', 12);
    $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
    $y+=8;

    $pdf->SetFont('times', 'I', 12);
    foreach ($codice as $key=>$value) {
        $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
    }

}


if ($schedaservizio['6']) {
    $pdf->AddPage('P', 'A4');
    $border=0;

    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 8, "Scheda Monitoraggio Lepidotteri N. ".$VISITA['nr_certificato'], $border, 'C', 1, 1, 5, 10, true);


    $pdf->SetFont('times', 'B', 16);
    $pdf->MultiCell(190, 15, 'Cliente: '.$nomecliente." ".$indirizzo, $border, 'L', 1, 1, 5, 23, true);

    $pdf->SetFont('times', 'B', 14);
    $border=1;
    $varx=0;
    $vary=10;

    $y=30+$vary;
    $pdf->MultiCell(20, 16, "Stazione ", $border, 'L', 1, 1, 5+$varx, $y, true);
    $pdf->MultiCell(15, 16, "Stato", $border, 'L', 1, 1, 25+$varx, $y, true);
    $pdf->MultiCell(45, 16, "Presenza Target", $border, 'L', 1, 1, 40+$varx, $y, true);
    $pdf->MultiCell(105, 16, "Tipo di Target", $border, 'L', 1, 1, 85+$varx, $y, true);


    $pdf->SetFont('times', '', 14);
    $y+=16;
    $ct=0;

    foreach ($scheda[6] as $isp):
        $y=$y+8;
        if ($y>$ylimit) { $y=8; $pdf->AddPage('P', 'A4');}
        $pdf->MultiCell(20, 16, $isp['nome'], $border, 'L', 1, 1, 5+$varx, $y, true);
        $pdf->MultiCell(15, 16, $codice[$isp['stato_postazione']], $border, 'L', 1, 1, 25+$varx, $y, true);
        $pdf->MultiCell(45, 16, $isp['presenza_target_lepidotteri'], $border, 'L', 1, 1, 40+$varx, $y, true);
        $pdf->MultiCell(105, 16, $isp['tipo_target_lepidotteri'], $border, 'L', 1, 1, 85+$varx, $y, true);
        $ct++;

    endforeach;

    //legenda
    $y+=32;
    if ($y>$ylimit-64) { $y=8; $pdf->AddPage('P', 'A4');}
    $pdf->SetFont('times', 'B', 12);
    $pdf->MultiCell(90, 8, "Stato Postazione", $border, 'L', 1, 1, 5+$varx, $y, true);
    $y+=8;

    $pdf->SetFont('times', 'I', 12);
    foreach ($codice as $key=>$value) {
        $pdf->MultiCell(90, 8, $value.": ".$key, $border, 'L', 1, 1, 5+$varx, $y, true);
        $y+=8;
    }


}


// move pointer to last page
$pdf->lastPage();

// ---------------------------------------------------------

if ($debug) {
//Close and output PDF document

$pdf->Output('Certificato-'.$NumCertificato.'.pdf', 'I');

exit;
} else {
    $pdf->Output($_SERVER['DOCUMENT_ROOT'].$directoryfiles.DIRECTORY_SEPARATOR.'Certificati'.DIRECTORY_SEPARATOR.'Certificato-'.$NumCertificato.'.pdf', 'F');
    $urlfile="http://".$_SERVER['HTTP_HOST'].$directoryfiles."/Certificati/Certificato-".$NumCertificato.".pdf";
}



/* --------------------------------------------------------------------------------------------------------------------------------------------------- */
/* ---------------------------------------------------------------------- (i) EMAIL ------------------------------------------------------------------ */
/* --------------------------------------------------------------------------------------------------------------------------------------------------- */

//per sospendere invio email
//$VISITA['email_inviata']=1;

if ($VISITA['email_inviata']==1) {

} else {
    require 'class.phpmailer.php';


    $testo="Buongiorno, di seguito il link per scaricare il certificato relativo alla visita effettuata presso la vostra sede e conclusa in data ".$VISITA['data_fine_visita'];
    $testo.="<br/><br/><a href='".$urlfile."'>".$urlfile."</a>";
    $testo.="<br/><br/>Se non riesci a visualizzare il link precedente, copia e incolla questo link sul tuo browser:<br/><br/> ".$urlfile;
    $testo.="<br/><br/>Cordiali saluti<br/><br/>Studio Web 19 - Pest Control System";

    $mail = new PHPMailer;

    $mail->IsSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.sparkpostmail.com';                 // Specify main and backup server
    $mail->Port = 587;                                    // Set the SMTP port
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'SMTP_Injection';                  // SMTP username
    $mail->Password = '85b57a8623f3ae1ed5351f5b3289a122105ae5c3';           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

    $mail->From = 'info@studioweb19.it';
    $mail->FromName = 'Studio Web 19 - Pest Control System';
    $mail->addAddress('fabio.franci@gmail.com');               // Name is optional
    $mail->addAddress($VISITA['email_cliente']);               // Name is optional
    $mail->addAddress($VISITA['email_sede']);               // Name is optional

    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = 'Studio Web 19 Pest Control System - Certificato Intervento '.$VISITA['nr_certificato'];
    $mail->Body    = $testo;

    if ($mail->send()) {
        $query="UPDATE pcs_visite SET email_inviata=1 WHERE codice_visita=?";
        $stmt=$dbh->prepare($query);
        $stmt->execute(array($codicevisita));
        setNotificheCRUD("APP","SUCCESS","generacertificati.php","Mail inviata: $codicevisita");
    } else {
        setNotificheCRUD("APP","ERROR","generacertificati.php","Mail non inviata: $codicevisita");
    }
}


/* --------------------------------------------------------------------------------------------------------------------------------------------------- */
/* ---------------------------------------------------------------------- (f) EMAIL ------------------------------------------------------------------ */
/* --------------------------------------------------------------------------------------------------------------------------------------------------- */


//============================================================+
// END OF FILE
//============================================================+
