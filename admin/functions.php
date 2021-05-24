<?php //tutte le funzioni possono essere definite dentro config.php



if (!function_exists('getElencoStudi')) {
    function getElencoStudi() {
        global $dbh,$GLOBAL_tb;
        $tbusers=$GLOBAL_tb['users'];

        $query="SELECT * FROM $tbusers WHERE id_ruolo=4 ";
        $stmt = $dbh->query($query);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $row['ClientiStudio']=getClientiStudio($row['id_user']);
            $studi[]=$row;
        }
        return $studi;
    }
}

if (!function_exists('getClientiStudio')) {
    function getClientiStudio($idStudio) {
        global $dbh,$GLOBAL_tb;
        $tbclienti=$GLOBAL_tb['clienti'];
        $query="SELECT * FROM $tbclienti WHERE idStudio=? ";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idStudio));
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clienti[]=$row;
        }
        return $clienti;
    }
}


if (!function_exists('soldi')) {
    function soldi($valore) {
        if ($valore=='') {
            return "-";
        } else {
            //return str_replace("EUR","",money_format('%.2n',$valore));
            if ($valore<0) {
                $return ="<span style='color:red;'>";
                $return .=number_format($valore, 2, ',', '.');
                $return.="</span>";
            } else {
                $return=number_format($valore, 2, ',', '.');
            }
            return $return;

        }
    }
}

if (!function_exists('alias')) {
    function alias($valore) {
        global $aliasnames;

        if ($aliasnames[$valore]!='') {
            return $aliasnames[$valore];
        } else {
            return $valore;
        }

    }
}

if (!function_exists('aggiornaCategorieConti')) {
    function aggiornaCategorieConti($idconto) {
        global $dbh,$GLOBAL_tb;
        $query="UPDATE ".$GLOBAL_tb['pianodeiconti']." p1 INNER JOIN ".$GLOBAL_tb['pianodeiconti']." p2 ON (p1.livello1=p2.livello1 AND p1.id='".$idconto."') SET p2.categoria = p1.categoria";
        return $stmt = $dbh->query($query);
    }
}

if (!function_exists('validateDate')) :
function validateDate($date)
{
    $d = DateTime::createFromFormat('d/m/Y', $date);
    if ($d && $d->format('d/m/Y') === $date) {
        //è una data tipo d/m/Y allora la converto in mysql format
        return $d->format('Y-m-d');
    } else {
        //non è una data, restituisco la stringa come è arrivata
        return $date;
    }
}
endif;

if (!function_exists('convertDate')) :
    function convertDate($date,$format='d/m/Y')
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            //è una data tipo Y-m-d allora la converto in $format (d/m/Y)
            return $d->format($format);
        } else {
            //non è una data, restituisco la stringa come è arrivata
            return $date;
        }
    }
endif;

if (!function_exists('genPdfThumbnail')) :
    function genPdfThumbnail($source, $target,$width=160,$height=120)
    {
        //$source = realpath($source);
        $target = dirname($source).DIRECTORY_SEPARATOR.$target;
        $im     = new Imagick($source."[0]"); // 0-first page, 1-second page
        //$im->setImageColorspace(255); // prevent image colors from inverting
        $im->setimageformat("jpeg");
        $im->thumbnailimage($width, $height); // width and height
        $im->writeimage($target);
        $im->clear();
        $im->destroy();
    }
endif;

if (!function_exists('TODDMMYYYY')) :
    function TODDMMYYYY($data) {
        if ($data=='') {
            return '';
        } else {
            list($aa,$mm,$gg)=explode("-",$data);
            return $gg."/".$mm."/".$aa;
        }
    }
endif;

if (!function_exists('getCliente')) :
    function getCliente($idcliente) {
        global $dbh,$GLOBAL_tb;
        $tbclienti=$GLOBAL_tb['clienti'];
        $query="SELECT * FROM $tbclienti WHERE id=? ";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($idcliente));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
endif;

