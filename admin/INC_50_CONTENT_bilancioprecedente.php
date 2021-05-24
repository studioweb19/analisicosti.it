<div class="container" id="firstcontainer">
    <?php include("mostrainfo.php");?>
    <!-- INTESTAZIONE REPORT-->
    <?php $token=json_decode(base64_decode($_GET['token']),true);?>
    <?php if ($_GET['sceglianno']>0) { $token['anno']=$_GET['sceglianno']; } ?>

    <?php include("barranavigazione.php");?>

    <?php
    $query="SELECT * FROM pcs_dati_consuntivi JOIN pcs_piano_conti ON pcs_piano_conti.codiceconto=pcs_dati_consuntivi.codiceconto WHERE anno=? and mese='12' and pcs_piano_conti.id_azienda=? AND pcs_dati_consuntivi.id_azienda=? order by categoria ASC, pcs_dati_consuntivi.codiceconto ASC";
    $stmt=$dbh->prepare($query);
    $stmt->execute(array($token['anno']-1,$token['id_azienda'],$token['id_azienda']));
    while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $bilancio[$row['categoria']][]=$row;
    }
    ?>

    <h3>Bilancio anno <?php echo $token['anno']-1;?> </h3>
    <?php //print_r($token); ?>

    <div class="row">
        <div class="col-sm-9 col-xs-12">
            <?php
            if (count($bilancio)>0) {
            ?>
<table class="table">
    <tr class="bg-primary">
        <th style="text-align:center;" colspan="4">Costi Generali bilancio anno <?php echo $token['anno']-1;?></th>
    </tr>
<?php
        foreach ($bilancio as $key => $value) : //1 ricavi 2 acquisti 3 costi generali
            foreach ($value as $singolovalore) :
                if ($key == 1) {
                    $trclass = "bg-success";
                }
                if ($key == 2) {
                    $trclass = "bg-warning";
                }
                if ($key == 3) {
                    $trclass = "bg-danger";
                }
                ?>
                <tr class="<?php echo $trclass; ?>">
                    <?php if ($singolovalore['livello2'] != '') { ?>

                        <td><?php echo $singolovalore['codiceconto']; ?></td>
                        <td><?php echo $singolovalore['nome']; ?></td>
                        <td style="text-align:right;"><?php echo soldi($singolovalore['dare']); ?></td>
                        <td style="text-align:right;"><?php echo soldi($singolovalore['avere']); ?></td>

                    <?php } else { ?>

                        <th><?php echo $singolovalore['codiceconto']; ?></th>
                        <th><?php echo $singolovalore['nome']; ?></th>
                        <th style="text-align:right;"><?php echo soldi($singolovalore['dare']); ?></th>
                        <th style="text-align:right;"><?php echo soldi($singolovalore['avere']); ?></th>

                    <?php } ?>
                </tr>
                <?php
            endforeach;
        endforeach;
?>
</table>
<?php
            } else {
            echo "<h3>Nessun bilancio definitivo per l'anno ";echo $token['anno']-1;echo "</h3>";
            }
            ?>

        </div>
    </div>


    <hr>
    <?php include("INC_90_FOOTER.php");?>

</div> <!-- /container -->