<?php //controllo se ci sono le info
$permessi_modulo_doc=permessi(81,$utente['id_ruolo'],$superuserOverride);

if ($_SERVER['SCRIPT_NAME']=="/admin/module.php") {
    if ($permessi_modulo_doc['Can_create']==1) {
        $mostrainfo=2;
    } else {
        $mostrainfo=1;
    }
    if ($_GET['id']>0) { ?>
        <?php $idpaginainfo=$_GET['id'];?>
    <?php } ?>
    <?php if ($_GET['modname']) { ?>
        <?php $idpaginainfo=getModuloFrom_nome_modulo($_GET['modname']);?>

    <?php }

} elseif ($_SERVER['SCRIPT_NAME']=="/admin/get_element.php") {
    if ($_GET['idmod']>0) {
        $idpaginainfo=100+$_GET['idmod'];
    }
    if ($permessi_modulo_doc['Can_create']==1) {
        $mostrainfo=2;
    } else {
        $mostrainfo=1;
    }
} elseif ($_SERVER['SCRIPT_NAME']=="/admin/report.php") {

    if ($permessi_modulo_doc['Can_create']==1) {
        $mostrainfo=2;
    } else {
        $mostrainfo=1;
    }
    $idpaginainfo=1;
} elseif ($_SERVER['SCRIPT_NAME']=="/admin/reportmese.php") {

    if ($permessi_modulo_doc['Can_create']==1) {
        $mostrainfo=2;
    } else {
        $mostrainfo=1;
    }
    $idpaginainfo=2;
} elseif ($_SERVER['SCRIPT_NAME']=="/admin/bilancioprecedente.php") {

    if ($permessi_modulo_doc['Can_create']==1) {
        $mostrainfo=2;
    } else {
        $mostrainfo=1;
    }
    $idpaginainfo=3;
} else {
    $mostrainfo=0;
}

if ($mostrainfo==2) {
    ?>
    <div class="pull-right">
        <br/>
        <a target="_blank" href="/admin/get_element.php?k1=pagina-<?php echo $idpaginainfo;?>&k2=&k=&debug=0&idmod=81&idele=-1"><i class="fa fa-info-circle fa-2x"></i></a>
    </div>
<?php } ?>
<?php if ($mostrainfo==1) {

    $query="SELECT * FROM pcs_inpage_doc WHERE pagina=$idpaginainfo";
    $stmt=$dbh->query($query);
    $row=$stmt->fetch(PDO::FETCH_ASSOC);
    $contenutoinfo=$row['testo'];
    ?>

    <div class="pull-right">
        <a tabindex="0" data-placement="left" data-toggle="popover" title="Info" data-html="true" data-content="<?php echo $contenutoinfo;?>"><span class="fa-2x fa fa-info-circle"></span></a>
    </div>
<?php } ?>