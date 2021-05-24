<div class="container" id="firstcontainer">
    <?php include("mostrainfo.php");?>
    <!-- INTESTAZIONE REPORT-->
    <?php include("barranavigazione.php");?>
    <?php $token=json_decode(base64_decode($_GET['token']),true);?>

    <?php //estraggo i parametri del progetto di bilancio
    $query1="SELECT * FROM pcs_progetto_bilancio WHERE anno=? AND id_azienda=? ";
    $stmt1=$dbh->prepare($query1);
    $stmt1->execute(array($token['anno'],$token['id_azienda']));
    $PROGETTO=$stmt1->fetch(PDO::FETCH_ASSOC);
    //print_r($PROGETTO);
    ?>

    <?php
    $query="SELECT * FROM pcs_dati_consuntivi JOIN pcs_piano_conti ON pcs_piano_conti.codiceconto=pcs_dati_consuntivi.codiceconto WHERE anno=? and mese=? and pcs_piano_conti.id_azienda=? AND pcs_dati_consuntivi.id_azienda=? order by categoria ASC, pcs_dati_consuntivi.codiceconto ASC";
    $stmt=$dbh->prepare($query);
    $stmt->execute(array($token['anno'],$token['mese'],$token['id_azienda'],$token['id_azienda']));
    while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $bilancio[$row['categoria']][]=$row;
    }
    ?>

    <?php
    $query="SELECT * FROM pcs_dati_consuntivi JOIN pcs_piano_conti ON pcs_piano_conti.codiceconto=pcs_dati_consuntivi.codiceconto WHERE anno=? and mese=? and pcs_piano_conti.id_azienda=? AND pcs_dati_consuntivi.id_azienda=? order by categoria ASC, pcs_dati_consuntivi.codiceconto ASC";
    $stmt=$dbh->prepare($query);
    $stmt->execute(array($token['anno']-1,$token['mese'],$token['id_azienda'],$token['id_azienda']));
    while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $bilancioprecedente[$row['categoria']][]=$row;
    }
    ?>

    <?php
    $query="SELECT * FROM pcs_analisi_costi WHERE meseanno=? AND id_azienda=?";
    $stmt=$dbh->prepare($query);
    $stmt->execute(array($token['anno']."-".$token['mese'],$token['id_azienda']));
    while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $ANALISI=$row;
    }
    ?>

    <h3><?php echo $meselong[$token['mese']];?> <?php echo $token['anno'];?> </h3>
    <?php //print_r($token); ?>

    <div class="row">
        <!-- (i) colonna sinistra-->
        <div class="col-sm-6 col-xs-12">
            <?php
            //(i) Ricavi
            $totalericavi=0;
            $totalericaviprecedente=0;
            foreach ($bilancio as $key=>$value) :
                if ($key!=1) continue; //salto tutto tranne i ricavi
                foreach ($value as $singolovalore) :
                    if ($singolovalore['livello2']!='') continue; //salto i sotto-conti
                    $td[$singolovalore['codiceconto']]['nome']=$singolovalore['codiceconto']." ".$singolovalore['nome'];
                    $td[$singolovalore['codiceconto']]['annoincorso']=$singolovalore['avere']-$singolovalore['dare'];
                    $totalericavi+=$singolovalore['avere']-$singolovalore['dare'];
                endforeach;
            endforeach;
            if (count($bilancioprecedente)>0) :
                foreach ($bilancioprecedente as $key=>$value) :
                    if ($key!=1) continue; //salto tutto tranne i ricavi
                    foreach ($value as $singolovalore) :
                        if ($singolovalore['livello2']!='') continue; //salto i sotto-conti
                        $td[$singolovalore['codiceconto']]['nome']=$singolovalore['codiceconto']." ".$singolovalore['nome'];
                        $td[$singolovalore['codiceconto']]['annoprecedente']=$singolovalore['avere']-$singolovalore['dare'];
                        $totalericaviprecedente+=$singolovalore['avere']-$singolovalore['dare'];
                    endforeach;
                endforeach;
            endif;
            //(f) Ricavi
            ?>
            <?php
            // (i) Costi Acquisti
            $totalecostiacquisti=0;
            $totalecostiacquistiprecedente=0;
            foreach ($bilancio as $key=>$value) :
                if ($key!=2) continue; //salto tutto tranne i costi acquisti
                foreach ($value as $singolovalore) :
                    if ($singolovalore['livello2']!='') continue; //salto i sotto-conti
                    $tdacq[$singolovalore['codiceconto']]['nome']=$singolovalore['codiceconto']." ".$singolovalore['nome'];
                    $tdacq[$singolovalore['codiceconto']]['annoincorso']=-$singolovalore['avere']+$singolovalore['dare'];
                    $totalecostiacquisti+=$singolovalore['dare']-$singolovalore['avere'];
                endforeach;
            endforeach;
            if (count($bilancioprecedente)>0) :
                foreach ($bilancioprecedente as $key=>$value) :
                    if ($key!=2) continue; //salto tutto tranne i costi acquisti
                    foreach ($value as $singolovalore) :
                        if ($singolovalore['livello2']!='') continue; //salto i sotto-conti
                        $tdacq[$singolovalore['codiceconto']]['nome']=$singolovalore['codiceconto']." ".$singolovalore['nome'];
                        $tdacq[$singolovalore['codiceconto']]['annoprecedente']=-$singolovalore['avere']+$singolovalore['dare'];
                        $totalecostiacquistiprecedente+=$singolovalore['dare']-$singolovalore['avere'];
                    endforeach;
                endforeach;
            endif;
            // (f) Costi Acquisti
            ?>

            <?php
            // (i) Costi Generali
            $totalecosti=0;
            $totalecostiprecedente=0;
            foreach ($bilancio as $key=>$value) :
                if ($key!=3) continue; //salto tutto tranne i costi generali
                foreach ($value as $singolovalore) :
                    if ($singolovalore['livello2']!='') continue; //salto i sotto-conti
                    $tdgen[$singolovalore['codiceconto']]['nome']=$singolovalore['codiceconto']." ".$singolovalore['nome'];
                    $tdgen[$singolovalore['codiceconto']]['annoincorso']=-$singolovalore['avere']+$singolovalore['dare'];
                    $totalecosti+=$singolovalore['dare']-$singolovalore['avere'];
                endforeach;
            endforeach;
            if (count($bilancioprecedente)>0) :
                foreach ($bilancioprecedente as $key=>$value) :
                    if ($key!=3) continue; //salto tutto tranne i costi generali
                    foreach ($value as $singolovalore) :
                        if ($singolovalore['livello2']!='') continue; //salto i sotto-conti
                        $tdgen[$singolovalore['codiceconto']]['nome']=$singolovalore['codiceconto']." ".$singolovalore['nome'];
                        $tdgen[$singolovalore['codiceconto']]['annoprecedente']=-$singolovalore['avere']+$singolovalore['dare'];
                        $totalecostiprecedente+=$singolovalore['dare']-$singolovalore['avere'];
                    endforeach;
                endforeach;
            endif;
            // (f) Costi Generali
            ?>


            <table class="table">
                <tr class="bg-success">
                    <th style="text-align:center;">Ricavi</th>
                    <th style="text-align:center;">Anno in corso</th>
                    <th style="text-align:center;">Anno precedente</th>
                </tr>