if (!function_exists('cambianomecampi')) :
    function cambianomecampi($idmod,$nomecampo) {
        if ($nomecampo=='ricavi_personalizzati_01') {
            $nomecampo='ricavi personalizzati GEN';
        }
        if ($nomecampo=='ricavi_personalizzati_02') {
            $nomecampo='ricavi personalizzati FEB';
        }
        if ($nomecampo=='ricavi_personalizzati_03') {
            $nomecampo='ricavi personalizzati MAR';
        }
        if ($nomecampo=='ricavi_personalizzati_04') {
            $nomecampo='ricavi personalizzati ARP';
        }
        if ($nomecampo=='ricavi_personalizzati_05') {
            $nomecampo='ricavi personalizzati MAG';
        }
        if ($nomecampo=='ricavi_personalizzati_06') {
            $nomecampo='ricavi personalizzati GIU';
        }
        if ($nomecampo=='ricavi_personalizzati_07') {
            $nomecampo='ricavi personalizzati LUG';
        }
        if ($nomecampo=='ricavi_personalizzati_08') {
            $nomecampo='ricavi personalizzati AGO';
        }
        if ($nomecampo=='ricavi_personalizzati_09') {
            $nomecampo='ricavi personalizzati SET';
        }
        if ($nomecampo=='ricavi_personalizzati_10') {
            $nomecampo='ricavi personalizzati OTT';
        }
        if ($nomecampo=='ricavi_personalizzati_11') {
            $nomecampo='ricavi personalizzati NOV';
        }
        if ($nomecampo=='ricavi_personalizzati_12') {
            $nomecampo='ricavi personalizzati DIC';
        }
        return $nomecampo;
    }
endif;

if (!function_exists('getUtente')) :
    function getUtente($id_user) {
        global $dbh,$GLOBAL_tb;
        $tbusers=$GLOBAL_tb['users'];
        $tbruoli=$GLOBAL_tb['ruoli'];
        $query="SELECT * FROM $tbusers,$tbruoli WHERE id_user=? AND $tbruoli.id_ruolo=$tbusers.id_ruolo";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($id_user));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
endif;

if (!function_exists('getAnni')) :
    function getAnni() {
        global $dbh,$GLOBAL_tb;

        $query="SELECT * FROM pcs_anni order by anno";
        $stmt = $dbh->query($query);

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $anni[]=$row['anno'];
        }
        return $anni;
    }
endif;


if (!function_exists('getLingue')) :
    function getLingue($id_user) {
        global $dbh,$GLOBAL_tb;
        $tblingue=$GLOBAL_tb['lingue'];
        $tblingueusers=$GLOBAL_tb['lingue_users'];

        $query="SELECT * FROM $tblingueusers LEFT JOIN $tblingue ON $tblingue.id_lang=$tblingueusers.id_lang WHERE id_user=$id_user";
        $stmt = $dbh->query($query);

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lingue[]=$row['lang'];
        }
        return $lingue;
    }
endif;

if (!function_exists('getLingueInterfaccia')) :
    function getLingueInterfaccia() {
        global $dbh,$GLOBAL_tb;
        $tblingueinterfaccia=$GLOBAL_tb['lingue_interfaccia'];

        $query="SELECT * FROM $tblingueinterfaccia ";
        $stmt = $dbh->query($query);

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lingue[]=$row;
        }
        return $lingue;
    }
endif;

if (!function_exists('getAllLangs')) :
    function getAllLangs() {
        global $dbh,$GLOBAL_tb;
        $tblingue=$GLOBAL_tb['lingue'];

        $query="SELECT * FROM $tblingue ";
        $stmt = $dbh->query($query);

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lingue[]=$row['lang'];
        }
        return $lingue;
    }
endif;

if (!function_exists('getFiles')) :
    function getFiles($id_elem,$tb,$tipofile,$maxfiles=-1) {
        global $dbh,$GLOBAL_tb;
        $tbfiles=$GLOBAL_tb['files'];
        if ($maxfiles>0) {
            $query="SELECT * FROM $tbfiles WHERE tipo_file='".$tipofile."' AND id_elem=$id_elem AND tb='".$tb."' order by ordine LIMIT 0,$maxfiles ";
        } else {
            $query="SELECT * FROM $tbfiles WHERE tipo_file='".$tipofile."' AND id_elem=$id_elem AND tb='".$tb."' order by ordine";
        }
        $stmt=$dbh->query($query);
        $files=array();
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $files[]=$row;
        }
        return $files;
    }
endif;

if (!function_exists('getModuli')) :
    function getModuli($pars='') {
        global $dbh,$GLOBAL_tb;
        $tbmoduli=$GLOBAL_tb['moduli'];

        $WHERE='';
        if ($pars['stato']) {
            $WHERE.=" AND stato='".$pars['stato']."'";
        }
        if ($pars['menu']) {
            $WHERE.=" AND menu='".$pars['menu']."'";
        }
        if ($pars['nome_modulo']) {
            $WHERE.=" AND nome_modulo='".$pars['nome_modulo']."'";
        }
        $query="SELECT * FROM $tbmoduli WHERE 1=1 $WHERE order by ordine";

        foreach($dbh->query($query) as $row) {
            foreach ($row as $key=>$value) {
                $row[$key]=stripslashes($value);
            }
            $moduli[]=$row;
        }
        return $moduli;
    }
