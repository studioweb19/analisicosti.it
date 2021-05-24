<?php if ($bloccoprivacy==0) : ?>
<div class="container" id="firstcontainer">
    <?php

    $modulotmp=getModuloFrom_nome_modulo("Clienti");
    $permessi_modulo_tmp=permessi($modulotmp,$utente['id_ruolo'],$superuserOverride);

    $can_read_all_tmp=0;
    if ($permessi_modulo_tmp['Can_read_all']=='si') {
        $can_read_all_tmp=1;
    }


    $queryClienti="SELECT * FROM ".$GLOBAL_tb['clienti']." WHERE (idStudio=? OR 1=?) order by RagioneSociale";
    $stmtClienti=$dbh->prepare($queryClienti);
    $stmtClienti->execute(array($utente['id_user'],$can_read_all_tmp));
        $ClientiStudio=array();
        while($row = $stmtClienti->fetch(PDO::FETCH_ASSOC)) {
            //facciamo qui lo stato di avanzamento
//            $query1="SELECT *,MAX(meseanno) FROM pcs_analisi_costi WHERE id_azienda=".$row['id'];
//            $stmt1=$dbh->query($query1);
//            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
//            $row['analisicosti']=$row1;

            $query2="SELECT * FROM pcs_caricamento_dati WHERE analisicosti_aggiornata='si' and id_azienda=".$row['id']." order by anno desc,mese desc limit 0,1";
            $stmt2=$dbh->query($query2);
            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $row['caricamentodati']=$row2;

            $ClientiStudio[] = $row;

        }
