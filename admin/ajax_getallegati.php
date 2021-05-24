<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

$idmod=$_REQUEST['idmod'];
$idele=$_REQUEST['idele'];
$debug=$_REQUEST['debug'];
//progettiamoci da sguardi indiscreti!
if ($debug!="VIACOLDEBUG") $debug=0;

$viewmode=$_REQUEST['view'];

$idmoduloFile=getModuloFrom_nome_tabella($GLOBAL_tb[files]);

if ($debug) {
    echo "viewmode:$viewmode";
    echo "<pre><h4>ajax_getmodulo.php</h4>";
    print_r($_REQUEST);
    echo "</pre>";
}

if ($idmod>0 and $idele!='') {
	$modulo=getModulo($idmod);
    $campi_visibility_hidden=explode(",",$modulo['campi_visibility_hidden']);
    $campi_readonly=explode(",",$modulo['campi_readonly']);
    $campi_readonly_on_update=explode(",",$modulo['campi_readonly_on_update']);
	$campi_nascosti=explode(",",$modulo['campi_nascosti']);
    if ($modulo['note']=='si' and $idele>0) { //solo in caso di update vedo le note!
        $note=getNote($idmod,$idele,$utente['id_user']);
        if ($debug) { echo "<b>note:</b>"; print_r($note); echo "<br/><hr/>";}
    }

	if ($modulo['nome_modulo']=="Plugins") {
		//aggiungo chaviesterne ai campi nascosti altrimenti si mangia l'espressione {}
        $campi_nascosti[]="chiaviesterne";
        $campi_nascosti[]="chiaviesternemultiple";
		$campi_nascosti[]="add_column";
	}


} else {
		setNotificheCRUD("admWeb","ERROR","ajax_getallegati.php","mod: $idmod, ele: $idele");
		echo "Modulo vuoto!";
		return false;
		exit;
}

$permessi=permessi($idmod,$utente['id_ruolo'],$superuserOverride);

if ($idele==-1) { //Crea nuovo elemento
	if ($permessi['Can_create']=='si') {
		$elemento=array();
        //forza gli elementi dell'array se glieli passo da query string
        if ($_REQUEST[k]) {
            list($key,$value)=explode("-",$_REQUEST[k]);
            $elemento[$key]=$value;
        }
        if ($_REQUEST[k1]) {
            list($key,$value)=explode("-",$_REQUEST[k1]);
            $elemento[$key]=$value;
        }
        if ($_REQUEST[k2]) {
            list($key,$value)=explode("-",$_REQUEST[k2]);
            $elemento[$key]=$value;
        }
	} else { ?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<?php echo _("<strong>Attenzione!</strong>Non puoi cancellare questo elemento!");?>
  		</div>
</div>
    <script>
	setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
	<?php
		exit;
	}

} else { //Update Elemento oppure view Elemento

    $elemento=getElemento($idmod,$idele);

foreach ($lingue as $lang):
    $testi=getTestiTraducibili($modulo['nome_tabella'],$idele,$lang);
    //if ($debug) { echo "<pre>"; print_r($testi); echo "</pre>"; }
	if (count($testi)>0) {
		foreach ($testi as $key=>$value) :
			$elemento[$key][$lang]=$value;
		endforeach;
	}
endforeach;

	//echo "<br/>idmod: ".$idmod;
	//echo "<br/>idele: ".$idele;
}

?>
<?php //inside modal-body ?>
<?php /* (i) ------------------------------------------ Generazione form in base ai campi della tabella -------------------------------------------- */?>

