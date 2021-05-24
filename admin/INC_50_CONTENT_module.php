<div class="container" id="firstcontainer">
    <?php if ($_GET['id']>0) { ?>
        <?php $modulo=getModulo($_GET['id']);?>
    <?php } ?>
    <?php include("mostrainfo.php");?>
    <?php //print_r($_SERVER);exit;?>
    <?php include("barranavigazione.php");?>
    <?php $backlist=$_SERVER['REQUEST_URI'];?>

    <!-- Example row of columns -->
    <div class="row">
<?php if ($_GET['modname']) { ?>
    <?php $_GET[id]=getModuloFrom_nome_modulo($_GET['modname']);?>
    <?php $modulo=getModulo($_GET[id]);?>
<?php } ?>
<?php $bStateSave=true;?>
<?php if ($_GET['azzera']==1) $bStateSave=false;?>
<?php $pars=$_GET['p']; ?>
<?php $filtridate=$_GET['fd'];?>
<div>
    <ul class="breadcrumb">
        <li>
            <a href="index.php">Home</a>
        </li>
        <li>
            <?php echo _($modulo['nome_modulo']);?>
        </li>
    </ul>
</div>

<?php

//proteggiamoci da sguardi indiscreti!
if ($_GET[debug]!='VIACOLDEBUG') $_GET[debug]=0;
?>

<?php /* (i) ------------------------------ elenco del singolo modulo --------------------------------------------------------------------------------------   */ ?>
<?php if ($_GET['id']>0) :
//$query="SHOW FULL COLUMNS FROM ".$modulo['nome_tabella'];
//echo $query;
//$res=mysql_query($query);

$permessi_modulo=permessi($_GET['id'],$utente['id_ruolo'],$superuserOverride);
$campi_non_mostrati_in_tabella=explode(",",$modulo['campi_non_mostrati_in_tabella']);
$campi_testo_in_lingua=explode(",",$modulo['campi_testo_in_lingua']);
$campi_hidden_xs=explode(",",$modulo['campi_hidden_xs']);
$campi_hidden_sm=explode(",",$modulo['campi_hidden_sm']);
$campi_readonly=explode(",",$modulo['campi_readonly']);

$espressione_per_chiave_primaria=$modulo['espressione_per_chiave_primaria'];

if ($modulo['add_column']) :
    $addcolumn=json_decode($modulo['add_column'],true);
endif;

    if ($_GET[debug]) print_r($addcolumn);

if ($modulo['query']) {
    $query=$modulo['query'];
} else {
    $query="SELECT $espressione_per_chiave_primaria,".$modulo['nome_tabella'].".* FROM ".$modulo['nome_tabella']." order by ordine";
}

$nuovaquery=str_replace("%id_user%",$utente['id_user'],$query);
$nuovaquery=str_replace("%id_cliente%","'".$utente['id_cliente']."'",$nuovaquery);
$canreadall=$permessi_modulo['Can_read_all']=='si' ? 1 : 0;
$nuovaquery=str_replace("%can_read_all%",$canreadall,$nuovaquery);
$nuovaquery=str_replace("%defaultLang%",$lang,$nuovaquery);
$nuovaquery=str_replace("%lang%",$lang,$nuovaquery);

    //filtri passati da $_GET['p']
    if (count($pars)>0) {
        foreach ($pars as $key=>$val) {
            $cond[]=$modulo['nome_tabella'].".$key='".stripslashes($val)."'";
        }
        $where="(".join(" AND ",$cond).")";
        //ora nella query devo inserire queste condizioni aggiuntive
        $nuovaquery=str_replace("%wherefiltropar%","$where ",$nuovaquery);
    } else {
        $nuovaquery=str_replace("%wherefiltropar%","10=10 ",$nuovaquery);
    }

    //filtri passati tramite filtro campo data
    if (count($filtridate)>0) {
        foreach ($filtridate as $key=>$val) {
            list($da,$a)=explode("|",$val);
            list($d1,$m1,$y1)=explode("/",$da);
            $datada=$y1."-".$m1."-".$d1." 00:00";
            list($d2,$m2,$y2)=explode("/",$a);
            $dataa=$y2."-".$m2."-".$d2." 23:59";
            $datacond[]=$modulo['nome_tabella'].".$key>='$datada' AND ".$modulo['nome_tabella'].".$key<='$dataa'";
        }
        $where="(".join(" AND ",$datacond).")";
        //ora nella query devo inserire queste condizioni aggiuntive
        $nuovaquery=str_replace("%wherefiltrodate%","$where ",$nuovaquery);
    } else {
        $nuovaquery=str_replace("%wherefiltrodate%","10=10 ",$nuovaquery);
    }

if ($_GET[debug]) echo $nuovaquery;

