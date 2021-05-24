<nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
    <div class="container">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php if ($utente['id_ruolo']!=4) { //studio ?>
            <a class="navbar-brand" href="#"><?php echo $clientescelto['RagioneSociale'];?></a>
            <?php } else { ?>
                <a class="navbar-brand" href="#">STUDIO: <?php echo $utente['Nome'];?> <?php echo $utente['Cognome'];?></a>
            <?php } ?>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <?php if ($bloccoprivacy==0) : ?>
            <ul class="nav navbar-nav">

                <li><a href="<?php echo $sitedir;?>"> <i class="fa fa-tachometer"></i> Home </a></li>

                <?php if ($utente['id_cliente']>0 and count($moduliattivati)>0) { ?>
                    <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo _("Moduli Attivi");?> (<?php echo count($moduliattivati);?>)<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                    <?php foreach ($moduliattivati as $mod) :
                        $urlmodulo="wizard.php?idmod=".$mod['id'];
                        ?>
                        <li><a href="<?php echo $sitedir;?><?php echo $urlmodulo;?>">
                            <i class="menu-icon <?php echo $mod['icona'];?>"></i>
                            <span class="menu-text"> <?php echo _($mod['nome']);?> </span>
                        </a>
                        <b class="arrow"></b>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                    </li>
                <?php } ?>



                <?php /* (i) ------------------------------------------------------------ MODULI --------------------------------------- */ ?>
                <?php $parsSidebar['stato']='attivo';$parsSidebar['menu']='si';$moduli=getModuli($parsSidebar); ?>

                <?php foreach ($moduli as $mod) :
                    $idmod=$mod['id_modulo'];
                    $permessi=permessi($idmod,$utente['id_ruolo'],$superuserOverride);

                    //se can_read=no allora non può vedere il modulo
                    if ($permessi['Can_read']=='no') continue;
                    $urlmodulo='module.php?id='.$idmod;
                    if ($mod['script_modulo']!='' and $mod['modulo_standard']=='no') $urlmodulo=$mod['script_modulo'];
                    ?>

                <?php if ($utente['id_ruolo']==3) { ?>
                    <?php if ($mod['nome_modulo']=="ModuliClienti") continue; ?>
                    <?php if ($mod['nome_modulo']=="Clienti") continue; ?>
                <?php } ?>

                    <?php if ($utente['id_ruolo']==4) { ?>
                    <?php if ($mod['nome_modulo']=="Utenti") continue; ?>
                <?php } ?>

                    <li <?php if (($_SERVER['REQUEST_URI'] ==$sitedir.$urlmodulo) || ($_GET[modname]==$mod['nome_modulo'])){ ?>class="active" <?php } ?>>
                        <a href="<?php echo $sitedir;?><?php echo $urlmodulo;?>">
                            <i class="menu-icon <?php echo $mod['font_icon'];?>"></i>
                            <span class="menu-text"> <?php echo _($mod['nome_modulo']);?> </span>
                        </a>
                        <b class="arrow"></b>
                    </li>

                <?php endforeach; ?>
                <?php /* (f) ------------------------------------------------------------ MODULI --------------------------------------- */ ?>

                <?php /* ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-star"></i> Clienti <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#"> <i class="fa fa-plus"></i> Nuovo Cliente</a></li>
                        <li><a href="#"> <i class="fa fa-list-ul"></i> Tutti i Clienti </a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-umbrella"></i> Polizze <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#"> <i class="fa fa-plus"></i> Nuova Polizza</a></li>
                        <li><a href="#"> <i class="fa fa-list-ul"></i> Tutte le polizze </a></li>
                        <li><a href="#"> <i class="fa fa-book"></i> Tipi di polizza</a></li>
                    </ul>
                </li>
                <li><a href="#"> <i class="fa fa-euro"></i> Incassi &nbsp;<span class="badge badge-important pull-right">4</span></a></a></li>
                <li><a href="#"> <i class="fa fa-file-text"></i> Fatturazione </a></li>
                <li><a href="#"> <i class="fa fa-bolt"></i> Sinistri</a></li>
                <li><a href="#"> <i class="fa fa-calendar"></i> Promemoria &nbsp;<span class="badge badge-important pull-right">2</span></a></li>

                <?php */ ?>


                <li><a href="<?php echo $sitedir;?>documentazione.php"> <i class="fa fa-info-circle"></i> Documentazione </a></li>

                <?php if ($utente['id_ruolo']==4) { ?>
                    <li><a href="<?php echo $sitedir;?>facsimile.php"> <i class="fa fa-file-excel-o"></i> Facsimile file </a></li>
                    <li><a href="get_element.php?debug=0&idmod=4&idele=<?php echo $utente['id_user'];?>"> <i class="fa fa-wrench"></i> Impostazioni </a></li>

                <?php } ?>

            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php"> <i class="fa fa-sign-out"></i> Logout </a></li>
                <?php /* ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $utente['Nome'];?> <?php echo $utente['Cognome'];?> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="logout.php"> <i class="fa fa-sign-out"></i> Logout </a></li>
                    </ul>
                </li>
                <?php */ ?>
            </ul>
            <?php endif; ?>

        </div><!--/.navbar-collapse -->
    </div>
