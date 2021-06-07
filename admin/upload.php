<?php
// HTTP headers for no cache etc
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

include("resizeImage/class_resizeMyImg.php");

/**
 * upload.php
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

//to read parameters passed by php page
$file = 'ajaxlogupload.txt';
// Open the file to get existing content
$current = "ANALISI COSTI\r\n";
// Rewrite data
$current .= date("Y-m-d H:i:s")."\r\n";
$current .= json_encode($_REQUEST)."\r\n";


//li ho già perché presenti nel file parametri
$ftp_server=$ftp_host;
$ftp_user_name=$ftp_id;
$ftp_user_pass=$ftp_pw;

// Settings
$targetDirRemote = $directoryfiles.DIRECTORY_SEPARATOR.$_GET['nome_tabella']."_".$_GET['idele'];
$targetDir = "../tmpfiles";

$current.="root_server= $root_server\n";
$current.="directoryfiles= $directoryfiles\n";
$current.="targetDirRemote= $targetDirRemote\n";
$current.="FTPUPLOADDIR= $FTPUPLOADDIR\n";

$FTPUPLOADDIR=$root_server;

$current.="Ora invece ho messo FTPUPLOADDIR= $FTPUPLOADDIR\n";


// set up basic connection
$conn_id = ftp_connect($ftp_server);

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

// try to create the directory $dir
$dirtobecreated=$FTPUPLOADDIR.$targetDirRemote;
if (@ftp_mkdir($conn_id, $dirtobecreated)) {
    @ftp_chmod($conn_id, 0777, $targetDirRemote);
    $current.="successfully created $dirtobecreated\n";
    //echo "successfully created $dirtobecreated\n";
} else {
    //echo "errore creazione directory $dirtobecreated";
    $current.="errore creazione directory $dirtobecreated oppure già presente\n";
}


$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds

// 5 minutes execution time
@set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Get parameters
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

// Clean the fileName for security reasons
$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

// Make sure the fileName is unique but only if chunking is disabled
if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
    $ext = strrpos($fileName, '.');
    $fileName_a = substr($fileName, 0, $ext);
    $fileName_b = substr($fileName, $ext);

    $count = 1;
    while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
        $count++;

    $fileName = $fileName_a . '_' . $count . $fileName_b;
}

$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;


$current.="filePath= $filePath\n";

// Write the contents back to the file
file_put_contents($file, $current);


// Create target dir
//if (!file_exists($targetDir))
//	@mkdir($targetDir);

// Remove old temp files
if ($cleanupTargetDir) {
    if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
        while (($file = readdir($dir)) !== false) {
            $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
            // Remove temp file if it is older than the max age and is not the current file
            if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                @unlink($tmpfilePath);
            }
        }
        closedir($dir);
    } else {
        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
    }
}

// Look for the content type header
if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
    $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

if (isset($_SERVER["CONTENT_TYPE"]))
    $contentType = $_SERVER["CONTENT_TYPE"];

// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
if (strpos($contentType, "multipart") !== false) {
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        // Open temp file
        $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
            // Read binary input stream and append it to temp file
            $in = @fopen($_FILES['file']['tmp_name'], "rb");

            if ($in) {
                while ($buff = fread($in, 4096))
                    fwrite($out, $buff);
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            @fclose($in);
            @fclose($out);
            @unlink($_FILES['file']['tmp_name']);
        } else
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    } else
        die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
} else {
    // Open temp file
    $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    if ($out) {
        // Read binary input stream and append it to temp file
        $in = @fopen("php://input", "rb");

        if ($in) {
            while ($buff = fread($in, 4096))
                fwrite($out, $buff);
        } else
            die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

        @fclose($in);
        @fclose($out);
    } else
        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}

// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
    // Strip the temp .part suffix off
    rename("{$filePath}.part", $filePath);
}

try {
    $table_ext=$_GET['nome_tabella'];
    $id_ext=$_GET['idele'];
    $tipo_file=$_GET['tipofile'];
    //ora devo fare l'ftp upload del file caricato e devo toglierlo dalla directory temporanea, poi salvarlo nel db e siamo a posto
    $targetDirRemote = $directoryfiles.DIRECTORY_SEPARATOR.$_GET['nome_tabella']."_".$_GET['idele'];
    $remoteFile=$targetDirRemote.DIRECTORY_SEPARATOR.$fileName;
    $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

    $current.="ora sono dentro al 'try'\n";
    $current.="targetDirRemote= $targetDirRemote\n";
    $current.="remoteFile= $remoteFile\n";
    $current.="filePath= $filePath\n";


    // upload the file
    @ftp_chdir($conn_id, $FTPUPLOADDIR.$targetDirRemote);

    if (ftp_put($conn_id, $fileName, $filePath, FTP_BINARY)) {

    } else {
        die('{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "Failed to upload file to ftp dir."}, "id" : "id"}');
    }

    //ora creo i vari crops e thumbnails, chiamandoli crop_width_height e thumb_width_height così non importa che li salvi nel db ma solo sul filesystem!

    if ($tipo_file=="allegato") :

        /*
        if (substr($fileName,-3)=='pdf') :

            if( extension_loaded('imagick') || class_exists("Imagick") ) {
                genPdfThumbnail($root_server.$sitedir.$filePath,$fileName.'.jpg',640,480); // generates /uploads/my.jpg
                $savelink=$targetDir . DIRECTORY_SEPARATOR.$fileName.'.jpg';

                // upload the file
                @ftp_chdir($conn_id, $FTPUPLOADDIR.$targetDirRemote);

                if (ftp_put($conn_id, $fileName.'.jpg', $savelink, FTP_BINARY)) {

                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "Failed to upload file to ftp dir."}, "id" : "id"}');
                }
                @unlink($savelink);
            }
        endif;
        */
    endif;

    if ($tipo_file=="immagine") :

        //$resizes è definito a livello di file _parametri.php
        //il primo è quello di default

        /*
        $resizes[0]['prefisso']="crop";
        $resizes[0]['crop']="crop";
        $resizes[0]['width']=80;
        $resizes[0]['height']=80;

        $resizes[1]['prefisso']="crop";
        $resizes[1]['crop']="crop";
        $resizes[1]['width']=150;
        $resizes[1]['height']=150;

        $resizes[2]['prefisso']="thumb";
        $resizes[2]['crop']="landscape"; //forzo il resize su width 400
        $resizes[2]['width']=400;
        $resizes[2]['height']=400;
        */

        foreach ($resizes as $r) :

            $width=$r['width'];
            $height=$r['height'];
            $prefisso=$r['prefisso'];
            $crop=$r['crop'];

            $savelink=$targetDir . DIRECTORY_SEPARATOR . $prefisso."_".$width."_".$height."_".$fileName;
            // *** 1) Initialise / load image
            $resizeObj = new resize($filePath);
            // *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
            $resizeObj -> resizeImage($width, $height, $crop);
            // *** 3) Save image
            $resizeObj -> saveImage($savelink, 99);

            // upload the file
            @ftp_chdir($conn_id, $FTPUPLOADDIR.$targetDirRemote);

            if (ftp_put($conn_id, $prefisso."_".$width."_".$height."_".$fileName, $savelink, FTP_BINARY)) {

            } else {
                die('{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "Failed to upload file to ftp dir."}, "id" : "id"}');
            }
            @unlink($savelink);

        endforeach; //resizes


    endif;

    @unlink($filePath);

    // close the connection
    ftp_close($conn_id);

    //il campo ordine deve essere incrementato di 1
    $newordine=getLastPosition($GLOBAL_tb['files']);
    $newordine++;

    $current.="newordine= $newordine\n";

    $queryFoto ="INSERT INTO ".$GLOBAL_tb['files']." (data_inserimento,tb,id_elem,tipo_file,file,ordine) values (NOW(),'".$table_ext."',".$id_ext.",'".$tipo_file."','".$remoteFile."',".$newordine.")";


    $stmt= $dbh->query($queryFoto);
} catch (MySQLException $getModelloException) {
    $iException++;
}
die('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "cleanFileName" : "'.$fileName.'"}');