$righe=array();
if ($stmt=$dbh->query($nuovaquery)) {
    $numero_records=$stmt->rowCount();
    $righe = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    setNotificheCRUD("admWeb","ERROR","module.php",$nuovaquery);
}
?>
    <div class="page-header">

        <h2>
            <i class="<?php echo $modulo['font_icon'];?>"></i> <?php echo _($modulo['nome_modulo']);?> <span class="badge"><?php echo $numero_records;?></span>
            <div class="pull-right">
                <?php if ($permessi_modulo['Can_delete']=='si') { ?>
                    <a id="multiplerowdelete" class="tooltip-danger btn btn-app btn-danger btn-xs" style="display:none;"
                       data-rel="tooltip" data-placement="left" title="Delete all selected items" >
                        <i class="glyphicon glyphicon-trash bigger-160"></i>
                    </a>
                <?php } ?>
                <?php
                if ($modulo['aprimodal'] == 'si') {
                    if (($permessi_modulo['Can_create'] == 'si') && ($modulo['abilita_bottone_new'] == 'si')) { ?>
                    <a class="tooltip-success btn btn-app btn-success btn-xs aprimodal-ele"
                       idmodalmod="<?php echo $modulo['id_modulo']; ?>" idmodalele="-1"
                       data-rel="tooltip" data-placement="left"
                       title="Aggiungi elemento di <?php echo _($modulo[nome_modulo]); ?>">
                        <span style="font-size:2.5em;" class="glyphicon glyphicon-plus"></span>
                    </a>
                    <?php } ?>
                    <?php } else {
                    if (($permessi_modulo['Can_create'] == 'si') && ($modulo['abilita_bottone_new'] == 'si')) { ?>
                        <?php
                        $k='';
                        $k1='';
                        $k2='';
                        if ($modulo['nome_modulo']=='CaricamentoDati' && $_GET['p']['id_azienda']) {
                            $k='id_azienda-'.$_GET['p']['id_azienda'];
                            //anche anno e mese consecutivo
                            $query="SELECT * FROM pcs_caricamento_dati WHERE id_azienda=? order by anno DESC,mese DESC LIMIT 0,1";
                            $stmt=$dbh->prepare($query);
                            $stmt->execute(array($_GET['p']['id_azienda']));
                            $ULTIMO=$stmt->fetch(PDO::FETCH_ASSOC);
                            if ($ULTIMO['mese']=='12') {
                                $nuovoanno=$ULTIMO['anno']+1;
                                $nuovomese="01";
                            } else {
                                $nuovoanno=$ULTIMO['anno'];
                                $nuovomese=sprintf("%02d",intval($ULTIMO['mese'])+1);
                            }
                            $k1="anno-".$nuovoanno;
                            $k2="mese-".$nuovomese;
                        } else {
                            if ($modulo['nome_modulo']=='ProspettoPrevisionale' && $_GET['p']['id_azienda']) {
                                $k='id_azienda-'.$_GET['p']['id_azienda'];
                                //anche anno e mese consecutivo
                                $query="SELECT * FROM pcs_progetto_bilancio WHERE id_azienda=? order by anno DESC LIMIT 0,1";
                                $stmt=$dbh->prepare($query);
                                $stmt->execute(array($_GET['p']['id_azienda']));
                                $ULTIMO=$stmt->fetch(PDO::FETCH_ASSOC);
                                $nuovoanno=$ULTIMO['anno']+1;
                                $k1="anno-".$nuovoanno;
                            }
                        }
                    ?>
                    <a
                           href="get_element.php?k1=<?php echo $k1;?>&k2=<?php echo $k2;?>&k=<?php echo $k;?>&debug=<?php echo $_GET['debug']; ?>&idmod=<?php echo $modulo['id_modulo']; ?>&idele=-1&backlist=<?php echo base64_encode($backlist);?>"
                           class="tooltip-success btn btn-app btn-success btn-xs"
                           data-rel="tooltip" data-placement="left"
                           title="Aggiungi elemento di <?php echo $modulo['nome_modulo']; ?>">
                            <i class="ace-icon glyphicon glyphicon-plus bigger-160"></i>
                        </a>
                    <?php }
                }
                ?>

                <?php if ($permessi_modulo['Can_update']=='si') {
                    foreach ($_GET as $key=>$value) {
                        if ($key=="reordering") continue;
                        $get[]=$key."=".$value;
                    }
                    if ($_GET['reordering']==1) { } else { $get[]="reordering=1"; }
                    $querystring=join("&",$get);
                    $url="http://".$_SERVER[HTTP_HOST].$_SERVER[SCRIPT_NAME]."?".$querystring;
                    ?>
                <?php } ?>
            </div>
        </h2>
        <?php if (($modulo['nome_modulo']=="PianoDeiConti") && ($permessi_modulo['Can_delete']=='si')){ ?>

<?php
            $queryAll="SELECT count(*) as continuovi FROM ".$GLOBAL_tb['pianodeiconti']." WHERE new=1 AND id_azienda=".$token['id_azienda'];
            $stmt2=$dbh->query($queryAll);
            $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
            $continuovi=$rowAll['continuovi'];
?>

            <a  id="eliminaPianoDeiConti" idazienda="<?php echo $token['id_azienda'];?>" class="btn btn-sm btn-danger btn-save" >
                <i class="glyphicon glyphicon-trash"></i> <?php echo _("Elimina Piano dei Conti");?>
            </a>

            <?php //echo base64_decode($_GET['backlist']);?>
        <?php } ?>
        <?php if (($modulo['nome_modulo']=="PianoDeiConti") && ($continuovi>0) && ($permessi_modulo['Can_delete']=='si')){ ?>
            <a  id="accettaNuoviConti" idazienda="<?php echo $token['id_azienda'];?>" class="btn btn-sm btn-warning btn-save" >
                <i class="glyphicon glyphicon-ok-sign"></i> <?php echo _("Accetta Tutti i nuovi conti");?>
            </a>

            <?php //echo base64_decode($_GET['backlist']);?>
        <?php } ?>
        <?php if ($_GET['backlist']!='' && $modulo['nome_modulo']=="PianoDeiConti") { ?>
            <a
                    href="<?php echo base64_decode($_GET['backlist']);?>"
                    class="btn btn-sm btn-info btn-save" >
                <i class="glyphicon glyphicon-backward"></i> <?php echo _("Torna Indietro");?>
            </a>

            <?php //echo base64_decode($_GET['backlist']);?>
        <?php } ?>

    </div><!-- /.page-header -->

    <?php //* * * * * * * * * * * * * * * * (i) modal allegati elemento  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  ?>
    <div id="ModalAllegati" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="blue bigger">Allegati</h4>
                </div>

                <div id="modal-body-ModalAllegati" class="modal-body">
                    LOADING...
                </div>

            </div>
        </div>
    </div><!-- PAGE CONTENT ENDS -->

    <?php //* * * * * * * * * * * * * * * * (f) modal allegati elemento  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  ?>

    <?php //* * * * * * * * * * * * * * * * (i) modal dettagli elemento  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  ?>
    <div id="ModalDetails" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="blue bigger">Dettaglio <?php echo _($modulo['nome_modulo']);?></h4>
                </div>

                <div id="modal-body-ModalDetails" class="modal-body">
                    LOADING...
                </div>

            </div>
        </div>
    </div><!-- PAGE CONTENT ENDS -->

    <?php //* * * * * * * * * * * * * * * * (f) modal dettagli elemento  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  ?>

    <?php // * * * * * * * * * * * * * * * * (i) modal nuovo elemento * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  ?>
    <div id="myModal" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="blue bigger"><span id="nomemodal"><?php echo _($modulo['nome_modulo']);?></span></h4>
                </div>

                <div id="modal-body-myModal" class="modal-body">
                    LOADING...
                </div>

                <div class="modal-footer">
                    <a class="btn btn-success" id="nuovo_elemento_save" name="nuovo_elemento_save">
                        <?php echo _("Salva");?>
                    </a>
                </div>
                    <button class="btn btn-sm btn-info btn-save" id="nuovo_elemento_close" name="nuovo_elemento_close" >
                        <i class="glyphicon glyphicon-repeat"></i>
                        <?php echo _("Chiudi");?>
                    </button>
            </div>
        </div>
    </div><!-- PAGE CONTENT ENDS -->
    <?php // * * * * * * * * * * * * * * * * (f) modal * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *  ?>


    <?php
    if ($numero_records>0) :

        //preparo le intestazioni

        $labels[0]="label-warning";
        $labels[1]="label-info";
        $labels[2]="label-danger";
        $labels[3]="label-success";
        $labels[4]="label-warning";
        $labels[5]="label-info";
        $labels[6]="label-danger";
        $labels[7]="label-success";
        $labels[8]="label-warning";
        $labels[9]="label-info";
        $labels[10]="label-danger";
        $labels[11]="label-success";
        $labels[12]="label-warning";
        $labels[13]="label-info";
        $labels[14]="label-danger";
        $labels[15]="label-success";

        $query1="SHOW FULL COLUMNS FROM ".$modulo['nome_tabella'];
        $stmt1=$dbh->query($query1);
        $columns1=array();
        while ($row=$stmt1->fetch(PDO::FETCH_ASSOC)) :
            //escludo la chiave primaria
            if ($row['Key']=='PRI') continue;

            $row['tipo']=getTipoColonna($row['Type']);
            if ($row['tipo']=='ENUM') {
                $values=getEnumValues($modulo['nome_tabella'],$row['Field']);
                $row['values']=$values;
                $label=array();
                $v=0;
                foreach ($values as $val) {
                    $label[$val]=$labels[$v];
                    if ($val=='no') { $label['no']=$labels[4]; continue; }
                    if ($val=='si') { $label['si']=$labels[5]; continue; }
                    $v++;
                }
                $row['labels']=$label;
            }

            //costruisco l'array dei campi con il loro tipo
            $columns1[$row['Field']]=$row;

        endwhile;

        $header=array();
        $riga1=$righe[0];
        foreach ($riga1 as $key=>$value) {

            if (!(in_array($key,$campi_non_mostrati_in_tabella))) $header[]=$key;
        }

        ?>

        <div>
            <form>
                <table id="dynamic-table" class="table table-striped table-bordered bootstrap-datatable datatable responsive">
                    <thead>
                    <tr>
                        <th> # </th>
                        <!--<th class="center">
                            <label class="pos-rel">
                                <input type="checkbox" class="ace" />
                                <span class="lbl"></span>
                            </label>
                        </th>-->
                        
                        <?php if ($modulo['nome_modulo']=='ProspettoPrevisionale') {
                            if ($utente['id_ruolo']==3) { ?>
                                <th><?php echo _("View");?>_<?php echo _("Report");?></th>
                            <?php } else { ?>
                                <th><?php echo _("View");?>_<?php echo _("Report");?>_<?php echo _("Azioni");?></th>
                            <?php } ?>
                        <?php } else { ?>
                            <th><?php echo _("Azioni");
                            if ($modulo['allegati_possibili']=='si') {  echo "_"._("Allegati");?></th><?php } else { ?></th><?php } ?>
                        <?php } ?>

                        <?php if ($modulo['nome_modulo']=='PianoDeiConti') {

                            $query="SELECT * FROM pcs_categorie_conti ";
                            $stmt = $dbh->query($query);
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $categorieconto[$row['id']]=$row['nome'];
                            }
                            $query="SELECT * FROM pcs_centri_ricavo WHERE id_azienda=? ";
                            $stmt = $dbh->prepare($query);
                            $stmt->execute(array($_GET['p']['id_azienda']));
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $centriricavo[$row['id']]=$row['descrizione'];
                            }
                        } ?>

                        <?php foreach ($header as $h) :
                            $classhidden='';
                            if (in_array($h,$campi_hidden_xs)) { $classhidden.=" hidden-xs "; }
                            if (in_array($h,$campi_hidden_sm)) { $classhidden.=" hidden-sm "; }

                            if ($modulo['nome_modulo']=='PianoDeiConti' and $h=='TipoConto') {
                                $classhidden.=" info ";
                            }

                            if ($modulo['nome_modulo']=='PianoDeiConti' and $h=='CentroRicavo') {
                                if (count($centriricavo)>0) {
                                    $classhidden.=" info ";
                                } else {
                                    $classhidden.=" hidden ";
                                }
                            }

                            ?>
                            <th class="<?php echo $classhidden;?>"><?php echo _("$h");?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($righe as $ev) : ?>
                        <tr
                                <?php if ($ev['new']==1) { ?>
                                    style="background-color: #efefef;"
                                <?php } ?>

                                id="<?php echo $ev['ordine'];?>" idmodalele="<?php echo $ev['chiaveprimaria'];?>" idmodalmod="<?php echo $modulo['id_modulo'];?>" >
                            <td class="center"> <?php echo $ev['ordine'];?> </td>
                            <td>
                                <div class="dropdown">
                                    <div class="btn-group">
                                        <div class="btn-group" style="text-align:center;">
                                            <?php if ($modulo['nome_modulo']=='ProspettoPrevisionale') { ?>
                                                <button idmodalele="<?php echo $ev['chiaveprimaria'];?>" idmodalmod="<?php echo $modulo['id_modulo'];?>" class="aprimodal-dettagli btn btn-warning" type="button"> &nbsp;<i class=" glyphicon glyphicon-search "></i>&nbsp;</button>
                                                <?php $tokenarray['id_azienda']=$ev['id_azienda'];?>
                                                <?php $tokenarray['anno']=$ev['anno'];?>
                                                <?php $token=base64_encode(json_encode($tokenarray));?>
                                                <a class="btn btn-success" data-toggle="tooltip" title="<?php echo _('Report');?>" href="report.php?token=<?php echo $token;?>"><i class="fa fa-bar-chart"></i></a>
                                            <?php } ?>

                                            <?php if (($modulo['nome_modulo']=='ProspettoPrevisionale') and ($utente['id_ruolo']==3)) { ?>

                                            <?php } else { ?>
                                                <button class="btn dropdown-toggle" type="button" data-toggle="dropdown"> <i class=" glyphicon glyphicon-wrench "></i> <span class="caret"></span></button>
                                                <ul class="dropdown-menu">
                                                    <li class="dropdown-header"><?php echo $modulo['nome_modulo'];?></li>
                                                    <?php if ($modulo['aprimodal']=='si') { ?>
                                                        <?php if ($permessi_modulo['Can_update']=='si') { ?>
                                                            <li><a data-toggle="tooltip" title="<?php echo _('Edit');?>" class="green aprimodal-ele" idmodalmod="<?php echo $modulo['id_modulo'];?>" idmodalele="<?php echo $ev['chiaveprimaria'];?>"><i class="glyphicon glyphicon-edit"></i> MODIFICA</a></li>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if ($permessi_modulo['Can_update']=='si') { ?>
                                                            <li><a href="get_element.php?debug=<?php echo $_GET['debug'];?>&idmod=<?php echo $modulo['id_modulo'];?>&idele=<?php echo $ev['chiaveprimaria'];?>&backlist=<?php echo base64_encode($backlist);?>" data-toggle="tooltip" title="<?php echo _('Edit');?>" class="green"><i class="glyphicon glyphicon-edit"></i> MODIFICA</a></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php if ($permessi_modulo['Can_delete']=='si') { ?>
                                                        <li class="red"><a data-toggle="tooltip" title="<?php echo _('Delete');?>" class="red delete-elemento" idmodalmod="<?php echo $modulo['id_modulo'];?>" idmodalele="<?php echo $ev['chiaveprimaria'];?>" href="#" ><i class="glyphicon glyphicon-trash"></i> CANCELLA</a></li>
                                                    <?php } ?>

                                                    <?php /* if ($modulo['nome_modulo']=='ProspettoPrevisionale') { ?>
                                                    <li class="divider"></li>
                                                    <?php $tokenarray['id_azienda']=$ev['id_azienda'];?>
                                                    <?php $tokenarray['anno']=$ev['anno'];?>
                                                    <?php $token=base64_encode(json_encode($tokenarray));?>
                                                    <li><a data-toggle="tooltip" title="<?php echo _('Report');?>" href="report.php?token=<?php echo $token;?>"><i class="fa fa-bar-chart"></i> REPORT</a></li>
                                                <?php } */?>

                                                    <?php //(i) Pacchetti ?>
                                                    <?php if ($modulo['nome_modulo']=='Pacchetti') { ?>
                                                        <li class="divider"></li>
                                                        <li class="dropdown-header"><?php echo _('Elementi Pacchetto');?></li>
                                                    <?php } ?>
                                                    <?php if (($modulo['nome_modulo']=='Pacchetti') and ($permessi_modulo['Can_create']=='si')) { $tmpmod=getModulo(getModuloFrom_nome_modulo('ElementiPacchetto')); ?>
                                                        <?php if ($tmpmod['aprimodal']=='si') { ?>
                                                            <li><a data-toggle="tooltip" title="<?php echo _('Altro Elemento');?>" class="aprimodal-ele" k="id_pacchetto-<?php echo $ev['chiaveprimaria'];?>" nomemodalmod="<?php echo $tmpmod['nome_modulo'];?>" idmodalmod="<?php echo $tmpmod['id_modulo'];?>" idmodalele="-1" href="#" >NUOVO </a></li>
                                                        <?php } else { ?>
                                                            <li><a href="get_element.php?idele=-1&debug=<?php echo $_GET['debug'];?>&idmod=<?php echo $tmpmod['id_modulo'];?>&k=id_pacchetto-<?php echo $ev['chiaveprimaria'];?>" data-toggle="tooltip" title="<?php echo _('Altro Elemento');?>">NUOVO </a></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php if (($modulo['nome_modulo']=='Pacchetti')) { $tmpmod=getModulo(getModuloFrom_nome_modulo('ElementiPacchetto')); ?>
                                                        <?php
                                                        $totsedi=0;
                                                        $querysedi="SELECT count(*) as tot FROM ".$GLOBAL_tb['elementi_pacchetto']." where id_pacchetto=".$ev['chiaveprimaria'];
                                                        $stmtreg=$dbh->query($querysedi);
                                                        $rowsedi=$stmtreg->fetch(PDO::FETCH_ASSOC);
                                                        $totsedi=$rowsedi['tot'];
                                                        ?>
                                                        <?php if ($totsedi==0) { ?>
                                                            <li class="disabled"><a data-toggle="tooltip" title="<?php echo _('Elementi Pacchetto');?>" >ELENCO <span class="badge"><?php echo $totsedi;?></span></a></li>
                                                        <?php } else { ?>
                                                            <li><a data-toggle="tooltip" title="<?php echo _('Elementi Pacchetto');?>" href="module.php?modname=ElementiPacchetto&p[id_pacchetto]=<?php echo $ev['chiaveprimaria'];?>">ELENCO <span class="badge"><?php echo $totsedi;?></span></a></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php //(f) Pacchetti ?>

                                                    <?php //(i) Pacchetti Attivati?>
                                                    <?php if ($modulo['nome_modulo']=='PacchettiAttivati') { ?>
                                                        <li class="divider"></li>
                                                        <li class="dropdown-header"><?php echo _('Elementi Pacchetti Attivati');?></li>
                                                    <?php } ?>
                                                    <?php if (($modulo['nome_modulo']=='PacchettiAttivati')) { $tmpmod=getModulo(getModuloFrom_nome_modulo('ElementiPacchettoAttivati')); ?>
                                                        <?php
                                                        $totsedi=0;
                                                        $querysedi="SELECT count(*) as tot FROM ".$GLOBAL_tb['elementi_pacchetto_attivato']." where id_pacchetto_attivato=".$ev['chiaveprimaria'];
                                                        $stmtreg=$dbh->query($querysedi);
                                                        $rowsedi=$stmtreg->fetch(PDO::FETCH_ASSOC);
                                                        $totsedi=$rowsedi['tot'];
                                                        ?>
                                                        <?php if ($totsedi==0) { ?>
                                                            <li class="disabled"><a data-toggle="tooltip" title="<?php echo _('Elementi Pacchetto Attivato');?>" >ELENCO <span class="badge"><?php echo $totsedi;?></span></a></li>
                                                        <?php } else { ?>
                                                            <li><a data-toggle="tooltip" title="<?php echo _('Elementi Pacchetto Attivato');?>" href="module.php?modname=ElementiPacchettoAttivati&p[id_pacchetto_attivato]=<?php echo $ev['chiaveprimaria'];?>">ELENCO <span class="badge"><?php echo $totsedi;?></span></a></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php //(f) Pacchetti ?>


                                                    <?php //(i) Utenti?>
                                                    <li><a data-toggle="tooltip" title="<?php echo _('Invia credenziali per email');?>" class="inviacredenzialistudio" iduser="<?php echo $ev['chiaveprimaria'];?>" > <i class="fa fa-envelope"></i> INVIA </a></li>

                                                    <?php //(f) Utenti ?>



                                                    <?php //(i) ModuliGenerati ?>
                                                    <?php if ($modulo['nome_modulo']=='ModuliGenerati') { ?>
                                                        <li class="divider"></li>
                                                        <li class="dropdown-header"><?php echo _('Elementi Moduli Generati');?></li>
                                                    <?php } ?>
                                                    <?php if (($modulo['nome_modulo']=='ModuliGenerati') and ($permessi_modulo['Can_create']=='si')) { $tmpmod=getModulo(getModuloFrom_nome_modulo('ElementiModuliGenerati')); ?>
                                                        <?php if ($tmpmod['aprimodal']=='si') { ?>
                                                            <li><a data-toggle="tooltip" title="<?php echo _('Altro Elemento');?>" class="aprimodal-ele" k="id_modulo_generato-<?php echo $ev['chiaveprimaria'];?>" nomemodalmod="<?php echo $tmpmod['nome_modulo'];?>" idmodalmod="<?php echo $tmpmod['id_modulo'];?>" idmodalele="-1" href="#" >NUOVO </a></li>
                                                        <?php } else { ?>
                                                            <li><a href="get_element.php?idele=-1&debug=<?php echo $_GET['debug'];?>&idmod=<?php echo $tmpmod['id_modulo'];?>&k=id_modulo_generato-<?php echo $ev['chiaveprimaria'];?>" data-toggle="tooltip" title="<?php echo _('Altro Elemento');?>">NUOVO </a></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php if (($modulo['nome_modulo']=='ModuliGenerati')) { $tmpmod=getModulo(getModuloFrom_nome_modulo('ElementiModuliGenerati')); ?>
                                                        <?php
                                                        $totsedi=0;
                                                        $querysedi="SELECT count(*) as tot FROM ".$GLOBAL_tb['elementi_moduli_generati']." where id_pacchetto=".$ev['chiaveprimaria'];
                                                        $stmtreg=$dbh->query($querysedi);
                                                        $rowsedi=$stmtreg->fetch(PDO::FETCH_ASSOC);
                                                        $totsedi=$rowsedi['tot'];
                                                        ?>
                                                        <?php if ($totsedi==0) { ?>
                                                            <li class="disabled"><a data-toggle="tooltip" title="<?php echo _('Elementi Moduli Generati');?>" >ELENCO <span class="badge"><?php echo $totsedi;?></span></a></li>
                                                        <?php } else { ?>
                                                            <li><a data-toggle="tooltip" title="<?php echo _('Elementi Moduli Generati');?>" href="module.php?modname=ElementiModuliGenerati&p[id_modulo_generato]=<?php echo $ev['chiaveprimaria'];?>">ELENCO <span class="badge"><?php echo $totsedi;?></span></a></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php //(f) ModuliGenerati ?>

                                                    <?php
                                                    if (count($addcolumn)>0) : ?>
                                                        <li class="divider"></li>
                                                        <li class="dropdown-header"><?php echo _('Addons');?></li>
                                                        <?php foreach ($addcolumn as $ac) :
                                                            $ac['url']=str_replace("|%chiaveprimaria%|",$ev['chiaveprimaria'],$ac['url']);
                                                            ?>
                                                            <li><a href="<?php echo $ac['url'];?>"><i class=" <?php echo $ac['font-icon'];?> bigger-130"></i></a></li>
                                                            <?php
                                                        endforeach;
                                                    endif;
                                                    ?>

                                                </ul>
                                            <?php } ?>

                                        </div>
                                        <?php
                                        if ($modulo['allegati_possibili']=='si') : ?>
                                            <?php
                                            $queryAll="SELECT count(*) as tot FROM ".$GLOBAL_tb['files']." WHERE id_elem=".$ev['chiaveprimaria']." AND tb='".$modulo['nome_tabella']."'";
                                            $stmt2=$dbh->query($queryAll);
                                            $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
                                            $totAllegati=$rowAll['tot'];
                                            ?>
                                            <?php if ($totAllegati>0) { ?>
                                                <button idmodalele="<?php echo $ev['chiaveprimaria'];?>" idmodalmod="<?php echo $modulo['id_modulo'];?>" class="aprimodal-allegati btn btn-info" type="button"> <i class=" glyphicon glyphicon-paperclip "></i> <?php echo $totAllegati;?> </button>
                                            <?php } else { ?>
                                                <button idmodalele="<?php echo $ev['chiaveprimaria'];?>" idmodalmod="<?php echo $modulo['id_modulo'];?>" class="btn btn-danger" type="button"> <i class=" glyphicon glyphicon-paperclip "></i> <?php echo $totAllegati;?> </button>
                                            <?php } ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <?php foreach ($header as $h) :
                                $classhidden='';
                                if (in_array($h,$campi_hidden_xs)) { $classhidden.=" hidden-xs "; }
                                if (in_array($h,$campi_hidden_sm)) { $classhidden.=" hidden-sm "; }
                                if ($modulo['nome_modulo']=='PianoDeiConti' and $h=='TipoConto') {
                                    if ($ev['categoria']=='1') {
                                        $classhidden.=" success ";
                                    }
                                    if ($ev['categoria']=='2') {
                                        $classhidden.=" warning ";
                                    }
                                    if ($ev['categoria']=='3') {
                                        $classhidden.=" danger ";
                                    }
                                }
                                if ($modulo['nome_modulo']=='PianoDeiConti' and $h=='CentroRicavo') {
                                    if (count($centriricavo)>0) {
                                    } else {
                                        $classhidden.=" hidden ";
                                    }
                                }
                                ?>
                                <td class="<?php echo $classhidden;?>">
                                    <?php //se è un campo enum mettiamo delle labels
                                    if (($columns1[$h]['tipo'])=="ENUM") {

                                        ?>
                                            <span class="label <?php echo $columns1[$h]['labels'][$ev[$h]];?> "><?php echo $ev[$h];?></span>
                                    <?php
                                    } else {
                                        echo $ev[$h];
                                    }
                                    ?>

                                    <?php //facciamo edit inline dei codici conto ma solo dei conti padre!!!?>
                                    <?php if ($h=="TipoConto" and $modulo['nome_modulo']=='PianoDeiConti' and $utente['id_ruolo']==4) {
                                        $pippo=str_replace("|","_",$ev['chiaveprimaria']);

                                        ?>
                                        <div class="form-group" id="formgroup_<?php echo $pippo;?>">
                                            <?php if ($ev['livello2']=='') { ?>
                                            <select id="select_<?php echo $pippo;?>" nomecampo="categoria" codicegruppo="<?php echo $pippo;?>" idConto="<?php echo $ev['chiaveprimaria'];?>" class="pianocontiaggiornaonline form-control" name="<?php echo $chiaveprimaria;?>|categoria" >
                                                <?php foreach ($categorieconto as $cat=>$value) : ?>
                                                    <option <?php if ($ev['categoria']==$cat) {  echo "selected"; } ?> value="<?php echo $cat;?>"><?php echo $value;?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php } ?>
                                        </div>
                                    <?php }?>



                                    <?php if ($h=="CentroRicavo" and $modulo['nome_modulo']=='PianoDeiConti' and (count($centriricavo)>0)) {
                                        $pippo=str_replace("|","_",$ev['chiaveprimaria']);

                                        ?>
                                        <div class="form-group" id="formgroup_<?php echo $pippo;?>">

                                            <select id="select_cr_<?php echo $pippo;?>" nomecampo="centro_ricavo" codicegruppo="<?php echo $pippo;?>" idConto="<?php echo $ev['chiaveprimaria'];?>" class="pianocontiaggiornaonlinecentroricavo form-control" name="<?php echo $chiaveprimaria;?>|centroricavo" >
                                                <option value="">-----scegli-----</option>
                                                <?php foreach ($centriricavo as $cat=>$value) : ?>
                                                    <option <?php if ($ev['centro_ricavo']==$cat) {  echo "selected"; } ?> value="<?php echo $cat;?>"><?php echo $value;?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php }?>
                                </td>
                            <?php endforeach; ?>

                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>

    <?php
    else:

        echo _("Nessun elemento presente!");

    endif;
endif; ?>
<?php /* (f) ------------------------------ elenco del singolo modulo --------------------------------------------------------------------------------------   */ ?>


<script src="ckeditor/ckeditor.js"></script>
<script src="plupload/js/plupload.full.min.js"></script>

<script type="text/javascript">
    jQuery(function($) {

//additional functions for data table
        var editor;

        $('[data-rel=tooltip]').tooltip();

        $('.pianocontiaggiornaonline').on('change', function (e) {
            var questo=$(this).attr("id");
            var optionSelected = $("option:selected", this);
            var valueSelected = this.value;
            var idConto = $(this).attr("idConto");
            var codicegruppo = $(this).attr("codicegruppo");
            var nomecampo = $(this).attr("nomecampo");
            var formgroup = "formgroup_"+codicegruppo;
            $("#"+formgroup).removeClass("has-success");
            $("#"+formgroup).removeClass("has-error");
            $("#"+questo).css("color","#cccccc");

            var params={};
            params.idConto=idConto;
            params.nomecampo=nomecampo;
            params.valorecampo=valueSelected;
            //console.log(params);
            $.ajax({
                dataType: "json",
                type: 'POST',
                url: "ajax_modificaCategoriaContoInline.php",
                data: jQuery.param(params),
                success: function (data) {
                    if (data.res==true) {
                        //alert("Tutto ok!");
                        $("#"+formgroup).addClass("has-success");
                        $("#"+questo).css("color","green");
                        setTimeout(function(){location.reload();}, 10);
                    } else {
                        $("#"+formgroup).addClass("has-error");
                        $("#"+questo).css("color","red");
                        alert(data.msg);
                    }
                }
            });

        });

        $('.pianocontiaggiornaonlinecentroricavo').on('change', function (e) {
            var questo=$(this).attr("id");
            var optionSelected = $("option:selected", this);
            var valueSelected = this.value;
            var idConto = $(this).attr("idConto");
            var codicegruppo = $(this).attr("codicegruppo");
            var nomecampo = $(this).attr("nomecampo");
            var formgroup = "formgroup_"+codicegruppo;
            $("#"+formgroup).removeClass("has-success");
            $("#"+formgroup).removeClass("has-error");
            $("#"+questo).css("color","#cccccc");

            var params={};
            params.idConto=idConto;
            params.nomecampo=nomecampo;
            params.valorecampo=valueSelected;
            console.log(params);
            $.ajax({
                dataType: "json",
                type: 'POST',
                url: "ajax_modificaCentroricavoContoInline.php",
                data: jQuery.param(params),
                success: function (data) {
                    if (data.res==true) {
                        //alert("Tutto ok!");
                        $("#"+formgroup).addClass("has-success");
                        $("#"+questo).css("color","green");
                        setTimeout(function(){location.reload();}, 10);
                    } else {
                        $("#"+formgroup).addClass("has-error");
                        $("#"+questo).css("color","red");
                        alert(data.msg);
                    }
                }
            });

        });

        $(document).on("click",".inviacredenzialistudio",function(){
            var iduser=$(this).attr("iduser");
            //alert("iduser="+iduser);
            var params={};
            params.iduser=iduser;
            $.ajax({
                type: "POST",
                url: "ajax_invia_credenziali_studio.php",
                data: params,
                dataType: 'json',
                success: function(data){
                    console.log(data);
                    if (data.result==true) {
                        $.notify(data.msg,'success');
                        setTimeout(function(){location.reload();}, 2000);
                    } else {
                        $.notify(data.error);
                        console.log(data);
                    }
                },
                error: function(data) {
                    console.log(data);
                }
            });

        });




        //dettagli modal
        $(document).on("click",".aprimodal-dettagli",function(){
            var idele=$(this).attr("idmodalele");
            var idmod=$(this).attr("idmodalmod");
            $("#ModalDetails").modal({show:true});

            $('#modal-body-ModalDetails').load('ajax_getdettaglioletturapolizza.php?'+ $.param({
                    backurl: 'https://<?php echo $_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];?>',
                    idmod: idmod,
                    idele: idele}),function(result){
            }); //fine $('#modal-body-ModalDettagli').load('ajax_getdettaglioletturapolizza.php?'+ $.param({
        });

        //allegati modal
        $(document).on("click",".aprimodal-allegati",function(){
            var idele=$(this).attr("idmodalele");
            var idmod=$(this).attr("idmodalmod");
            var view=$(this).attr("view");
           $("#ModalAllegati").modal({show:true});

            $('#modal-body-ModalAllegati').load('ajax_getallegati.php?'+ $.param({
                    backurl: 'http://<?php echo $_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];?>',
                    idmod: idmod,
                    view: view,
                    idele: idele}),function(result){
                //a questo punto il form dinamico è caricato!
                var ck=0;
                var editor=new Array;
                //attivo CKEDITOR su tutte le textarea di classe ckeditortextarea
                //e metto in ascolto l'evento onchange così da tenere sempre aggiornato il campo del form di riferimento da mandare in POST
                $(".ckeditortextarea").each(function(){
                    var nometextarea=$(this).attr("id");
                    editor[ck]=CKEDITOR.replace( nometextarea );
                    editor[ck].on('change', function( evt ) {
                        var data=evt.editor.getData();
                        var elemento=evt.editor.element;
                        var idelemento=elemento.getId();
                        $("#"+idelemento).text(data);
                        //alert(elemento.getId());
                        //alert(data);
                    });
                    ck++;
                });

            }); //fine $('#modal-body-ModalAllegati').load('ajax_getallegati.php?'+ $.param({
        });


        $( document ).on( "click", ".aprimodal-ele", function() {
            var idele=$(this).attr("idmodalele");
            var idmod=$(this).attr("idmodalmod");
            var view=$(this).attr("view");
            var k='';
            var k1='';
            var k2='';
            var k=$(this).attr("k");
            var k1=$(this).attr("k1");
            var k2=$(this).attr("k2");
            var nomemodalmod=$(this).attr("nomemodalmod");
            if (nomemodalmod != '') {
                $("#nomemodal").text(nomemodalmod);
            }
            $('#myModal').modal({show:true});

            $('#modal-body-myModal').load('ajax_getmodulo.php?'+ $.param({
                backurl: 'http://<?php echo $_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];?>',
                idmod: idmod,
                view: view,
                k: k,
                k1: k1,
                k2: k2,
                <?php if ($_REQUEST['debug']) { ?>
                debug: <?php echo $_REQUEST['debug'];?>,
                <?php } ?>
                idele: idele}),function(result){
                //a questo punto il form dinamico è caricato!
                var ck=0;
                var editor=new Array;
                //attivo CKEDITOR su tutte le textarea di classe ckeditortextarea
                //e metto in ascolto l'evento onchange così da tenere sempre aggiornato il campo del form di riferimento da mandare in POST
                $(".ckeditortextarea").each(function(){
                    var nometextarea=$(this).attr("id");
                    editor[ck]=CKEDITOR.replace( nometextarea );
                    editor[ck].on('change', function( evt ) {
                        var data=evt.editor.getData();
                        var elemento=evt.editor.element;
                        var idelemento=elemento.getId();
                        $("#"+idelemento).text(data);
                        //alert(elemento.getId());
                        //alert(data);
                    });
                    ck++;
                });
                <?php /* da vedere ?>
                $('#nuovo_elemento').formValidation('resetForm', true);

                $('#nuovo_elemento').formValidation({
                    framework: 'bootstrap',
                    excluded: [':disabled'],
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        social: {
                            validators: {
                                notEmpty: {
                                    message: 'Social is required'
                                }
                            }
                        }
                    }
                });
                <?php */ ?>

            }); //fine $('#modal-body-myModal').load('ajax_getmodulo.php?'+ $.param({

        }); // fine $( document ).on( "click", ".aprimodal-ele", function() {

        $('#myModal').on('shown.bs.modal', function(e){

            $("#nuovo_elemento_save").click(function(){

                //devo riattivare i campi enum messi in readonly
                $('input, select').attr('disabled', false);
                $.post("ajax_modifica_elemento.php", $("#nuovo_elemento").serialize(), function(msg){$("#messaggiovalidazione").html(msg);});
            });

            $("#nuovo_elemento_close").click(function(e){
                e.preventDefault();
                //window.location.href = $("#backurl").val();
                $("#myModal").modal('hide');
            });

            $("#nuovo_elemento_rimani").click(function(){

                //devo riattivare i campi enum messi in readonly
                $('input, select').attr('disabled', false);
                $.post("ajax_modifica_elemento.php?rimani=1", $("#nuovo_elemento").serialize(), function(msg){$("#messaggiovalidazione").html(msg);} );
            });

        });

        $(".delete-elemento").click(function(){
            var idele=$(this).attr("idmodalele");
            var idmod=$(this).attr("idmodalmod");
            bootbox.confirm("<?php echo _('Sicuro di voler eliminare questo elemento?');?>", function(result) {
                if (result) {
                    $.post("ajax_delete_elemento.php", { idmod: idmod, idele: idele } , function(msg){$("#responso").html(msg);} );
                    setTimeout(function(){location.reload();}, 2000);
                }
            });
        });

        $("#eliminaPianoDeiConti").click(function(){
            var idazienda=$(this).attr("idazienda");
            bootbox.confirm("<?php echo _('Sicuro di voler eliminare il piano dei conti?');?>", function(result) {
                if (result) {
                    $.post("ajax_delete_piano_dei_conti.php", { idazienda: idazienda } , function(msg){$("#responso").html(msg);} );
                    setTimeout(function(){location.reload();}, 2000);
                }
            });
        });

        $("#accettaNuoviConti").click(function(){
            var idazienda=$(this).attr("idazienda");
                    $.post("accettanuoviconti.php", { idazienda: idazienda } , function(msg){$.notify(data.msg,'success');$("#responso").html(msg);} );
            setTimeout(function(){location.reload();}, 1000);
        });

    <?php if ($numero_records>0) : ?>

        <?php //print_r($columns1);?>
        <?php //print_r($header);?>

        //initiate dataTables plugin

        /* Add events */
        var table=$('.datatable')
            //.wrap("<div class='dataTables_borderWrap' />")   //if you are applying horizontal scrolling (sScrollX)
            .dataTable( {
                columnDefs: [ {
                    orderable: false,
                    className: 'select-checkbox',
                    targets:   0
                } ],
                select: {
                    style:    'os',
                    selector: 'td:first-child'
                },
                "sPaginationType": "bootstrap",
                "bAutoWidth": false,
                "bStateSave": true,
                "aoColumns": [
                    { "mData": "RowOrder", "bVisible": false },
                    { "bSortable": false },
                    <?php foreach ($header as $h) {
                        $nullcol[]='null';
                     ?>
                    <?php } ?>
                    <?php echo join(",",$nullcol);?>
                ],
                "aaSorting": [],
                "lengthMenu": [[10, 50, 100, -1], [10, 50, 100, "Tutti"]],
                "iDisplayLength": 50,
            } );

        function fnGetSelected( oTableLocal )
        {
            return oTableLocal.$('tr.row_selected');
        }

        $('#deleteSelected').click( function(e) {
            e.preventDefault();
            var anSelected = fnGetSelected( table );
            $(anSelected).remove();
        } );


        $.fn.dataTableExt.oApi.fnPagingInfo = function (oSettings) {
            return {
                "iStart": oSettings._iDisplayStart,
                "iEnd": oSettings.fnDisplayEnd(),
                "iLength": oSettings._iDisplayLength,
                "iTotal": oSettings.fnRecordsTotal(),
                "iFilteredTotal": oSettings.fnRecordsDisplay(),
                "iPage": Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
                "iTotalPages": Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
            };
        }
        $.extend($.fn.dataTableExt.oPagination, {
            "bootstrap": {
                "fnInit": function (oSettings, nPaging, fnDraw) {
                    var oLang = oSettings.oLanguage.oPaginate;
                    var fnClickHandler = function (e) {
                        e.preventDefault();
                        if (oSettings.oApi._fnPageChange(oSettings, e.data.action)) {
                            fnDraw(oSettings);
                        }
                    };

                    $(nPaging).addClass('pagination').append(
                        '<ul class="pagination">' +
                        '<li class="prev disabled"><a href="#">&larr; ' + oLang.sPrevious + '</a></li>' +
                        '<li class="next disabled"><a href="#">' + oLang.sNext + ' &rarr; </a></li>' +
                        '</ul>'
                    );
                    var els = $('a', nPaging);
                    $(els[0]).bind('click.DT', { action: "previous" }, fnClickHandler);
                    $(els[1]).bind('click.DT', { action: "next" }, fnClickHandler);
                },

                "fnUpdate": function (oSettings, fnDraw) {
                    var iListLength = 5;
                    var oPaging = oSettings.oInstance.fnPagingInfo();
                    var an = oSettings.aanFeatures.p;
                    var i, j, sClass, iStart, iEnd, iHalf = Math.floor(iListLength / 2);

                    if (oPaging.iTotalPages < iListLength) {
                        iStart = 1;
                        iEnd = oPaging.iTotalPages;
                    }
                    else if (oPaging.iPage <= iHalf) {
                        iStart = 1;
                        iEnd = iListLength;
                    } else if (oPaging.iPage >= (oPaging.iTotalPages - iHalf)) {
                        iStart = oPaging.iTotalPages - iListLength + 1;
                        iEnd = oPaging.iTotalPages;
                    } else {
                        iStart = oPaging.iPage - iHalf + 1;
                        iEnd = iStart + iListLength - 1;
                    }

                    for (i = 0, iLen = an.length; i < iLen; i++) {
                        // remove the middle elements
                        $('li:gt(0)', an[i]).filter(':not(:last)').remove();

                        // add the new list items and their event handlers
                        for (j = iStart; j <= iEnd; j++) {
                            sClass = (j == oPaging.iPage + 1) ? 'class="active"' : '';
                            $('<li ' + sClass + '><a href="#">' + j + '</a></li>')
                                .insertBefore($('li:last', an[i])[0])
                                .bind('click', function (e) {
                                    e.preventDefault();
                                    oSettings._iDisplayStart = (parseInt($('a', this).text(), 10) - 1) * oPaging.iLength;
                                    fnDraw(oSettings);
                                });
                        }

                        // add / remove disabled classes from the static elements
                        if (oPaging.iPage === 0) {
                            $('li:first', an[i]).addClass('disabled');
                        } else {
                            $('li:first', an[i]).removeClass('disabled');
                        }

                        if (oPaging.iPage === oPaging.iTotalPages - 1 || oPaging.iTotalPages === 0) {
                            $('li:last', an[i]).addClass('disabled');
                        } else {
                            $('li:last', an[i]).removeClass('disabled');
                        }
                    }
                }
            }
        });

        <?php endif; ?>


        /********************************/
        //add tooltip for small view action buttons in dropdown menu
        $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});

        //tooltip placement on right or left
        function tooltip_placement(context, source) {
            var $source = $(source);
            var $parent = $source.closest('table')
            var off1 = $parent.offset();
            var w1 = $parent.width();

            var off2 = $source.offset();
            //var w2 = $source.width();

            if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
            return 'left';
        }







    })
</script>
<!-- (f) inline scripts related to this page -->
        <hr>
        <?php include("INC_90_FOOTER.php");?>
    </div> <!-- /container -->