<?php foreach ($td as $key=>$value) : ?>
<tr>
    <td >
        <?php echo $value['nome'];?>
    </td>
    <td style="text-align:right;">
        <?php echo soldi($value['annoincorso']);?>
    </td>
    <td style="text-align:right;">
        <?php echo soldi($value['annoprecedente']);?>
    </td>
</tr>
<?php endforeach; ?>

                <tr>
                    <th>
                        Totale ricavi:
                    </th>
                    <th style="text-align:right;">
                        <?php echo soldi( $totalericavi);?>
                    </th>
                    <th style="text-align:right;">
                        <?php echo soldi( $totalericaviprecedente);?>
                    </th>
                </tr>

                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>

                <tr class="bg-warning">
                    <th style="text-align:center;">Costi Acquisti</th>
                    <th style="text-align:center;">Anno in corso</th>
                    <th style="text-align:center;">Anno precedente</th>
                </tr>
                <?php foreach ($tdacq as $key=>$value) : ?>
                    <tr>
                        <td >
                            <?php echo $value['nome'];?>
                        </td>
                        <td style="text-align:right;">
                            <?php echo soldi($value['annoincorso']);?>
                        </td>
                        <td style="text-align:right;">
                            <?php echo soldi($value['annoprecedente']);?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <th>
                        Totale costi acquisti:
                    </th>
                    <th style="text-align:right;">
                        <?php echo soldi( $totalecostiacquisti);?>
                    </th>
                    <th style="text-align:right;">
                        <?php echo soldi( $totalecostiacquistiprecedente);?>
                    </th>
                </tr>


                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>

                <tr class="bg-danger">
                    <th style="text-align:center;">Costi Generali</th>
                    <th style="text-align:center;">Anno in corso</th>
                    <th style="text-align:center;">Anno precedente</th>
                </tr>
                <?php foreach ($tdgen as $key=>$value) : ?>
                    <tr>
                        <td >
                            <?php echo $value['nome'];?>
                        </td>
                        <td style="text-align:right;">
                            <?php echo soldi($value['annoincorso']);?>
                        </td>
                        <td style="text-align:right;">
                            <?php echo soldi($value['annoprecedente']);?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <th>
                        Totale costi generali:
                    </th>
                    <th style="text-align:right;">
                        <?php echo soldi( $totalecosti);?>
                    </th>
                    <th style="text-align:right;">
                        <?php echo soldi( $totalecostiprecedente);?>
                    </th>
                </tr>



                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>

                <tr class="bg-primary">
                    <th style="text-align:center;" colspan="4">Consuntivo Mese</th>
                </tr>
                <tr>
                    <td>Ricavi</td>
                    <td style="text-align:right;"><?php echo soldi( $totalericavi);?></td>
                </tr>
                <tr>
                    <td>Costi Acquisti</td>
                    <td style="text-align:right;"><?php echo soldi( $totalecostiacquisti);?></td>
                </tr>
                <tr>
                    <td>Costi Generali</td>
                    <td style="text-align:right;"><?php echo soldi( $totalecosti);?></td>
                </tr>
                <tr>
                    <td>Reddito</td>
                    <?php if ($totalericavi-$totalecostiacquisti-$totalecosti>0) {
                        $color="#000000";
                    } else {
                        $color="#FF0000";
                    }?>
                    <td style="text-align:right; color:<?php echo $color;?>"><?php echo soldi( $totalericavi-$totalecostiacquisti-$totalecosti);?></td>
                </tr>
                <tr>
                    <td>Utile Desiderato</td>
                    <td style="text-align:right;"><?php echo soldi( $PROGETTO['utili_attesi']);?></td>
                </tr>
                <tr>
                    <td>Distanza da utile desiderato</td>
                    <?php if ($totalericavi-$totalecostiacquisti-$totalecosti-$PROGETTO['utili_attesi']>0) {
                        $color="#000000";
                    } else {
                        $color="#FF0000";
                    }?>
                    <th style="text-align:right; color:<?php echo $color;?>"><?php echo soldi( $totalericavi-$totalecostiacquisti-$totalecosti-$PROGETTO['utili_attesi']);?></th>
                </tr>



                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>

            </table>
            <div id="noteStudioDiv">
                <h3><?php echo _("Note Analisi Bilancio");?></h3>
            <?php if ($utente['id_ruolo']!=3) { ?>
                <textarea  name="noteStudio" id="noteStudio" class="textarea col-xs-12 ckeditortextarea"><?php echo $ANALISI['noteStudio'];?></textarea>
                <br/>
                <?php if ($ANALISI['data_invio_email']) { ?>
                <p>Email inviata in data <?php echo convertDate($ANALISI['data_invio_email']);?></p>
                <?php } ?>

                    <?php
                $disabled='';
                if ($ANALISI['noteStudio']=='') { $disabled="disabled";}
                ?>
                    <a id="salvanote" data-meseanno="<?php echo $token['anno']."-".$token['mese'];?>" data-idazienda="<?php echo $token['id_azienda'];?>" class="btn btn-success"><?php echo _("Salva Note");?></a>
                <?php if ($ANALISI['email_inviata']=='') { ?>
                    <a id="invianote" data-mese="<?php echo $token['mese'];?>" data-anno="<?php echo $token['anno'];?>" data-idazienda="<?php echo $token['id_azienda'];?>" class="btn btn-info <?php echo $disabled;?>"><?php echo _("Invia Note al Cliente");?></a>
                <?php } else { ?>
                    <a id="invianote" data-mese="<?php echo $token['mese'];?>" data-anno="<?php echo $token['anno'];?>" data-idazienda="<?php echo $token['id_azienda'];?>" class="btn btn-danger <?php echo $disabled;?>"><?php echo _("Re-Invia Note al Cliente");?></a>
                    <?php } ?>
            <?php } else { ?>
                <div class="row">
                    <div class="col-xs-12">
                        <?php echo $ANALISI['noteStudio'];?>
                    </div>
                </div>
            <?php } ?>
            </div>
            <br/><br/><div id="responso"></div>
        </div>
        <!-- (f) colonna sinistra-->

        <!-- (i) colonna destra-->
        <div class="col-sm-6 col-xs-12">
