<?php if ($modulo['nome_modulo']=='Contatti') : ?>

<?php else : ?>
<?php if ($bloccoprivacy==0) : ?>
    <div class="page-header">
    <div class="pull-left">
        <?php if ($_GET['sceglianno']>0) { $_SESSION['annoscelto']=$_GET['sceglianno']; } ?>

        <h3>Cliente: <?php echo $CLIENTE['RagioneSociale'];?> <?php if ($_SESSION['annoscelto']!='') { ?> anno <?php echo $_SESSION['annoscelto'];?> <?php } ?>
            <!--
                <select id="annoreport" name="annoreport">
                    <?php foreach ($annireport as $annoreport) : ?>
                        <option <?php if ($annoreport==$_SESSION['annoscelto']) echo "SELECTED"; ?>><?php echo $annoreport;?></option>
                    <?php endforeach;?>
                </select>-->
        </h3>
    </div>
    <div class="pull-right" id="barradinavigazione" <?php if ($token['anno']=='') { ?> style="display:none;" <?php } ?>>
        <div class="btn-group" role="group" aria-label="...">
            <?php $backlist=$_SERVER['REQUEST_URI'];?>
<?php /* ?>
            <?php $totanni=getAnni();?>
            <div class="btn-group">
                <button type="button" class="btn btn-default"><?php echo _("ANNO");?></button>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($totanni as $keyanno) : ?>
                        <li><a href="sceglianno.php?idazienda=<?php echo $token['id_azienda'];?>&backlist=<?php echo $backlist;?>&anno=<?php echo $keyanno;?>"><?php echo $keyanno;?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
 <?php */?>
            <?php

            if (count($newtoken)>0 && $_SERVER['SCRIPT_NAME']!='/admin/reportmese.php' && $_SERVER['SCRIPT_NAME']!='/admin/report.php') :
                //se sono dentro report o dentro reportmese tolgo la tendina ANNO

                ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default"><?php echo _("ANNO");?></button>
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($newtoken as $keyanno=> $tok) : ?>
                            <li><a href="sceglianno.php?token=<?php echo $tok;?>&idazienda=<?php echo $token['id_azienda'];?>&backlist=<?php echo $backlist;?>&anno=<?php echo $keyanno;?>"><?php echo $keyanno;?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php endif; ?>

            <?php if ($_SESSION['pcs_id_cliente']>0) { ?>
                <a class="btn btn-danger" href="module.php?modname=ProspettoPrevisionale&p[anno]=<?php echo $_SESSION['annoscelto'];?>&p[id_azienda]=<?php echo $token['id_azienda'];?>"><?php echo _("Prospetto Previsionale");?></a>
            <?php } else { ?>
                <!--<a class="btn btn-success" href="clienti.php?f[c-id]=<?php echo $token['id_azienda'];?>"><?php echo _("Scheda Cliente");?></a>-->
            <a class="btn btn-warning" href="module.php?modname=CaricamentoDati&p[anno]=<?php echo $_SESSION['annoscelto'];?>&p[id_azienda]=<?php echo $token['id_azienda'];?>"><?php echo _("Caricamento Dati");?></a>
            <a class="btn btn-danger" href="module.php?modname=ProspettoPrevisionale&p[anno]=<?php echo $_SESSION['annoscelto'];?>&p[id_azienda]=<?php echo $token['id_azienda'];?>"><?php echo _("Prospetto Previsionale");?></a>
            <?php } ?>
            <!-- Split button -->
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo _("Report");?>&nbsp;&nbsp;<span class="caret"></span>
                    <span class="sr-only">Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($newtoken as $keyanno => $tok) : ?>
                        <li><a href="report.php?token=<?php echo $tok;?>"><?php echo $keyanno;?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <a class="btn btn-primary" href="bilancioprecedente.php?token=<?php echo $newtoken[$_SESSION['annoscelto']];?>"><?php echo _("Bilancio Precedente");?></a>
            <a class="btn btn-info" href="module.php?modname=PianoDeiConti&p[id_azienda]=<?php echo $token['id_azienda'];?>"><?php echo _("Piano Dei Conti");?></a>

            <?php if ($utente['id_ruolo']==3) { ?>
                <a class="btn btn-success" href="get_element.php?backlist=<?php echo base64_encode($backlist);?>&view=1&idmod=80&idele=<?php echo $token['id_azienda'];?>"><?php echo _("Scheda Cliente");?></a>
            <?php } else { ?>
                <a class="btn btn-success" href="get_element.php?backlist=<?php echo base64_encode($backlist);?>&debug=0&idmod=9&idele=<?php echo $token['id_azienda'];?>"><?php echo _("Scheda Cliente");?></a>
            <?php } ?>

        </div>
    </div>
    <div class="clearfix"></div>
</div>
<?php else : ?>
    <?php exit; ?>
<?php endif; ?>
<?php endif; ?>
