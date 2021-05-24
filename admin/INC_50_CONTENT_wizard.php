<div class="container" id="firstcontainer">
    <!-- INTESTAZIONE WIZARD-->
    <br/>
    <?php $idmod=$_GET['idmod']; ?>
    <?php //controllo che il modulo e il cliente corrispondano
    $querycheck="SELECT * FROM ".$GLOBAL_tb['moduliclienti']. " WHERE id=? AND id_cliente=?";
    $stmt=$dbh->prepare($querycheck);
    $stmt->execute(array($idmod,$utente['id_cliente']));
    if ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $elementimoduloquery="SELECT * FROM ".$GLOBAL_tb['moduliclientistoria']." WHERE id_modulo_cliente=? order by data_aggiornamento DESC";
        $stmt=$dbh->prepare($elementimoduloquery);
        $stmt->execute(array($idmod));
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $storiamodulo[]=$row;
        }
        if (count($storiamodulo)>0) {
            $defaultvalue=json_decode($storiamodulo[0]['dati'],true);
        }
        //print_r($defaultvalue);
    } else {
        echo "ERRORE! Questo modulo non ti appartiene!";
        exit;
    }

//-- Generazione Campi Del Modulo -->
//-- Un pannello per ogni sotto modulo -->
    $querypacchetti="SELECT * FROM ".$GLOBAL_tb['elementi_moduli_generati']." mgr JOIN ".$GLOBAL_tb['pacchetti']." mp ON mgr.id_pacchetto=mp.id WHERE id_modulo_generato=?";
    $stmt=$dbh->prepare($querypacchetti);
    $stmt->execute(array($idmod));
    while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $elencopacchetti[]=$row;
    }
    ?>


    <div class="row">

    <div class="col-md-8 col-sm-9 panel-group">
        <form class="formElementiClienti" id="form_0">
    <?php foreach ($elencopacchetti as $pacchetto) :?>
        <?php
        $queryelementipacchetto="SELECT * FROM ".$GLOBAL_tb['elementi_pacchetto']." ep WHERE ep.id_pacchetto=?";
        $stmt2=$dbh->prepare($queryelementipacchetto);
        $stmt2->execute(array($pacchetto['id']));
        $elementipacchetto=array();
        while ($row2=$stmt2->fetch(PDO::FETCH_ASSOC)) {
            $elementipacchetto[$row2['id']]=$row2;
        }
        ?>
            <input type="hidden" name="idmodulo" id="idmodulo" value="<?php echo $idmod;?>">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#collapse<?php echo $pacchetto['id'];?>"><?php echo $pacchetto['nome']; ?></a>
                </h4>
            </div>
            <div id="collapse<?php echo $pacchetto['id'];?>" class="panel-collapse collapse in">
                <div class="panel-body">
                    <?php
                    foreach ($elementipacchetto as $key=>$el) :
                        //genero l'elemento del form in base al tipo di elemento
                        $idelemento=$el['id'];
                        //echo "<pre>";
                        //print_r($el);
                        //echo "</pre>";
                        if ($el['obbligatorio']=='si') {
                            $required="REQUIRED";
                            $stringarequired=" (*)";
                        } else {
                            $required='';
                            $stringarequired='';
                        }
                        echo "<div class='row'>";
                        echo "<div class='col-xs-6 col-sm-6'>";
                        echo "<p style='text-align:right;'><b>".$el['nome_elemento']." $stringarequired : </b></p>";


                        echo "</div>";
                        //echo " (elemento indice "; echo $pacchetto['id']."-".$el['id']; echo ")";
                        echo "<div class='col-xs-6 col-sm-6'>";
                        if ($el['tipo_elemento']=='1') { //short text
                            $value=$defaultvalue['el_'.$idelemento];
                            echo "<input $required type='text' id='el_$idelemento' name='el_$idelemento' value='$value'/>";
                        }
                        if ($el['tipo_elemento']=='2') { //textarea
                            $value=$defaultvalue['el_'.$idelemento];
                            echo "<textarea id='el_$idelemento' name='el_$idelemento'>$value</textarea>";
                        }
                        if ($el['tipo_elemento']=='3') { //single choice
                            echo "<select id='el_$idelemento' name='el_$idelemento'>";
                            $tmp=explode(",",$el['opzioni_elemento']);
                            foreach ($tmp as $op) :
                                echo "<option>$op</option>";
                            endforeach;
                            echo "</select>";

                        }
                        if ($el['tipo_elemento']=='4') { //multiple choice
                            echo "<select multiple id='el_$idelemento' name='el_$idelemento'>";
                            $tmp=explode(",",$el['opzioni_elemento']);
                            foreach ($tmp as $op) :
                                echo "<option>$op</option>";
                            endforeach;
                            echo "</select>";

                        }/*
                        if ($el['tipoelemento']=='4') { //multiple choice

                        }*/
                        echo "</div>";
                        echo "</div>";


                    endforeach;
                    ?>
                    <div class="col-xs-12">&nbsp;</div>
                    <div style="text-align:center;">
                        <a class="btn btn-success salva" formid="<?php echo $pacchetto['id'];?>" id="salva_<?php echo $pacchetto['id'];?>"> <?php echo _("Salva");?></a>
                    </div>

                </div>
            </div>
        </div>

<?php endforeach; ?>
        </form>
    </div>

    <div class="col-md-4 col-sm-3">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#collapseSuggerimenti"><?php echo _("Suggerimenti");?></a>
                </h4>
            </div>
            <div id="collapseSuggerimenti" class="panel-collapse collapse in">
                <div class="panel-body">
                    Tutti i suggerimenti per questa parte
                </div>
            </div>
        </div>
    </div>
    </div>

    <hr>
    <?php include("INC_90_FOOTER.php");?>

    <script>
        $(document).ready(function(){

            $(".salva").click(function(){
                var params={};
                var idform=$(this).attr("formid");
                $.notify("<?php echo _("Salvataggio elemento in corso...");?>", "warn");
                //var datastring=$("#form_"+idform).serialize();
                var datastring=$("#form_0").serialize();

                console.log(datastring);

                $.ajax({
                    type: "POST",
                    url: "ajax_salvamoduliclienti.php",
                    data: datastring,
                    dataType: "json",
                    success: function(data) {
                        if (data.result==true) {
                            $.notify("<?php echo _("Salvataggio riuscito...");?>", "success");
                        } else {
                            $.notify("<?php echo _("Errore Salvataggio:");?>"+data.error, "error");
                        }
                    },
                    error: function() {
                        $.notify("<?php echo _("Errore Salvataggio Generico...");?>", "error");
                    }
                });

            })
        });
    </script>

</div> <!-- /container -->