<table class="table">
    <tr class="bg-primary">
        <th style="text-align:center;" colspan="4">Costi Generali alla data</th>
    </tr>
    <?php

    //echo "<pre>";
    //print_r($bilancio);
    //echo "</pre>";

    foreach ($bilancio as $key=>$value) : //1 ricavi 2 acquisti 3 costi generali
        foreach ($value as $singolovalore) :
            if ($key==1) {
                $trclass="bg-success";
            }
            if ($key==2) {
                $trclass="bg-warning";
            }
            if ($key==3) {
                $trclass="bg-danger";
            }
            if ($singolovalore['livello2']!='') {
                $trclass.=" sottolivello sottolivello-".$singolovalore['livello1'];
            }
    ?>
    <tr class="<?php echo $trclass;?>" <?php if ($singolovalore['livello2']!='') { ?> style="display:none;"<?php } ?>>
        <?php if ($singolovalore['livello2']!='') { ?>

            <td ><?php echo $singolovalore['codiceconto'];?></td>
            <td ><?php echo $singolovalore['nome'];?></td>
            <td style="text-align:right;"><?php echo soldi($singolovalore['dare']) ;?></td>
            <td style="text-align:right;"><?php echo soldi($singolovalore['avere']) ;?></td>

            <?php } else { ?>

            <th><?php echo $singolovalore['codiceconto'];?> <a class="toggle-conti" dataid="<?php echo $singolovalore['livello1'];?>"><i class="fa fa-chevron-down"></i></a></th>
            <th><?php echo $singolovalore['nome'];?></th>
            <th style="text-align:right;"><?php echo soldi($singolovalore['dare']) ;?></th>
            <th style="text-align:right;"><?php echo soldi($singolovalore['avere']) ;?></th>

        <?php } ?>
    </tr>
    <?php
        endforeach;
    endforeach;
    ?>