endif;

if (!function_exists('getModulo')) :
    function getModulo($id) {
        global $dbh,$GLOBAL_tb;
        $tbmoduli=$GLOBAL_tb['moduli'];
        $query="SELECT * FROM $tbmoduli WHERE id_modulo=$id";
        foreach($dbh->query($query) as $row) {
            foreach ($row as $key=>$value) {
                $row[$key]=stripslashes($value);
            }
        }
        return $row;
    }
endif;

if (!function_exists('permessi')) :
    function permessi($id_modulo,$id_ruolo,$superuser=false) {
        global $dbh,$GLOBAL_tb;
        $tbpermessi=$GLOBAL_tb['permessi'];

        if ($superuser) {
            $perm['Can_create']='si';
            $perm['Can_read']='si';
            $perm['Can_update']='si';
            $perm['Can_delete']='si';
            return $perm;
        }
        $query="SELECT * FROM $tbpermessi WHERE id_modulo=? AND id_ruolo=? LIMIT 0,1";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($id_modulo, $id_ruolo));

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            $perm['Can_create']='no';
            $perm['Can_read']='no';
            $perm['Can_update']='no';
            $perm['Can_delete']='no';
            return $perm;
        }
    }
endif;

if (!function_exists('getElemento')) :
    function getElemento($idmod,$idele) {
        global $dbh;
        $modulo=getModulo($idmod);
        $query="SELECT * FROM ".$modulo['nome_tabella']." WHERE ".$modulo['chiaveprimaria']."='".$idele."' LIMIT 0,1";
        $stmt=$dbh->query($query);
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
endif;

if (!function_exists('getPostazione')) :
    function getPostazione($idele) {
        global $dbh,$GLOBAL_tb;
        $tbpostazioni=$GLOBAL_tb['postazioni'];
        $query="SELECT * FROM ".$tbpostazioni." WHERE id=? LIMIT 0,1";
        $stmt=$dbh->prepare($query);
        $stmt->execute(array($idele));
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
endif;

if (!function_exists('getTestiTraducibili')) :
    function getTestiTraducibili($nome_tabella,$idele,$lang) {
        global $dbh,$GLOBAL_tb;
        $tbcampitraducibili=$GLOBAL_tb['campi_traducibili'];
        $tbtesti=$GLOBAL_tb['testi'];
        $query="SELECT * FROM $tbcampitraducibili WHERE nome_tabella=? ";
        $stmt=$dbh->prepare($query);
        $stmt->execute(array($nome_tabella));
        $campi=array();
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $campi[]=$row['nome_campo'];
        }
        if (count($campi)==0) {
            return;
        }
        $testi=array();
        foreach ($campi as $chiave) {
            if ($chiave=='') continue;
            $query="SELECT valore FROM $tbtesti WHERE id_ext=? AND table_ext=? AND lang=? AND chiave=? LIMIT 0,1";
            $stmt=$dbh->prepare($query);
            $stmt->execute(array($idele,$nome_tabella,$lang,$chiave));

            if ($row=$stmt->fetch(PDO::FETCH_ASSOC)) { $testi[$chiave]=$row['valore']; }
        }
        return $testi;
    }
endif;

if (!function_exists('getTipoColonna')) :
    function getTipoColonna($colType) {
        //NUMERIC (input type=text)
        if ( strstr($colType,"bigint") || strstr($colType,"int") || strstr($colType,"mediumint") || strstr($colType,"smallint")   ) {
            return "INTEGER";
        }

        //NUMERIC (input type=text)
        if ( strstr($colType,"float")  || strstr($colType,"double")  ) {
            return "NUMERIC";
        }

        //DECIMAL (input type=text)
        if ( strstr($colType,"decimal")   ) {
            return "DECIMAL";
        }

        //VARCHAR e TINYTEXT (input type=text)
        if ( strstr($colType,"varchar")  || strstr($colType,"tinytext"))
        { //guarda se è enum
            return "TEXT";
        }

        //TEXT MEDIUMTEXT e LONGTEXT (textarea)
        if ( strstr($colType,"mediumtext") || strstr($colType,"text") || strstr($colType,"longtext"))
        { //guarda se è enum
            return "TEXTAREA";
        }

        //ENUM (radio button or select)
        if ( strstr($colType,"enum") )
        { //guarda se è enum
            return "ENUM";
        }

        //SET (checkbox or multiselect)
        if ( strstr($colType,"set"))
        { //guarda se è enum
            return "SET";
        }

        //DATE (datepicker)
        if ( strstr($colType,"date"))
        { //guarda se è enum
            return "DATE";
        }

        //DATETIME (datetimepicker)
        if ( strstr($colType,"datetime"))
        { //guarda se è enum
            return "DATETIME";
        }

        //TIME o TIMESTAMP (timepicker)
        if ( strstr($colType,"time") || strstr($colType,"timestamp"))
        { //guarda se è enum
            return "TIME";
        }

    }
endif;

if (!function_exists('getLastPosition')) :
    function getLastPosition($tab) {
        global $dbh;
        $query="SELECT max(ordine) as last FROM $tab ";
        if ($stmt=$dbh->query($query) ) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['last'];
        } else {
            return 0;
        }
    }
