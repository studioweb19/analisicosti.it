<?php $page="generaqrcode";?>
<?php

include("config.php");

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Studio Web 19');
$pdf->SetTitle('StudioWeb19 QRCODE Trappole');
$pdf->SetSubject('StudioWeb19 QRCODE Trappole');

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

$precodice=$_REQUEST['precodice'];
$inizionumerazione=$_REQUEST['inizionumerazione'];
$numerocodici=$_REQUEST['numerocodici'];

if ($precodice=='') {
    $precodice=$prefissoqrcode;

}
if ($inizionumerazione>0) {
} else {
    $inizionumerazione=1001;
}
if ($numerocodici>0) {
} else {
    $numerocodici=81;
}
if ($_REQUEST['debug']) {
    echo "inizionumerazione:$inizionumerazione<br/>";
    echo "precodice:$precodice<br/>";
    echo "numerocodici:$numerocodici<br/>";
    exit;
}

/*--- Form generazione ----*/
if ($_REQUEST['stampa']) {

} else {
?>
<?php include("INC_10_HEADER.php");?>
<?php include("INC_15_SCRIPTS.php");?>
    <body>
    <!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->
    <?php include("INC_20_NAVBAR.php");?>
    <?php include("INC_50_CONTENT_$page.php");?>
    </body>
    </html>

	<?php
	exit;
}


/*--- fine Form generazione ----*/

/*--- Stampa Codici ----*/


for ($i=0;$i<$numerocodici;$i++) {
	$j=$i+$inizionumerazione;
	$arraycodici[$i]=$precodice.$j;
}

// set font
$pdf->SetFont('times', '', 10);

// set cell padding
$pdf->setCellPaddings(10, 1, 1, 1);

// set cell margins
$pdf->setCellMargins(0, 0, 0, 0);

// set color for background
$pdf->SetFillColor(255, 255, 127);

// new style
$style = array(
	'border' => 2,
	'padding' => 'auto',
	'fgcolor' => array(0,0,0),
	'bgcolor' => array(255,255,255)
);

/*
$qrcodedim=40;
$qrcodeperpagina=35;
$qrcodeperriga=5;
$spaziox=10;
$spazioy=10;
	//$pdf->write2DBarcode($codice,'QRCODE,H', ($i%5)*$qrcodedim+10, floor($k/5)*$qrcodedim+10, $qrcodedim-10, $qrcodedim-10, $style, 'N');
*/

$qrcodedim=28;
$qrcodeperpagina=27;
$qrcodeperriga=3;
$marginesinistro=10;
$marginealto=10;
$spaziotraetichette=10;
$paddingorizzontale=13;
$paddingvertiale=0;
$spazioverticale=3;



//genera e stampa qrcodes
for ($i=0;$i<count($arraycodici);$i++) {

	$codice=$arraycodici[$i];

	if ($i %$qrcodeperpagina ==0) {
		$k=0;
		// add a page
		$pdf->AddPage();
	}

	/* con il riquadro
	if ($i%2==0) {
		$pdf->MultiCell(104, 72, $txt, 1, 'L', 0, 0, '', '', true);
	} else {
		$pdf->MultiCell(104, 72, $txt, 1, 'L', 0, 1, '', '', true);
	}
	*/
	//$pdf->write2DBarcode($codice,'QRCODE,H', ($i%5)*$qrcodedim+10, floor($k/5)*$qrcodedim+10, $qrcodedim-10, $qrcodedim-10, $style, 'N');
	$x=$marginesinistro+$paddingorizzontale+($i%$qrcodeperriga)*($qrcodedim+$paddingorizzontale*2+$spaziotraetichette);
	$y=$marginealto+floor($k/$qrcodeperriga)*($qrcodedim+$paddingverticale*2+$spazioverticale);

	$pdf->write2DBarcode($codice,'QRCODE,H', $x, $y, $qrcodedim, $qrcodedim, $style, 'N');
	$pdf->Text($x-5,$y+$qrcodedim-5, $codice);
			$k++;

}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// move pointer to last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('QRCODE.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
















// ---------------------------------------------------------

// NOTE: 2D barcode algorithms must be implemented on 2dbarcode.php class file.

// set font
$pdf->SetFont('helvetica', '', 11);

// add a page
$pdf->AddPage();