//    echo "<pre>";
//        print_r($ClientiStudio);
//    echo "</pre>";
    ?>
    <?php $queryClientiAnnoMax="SELECT id_azienda,max(anno) as annomax FROM ".$GLOBAL_tb['prospetto_previsionale']." group by id_azienda";
    $stmtClientiAnnoMax=$dbh->query($queryClientiAnnoMax);
    $ClientiAnnoMax=array();
    while($row = $stmtClientiAnnoMax->fetch(PDO::FETCH_ASSOC)) {
        $ClientiAnnoMax[$row['id_azienda']] = $row['annomax'];
    }

    //------------------------------------------------------------------------------------------------------------------------
    //-------------------------------------------- (i)  A D M I N  -----------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------

    if ($utente['id_ruolo']==2) {
        //elenco studi attivati
        $elencostudi=getElencoStudi();


        $queryultimistudi="SELECT * FROM pcs_log_studi JOIN pcs_users ON pcs_users.id_user=pcs_log_studi.id_user WHERE pcs_users.id_ruolo=4 order by data_login DESC LIMIT 0,10 ";
        $stmtultimistudi=$dbh->query($queryultimistudi);
        while($rowultimistudi = $stmtultimistudi->fetch(PDO::FETCH_ASSOC)) {
            $us[]=$rowultimistudi;
        }

        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-6">
                <div class="panel panel-warning">
                    <div class="panel panel-heading">
                        <h1 class="panel-title">ULTIMI 10 STUDI LOGGATI</h1>
                    </div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Studio</th>
                            <th>Data login</th>
                            <th>Ora login</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php if (count($us)>0) : ?>
                            <?php foreach ($us as $n) : ?>
                                <tr>
                                    <td><?php echo $n['Nome'];?> <?php echo $n['Cognome'];?></td>
                                    <td><?php echo convertDate($n['data_login']);?> </td>
                                    <td> <?php echo $n['ora_login'];?> </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <br/>
                <div class="panel panel-primary">
                    <div class="panel panel-heading">
                        <h1 class="panel-title">STUDI REGISTRATI NEL SISTEMA</h1>
                    </div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Studio</th>
                            <th>Email</th>
                            <th>Numero di  <br/> clienti </th>
                            <th>Pagamento  <br/> attivo </th>
                            <th>Ultimo  <br/> pagamento </th>
                        </tr>
                        </thead>
                        <?php
                        foreach ($elencostudi as $c) : ?>
                            <tr>

                                <td><?php echo $c['Nome'];?> <?php echo $c['Cognome'];?></td>
                                <td><?php echo $c['email'];?></td>
                                <td><span class="badge badge-success"><?php echo count($c['ClientiStudio']);?></span></td>
                                <td><?php echo $c['pagamentoattivo'];?></td>
                                <td><?php echo $c['ultimopagamento'];?></td>
                            </tr>
                            <?php
                        endforeach;

                        ?>
                    </table>
                </div>
            </div>
        </div>

    <?php }

    //------------------------------------------------------------------------------------------------------------------------
    //-------------------------------------------- (f)  A D M I N  -----------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------



    //------------------------------------------------------------------------------------------------------------------------
    //-------------------------------------------- (i) S T U D I O -----------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------

    if ($utente['id_ruolo']==4) {

        //elenco clienti dello studio
        $clientistudio=getClientiStudio($utente['id_user']);
        if (count($clientistudio)>0) {
            foreach ($clientistudio as $c) {
                $clientiid[]=$c['id'];
            }
            $clientistudiolista=join(",",$clientiid);
            ?>
            <div class="row">
                <div class="col col-xs-12">
                            <br/>
                    <a class="pull-right btn btn-success" href="get_element.php?debug=&idmod=9&idele=-1&backlist=aHR0cDovL3d3dy5hbmFsaXNpLWNvc3RpLnN0dWRpb3dlYjE5Lml0L2FkbWluL2NsaWVudGkucGhwPw==" role="button">Aggiungi un cliente</a>
                </div>
            </div>
        <?php } else { ?>
            <div class="row">
                <div class="col col-xs-12">
                    <div class="jumbotron">
                        <h3>Benvenuto!</h3>
                        <p class="lead">Al momento non è presente nessun cliente associato al tuo studio!</p>

                        <p class="lead">
                            <a class="btn btn-success btn-lg" href="get_element.php?debug=&idmod=9&idele=-1&backlist=aHR0cDovL3d3dy5hbmFsaXNpLWNvc3RpLnN0dWRpb3dlYjE5Lml0L2FkbWluL2NsaWVudGkucGhwPw==" role="button">Inserisci il tuo primo cliente!</a>
                        </p>
                    </div>
                </div>
            </div>

        <?php }



        ?>
        <br/>
        <!-- elenco degli ultimi aggiornamenti inviati -->
        <!-- elenco dei clienti loggati di recente FATTO registrazione log clienti in tabella -->
        <!-- news da parte di admin del sistema -->
        <!-- tabella con stato di avanzamento dei clienti -->


        <div class="row"><!-- row widegets -->

        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <!-- --------------------------------------(i) ultime note inviate------------------------------------------------------------->
        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <?php
        $ultimenote=5;
        if  (count($clientiid)>0) {
            $querynoteinviate="SELECT * FROM pcs_analisi_costi JOIN pcs_clienti ON pcs_clienti.id=pcs_analisi_costi.id_azienda WHERE pcs_analisi_costi.id_azienda IN ($clientistudiolista) AND noteStudio<>'' and email_inviata='si' IS NOT NULL order by data_invio_email DESC LIMIT 0,$ultimenote ";
            $stmtnoteinviate=$dbh->query($querynoteinviate);
            while($rownoteinviate = $stmtnoteinviate->fetch(PDO::FETCH_ASSOC)) {
                $noteinviate[]=$rownoteinviate;
            }
        } ?>
                <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="panel panel-success">
                        <div class="panel panel-heading">
                            <h1 class="panel-title">ULTIME <?php echo $ultimenote;?> NOTE INVIATE</h1>
                        </div>
                        <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Data invio</th>
                                    <th>Nota</th>
                                </tr>
                                </thead>

                                <tbody>
                                <?php if (count($noteinviate)>0) : ?>
                                <?php foreach ($noteinviate as $n) : ?>
                                    <tr>
                                        <td><?php echo $n['RagioneSociale'];?></td>
                                        <td><?php echo convertDate($n['data_invio_email']);?> </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-success" data-toggle="tooltip" data-html="true" title="<?php echo $n['noteStudio'];?>">NOTA</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>

                    </div>
                </div>


        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <!-- --------------------------------------(f) ultime note inviate------------------------------------------------------------->
        <!-- -------------------------------------------------------------------------------------------------------------------------->

        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <!-- --------------------------------------(i) ultimi clienti loggati inviate-------------------------------------------------->
        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <?php
        $ultimiclienti=5;
        if  (count($clientiid)>0) {
            $queryultimiclienti="SELECT * FROM pcs_log JOIN pcs_clienti ON pcs_clienti.id=pcs_log.id_cliente WHERE pcs_log.id_cliente IN ($clientistudiolista) order by data_login DESC LIMIT 0,$ultimiclienti ";
            $stmtultimiclienti=$dbh->query($queryultimiclienti);
            while($rowultimiclienti = $stmtultimiclienti->fetch(PDO::FETCH_ASSOC)) {
                $uc[]=$rowultimiclienti;
            }
        } ?>
                <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="panel panel-warning">
                        <div class="panel panel-heading">
                            <h1 class="panel-title">ULTIMI <?php echo $ultimiclienti;?> CLIENTI LOGGATI</h1>
                        </div>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Data login</th>
                                <th>Ora login</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php if (count($uc)>0) : ?>
                            <?php foreach ($uc as $n) : ?>
                                <tr>
                                    <td><?php echo $n['RagioneSociale'];?></td>
                                    <td><?php echo convertDate($n['data_login']);?> </td>
                                    <td> <?php echo $n['ora_login'];?> </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>

        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <!-- --------------------------------------(f) ultimi clienti loggati inviate-------------------------------------------------->
        <!-- -------------------------------------------------------------------------------------------------------------------------->

        </div><!-- row widegets -->


        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <!-- --------------------------------------(i) stato avanzamento clienti     -------------------------------------------------->
        <!-- -------------------------------------------------------------------------------------------------------------------------->

        <?php if (count($ClientiStudio)>0) : ?>

        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-primary">
                    <div class="panel panel-heading">
                        <h1 class="panel-title">SITUAZIONE ANALISI E CARICAMENTO DATI</h1>
                    </div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Ragione Sociale <br/>Cliente</th>
                            <th>Prospetto <br/>Previsionale</th>
                            <th>Ultimo <br/>Caricamento Dati</th>
                            <th>Analisi Costi<br/> Aggiornata</th>
                        </tr>
                        </thead>
                        <?php
                        foreach ($ClientiStudio as $c) : ?>
                            <?php $tokenarray=array();?>
                                <?php $tokenarray['id_azienda']=$c['id'];?>
                                <?php $tokenarray['anno']=$ClientiAnnoMax[$c['id']];?>
                                <?php $token=base64_encode(json_encode($tokenarray));?>
                            <tr>

                                <td><a href="<?php echo $sitoweb;?>/admin/report.php?token=<?php echo $token;?>"><?php echo $c['RagioneSociale'];?></a></td>
                                <td><?php echo $ClientiAnnoMax[$c['id']];?></td>
                                <td><?php echo $mese[$c['caricamentodati']['mese']];?> <?php echo $c['caricamentodati']['anno'];?> </td>
                                <td><?php if ($c['caricamentodati']['analisicosti_aggiornata']=='si') {$labelclass="label-success";} else {$labelclass="label-danger";} ?><span class="label <?php echo $labelclass;?>"><?php echo $c['caricamentodati']['analisicosti_aggiornata'];?></span> </td>
                            </tr>
                            <?php
                        endforeach;

                        ?>
                    </table>
                </div>
            </div>
        </div>
        <?php endif;?>

        <!-- -------------------------------------------------------------------------------------------------------------------------->
        <!-- --------------------------------------(f) stato avanzamento clienti     -------------------------------------------------->
        <!-- -------------------------------------------------------------------------------------------------------------------------->

    <?php }?>