endif;

if (!function_exists('sanitate')) :
    function sanitate($array) {
        foreach($array as $key=>$value) {
            if(is_array($value)) { sanitate($value); }
            else {
                if ($array[$key]) { $array[$key] = addslashes($value); }
            }
        }
        return $array;
    }
endif;

if (!function_exists('getSetValues')) :
    function getSetValues($table,$column)
    {
        global $dbh;
        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";

        $stmt=$dbh->query($sql);

        $line = $stmt->fetch(PDO::FETCH_ASSOC);
        $set  = $line['Type'];
        // Remove "set(" at start and ");" at end.
        $set  = substr($set,5,strlen($set)-7);
        // Split into an array.
        return preg_split("/','/",$set);
    }
endif;

if (!function_exists('getEnumValues')) :
    function getEnumValues($table_name,$column_name) {
        global $dbh;
        $stmt = $dbh->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND COLUMN_NAME = '$column_name'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $enumList = explode(",", str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6))));
        return $enumList;
    }
endif;

if (!function_exists('getModuloFrom_script_modulo')) :
    function getModuloFrom_script_modulo($string) {
        global $dbh,$GLOBAL_tb;
        $tbmoduli=$GLOBAL_tb['moduli'];
        $query="SELECT id_modulo FROM $tbmoduli WHERE modulo_standard='no' AND script_modulo=?";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($string));
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id_modulo'];
    }
endif;

if (!function_exists('getModuloFrom_nome_modulo')) :
    function getModuloFrom_nome_modulo($string) {
        global $dbh,$GLOBAL_tb;
        $tbmoduli=$GLOBAL_tb['moduli'];
        $query="SELECT id_modulo FROM $tbmoduli WHERE nome_modulo=?";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($string));
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id_modulo'];
    }
endif;

if (!function_exists('getModuloFrom_nome_tabella')) :
    function getModuloFrom_nome_tabella($string) {
        global $dbh,$GLOBAL_tb;
        $tbmoduli=$GLOBAL_tb['moduli'];
        $query="SELECT id_modulo FROM $tbmoduli WHERE nome_tabella='".$string."'";
        $stmt=$dbh->query($query);
        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id_modulo'];
    }
endif;

if (!function_exists('setNotificheCRUD')) :
    function setNotificheCRUD($pTipoUtente="admWeb",$pCategoria=NULL,$pTipologia=NULL,$pEvento=NULL) {
        global $dbh,$GLOBAL_tb;
        $tbnotificheCRUD=$GLOBAL_tb['notificheCRUD'];
        $iException=0;
        // (i) Prerequisiti
        if (empty($pTipoUtente)) {
            return 0;
        }
        // () Formatting
        $pTipoUtente=addslashes($pTipoUtente);
        $pCategoria=addslashes($pCategoria);
        $pTipologia=addslashes($pTipologia);
        $pEvento=addslashes($pEvento);

        // () Formatting
        // (f) Prerequisiti
        try {
            $sqlInsNotifiche="";
            $sqlInsNotifiche="INSERT INTO $tbnotificheCRUD (data_notifica, id_utente, categoria, tipologia, evento)".
                " VALUES ( now(), '".trim($pTipoUtente)."','".
                trim($pCategoria)."','".
                trim($pTipologia)."','".
                trim($pEvento)."')";
            $insNotifiche = $dbh->query($sqlInsNotifiche);
            if (!$insNotifiche) {
                $iException++;
            }
        } catch  (Exception $setNotificheAggException){
            $iException++;

        }
        if ($iException>0) {
            return 0;
        } else {
            return 1;
        }
    }
endif;

?>
