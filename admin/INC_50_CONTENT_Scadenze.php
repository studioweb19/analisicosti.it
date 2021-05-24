<?php

$idmod=getModuloFrom_nome_modulo("Pagamenti");
$permessi=permessi($idmod,$utente['id_ruolo'],$superuserOverride);
$today=date('Y-m-d');
$datainiziale=validateDate($_REQUEST['datainizio']);
$datafinale=validateDate($_REQUEST['datafine']);
if ($datainiziale=='') {
    $datainiziale=date('Y-m-01');
}
if ($datafinale=='') {
    $datafinale = date('Y-m-01', strtotime('+3 months'));
}
$scadenze=getPolizzeScadenza(validateDate($datainiziale),validateDate($datafinale),$utente['id_user'],$permessi['Can_read_all']=='si');
$totscadenze=0;
foreach ($scadenze as $periodo=>$polizze) {
    $totscadenze=count($polizze)+$totscadenze;
}


$datainizialepag=validateDate($_REQUEST['datainiziopag']);
$datafinalepag=validateDate($_REQUEST['datafinepag']);
if ($datainizialepag=='') {
    $datainizialepag=date('Y-m-01');
}
if ($datafinalepag=='') {
    $datafinalepag = date('Y-m-01', strtotime('+3 months'));
}
$scadenzepagamenti=getPagamentiScadenza(validateDate($datainizialepag),validateDate($datafinalepag),$utente['id_user'],$permessi['Can_read_all']=='si');
$totscadenzepag=0;
foreach ($scadenzepagamenti as $periodo=>$pagamenti) {
    $totscadenzepag=count($pagamenti)+$totscadenzepag;
}
//default primo di questo mese e i prossimi 12 mesi
?>
<div class="container">
    <!-- Example row of columns -->
    <br/><br/>
    <div class="row">
        <div class="col-xs-12 col-sm-5 col-md-5">
            <div class="row">
                <div class="panel panel-warning">
                    <div class="panel-heading"><h4>Scadenze Polizze <span class="badge badge-info"><?php echo $totscadenze;?></span></h4>
                        <form class="form-inline">
                            <div class="form-group">
                                <label for="datainizio">Dal </label>
                                <div class="input-group">
                                    <?php $datetimepickervalue=$datainiziale;?>
                                    <input id="datainizio" name="datainizio" type="text" class="datepicker form-control" value="<?php echo TODDMMYYYY($datetimepickervalue);?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="datafine">Al </label>
                                <div class="input-group">
                                    <?php $datetimepickervalue=$datafinale;?>
                                    <input id="datafine" name="datafine" type="text" class="datepicker form-control" value="<?php echo TODDMMYYYY($datetimepickervalue);?>" />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Vai</button>
                        </form>
                    </div>
                    <div class="panel-body">
                        <?php foreach ($scadenze as $periodo=>$polizze) : ?>
                            <div class="col-xs-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <a class="pull-left"  data-toggle="collapse" href="#collapse_<?php echo $periodo;?>">
                                                    <?php list($aa,$mm)=explode("-",$periodo); ?><h4 ><?php echo $mese[$mm];?> <?php echo $aa;?></h4>
                                                </a>
                                                <a class="pull-right" data-toggle="collapse" href="#collapse_<?php echo $periodo;?>">
                                                    <h4><span class="badge badge-info"><?php echo count($polizze);?></span></h4>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="collapse_<?php echo $periodo;?>" class="panel-collapse collapse">
                                        <ul class="list-group">
                                            <?php foreach ($polizze as $polizza) : ?>
                                                <?php if ($polizza['scadenza']<$today) { ?>
                                                    <li class="list-group-item-danger list-group-item"><?php echo convertDate($polizza['scadenza']);?> <i><?php echo $polizza['Polizza'];?></i> <?php echo $polizza['Cliente'];?> <?php echo $polizza['stato_polizza'];?></li>
                                                <?php } else { ?>
                                                    <li class="list-group-item"><?php echo convertDate($polizza['scadenza']);?> <i><?php echo $polizza['Polizza'];?></i> <?php echo $polizza['Cliente'];?> <?php echo $polizza['stato_polizza'];?></li>
                                                <?php }?>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="panel-footer">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <a class="btn btn-danger btn-xs pull-right" data-toggle="collapse" href="#collapse_<?php echo $periodo;?>">Chiudi</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="hidden-xs col-sm-1 col-md-1">&nbsp;</div>

        <div class="col-xs-12 col-sm-5 col-md-5">
            <div class="row">
                <div class="panel panel-info">
                    <div class="panel-heading"><h4>Scadenze Pagamenti <span class="badge badge-info"><?php echo $totscadenzepag;?></span></h4>
                        <form class="form-inline">
                            <div class="form-group">
                                <label for="datainizio">Dal </label>
                                <div class="input-group">
                                    <?php $datetimepickervalue=$datainizialepag;?>
                                    <input id="datainiziopag" name="datainiziopag" type="text" class="datepicker form-control" value="<?php echo TODDMMYYYY($datetimepickervalue);?>" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="datafinepag">Al </label>
                                <div class="input-group">
                                    <?php $datetimepickervalue=$datafinalepag;?>
                                    <input id="datafinepag" name="datafinepag" type="text" class="datepicker form-control" value="<?php echo TODDMMYYYY($datetimepickervalue);?>" />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">Vai</button>
                        </form>

                    </div>
                    <div class="panel-body">
                        <?php foreach ($scadenzepagamenti as $periodo=>$pagamenti) : ?>
                        <?php
                            $totalelordo=0;
                            $totaleincassato=0;
                            foreach ($pagamenti as $pagamento) :
                                $totalelordo+=$pagamento['Lordo'];
                                $totaleincassato+=$pagamento['Incassato'];
                            endforeach; ?>
                            <div class="col-xs-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <a class="pull-left"  data-toggle="collapse" href="#pag_collapse_<?php echo $periodo;?>">
                                                    <?php list($aa,$mm)=explode("-",$periodo); ?><h4 ><?php echo $mese[$mm];?> <?php echo $aa;?> (<?php printf("%.2f",$totaleincassato);?> € su <?php  printf("%.2f",$totalelordo);?> €)</h4>
                                                </a>
                                                <a class="pull-right" data-toggle="collapse" href="#pag_collapse_<?php echo $periodo;?>">
                                                    <h4><span class="badge badge-info"><?php echo count($pagamenti);?></span></h4>
                                                </a>
                                            </div>
                                            <div class="col-xs-12">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?php printf("%.2d",100*$totaleincassato/$totalelordo);?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php printf("%.2d",100*$totaleincassato/$totalelordo);?>%;min-width:2em;">
                                                        <?php printf("%.2d",100*$totaleincassato/$totalelordo);?>%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="pag_collapse_<?php echo $periodo;?>" class="panel-collapse collapse">
                                        <ul class="list-group">
                                            <?php foreach ($pagamenti as $pagamento) : ?>

                                                <?php if ($pagamento['pagato']=='si') { ?>
                                                    <li class="list-group-item"><b><?php echo convertDate($pagamento['Scadenza']);?></b> <i><?php echo $pagamento['Polizza'];?></i> <?php echo $pagamento['Cliente'];?> <?php echo $pagamento['LordoFormattato'];?> <span class="label label-success">Pagato:<?php echo convertDate($pagamento['Pagamento']);?></span></li>
                                                <?php } else if ($pagamento['Scadenza']<$today) { ?>
                                                    <li class="list-group-item-danger list-group-item"><b><?php echo convertDate($pagamento['Scadenza']);?></b> <i><?php echo $pagamento['Polizza'];?></i>  <?php echo $pagamento['Cliente'];?> <?php echo $pagamento['LordoFormattato'];?></li>
                                                <?php } else { ?>
                                                    <li class="list-group-item"><b><?php echo convertDate($pagamento['Scadenza']);?></b> <i><?php echo $pagamento['Polizza'];?></i> <?php echo $pagamento['Cliente'];?> <?php echo $pagamento['LordoFormattato'];?></li>
                                                <?php }?>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="panel-footer">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <a class="btn btn-danger btn-xs pull-right" data-toggle="collapse" href="#collapse_<?php echo $periodo;?>">Chiudi</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row">


</div>

    <script type="text/javascript">
        jQuery(function($) {
            $(".datepicker").datepicker({autoclose: true,language: "it",format: "dd/mm/yyyy",todayHighlight: true});
            });
    </script>
    <hr>
    <?php include("INC_90_FOOTER.php");?>
</div> <!-- /container -->