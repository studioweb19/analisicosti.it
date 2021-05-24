<?php if ($_REQUEST['modname']!='') { ?>
    <?php $_REQUEST['idmod']=getModuloFrom_nome_modulo($_REQUEST['modname']); ?>
<?php } ?>
<?php

$backlist=$_REQUEST['backlist'];

$idmodGE=$_REQUEST['idmod'];
$idele=$_REQUEST['idele'];
$k=$_REQUEST['k'];
$k1=$_REQUEST['k1'];
$k2=$_REQUEST['k2'];
$view=$_REQUEST['view'];
?>
<?php if ($idmodGE>0) { ?>
    <?php $modulo=getModulo($idmodGE);?>
<?php } ?>
<div class="container" id="firstcontainer">
    <?php include("mostrainfo.php");?>
    <!-- Example row of columns -->
    <?php //echo base64_decode($_REQUEST['backlist']);?>
    <div class="row">
        <div class="col-md-12">
            <div class="box-inner">
                <div class="box-header" data-original-title="">
                    <h2><?php echo _($modulo['nome_modulo']);?> </h2>

                </div>
                <div class="box-content">
                    <?php /* (i) ------------------------------ elenco del singolo modulo --------------------------------------------------------------------------------------   */ ?>
                    <div id="singoloelemento" >
                        LOADING...
                    </div>

                    <?php
                    //--------------------------------------------------------------------------------------------------------------------
                    //-------------------------------(i) CARICAMENTO DATI ----------------------------------------------------------------
                    //--------------------------------------------------------------------------------------------------------------------
                    if ($modulo['nome_modulo']=="CaricamentoDati") : ?>
                    <?php
                    $queryAll="SELECT file FROM ".$GLOBAL_tb['files']." WHERE id_elem=".$idele." AND tb='".$modulo['nome_tabella']."'";
                    $stmt2=$dbh->query($queryAll);
                    $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
                    $nomefile=$rowAll['file'];


                    if ($idele>0) {
                    $elementoTMP=getElemento($modulo['id_modulo'],$idele);
                    $pianocontiok='';
                        $continuovi=0;

                        $queryAll="SELECT count(*) as continuovi FROM ".$GLOBAL_tb['pianodeiconti']." WHERE new=1 AND id_azienda=".$elementoTMP['id_azienda'];
                        $stmt2=$dbh->query($queryAll);
                        $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
                        $continuovi=$rowAll['continuovi'];


                        $queryAll="SELECT * FROM ".$GLOBAL_tb['pianodeiconti']." WHERE id_azienda=".$elementoTMP['id_azienda'];
                    $stmt2=$dbh->query($queryAll);
                    $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
                    $pianocontiok=$rowAll['codiceconto'];


                    $campiricavi=0;
                    $queryAll="SELECT count(*) as totricavi FROM ".$GLOBAL_tb['pianodeiconti']." WHERE id_azienda=".$elementoTMP['id_azienda']." group by categoria having categoria=1";
                    $stmt2=$dbh->query($queryAll);
                    $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
                    $campiricavi=$rowAll['totricavi'];

                    $pianocontiaggiornato='';
                    $analisicostiaggiornata='';
                    $queryAll="SELECT * FROM ".$GLOBAL_tb['caricamento_dati']." WHERE anno=? AND mese=? AND id_azienda=".$elementoTMP['id_azienda'];
                    $stmt2=$dbh->prepare($queryAll);
                    $stmt2->execute(array($elementoTMP['anno'],$elementoTMP['mese']));
                    $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
                    $pianocontiaggiornato=$rowAll['pianoconti_aggiornato']=='si' && $pianocontiok;
                    $analisicostiaggiornata=$rowAll['analisicosti_aggiornata']=='si' && $pianocontiok;

                    $prospettoprevisionalepresente='';
                    $queryAll="SELECT * FROM ".$GLOBAL_tb['prospetto_previsionale']." WHERE anno=? AND id_azienda=".$elementoTMP['id_azienda'];
                    $stmt2=$dbh->prepare($queryAll);
                    $stmt2->execute(array($elementoTMP['anno']));
                    $rowAll=$stmt2->fetch(PDO::FETCH_ASSOC);
                    if ($rowAll['id']>0) {
                    $prospettoprevisionalepresente='si';
                    }

                    //wizard (tipo)

                        /*
                    echo "nomefile:".$nomefile;
                    echo "pianocontiok:".$pianocontiok;
                    echo "campiricavi:".$campiricavi;
                    echo "pianocontiaggiornato:".$pianocontiaggiornato;
                    echo "prospettoprevisionalepresente:".$prospettoprevisionalepresente;
                    echo "analisicostiaggiornata:".$analisicostiaggiornata;

                        */

                        ?>

                        <div class="row border-dashed">
                            <div class="col-xs-12 col-sm-6 col-md-4 vertical-space">
                                <?php if ($nomefile=='') { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled data-toggle="toggle"> <?php echo _("Allegato Caricato");?>
                                    </label>
                                <?php } else { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled checked data-toggle="toggle"> <?php echo _("Allegato Caricato");?>
                                    </label>
                                <?php } ?>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4 vertical-space">
                                <?php if ($pianocontiok=='') { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled data-toggle="toggle"> <?php echo _("Piano Conti Creato");?>
                                    </label>
                                <?php } else { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled checked data-toggle="toggle"> <?php echo _("Piano Conti Creato");?>
                                    </label>
                                <?php } ?>
                            </div>


                            <!-- per i ricavi, non è obbligatorio per andare avanti, ma è bene lasciare il check -->

                            <div class="col-xs-12 col-sm-6 col-md-4 vertical-space">
                                <?php if ($campiricavi==0) { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled data-toggle="toggle"> <?php echo _("Conto ricavo inserito");?>
                                    </label>
                                <?php } else { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled checked data-toggle="toggle"> <?php echo _("Conto ricavo inserito");?>
                                    </label>
                                <?php } ?>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4 vertical-space">
                                <?php if ($prospettoprevisionalepresente!='si') { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled data-toggle="toggle"> <?php echo _("Prospetto previsionale creato");?>
                                    </label>
                                <?php } else { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled checked data-toggle="toggle"> <?php echo _("Prospetto previsionale creato");?>
                                    </label>
                                <?php } ?>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4 vertical-space">
                                <?php if ($pianocontiaggiornato!='si') { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled data-toggle="toggle"> <?php echo _("Piano Conti aggiornato");?>
                                    </label>
                                <?php } else { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled checked data-toggle="toggle"> <?php echo _("Piano Conti aggiornato");?>
                                    </label>
                                <?php } ?>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4 vertical-space">
                                <?php if ($analisicostiaggiornata!='si') { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled data-toggle="toggle"> <?php echo _("Analisi costi aggiornata");?>
                                    </label>
                                <?php } else { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" data-onstyle="success" data-offstyle="danger" data-on="<?php echo _("si");?>" data-off="<?php echo _("no");?>" disabled checked data-toggle="toggle"> <?php echo _("Analisi costi aggiornata");?>
                                    </label>
                                <?php } ?>
                            </div>

                        </div>




                        <?php
                    } ?>

                    <?php endif; ?>

                    <!-- (i) bottoni -->
                    <div id="bottoni" class="visualizzadopo" style="display:none;margin-top:10px;">
                        <?php if ($_GET['view']==1) { ?> <?php } else { ?>
                        <button class="btn btn-sm btn-warning btn-save" id="nuovo_elemento_reload" name="nuovo_elemento_reload" >
                            <i class="glyphicon glyphicon-save"></i>
                            <?php echo _("Salva e continua");?>
                        </button>
                        <button class="btn btn-sm btn-info btn-save" id="nuovo_elemento_back" name="nuovo_elemento_back" >
                            <i class="glyphicon glyphicon-repeat"></i>
                            <?php echo _("Salva ed esci");?>
                        </button>
                        <?php } ?>
                        <button class="btn btn-sm btn-danger btn-save" data-anno="<?php echo $elementoTMP['anno'];?>" data-idazienda="<?php echo $elementoTMP['id_azienda'];?>" id="nuovo_elemento_close" name="nuovo_elemento_close" >
                                <i class="glyphicon glyphicon-list"></i>
                                <?php echo _("Indietro");?>
                            </button>

                        <?php
                        //--------------------------------------------------------------------------------------------------------------------
                        //-------------------------------(i) CARICAMENTO DATI ----------------------------------------------------------------
                        //--------------------------------------------------------------------------------------------------------------------
                        if ($modulo['nome_modulo']=="CaricamentoDati") : ?>

                            <?php /* se ancora non esiste il piano dei conti, devo fare comparire solo il pulsante Genera Piano Dei Conti */ ?>

                            <?php

                            if ($nomefile!='' and $idele>0) :

                                $urltmp=$_SERVER['REQUEST_URI'];

                                $backlist2=base64_encode($urltmp);
                                if ($pianocontiok=='') { ?>
                                    <button class="btn btn-sm btn-danger btn-save pianoconti" data-anno="<?php echo $elementoTMP['anno'];?>" data-rigenera="1" data-mese="<?php echo $elementoTMP['mese'];?>" data-idazienda="<?php echo $elementoTMP['id_azienda'];?>" data-file="<?php echo $nomefile;?>" id="rigenerapianoconti" name="rigenerapianoconti" >
                                        <i class="glyphicon glyphicon-repeat"></i>
                                        <?php echo _("Genera Piano Dei Conti");?>
                                    </button>
                                <?php } else {

                                        //togliamo il ricavo obbligatorio, lasciamo solo il check
                                    /*
                                         if ($campiricavi==0 or $continuovi>0) {
                                             if ($continuovi>0) { ?>
                                                 <a
                                                         href="module.php?modname=PianoDeiConti&p[id_azienda]=<?php echo $elementoTMP['id_azienda'];?>&backlist=<?php echo $backlist2;?>"
                                                         class="btn btn-sm btn-info btn-save" >
                                                     <i class="glyphicon glyphicon-euro"></i> <?php echo _("Gestisci i nuovi conti inseriti");?>
                                                 </a>
                                             <?php } else { ?>
                                                 <a
                                                         href="module.php?modname=PianoDeiConti&p[id_azienda]=<?php echo $elementoTMP['id_azienda'];?>&backlist=<?php echo $backlist2;?>"
                                                         class="btn btn-sm btn-info btn-save" >
                                                     <i class="glyphicon glyphicon-euro"></i> <?php echo _("Definisci almeno un ricavo");?>
                                                 </a>
                                             <?php }
                                    */

                                        if ($continuovi>0) { ?>
                                            <a
                                                    href="module.php?modname=PianoDeiConti&p[id_azienda]=<?php echo $elementoTMP['id_azienda'];?>&backlist=<?php echo $backlist2;?>"
                                                    class="btn btn-sm btn-info btn-save" >
                                                <i class="glyphicon glyphicon-euro"></i> <?php echo _("Gestisci i nuovi conti inseriti");?>
                                            </a>
                                            <?php
                                    } else if ($prospettoprevisionalepresente!='si') {  ?>

                                        <?php
                                        $k='id_azienda-'.$elementoTMP['id_azienda'];
                                             $k1="anno-".$elementoTMP['anno'];
                                             $k2="mese_iniziale-".$elementoTMP['mese'];
                                         ?>
                                        <a
                                                href="get_element.php?k1=<?php echo $k1;?>&k2=<?php echo $k2;?>&k=<?php echo $k;?>&idmod=53&idele=-1&backlist=<?php echo $backlist2;?>"
                                                class="btn btn-sm btn-info btn-save" >
                                            <i class="glyphicon glyphicon-euro"></i> <?php echo _("Crea Prospetto Previsionale");?>
                                        </a>


                                         <?php
                                         } else if ($pianocontiaggiornato!='si') {  ?>

                                             <button class="btn btn-sm btn-primary btn-save pianoconti" data-anno="<?php echo $elementoTMP['anno'];?>" data-rigenera="0" data-mese="<?php echo $elementoTMP['mese'];?>" data-idazienda="<?php echo $elementoTMP['id_azienda'];?>" data-file="<?php echo $nomefile;?>" id="aggiornapianoconti" name="aggiornapianoconti" >
                                                 <i class="glyphicon glyphicon-repeat"></i>
                                                 <?php echo _("Aggiorna Piano Dei Conti");?>
                                             </button>

                                    <?php } else if ($analisicostiaggiornata!='si') { ?>
                                        <button class="btn btn-sm btn-success btn-save" data-idCaricamento="<?php echo $idele;?>" data-anno="<?php echo $elementoTMP['anno'];?>" data-mese="<?php echo $elementoTMP['mese'];?>" data-idazienda="<?php echo $elementoTMP['id_azienda'];?>" data-file="<?php echo $nomefile;?>" id="aggiornaanalisicosti" name="aggiornaanalisicosti" >
                                            <i class="glyphicon glyphicon-repeat"></i>
                                            <?php echo _("Aggiorna Analisi Costi");?>
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn btn-sm btn-primary btn-save pianoconti" data-anno="<?php echo $elementoTMP['anno'];?>" data-rigenera="0" data-mese="<?php echo $elementoTMP['mese'];?>" data-idazienda="<?php echo $elementoTMP['id_azienda'];?>" data-file="<?php echo $nomefile;?>" id="aggiornapianoconti" name="aggiornapianoconti" >
                                            <i class="glyphicon glyphicon-repeat"></i>
                                            <?php echo _("Aggiorna Piano Dei Conti");?>
                                        </button>
                                        <button class="btn btn-sm btn-success btn-save" data-idCaricamento="<?php echo $idele;?>" data-anno="<?php echo $elementoTMP['anno'];?>" data-mese="<?php echo $elementoTMP['mese'];?>" data-idazienda="<?php echo $elementoTMP['id_azienda'];?>" data-file="<?php echo $nomefile;?>" id="aggiornaanalisicosti" name="aggiornaanalisicosti" >
                                            <i class="glyphicon glyphicon-repeat"></i>
                                            <?php echo _("Aggiorna Analisi Costi");?>
                                        </button>
                                        <?php
                                        $k='id_azienda-'.$elementoTMP['id_azienda'];
                                        //anche anno e mese consecutivo
                                        $query="SELECT * FROM pcs_caricamento_dati WHERE id_azienda=? order by anno DESC,mese DESC LIMIT 0,1";
                                        $stmt=$dbh->prepare($query);
                                        $stmt->execute(array($elementoTMP['id_azienda']));
                                        $ULTIMO=$stmt->fetch(PDO::FETCH_ASSOC);
                                        if ($ULTIMO['mese']=='12') {
                                            $nuovoanno=$ULTIMO['anno']+1;
                                            $nuovomese="01";
                                        } else {
                                            $nuovoanno=$ULTIMO['anno'];
                                            $nuovomese=sprintf("%02d",intval($ULTIMO['mese'])+1);
                                        }
                                        $k1="anno-".$nuovoanno;
                                        $k2="mese-".$nuovomese; ?>
                                        <a
                                                href="get_element.php?k1=<?php echo $k1;?>&k2=<?php echo $k2;?>&k=<?php echo $k;?>&idmod=55&idele=-1&backlist=<?php echo $backlist;?>"
                                                class="btn btn-sm btn-info btn-save" >
                                            <i class="glyphicon glyphicon-forward"></i> <?php echo _("Inserisci mese successivo");?>
                                        </a>

                                         <?php if ($prospettoprevisionalepresente!='si') {  ?>

                                        <?php
                                        $k='id_azienda-'.$elementoTMP['id_azienda'];
                                             $k1="anno-".$elementoTMP['anno'];
                                             $k2="mese_iniziale-".$elementoTMP['mese'];
                                         ?>
                                        <a
                                                href="get_element.php?k1=<?php echo $k1;?>&k2=<?php echo $k2;?>&k=<?php echo $k;?>&idmod=53&idele=-1&backlist=<?php echo $backlist2;?>"
                                                class="btn btn-sm btn-info btn-save" >
                                            <i class="glyphicon glyphicon-euro"></i> <?php echo _("Crea Prospetto Previsionale");?>
                                        </a>


                                         <?php } ?>



                                    <?php } //else camporicavi==0 ?>

                                <?php } ?>

                            <?php endif; //nomefile != ''?>

                        <?php endif; //caricamento dati
                        //--------------------------------------------------------------------------------------------------------------------
                        //-------------------------------(f) CARICAMENTO DATI ----------------------------------------------------------------
                        //--------------------------------------------------------------------------------------------------------------------

                        //--------------------------------------------------------------------------------------------------------------------
                        //-------------------------------(i) PROSPETTO PREVISIONALE ----------------------------------------------------------
                        //--------------------------------------------------------------------------------------------------------------------
                        if ($modulo['nome_modulo']=="ProspettoPrevisionale") :

                            $elementoTMP=getElemento($modulo['id_modulo'],$idele);

                            $queryPP="SELECT * FROM pcs_analisi_costi WHERE anno=? and mese='12' AND id_azienda=?";
                            $stmtPP=$dbh->prepare($queryPP);
                            $stmtPP->execute(array($elementoTMP['anno']-1,$elementoTMP['id_azienda']));
                            if ($rowPP=$stmtPP->fetch(PDO::FETCH_ASSOC)) {
                                $totalericavi=$rowPP['ricavi_totali'];
                                $totaleacquisti=$rowPP['acquisti_totali'];
                                ?>
                                <button class="btn btn-sm btn-success btn-save" data-totaleacquisti="<?php echo $totaleacquisti;?>" data-totalericavi="<?php echo $totalericavi;?>" id="aggiornaincidenzaacquisti" name="aggiornaincidenzaacquisti" >
                                    <i class="glyphicon glyphicon-euro"></i>
                                    <?php echo _("Suggerisci Incidenza Acquisti");?>
                                </button>
                            <?php }?>
                        <?php endif;
                        //--------------------------------------------------------------------------------------------------------------------
                        //-------------------------------(f) PROSPETTO PREVISIONALE ----------------------------------------------------------
                        //--------------------------------------------------------------------------------------------------------------------

                        if ($modulo['nome_modulo']=="CaricamentoDati") :

                        endif;
                        ?>

                    </div>

                    <?php /* (f) ------------------------------ elenco del singolo modulo --------------------------------------------------------------------------------------   */ ?>

                </div>
                <!-- (f) bottoni -->
            </div>
        </div>
    </div><!--/row-->
    <!-- (i) inline scripts related to this page -->


    <script src="ckeditor/ckeditor.js"></script>
    <script src="plupload/js/plupload.full.min.js"></script>
    <script type="text/javascript">

        jQuery(function($) {

            var idele="<?php echo $idele;?>";
            var idmod=<?php echo $idmodGE;?>;

            function validateEmail(email) {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            }

            function checkCampiObbligatori() {
                <?php if ($modulo['id_modulo']==9) : ?>
                var tuttook=true;
                var errori='';
                    if ($("#RagioneSociale").val()=='') {
                        tuttook=false;
                        $("#DIV_RagioneSociale").addClass("has-error");
                        $("#DIV_RagioneSociale").removeClass("has-success");
                        errori+="Ragione sociale obbligatoria\r\n";

                    } else {
                        $("#DIV_RagioneSociale").removeClass("has-error");
                        $("#DIV_RagioneSociale").addClass("has-success");

                    }

                    if (!(validateEmail($("#Mail").val()))) {
                        tuttook=false;
                        $("#DIV_Mail").addClass("has-error");
                        $("#DIV_Mail").removeClass("has-success");
                        errori+="Email non valida\r\n";

                    } else {
                        $("#DIV_Mail").removeClass("has-error");
                        $("#DIV_Mail").addClass("has-success");
                    }

                if ($("#Password").val().length<8) {
                    tuttook=false;
                    $("#DIV_Password").addClass("has-error");
                    $("#DIV_Password").removeClass("has-success");
                    errori+="Password troppo breve\r\n";
                } else {
                    $("#DIV_Password").removeClass("has-error");
                    $("#DIV_Password").addClass("has-success");
                }

                if (tuttook==false) {
                        $.notify(errori);
                }

                return tuttook;
                <?php else : ?>
                return true;

                <?php endif; ?>
            }

            $(document).on("click",".vedicontoeconomico",function(e){
                <?php if ($analisicostiaggiornata=='si') { ?>

                <?php } else { ?>
                alert("Questo link sarà disponibile dopo aver aggiornato l'analisi dei costi!");
                e.preventDefault();
                <?php } ?>
            });



            var decorrenzaattuale=''; //global, per prevenire il primo avviso sul cambio datadecorrenza
            $("#nuovo_elemento").click(function(e){
                e.preventDefault();
                e.stopPropagation();

                if (checkCampiObbligatori()==true) {
                    $(".btn-save").attr('disabled','disabled');
                    //devo riattivare i campi enum messi in readonly
                    $('input, select').attr('disabled', false);
                    $.post("ajax_modifica_elemento.php", $("#nuovo_elemento").serialize(), function(msg){$("#messaggiovalidazione").html(msg);});
                    setTimeout(function(){window.close();}, 2000);
                } else {
                    $.notify("Controlla i campi obbligatori");
                }

            });

            $("#nuovo_elemento_close").click(function(e){
                e.preventDefault();
                e.stopPropagation();


                $(".btn-save").attr('disabled','disabled');
                    var url='<?php echo base64_decode($backlist);?>';

                <?php if ($modulo['nome_modulo']=="CaricamentoDati") { ?>
                var idazienda=$(this).attr("data-idazienda");
                var anno=$(this).attr("data-anno");
                    var url="https://analisicosti.it/admin/module.php?modname=CaricamentoDati&p[anno]="+anno+"&p[id_azienda]="+idazienda;
                <?php } ?>
                setTimeout(function(){$(location).attr('href',url);}, 100);




            });

            $("#nuovo_elemento_reload").click(function(e){ //Salva e continua


                if ($("#utili_attesi").val()=='') {
                    $("#utili_attesi").val(0);
                }

                e.preventDefault();
                e.stopPropagation();

                if (checkCampiObbligatori()==true) {
                    $(".btn-save").attr('disabled','disabled');
                    //devo riattivare i campi enum messi in readonly
                    $('input, select').attr('disabled', false);
                    $.post("ajax_modifica_elemento.php?saveandreload=1&backlist=<?php echo $backlist;?>", $("#nuovo_elemento").serialize(), function(msg){$("#messaggiovalidazione").html(msg);});
                    $(".btn-save").removeAttr('disabled');
                } else {
                    $.notify("Controlla i campi obbligatori");
                }

            });

            $("#nuovo_elemento_back").click(function(e){ //Salva ed esci

                if ($("#utili_attesi").val()=='') {
                    $("#utili_attesi").val(0);
                }


                e.preventDefault();
                e.stopPropagation();

                if (checkCampiObbligatori()==true) {
                    $(".btn-save").attr('disabled','disabled');
                    //devo riattivare i campi enum messi in readonly
                    $('input, select').attr('disabled', false);
                    $.post("ajax_modifica_elemento.php?backlist=<?php echo $backlist;?>", $("#nuovo_elemento").serialize(), function(msg){$("#messaggiovalidazione").html(msg);});
                    $(".btn-save").removeAttr('disabled');
                } else {
                    $.notify("Controlla i campi obbligatori");
                }

            });

            $("#nuovo_elemento_rimani").click(function(e){
                e.preventDefault();
                e.stopPropagation();

                if (checkCampiObbligatori()==true) {
                    $(".btn-save").attr('disabled','disabled');
                    //devo riattivare i campi enum messi in readonly
                    $('input, select').attr('disabled', false);
                    $.post("ajax_modifica_elemento.php?rimani=1", $("#nuovo_elemento").serialize(), function(msg){$("#messaggiovalidazione").html(msg);} );
                } else {
                    $.notify("Controlla i campi obbligatori");
                }


            });

            $(".pianoconti").click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $(".btn-save").attr('disabled','disabled');

                var params= {};

                params.idazienda=$(this).attr("data-idazienda");
                params.anno=$(this).attr("data-anno");
                params.mese=$(this).attr("data-mese");
                params.nomefile=$(this).attr("data-file");
                params.rigenera=$(this).attr("data-rigenera");

                console.log(params);


                $.ajax({
                    type: "POST",
                    url: "aggiornapianoconti.php",
                    data: params,
                    dataType: 'json',
                    success: function(data){
                        console.log(data);
                        if (data.result==true) {
                            $.notify(data.msg,'success');
                            //var url='<?php echo $_SERVER[HTTP_REFERER];?>';
                            var url="get_element.php?debug=0&idmod=55&idele=<?php echo $idele;?>";
                            setTimeout(function(){$(location).attr('href',url);}, 1000);
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

            $("#aggiornaanalisicosti").click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $(".btn-save").attr('disabled','disabled');

                var params= {};

                params.idazienda=$(this).attr("data-idazienda");
                params.anno=$(this).attr("data-anno");
                params.mese=$(this).attr("data-mese");
                params.nomefile=$(this).attr("data-file");
                params.idCaricamento=$(this).attr("data-idCaricamento");

                console.log(params);


                $.ajax({
                    type: "POST",
                    url: "aggiornaanalisicosti.php",
                    data: params,
                    dataType: 'json',
                    success: function(data){
                        console.log(data);
                        if (data.result==true) {
                            $.notify(data.msg,'success');
                            var url='<?php echo $_SERVER[HTTP_REFERER];?>';
                            setTimeout(function(){window.location.reload(true);}, 1000);
                        } else {
                            $.notify(data.error,"error");
                            console.log(data);
                            $("#nuovo_elemento_close").removeAttr('disabled');

                            var url="module.php?modname=CaricamentoDati&p[anno]=<?php echo $elementoTMP['anno'];?>&p[id_azienda]=<?php echo $elementoTMP['id_azienda'];?>";
                            setTimeout(function(){$(location).attr('href',url);}, 1000);
                        }
                    },
                    error: function(data) {
                        console.log(data);
                        $.notify("ERRORE DI RETE!","error");
                        setTimeout(function(){$(location).attr('href',url);}, 1000);
                    }
                });

            });

            $("#aggiornaincidenzaacquisti").click(function(e){
                e.preventDefault();
                e.stopPropagation();

                var totalericavi=parseFloat($(this).attr("data-totalericavi"));
                var totaleacquisti=parseFloat($(this).attr("data-totaleacquisti"));

                //costo del venduto=rimanenze iniziali + costo acquisti - rimanenze finali
                //incidenza acquisti = costo del venduto/totale ricavi

                var rimanenze_iniziali  =parseFloat($("#rimanenze_iniziali").val()) || 0;
                var rimanenze_finali    =parseFloat($("#rimanenze_finali").val()) || 0;

                var costodelvenduto=parseFloat(totaleacquisti-rimanenze_finali+rimanenze_iniziali);

                var incidacquisti=parseFloat(100*(costodelvenduto)/totalericavi);
                //alert("totale ricavi:"+totalericavi);
                //alert("totale acquisti:"+totaleacquisti);
                //alert("costo del venduto:"+costodelvenduto);

                $("#incidenza_acquisti").val(incidacquisti.toFixed(2));

            });

            var editor;

            $('[data-rel=tooltip]').tooltip();

            $('#singoloelemento').load('ajax_getmodulo.php?'+ $.param({
                    backurl: 'http://<?php echo $_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];?>',
                    <?php if ($_REQUEST['debug']) { ?>
                    debug: '<?php echo $_REQUEST['debug'];?>',
                    <?php } ?>
                    <?php if ($k) { ?>
                    k: '<?php echo $k;?>',
                    <?php } ?>
                    <?php if ($k1) { ?>
                    k1: '<?php echo $k1;?>',
                    <?php } ?>
                    <?php if ($k2) { ?>
                    k2: '<?php echo $k2;?>',
                    <?php } ?>
                    <?php if ($view) { ?>
                    view: '<?php echo $view;?>',
                    <?php } ?>
                    idmod: idmod,
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
                $(".visualizzadopo").show();
                decorrenzaattuale=$("#decorrenza").val();
                $('.timepicker_interno').timepicker({showMeridian: false});
                $(".datepicker").datepicker({
                    autoclose: true,language: "it",format: "dd/mm/yyyy",todayHighlight: true});
            }); //fine $('#modal-body-myModal').load('ajax_getmodulo.php?'+ $.param({

        })
    </script>
    <!-- (f) inline scripts related to this page -->
    <hr>
    <?php include("INC_90_FOOTER.php");?>
</div> <!-- /container -->