<?php /* (i) ---------------------- files ---------------- */ ?>
<?php if ($idele>0 and $modulo['allegati_possibili']=='si') : ?>
	<?php if ($modulo['max_files_immagini']) : ?>
		<div class="row border-dashed">
			<div class="col-xs-12 col-sm-12">
				<div class="box-inner">
					<div class="box-header well">
						<h2><?php echo _("Immagini");?></h2>
					</div>

					<div class="box-content">
						<div class="widget-main">

							<?php
							$files=array();
							$files=getFiles($idele,$modulo['nome_tabella'],'immagine',$modulo['max_files_immagini']);
							if (count($files)>0) : ?>
								<ul id="immagini-sortable" class="sortable">
									<?php
									foreach ($files as $f) :
										$pezzi=explode("/",$f['file']);
										$tmp=$pezzi[count($pezzi)-1];
										$pezzi[count($pezzi)-1]=$resizes[0]['prefisso']."_".$resizes[0]['width']."_".$resizes[0]['height']."_".$tmp;
										$nomefile=join("/",$pezzi);

										foreach ($lingue as $lang):
											$testif=getTestiTraducibili($GLOBAL_tb[files],$f['id_file'],$lang);
											if (count($testif)>0) {
												foreach ($testif as $key=>$value) :
													$f[$key][$lang]=$value;
												endforeach;
											}
										endforeach;
										?>

										<li class="file-sortable" id="file-<?php echo $f['id_file'];?>">
											<div class="row">
												<div class="col-xs-7">
													<img class="img-responsive" style="padding-bottom:5px;" width="300" height="300" alt="300x300" src="<?php echo $sitoweb.$nomefile;?>"/>
												</div>
												<div class="col-xs-5">
													<div class="tabbable">
														<ul class="nav nav-tabs" >
															<?php $ll=0;foreach ($lingue as $lang) : ?>
																<li <?php if ($ll==0) { ?> class="active" <?php } ?> >
																	<a data-toggle="tab" class="tablang tablang-<?php echo $lang;?>" data-lang="<?php echo $lang;?>" href=".panel-<?php echo $lang;?>">
																		<?php echo strtoupper($lang);?>
																	</a>
																</li>
																<?php $ll++;endforeach; ?>
														</ul>
														<div class="tab-content" style="height:70px;padding:10px;">
															<?php $ll=0;foreach ($lingue as $lang) : ?>
																<div id="panelfile-<?php echo $f['id_file'];?>-<?php echo $lang;?>" class="tab-pane fade panel-<?php echo $lang;?> <?php if ($ll==0) { ?>in active <?php } ?>">

																	<input class="col-xs-12" type="text"
																		   name="file-nome-<?php echo $f['id_file'];?>-<?php echo $lang;?>" id="file-nome-<?php echo $f['id_file'];?>-<?php echo $lang;?>"
																		   value="<?php echo $f['nome'][$lang];?>" />

																</div>

																<?php $ll++;endforeach; ?>
														</div>

													</div><!-- chiudo tabbable -->

												</div>

											</div>
											<hr/>
										</li>

										<?php
									endforeach;
									?>
								</ul>
								<?php
							endif;
							?>

						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($modulo['max_files_allegati']) : ?>
		<div class="row border-dashed">
			<div class="col-xs-12 col-sm-12">
				<div class="box-inner">
					<div class="box-header well">
						<h2><?php echo _("Allegati");?></h2>
					</div>

					<div class="box-content">
						<div class="widget-main">

							<?php
							$files_allegati=array();
							$files_allegati=getFiles($idele,$modulo['nome_tabella'],'allegato',$modulo['max_files_allegati']);
							if (count($files_allegati)>0) : ?>
								<ul id="allegati-sortable" class="sortable">
									<?php
									foreach ($files_allegati as $f) :
										foreach ($lingue as $lang):
											$testif=getTestiTraducibili($GLOBAL_tb[files],$f['id_file'],$lang);
											if (count($testif)>0) {
												foreach ($testif as $key=>$value) :
													$f[$key][$lang]=$value;
												endforeach;
											}
										endforeach;
										?>

										<li class="file-sortable" id="file-<?php echo $f['id_file'];?>">
											<div class="row">
												<div class="col-xs-7">
													<?php if (substr($f['file'],-3)=="pdf") {
														$pezzi=explode("/",$f['file']);
														$tmp=$pezzi[count($pezzi)-1];
														$pezzi[count($pezzi)-1]=$tmp;
														$nomefile=join("/",$pezzi);
														?>
														<a target="_blank" href="<?php echo $sitoweb.$f['file'];?>"><img class="img-responsive" style="padding-bottom:5px;" width="300" height="300" alt="300x300" src="<?php echo $sitoweb.$nomefile.".jpg";?>"/></a>
													<?php } else { ?>
														<a target="_blank" href="<?php echo $sitoweb.$f['file'];?>"><i style="font-size:80px;" class="ace-icon glyphicon glyphicon-file bigger-230"></i></a>
													<?php } ?>
                                                    <?php if ($modulo['nome_modulo']=="CaricamentoDati") { ?>
                                                        <?php $tokenarray['id_azienda']=$elemento['id_azienda'];?>
                                                        <?php $tokenarray['anno']=$elemento['anno'];?>
                                                        <?php $tokenarray['mese']=$elemento['mese'];?>
                                                        <?php $token=base64_encode(json_encode($tokenarray));?>
                                                        <?php $url=$sitedir."bilanciocaricato.php?token=".$token; ?>
                                                        <br/>
                                                        <!--<div class="vedicontoeconomico"><a target="_blank" href="<?php echo $url;?>"><?php echo _("Vedi Conto Economico");?></a></div>-->
                                                    <?php } ?>
												</div>
												<div class="col-xs-5">
													<div class="tabbable">
														<ul class="nav nav-tabs" >
															<?php $ll=0;foreach ($lingue as $lang) : ?>
																<li <?php if ($ll==0) { ?> class="active" <?php } ?> >
																	<a data-toggle="tab" class="tablang tablang-<?php echo $lang;?>" data-lang="<?php echo $lang;?>" href=".panel-<?php echo $lang;?>">
																		<?php echo strtoupper($lang);?>
																	</a>
																</li>
																<?php $ll++;endforeach; ?>
														</ul>
														<div class="tab-content" style="height:70px;padding:10px;">
															<?php $ll=0;foreach ($lingue as $lang) : ?>
																<div id="panelfile-<?php echo $f['id_file'];?>-<?php echo $lang;?>" class="tab-pane fade panel-<?php echo $lang;?> <?php if ($ll==0) { ?>in active <?php } ?>">

																	<input class="col-xs-12" type="text"
																		   name="file-nome-<?php echo $f['id_file'];?>-<?php echo $lang;?>" id="file-nome-<?php echo $f['id_file'];?>-<?php echo $lang;?>"
																		   value="<?php echo $f['nome'][$lang];?>" />
																</div>

																<?php $ll++;endforeach; ?>
														</div>

													</div><!-- chiudo tabbable -->

												</div>
											</div>
											<hr/>
										</li>

										<?php
									endforeach;
									?>
								</ul>
								<?php
							endif;
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>
<?php /* (f) ---------------------- files ---------------- */ ?>


<?php

exit;