<?php
    //------------------------------------------------------------------------------------------------------------------------
    //-------------------------------------------- (f) S T U D I O -----------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------
?>



    <?php if (($utente['id_ruolo']!=3) and (count($ClientiStudio)>=0)) {
//------------------------------------------------------------------------------------------------------------------------
//-------------------------------------------- (i) N O N   C L I E N T E -------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
        ?>
    <br/>
<?php /* ?>
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-sm-offset-3">
            <div class="well well-lg" >
                    <div class="form-group">
                        <label for="sceltacliente">Scelta Cliente</label>
                        <select name="sceltacliente" id="sceltacliente" class="chosen-select form-control">
                            <option value=""><?php echo _("-- Scegli Cliente-");?></option>
                            <?php foreach ($ClientiStudio as $clientestudio) : ?>
                                <?php $tokenarray=array();?>
                                <?php $tokenarray['id_azienda']=$clientestudio['id'];?>
                                <?php $tokenarray['anno']=$ClientiAnnoMax[$clientestudio['id']];?>
                                <?php $token=base64_encode(json_encode($tokenarray));?>
                                <option <?php if ($clientestudio['id']==$_GET['ida']) echo "SELECTED " ;?> value="<?php echo $token;?>"><?php echo $clientestudio['RagioneSociale'];?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
            </div>
        </div>
    </div>
<?php */ ?>
    <?php }