</table>

        </div>
        <!-- (f) colonna destra-->
    </div>
    <script src="ckeditor/ckeditor.js"></script>
    <script>
        $(document).ready(function(){

            $(".sottolivello").hide();

            $(".toggle-conti").click(function(){
                var idconto=$(this).attr("dataid");
                $(".sottolivello-"+idconto).toggle();
            })


            $("#salvanote").click(function(){
                var idazienda=$(this).attr("data-idazienda");
                var meseanno=$(this).attr("data-meseanno");
                var notestudio=$("#noteStudio").val();
                $.post("ajax_salva_note_bilancio.php", { notestudio:notestudio, idazienda: idazienda, meseanno: meseanno } , function(msg)
                {
                    $("#responso").html(msg);
                    var url='<?php echo $_SERVER["REQUEST_URI"];?>';
                    setTimeout(function(){$(location).attr('href',url);}, 1000);
                } );
            });

            $("#invianote").click(function(){
                var idazienda=$(this).attr("data-idazienda");
                var mese=$(this).attr("data-mese");
                var anno=$(this).attr("data-anno");
                var notestudio=$("#noteStudio").val();
                var url='<?php echo $_SERVER["REQUEST_URI"];?>';
                $.post("ajax_invia_note_bilancio.php", { url:url, notestudio:notestudio, idazienda: idazienda, mese:mese, anno: anno } , function(msg)
                {
                    $("#responso").html(msg);
                    setTimeout(function(){$(location).attr('href',url);}, 1000);
                } );
            });

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
        });
    </script>



    <hr>
    <?php include("INC_90_FOOTER.php");?>

</div> <!-- /container -->