</nav>

<?php //estraggo i parametri del progetto di bilancio
$query0="SELECT anno FROM pcs_progetto_bilancio WHERE id_azienda=? ";
$stmt0=$dbh->prepare($query0);
$stmt0->execute(array($token['id_azienda']));
$annireport=array();
$newtoken=array();
while ($row0=$stmt0->fetch(PDO::FETCH_ASSOC)) {
    $annireport[]=$row0['anno'];

    $tmp['id_azienda']=$token['id_azienda'];
    $tmp['anno']=$row0['anno'];
    $newtoken[$row0['anno']]=base64_encode(json_encode($tmp));

}
?>
<?php //estraggo i parametri del progetto di bilancio
$query1="SELECT * FROM pcs_progetto_bilancio WHERE anno=? AND id_azienda=? ";
$stmt1=$dbh->prepare($query1);
$stmt1->execute(array($token['anno'],$token['id_azienda']));
$PROGETTO=$stmt1->fetch(PDO::FETCH_ASSOC);
?>

<?php //CLIENTE
$query1="SELECT * FROM pcs_clienti WHERE id=?";
$stmt1=$dbh->prepare($query1);
$stmt1->execute(array($token['id_azienda']));
$CLIENTE=$stmt1->fetch(PDO::FETCH_ASSOC);
//print_r($CLIENTE);
?>

<?php //controllo a che punto siamo aggiornati
$query="SELECT * FROM pcs_analisi_costi WHERE anno=? and id_azienda=?";
$stmt=$dbh->prepare($query);
$stmt->execute(array($token['anno'],$token['id_azienda']));
while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
    $campi[$row['mese']]=$row;
}
$maxmese=max(array_keys($campi));
//echo "<pre>";
//print_r($campi);
//echo "</pre>";
?>

<?php //controllo l'anno precedente
$query="SELECT * FROM pcs_analisi_costi WHERE anno=? and id_azienda=?";
$stmt=$dbh->prepare($query);
$stmt->execute(array($token['anno']-1,$token['id_azienda']));
while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
    $campiprec[$row['mese']]=$row;
}
if (count($campiprec)>0) $maxmeseprec=count(array_keys($campiprec));
//echo "<pre>";
//print_r($campi);
//echo "</pre>";
?>



<?php
$tokenmese=array();
for ($i=1;$i<13;$i++) :
    $ii=sprintf("%02d",$i);
    $tokenarray[$ii]['id_azienda']  =$token['id_azienda'];
    $tokenarray[$ii]['anno']        =$token['anno'];
    $tokenarray[$ii]['mese']        =$ii;
    $tokenmese[$ii]=base64_encode(json_encode($tokenarray[$ii]));
endfor;
?>
<!--
    <?php foreach ($newtoken as $keyanno => $newtok) : ?>
        <input type="hidden" name="newtoken_<?php echo $keyanno;?>" id="newtoken_<?php echo $keyanno;?>" value="<?php echo $newtok;?>"/>
    <?php endforeach;?>
 -->