//------------------------------------------------------------------------------------------------------------------------
//-------------------------------------------- (f) N O N   C L I E N T E -------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

    else {

//------------------------------------------------------------------------------------------------------------------------
//-------------------------------------------- (i) C L I E N T E ---------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------


            $ultimenote=5;
            $querynoteinviate="SELECT * FROM pcs_analisi_costi JOIN pcs_clienti ON pcs_clienti.id=pcs_analisi_costi.id_azienda WHERE pcs_analisi_costi.id_azienda = ? AND noteStudio<>'' and email_inviata='si' IS NOT NULL order by data_invio_email DESC LIMIT 0,$ultimenote ";
        $stmtnoteinviate=$dbh->prepare($querynoteinviate);
        $stmtnoteinviate->execute(array($utente['id_cliente']));
            while($rownoteinviate = $stmtnoteinviate->fetch(PDO::FETCH_ASSOC)) {
                $noteinviate[]=$rownoteinviate;
            }


        $queryClienti="SELECT * FROM ".$GLOBAL_tb['clienti']." WHERE (id=?) order by RagioneSociale";
        $stmtClienti=$dbh->prepare($queryClienti);
        $stmtClienti->execute(array($utente['id_cliente']));
        $ClientiStudio=array();
        while($row = $stmtClienti->fetch(PDO::FETCH_ASSOC)) {
            $ClientiStudio[] = $row;
        }

        //se sono il cliente, devo andare al report come se avessi scelto da studio quel cliente
        $queryClientiAnnoMax="SELECT id_azienda,max(anno) as annomax FROM ".$GLOBAL_tb['prospetto_previsionale']." WHERE id_azienda=?";
        $stmtClientiAnnoMax=$dbh->prepare($queryClientiAnnoMax);
        $stmtClientiAnnoMax->execute(array($utente['id_cliente']));
        $ClientiAnnoMax=array();
        while($row = $stmtClientiAnnoMax->fetch(PDO::FETCH_ASSOC)) {
            $ClientiAnnoMax[$row['id_azienda']] = $row['annomax'];
        }
        foreach ($ClientiStudio as $clientestudio) : ?>
            <?php $tokenarray=array();?>
            <?php $tokenarray['id_azienda']=$clientestudio['id'];?>
            <?php $tokenarray['anno']=$ClientiAnnoMax[$clientestudio['id']];?>
            <?php $tokencliente=base64_encode(json_encode($tokenarray));?>
            <?php //qui ora va fatto il redirect verso la pagina del report ?>
        <?php endforeach;

        if (count($moduliattivati)>0) {

        } else {
            //echo _("Nessun modulo attivato!");
        }
        ?>

        <?php foreach ($ClientiStudio as $clientestudio) : ?>
            <?php $tokenarray=array();?>
            <?php $tokenarray['id_azienda']=$clientestudio['id'];?>
            <?php $tokenarray['anno']=$ClientiAnnoMax[$clientestudio['id']];?>
            <?php $tokencliente=base64_encode(json_encode($tokenarray));?>
        <?php endforeach; ?>




        <div class="row" style="margin-top:20px;">
        <?php if (count($noteinviate)>0) : ?>
                <div class="col-xs-12 col-sm-6 col-md-6">
                    <div class="panel panel-success">
                        <div class="panel panel-heading">
                            <h1 class="panel-title">ULTIME <?php echo $ultimenote;?> NOTE INVIATE</h1>
                        </div>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Data invio</th>
                                <th>Nota</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($noteinviate as $n) : ?>
                                <tr>
                                    <td><?php echo $n['RagioneSociale'];?></td>
                                    <td><?php echo convertDate($n['data_invio_email']);?> </td>
                                    <td> <a data-placement="left" class="btn btn-xs btn-success" data-toggle="popover" title="NOTA" data-html="true" data-content="<?php echo $n['noteStudio'];?>">NOTA</a> </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>

        <?php else : ?>

        <?php endif; ?>

            <div class="broker-studio_dashboard">
                <ul class="a-btn-group ">
                    <?php foreach ($moduli as $mod) :
                        $idmod=$mod['id_modulo'];
                        $permessi=permessi($idmod,$utente['id_ruolo'],$superuserOverride);

                        //se can_read=no allora non può vedere il modulo
                        if ($permessi['Can_read']=='no') continue;
                        $urlmodulo='module.php?id='.$idmod;
                        if ($mod['script_modulo']!='' and $mod['modulo_standard']=='no') $urlmodulo=$mod['script_modulo'];
                        ?>

                        <?php if ($mod['nome_modulo']=="ModuliClienti") continue; ?>
                        <?php if ($mod['nome_modulo']=="Clienti") continue; ?>

                        <li><a href="<?php echo $sitedir;?><?php echo $urlmodulo;?>"><i class="<?php echo $mod['font_icon'];?>"></i><br /><span><?php echo _($mod['nome_modulo']);?></span></a></li>
                    <?php endforeach; ?>

                    <?php $url = "report.php?token=".$tokencliente; ?>


                    <li><a href="<?php echo $sitedir;?><?php echo $url;?>"><i class="fa fa-eur"></i><br /><span><?php echo _("Analisi Costi");?></span></a></li>



                    <?php if (count($moduliattivati)>0) { ?>
                        <?php foreach ($moduliattivati as $mod) :
                            $urlmodulo="wizard.php?idmod=".$mod['id'];
                            ?>
                            <li><a href="<?php echo $sitedir;?><?php echo $urlmodulo;?>"><i class="<?php echo $mod['icona'];?>"></i><br /><span><?php echo _($mod['nome']);?></span></a></li>
                        <?php endforeach; ?>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php }
    //------------------------------------------------------------------------------------------------------------------------
    //-------------------------------------------- (f) C L I E N T E ---------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------------
    ?>
        <hr>

    <?php include("INC_90_FOOTER.php");?>
    <script>
        $(document).ready(function() {

            <?php
            if ($_GET['ida']>0) :
            foreach ($ClientiStudio as $c) :
            if ($c['id']==$_GET['ida']) :
            $tokenarray=array();
            $tokenarray['id_azienda']=$c['id'];
            $tokenarray['anno']=$ClientiAnnoMax[$c['id']];
            $token=base64_encode(json_encode($tokenarray));
             else :
                continue;
            endif;
            endforeach; //foreach
            ?>

            var url = "<?php echo $sitoweb;?>/admin/report.php?token=<?php echo $token;?>";
            location.href = url;
            <?php endif; //if ?>

        });
    </script>
</div> <!-- /container -->

<?php else : ?>
    <?php
    if ($utente['id_ruolo']==4) {
        if ($utente['privacy']>0)     {
            $privacychecked="checked";
        } else {
            $privacychecked="";
        }
        if ($utente['termini']>0)     {
            $terminichecked="checked";
        } else {
            $terminichecked="";
        }
    }
    ?>
    <div class="container" id="firstcontainer">
        <div class="row">
            <div class="col-xs-12">
                <h4>Leggi e accetta le <a href="condizionigeneraliprivacy.pdf" target="_blank">condizioni generali sulla privacy</a></h4>
                <input id="privacycheck" <?php echo $privacychecked;?> type="checkbox" data-on="<i class='fa fa-check'></i> " data-off="<i class='fa fa-times'></i> "  data-toggle="toggle" data-offstyle="danger" data-onstyle="success"> Ho preso visione delle condizioni generali sulla privacy e le accetto
                <input type="hidden" id="idcliente" value="<?php echo $_SESSION['pcs_id_cliente'];?>">
                <input type="hidden" id="iduser" value="<?php echo $_SESSION['pcs_id_user'];?>">
                <h4>Leggi e accetta i <a href="terminidelservizio.pdf" target="_blank">termini del servizio</a></h4>
                <input id="terminicheck" <?php echo $terminichecked;?> type="checkbox" data-on="<i class='fa fa-check'></i> " data-off="<i class='fa fa-times'></i> "  data-toggle="toggle" data-offstyle="danger" data-onstyle="success"> Ho preso visione dei termini del servizio e li accetto
                <input type="hidden" id="idcliente" value="<?php echo $_SESSION['pcs_id_cliente'];?>">
                <input type="hidden" id="iduser" value="<?php echo $_SESSION['pcs_id_user'];?>">
            </div>
        </div>
    </div>
    <script>
        $(function() {
            $('#privacycheck').change(function() {
                if ($(this).prop('checked')==true) {

                    var params={};
                    params.idcliente=$("#idcliente").val();
                    params.iduser=$("#iduser").val();
                    $.ajax({
                        type: "POST",
                        url: "ajax_salva_privacy.php",
                        data: params,
                        dataType: 'json',
                        success: function(data){
                            console.log(data);
                            if (data.result==true) {
                                $.notify('Condizioni accettate','success');
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

                }
            })
            $('#terminicheck').change(function() {
                if ($(this).prop('checked')==true) {

                    var params={};
                    params.idcliente=$("#idcliente").val();
                    params.iduser=$("#iduser").val();
                    $.ajax({
                        type: "POST",
                        url: "ajax_salva_termini.php",
                        data: params,
                        dataType: 'json',
                        success: function(data){
                            console.log(data);
                            if (data.result==true) {
                                $.notify('Termini accettati','success');
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

                }
            })
        })
    </script>
    <?php exit; ?>
<?php endif; ?>