// print a message
$txt = "You can also export 2D barcodes in other formats (PNG, SVG, HTML). Check the examples inside the barcode directory.\n";
$pdf->MultiCell(70, 50, $txt, 0, 'J', false, 1, 125, 30, true, 0, false, true, 0, 'T', false);


$pdf->SetFont('helvetica', '', 10);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// set style for barcode
$style = array(
	'border' => true,
	'vpadding' => 'auto',
	'hpadding' => 'auto',
	'fgcolor' => array(0,0,0),
	'bgcolor' => false, //array(255,255,255)
	'module_width' => 1, // width of a single module in points
	'module_height' => 1 // height of a single module in points
);

// write RAW 2D Barcode

$code = '111011101110111,010010001000010,010011001110010,010010000010010,010011101110010';
$pdf->write2DBarcode($code, 'RAW', 80, 30, 30, 20, $style, 'N');

// write RAW2 2D Barcode
$code = '[111011101110111][010010001000010][010011001110010][010010000010010][010011101110010]';
$pdf->write2DBarcode($code, 'RAW2', 80, 60, 30, 20, $style, 'N');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// set style for barcode
$style = array(
	'border' => 2,
	'vpadding' => 'auto',
	'hpadding' => 'auto',
	'fgcolor' => array(0,0,0),
	'bgcolor' => false, //array(255,255,255)
	'module_width' => 1, // width of a single module in points
	'module_height' => 1 // height of a single module in points
);

// QRCODE,L : QR-CODE Low error correction
$pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,L', 20, 30, 50, 50, $style, 'N');
$pdf->Text(20, 25, 'QRCODE L');

// QRCODE,M : QR-CODE Medium error correction
$pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,M', 20, 90, 50, 50, $style, 'N');
$pdf->Text(20, 85, 'QRCODE M');

// QRCODE,Q : QR-CODE Better error correction
$pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,Q', 20, 150, 50, 50, $style, 'N');
$pdf->Text(20, 145, 'QRCODE Q');

// QRCODE,H : QR-CODE Best error correction
$pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,H', 20, 210, 50, 50, $style, 'N');
$pdf->Text(20, 205, 'QRCODE H');

// -------------------------------------------------------------------
// PDF417 (ISO/IEC 15438:2006)

/*

 The $type parameter can be simple 'PDF417' or 'PDF417' followed by a
 number of comma-separated options:

 'PDF417,a,e,t,s,f,o0,o1,o2,o3,o4,o5,o6'

 Possible options are:

     a  = aspect ratio (width/height);
     e  = error correction level (0-8);

     Macro Control Block options:

     t  = total number of macro segments;
     s  = macro segment index (0-99998);
     f  = file ID;
     o0 = File Name (text);
     o1 = Segment Count (numeric);
     o2 = Time Stamp (numeric);
     o3 = Sender (text);
     o4 = Addressee (text);
     o5 = File Size (numeric);
     o6 = Checksum (numeric).

 Parameters t, s and f are required for a Macro Control Block, all other parametrs are optional.
 To use a comma character ',' on text options, replace it with the character 255: "\xff".

*/

$pdf->write2DBarcode('www.tcpdf.org', 'PDF417', 80, 90, 0, 30, $style, 'N');
$pdf->Text(80, 85, 'PDF417 (ISO/IEC 15438:2006)');

// -------------------------------------------------------------------
// DATAMATRIX (ISO/IEC 16022:2006)

$pdf->write2DBarcode('http://www.tcpdf.org', 'DATAMATRIX', 80, 150, 50, 50, $style, 'N');
$pdf->Text(80, 145, 'DATAMATRIX (ISO/IEC 16022:2006)');

// -------------------------------------------------------------------

// new style
$style = array(
	'border' => 2,
	'padding' => 'auto',
	'fgcolor' => array(0,0,255),
	'bgcolor' => array(255,255,64)
);

// QRCODE,H : QR-CODE Best error correction
$pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,H', 80, 210, 50, 50, $style, 'N');
$pdf->Text(80, 205, 'QRCODE H - COLORED');

// new style
$style = array(
	'border' => false,
	'padding' => 0,
	'fgcolor' => array(128,0,0),
	'bgcolor' => false
);

// QRCODE,H : QR-CODE Best error correction
$pdf->write2DBarcode('www.tcpdf.org', 'QRCODE,H', 140, 210, 50, 50, $style, 'N');
$pdf->Text(140, 205, 'QRCODE H - NO PADDING');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('qrcode.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
