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


	$chiaviesternearray=array();
    if ($debug) { echo "<b>modulo chiaviesterne:</b>";echo $modulo['chiaviesterne'];echo "<br/>"; }
	$chiaviesternearray=json_decode($modulo['chiaviesterne'],true);

    if ($debug) { echo "<b>chiavi esterne array:</b>";print_r($chiaviesternearray); echo "<br/>"; }

	$chiaviesterne=array();
	if (count($chiaviesternearray)>0)
		$chiaviesterne=array_keys($chiaviesternearray);

    if ($debug) { echo "<b>chiavi esterne:</b>";print_r($chiaviesterne); echo "<br/><hr/>"; }

    $chiaviesternemultiplearray=array();
    if ($debug) { echo "<b>modulo chiaviesterne multiple:</b>";echo $modulo['chiaviesternemultiple'];echo "<br/>"; }
    $chiaviesternemultiplearray=json_decode($modulo['chiaviesternemultiple'],true);

    if ($debug) { echo "<b>chiavi esterne multiple array:</b>";print_r($chiaviesternemultiplearray); echo "<br/>"; }

    $chiaviesternemultiple=array();
    if (count($chiaviesternemultiplearray)>0)
        $chiaviesternemultiple=array_keys($chiaviesternemultiplearray);

    if ($debug) { echo "<b>chiavi esterne multiple:</b>";print_r($chiaviesternemultiple); echo "<br/><hr/>";}
    
    
} else {
		setNotificheCRUD("admWeb","ERROR","ajax_getmodulo.php","mod: $idmod, ele: $idele");
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

    if ($viewmode) {

    } else {
        if ($permessi['Can_update'] == 'si') {

        } else { ?>
            <div class="registrazionerror alert alert-danger" role="alert">
                <div class="center">
                    <?php echo _("<strong>Attenzione!</strong>Non puoi modificare questo elemento!"); ?>
                </div>
            </div>
            <script>
                setTimeout(function () {
                    $(".registrazionerror").hide();
                }, 2000);
            </script>
            <?php
            exit;
        }
    }

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
												<div id="messaggiovalidazione"></div>
												<form id="nuovo_elemento" name="nuovo_elemento" method="post">
												<input type="hidden" name="id_user" id="id_user" value="<?php echo $_SESSION['id_user'];?>"/>
												<input type="hidden" name="idmod" id="idmod" value="<?php echo $idmod;?>"/>
												<input type="hidden" name="idele" id="idele" value="<?php echo $idele;?>"/>
												<input type="hidden" name="backurl" id="backurl" value="<?php echo $_REQUEST['backurl'];?>"/>

												<?php 	$campiobbligatori=array();
														$query="SHOW FULL COLUMNS FROM ".$modulo['nome_tabella'];
														$stmt=$dbh->query($query);
														$columns=array();
														$fields=array();
														while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) :
															//escludo la chiave primaria
															if ($row['Key']=='PRI') continue;

                                                            if (in_array($row['Field'],$campi_readonly) || $viewmode) $row['readonly']='si';
                                                            //se sono in update, devo escludere i campi readonly on update
                                                            if ($idele>0) {
                                                                if (in_array($row['Field'],$campi_readonly_on_update) || $viewmode) $row['readonly']='si';
                                                            }

															if (in_array($row['Field'],$campi_nascosti)) continue;

															//chiavi esterne, creo la select
															if (in_array($row['Field'],$chiaviesterne)) {
																$row['chiaveesterna']=$chiaviesternearray[$row['Field']];
															}

                                                            //chiavi esterne multiple, creo la select
                                                            if (in_array($row['Field'],$chiaviesternemultiple)) {
                                                                $row['chiaveesternamultipla']=$chiaviesternemultiplearray[$row['Field']];
                                                            }

                                                            //costruisco l'array dei campi con il loro tipo e la loro "obbligatorietà"
															$columns[]=$row;
															$fields[$row['Field']]=1;
															if ($row['Null']=='NO') {
																$campiobbligatori[$row['Field']]=1;
															}
														endwhile;

														//aggiungo i campi traducibili in fondo

														$fieldstraducibili=array();
                                                        $tbcampitraducibili=$GLOBAL_tb['campi_traducibili'];
														$query2="SELECT * FROM $tbcampitraducibili WHERE nome_tabella='".$modulo['nome_tabella']."' order by ordine";
														if ($stmt2=$dbh->query($query2)) {
                                                            while ($row=$stmt2->fetch(PDO::FETCH_ASSOC)) :
                                                                if ($row['html']=='si') {
                                                                    $col['Type']="text";
                                                                } else {
                                                                    $col['Type']="varchar(100)";
                                                                }
                                                                if ($row['obbligatorio']=='si') {
                                                                    $col['Null']="NO";
																	$campiobbligatori[$col['Field']]=1;
                                                                } else {
                                                                    $col['Null']="";
                                                                }
                                                                if (in_array($row['nome_campo'],$campi_readonly) || $viewmode) $col['readonly']='si';
                                                                $col['Field']=$row['nome_campo'];
                                                                $col['Traducibile']='si';
                                                                $fieldstraducibili[$col['Field']]=1;
                                                                $columns[]=$col;
                                                            endwhile;
                                                        }

														$elencocampi=join(",",array_keys($fields));
														$elencocampitraducibili=join(",",array_keys($fieldstraducibili));
                                                        $elencocampiobbligatori=join(",",array_keys($campiobbligatori));
                                                        $campiset=array();?>
												<input type="hidden" name="elencocampi"                 id="elencocampi"                    value="<?php echo $elencocampi;?>"/>
												<input type="hidden" name="elencocampitraducibili"      id="elencocampitraducibili"         value="<?php echo $elencocampitraducibili;?>"/>
                                                <input type="hidden" name="elencocampiobbligatori"      id="elencocampiobbligatori"         value="<?php echo $elencocampiobbligatori;?>"/>
<?php

//$colsmprimacolonna=6;
//$colsmsecondacolonna=5;

//if ($modulo['aprimodal']=='si') {
    $colsmprimacolonna=12;
    $colsmsecondacolonna=12;
//}

/*
------------------------------------------------------------------------------------------------------------------------------------
//
// Le colonne sono costruite leggendo prima i campi della query dentro la tabella moduli
// poi vengono aggiunti i campi presenti nella tabella "Traduzioni"
// si forma pertanto l'array columns, dove gli ultimi n elementi saranno i campi traducibili, contrassegnato con il campo 'Traducibile'='si'
// se il campo è traducibile, creo le linguette delle lingue, una per ogni campo
// e poi chiudo
// in fase di inserimento dati, mando in post nel campo hidden l'elenco dei campi (nomeprincipale) traducibili e poi li splitto per assegnarli alle lingue
// esempio, se i campi sono titolo-it, titolo-en, titolo-fr io manderò nel campo hidden campitraducibili il nome "titolo"
-------------------------------------------------------------------------------------------------------------------------------------
*/?>
                                                    <div class="row border-dashed col-xs-12 col-sm-<?php echo $colsmprimacolonna;?>" ><!-- prima colonna -->
                                                    <?php
														foreach ($columns as $col) :

														?>
														<div class="row-new" id="DIV_<?php echo $col['Field'];?>" <?php if (in_array($col['Field'],$campi_visibility_hidden)) echo "style='display:none;'";?>>
														<?php if ($modulo['aprimodal']=='si') { ?>
															<div class="col-xs-12 col-sm-6 col-md-6">
														<?php } else { ?>


																<?php if (getTipoColonna($col['Type'])=="TEXTAREA") { ?>
																<div class="col-xs-12 ">
															<?php } else { ?>
																<div class="col-xs-12 col-sm-6 col-md-3">
																<?php }?>

														<?php } ?>
																<div class="form-group"  >
																	<label><?php echo cambianomecampi($idmod,$col['Field']);?>
																	<?php if ($col['Null']=='NO')
																	{
																		echo " (*) ";
																	} ?>
																	</label>

																<?php /* (i) creo il panel con le linguette */ ?>
																	<?php if ($col['Traducibile']=='si') : ?>

													<div class="tabbable">
														<ul class="nav nav-tabs" >
														<?php $ll=0;foreach ($lingue as $lang) : ?>
															<li <?php if ($ll==0) { ?> class="active" <?php } ?> >
																<!--<a data-toggle="tab" class="tablang" data-lang="<?php echo $lang;?>" href="#panel-<?php echo $col['Field'];?>-<?php echo $lang;?>">-->
																<a data-toggle="tab" class="tablang tablang-<?php echo $lang;?>" data-lang="<?php echo $lang;?>" href=".panel-<?php echo $lang;?>">
																	<?php echo strtoupper($lang);?>
																</a>
															</li>
														<?php $ll++;endforeach; ?>
														</ul>

																	<?php endif; ?><?php // ?>
																<?php /* (i) creo il panel con le linguette */ ?>

																	<div>
																		<?php
																		/* (i) cerco le chiavi esterne */
																		if ($col['chiaveesterna']) :
																			$query_est=$col['chiaveesterna'];


                                                                            //if ($debug) print_r($permessi);

                                                                            if ($debug) echo "<b>chiaveesterna -> queryest nativa:</b>".$query_est."<br/><hr/>";
                                                                            if ($debug) print_r($elemento);
                                                                            $nuovaqueryest=str_replace("%chiaveprimaria%",$idele,$query_est);
                                                                            $nuovaqueryest=str_replace("%id_user%",$utente['id_user'],$nuovaqueryest);
                                                                            $nuovaqueryest=str_replace("%id_ruolo%",$utente['id_ruolo'],$nuovaqueryest);
                                                                            $nuovaqueryest=str_replace("%id_cliente%",$utente['id_cliente'],$nuovaqueryest);
                                                                            $canreadall=$permessi['Can_read_all']=='si' ? 1 : 0;
                                                                            $nuovaqueryest=str_replace("%can_read_all%",$canreadall,$nuovaqueryest);
                                                                            $nuovaqueryest=str_replace("%defaultLang%",$defaultLang,$nuovaqueryest);
                                                                            $nuovaqueryest=str_replace("%lang%",$_SESSION['lang'],$nuovaqueryest);
                                                                            $nuovaqueryest=str_replace("%chiaveprimaria%",$elemento['chiaveprimaria'],$nuovaqueryest);


                                                                            if ($debug) echo "<b>chiaveesterna -> nuovaqueryest:</b>".$nuovaqueryest."<br/><hr/>";

																			$stmt=$dbh->query($nuovaqueryest);
																			$enum_est=array();
																			if ($col['Null']=='YES') {
																				$enum_est['']='';
																			}
																			while ($row_est=$stmt->fetch(PDO::FETCH_ASSOC)) {
																				$enum_est[$row_est['id']]=$row_est['value'];
																			}
																		?>
																			<select class="form-control chosen-select" id="<?php echo $col['Field'];?>" name="<?php echo $col['Field'];?>" <?php if ($col['readonly']=='si') echo "disabled";?> data-placeholder="<?php echo $col['Field'];?>">
																			<?php foreach ($enum_est as $key=>$en) : ?>
																			<option <?php if ($elemento[$col['Field']]==$key) {  echo "selected"; } ?> value="<?php echo $key;?>"><?php echo $en;?></option>
																			<?php endforeach; ?>
																			</select>

																		<?php
                                                                            //continue; // ? non capisco perché il continue
																		endif;

																		/* (f) cerco le chiavi esterne */

                                                            /* (i) cerco le chiavi esterne multiple*/
                                                            if ($col['chiaveesternamultipla']) :
                                                                $query_est=$col['chiaveesternamultipla'];

                                                                $nuovaqueryest=str_replace("%chiaveprimaria%",$idele,$query_est);
                                                                $nuovaqueryest=str_replace("%id_user%",$utente['id_user'],$nuovaqueryest);
                                                                $nuovaqueryest=str_replace("%id_ruolo%",$utente['id_ruolo'],$nuovaqueryest);
                                                                $nuovaqueryest=str_replace("%id_cliente%",$utente['id_cliente'],$nuovaqueryest);
                                                                $canreadall=$permessi['Can_read_all']=='si' ? 1 : 0;
                                                                $nuovaqueryest=str_replace("%can_read_all%",$canreadall,$nuovaqueryest);
                                                                $nuovaqueryest=str_replace("%defaultLang%",$defaultLang,$nuovaqueryest);
                                                                $nuovaqueryest=str_replace("%lang%",$lang,$nuovaqueryest);

                                                                if ($debug) echo "<b>chiave esterna multipla -> nuovaqueryest:</b>".$nuovaqueryest."<br/><hr/>";

                                                                $valoriattuali=explode(",",$elemento[$col['Field']]);

                                                                $stmt=$dbh->query($nuovaqueryest);
                                                                $enum_est=array();
                                                                if ($col['Null']=='YES') {
                                                                    $enum_est['']='';
                                                                }
                                                                $campiset[]=$col['Field'];
                                                                while ($row_est=$stmt->fetch(PDO::FETCH_ASSOC)) {
                                                                    $enum_est[$row_est['id']]=$row_est['value'];
                                                                }
                                                                                ?>



                <div class="control-group">
                    <div class="controls">
                        <select name="<?php echo $col['Field'];?>[]" id="<?php echo $col['Field'];?>" multiple class="form-control" data-rel="chosen" <?php if ($col['readonly']=='si') echo "disabled";?>>
                                                           <?php foreach ($enum_est as $en=>$val) : ?>
                                                                    <option <?php if (in_array($en,$valoriattuali)) {  echo "SELECTED"; } ?> value="<?php echo $en;?>"><?php echo $val;?></option>
                                                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                                                                <?php
                                                                  //continue; // ? non capisco perché il continue
                                                            endif;

                                                            /* (f) cerco le chiavi esterne multiple*/

                                                                        if (!(($col['chiaveesternamultipla']) or ($col['chiaveesterna']))) :

																		switch (getTipoColonna($col['Type'])) {

																		/* (i) ----------------------- INTEGER ----------------------- */

																		case 'INTEGER' : //mettere se è integer un counter
																		?>

																		<div class="input-group">
                                                                        <input  class="col-xs-12 form-control" type="text" <?php if ($col['readonly']=='si') echo "readonly";?>
																		name="<?php echo $col['Field'];?>" id="<?php echo $col['Field'];?>"
																		value="<?php echo $elemento[$col['Field']];?>" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="fa fa-sort-numeric-asc"></i>
                                                                                                    </span>
																		</div>

																		<?php
																		break;

																		/* (f) ----------------------- INTEGER ----------------------- */

																		    /* (i) ----------------------- NUMERIC ----------------------- */

																		case 'NUMERIC' : //mettere se è integer un counter
																		?>

																		<div class="input-group">
																		<input  class="col-xs-12 form-control" type="text" <?php if ($col['readonly']=='si') echo "readonly";?>
																		name="<?php echo $col['Field'];?>" id="<?php echo $col['Field'];?>"
																		value="<?php echo $elemento[$col['Field']];?>" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="fa fa-sort-numeric-asc"></i>
                                                                                                    </span>
																		</div>

																		<?php
																		break;

																		    /* (f) ----------------------- NUMERIC ----------------------- */

																		    /* (i) ----------------------- DECIMAL ----------------------- */

																		case 'DECIMAL' : //mettere se è integer un counter
																		?>

																		<div class="input-group">
																		<input  class="col-xs-12 form-control" type="text" <?php if ($col['readonly']=='si') echo "readonly";?>
																		name="<?php echo $col['Field'];?>" id="<?php echo $col['Field'];?>"
																		value="<?php echo $elemento[$col['Field']];?>" />
																			<span class="input-group-addon">
                                                                                <i class="glyphicon glyphicon-euro"></i>
																			</span>
																		</div>

																		<?php
																		break;

																		    /* (f) ----------------------- DECIMAL ----------------------- */

																		    /* (i) ----------------------- DATETIME ----------------------- */

																		case 'DATETIME':
																		?>

																		<div class="input-group">
                                                                            <?php $datetimepickervalue=$elemento[$col['Field']];
                                                                            list($first,$i,$s)=explode(":",$datetimepickervalue);
                                                                            $datetimepickervalue=$first.":".$i;?>
																			<input  <?php if ($col['readonly']=='si') echo "readonly";?> id="<?php echo $col['Field'];?>" name="<?php echo $col['Field'];?>" type="text" class="datetimepicker form-control" value="<?php echo $datetimepickervalue;?>" />
																			<span class="input-group-addon">
                                                                                <i class="glyphicon glyphicon-calendar"></i>
																			</span>
																		</div>

																		<?php
																		break;

																		    /* (f) ----------------------- DATETIME ----------------------- */


                                                                            /* (i) ----------------------- DATE ----------------------- */

                                                                        case 'DATE':
                                                                            ?>

                                                                        <div class="input-group">
                                                                            <?php $datetimepickervalue=$elemento[$col['Field']];?>
                                                                            <input  <?php if ($col['readonly']=='si') echo "readonly";?> id="<?php echo $col['Field'];?>" name="<?php echo $col['Field'];?>" type="text" class="datepicker form-control" value="<?php echo TODDMMYYYY($datetimepickervalue);?>" />
                                                                            <span class="input-group-addon">
                                                                                <i class="glyphicon glyphicon-calendar"></i>
                                                                            </span>
                                                                        </div>

                                                                        <?php
                                                                        break;

                                                                            /* (f) ----------------------- DATE ----------------------- */


                                                                            /* (i) ----------------------- TIME ----------------------- */

                                                                            case 'TIME':
                                                                                ?>
                                                                                <div class="input-group bootstrap-timepicker timepicker">
                                                                                    <?php $datetimepickervalue=$elemento[$col['Field']];?>
                                                                                    <input  <?php if ($col['readonly']=='si') echo "readonly";?> id="<?php echo $col['Field'];?>" name="<?php echo $col['Field'];?>" type="text" class="timepicker_interno form-control" value="<?php echo $datetimepickervalue;?>" />
                                                                            <span class="input-group-addon">
                                                                                <i class="glyphicon glyphicon-time"></i>
                                                                            </span>
                                                                                </div>

                                                                                <?php
                                                                                break;

                                                                            /* (f) ----------------------- TIME ----------------------- */

                                                                            /* (i) ----------------------- ENUM ----------------------- */

																		case 'ENUM':

																		$enumvalues=getEnumValues($modulo['nome_tabella'],$col['Field']);
																		?>
                                                                        <?php
                                                                            if (count($enumvalues)<1) {
																		?>

                                                                        <div class="radio">
																			<?php foreach ($enumvalues as $en) : ?>
																				<label>
																					<input  name="<?php echo $col['Field'];?>" type="radio" value="<?php echo $en;?>" class="ace" <?php if ($col['readonly']=='si') echo "disabled";?> <?php if ($elemento[$col['Field']]==$en) {  echo "checked"; } ?>/>
																					<span class="lbl"> <?php echo $en;?></span>
																				</label>&nbsp;&nbsp;&nbsp;&nbsp;
																			<?php endforeach; ?>
																		</div>

																		<?php
																		} else { ?>

																			<select  class="chosen-select form-control" id="<?php echo $col['Field'];?>" name="<?php echo $col['Field'];?>" <?php if ($col['readonly']=='si') echo "disabled";?>  data-placeholder="<?php echo $col['Field'];?>">
																			<?php foreach ($enumvalues as $en) : ?>
																			<option <?php if ($elemento[$col['Field']]==$en) {  echo "selected"; } ?> value="<?php echo $en;?>"><?php echo $en;?></option>
																			<?php endforeach; ?>
																			</select>


																		<?php
																		} ?>


																		<?php
																		break;

																		/* (f) ----------------------- ENUM ----------------------- */

                                                                        /* (i) ----------------------- SET ----------------------- */

                                                                            case 'SET':

                                                                                $campiset[]=$col['Field'];
                                                                                $setvalues=getSetValues($modulo['nome_tabella'],$col['Field']);
                                                                                $arraycheckbox=explode(",",$elemento[$col['Field']]);
                                                                                ?>

                                                                                <div class="checkbox">
                                                                                    <?php foreach ($setvalues as $en) : ?>
                                                                                        <label>
                                                                                            <input  name="<?php echo $col['Field'];?>[]" type="checkbox" value="<?php echo $en;?>" class="ace" <?php if ($col['readonly']=='si') echo "disabled";?> <?php if (in_array($en,$arraycheckbox)) {  echo "checked"; } ?>/>
                                                                                            <span class="lbl"> <?php echo $en;?></span>
                                                                                        </label>&nbsp;&nbsp;&nbsp;&nbsp;
                                                                                    <?php endforeach; ?>
                                                                                </div>

                                                                                <?php
                                                                                break;

                                                                        /* (f) ----------------------- SET ----------------------- */

                                                                        /* (i) ----------------------- TEXT ----------------------- */

																		case 'TEXT':
																		?>

																	<?php if ($col['Traducibile']=='si') { // (1 inizio) if then else campo traducibile ?>
																		<div class="tab-content" style="height:70px;padding:10px;">
																		<?php $ll=0;foreach ($lingue as $lang) : ?>
																			<div id="panel-<?php echo $col['Field'];?>-<?php echo $lang;?>" class="tab-pane fade panel-<?php echo $lang;?> <?php if ($ll==0) { ?>in active <?php } ?>">


																		<div class="input-group">
																		<input  class="col-xs-12 form-control" type="text" <?php if ($col['readonly']=='si') echo "readonly";?>
																			   name="<?php echo $col['Field'];?>-<?php echo $lang;?>" id="<?php echo $col['Field'];?>-<?php echo $lang;?>"
																		value="<?php echo $elemento[$col['Field']][$lang];?>" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="fa fa-sort-alpha-asc"></i>
                                                                                                    </span>
                                                                        </div>

																			</div>

																		<?php $ll++;endforeach; ?>
																		</div>

																	</div><!-- chiudo tabbable -->

																	<?php } else {  // (2) if then else campo traducibile ?>

																		<div class="input-group">
																		<input  class="col-xs-12 form-control" type="text" <?php if ($col['readonly']=='si') echo "readonly";?>
																		name="<?php echo $col['Field'];?>" id="<?php echo $col['Field'];?>"
																		value="<?php echo $elemento[$col['Field']];?>" />
                                                                                                    <span class="input-group-addon">
                                                                                                        <i class="fa fa-sort-alpha-asc"></i>
                                                                                                    </span>
                                                                        </div>

																	<?php } // (3 fine) if then else campo traducibile ?>

																		<?php
																		break;

																		/* (f) ----------------------- TEXT ----------------------- */

																		/* (i) ----------------------- TEXTAREA ----------------------- */

																		case 'TEXTAREA': $textarea++;
																		?>


																	<?php if ($col['Traducibile']=='si') { // (1 inizio) if then else campo traducibile ?>
																		<div class="tab-content" style="height:360px;padding:10px;">
																		<?php $ll=0;foreach ($lingue as $lang) : ?>
																			<div id="panel-<?php echo $col['Field'];?>-<?php echo $lang;?>" class="tab-pane fade panel-<?php echo $lang;?> <?php if ($ll==0) { ?>in active <?php } ?>">
                                                                                <?php if ($col['readonly']=='si') { ?>
                                                                                     <div class="col-xs-12" ><?php echo $elemento[$col['Field']][$lang];?></div>
                                                                                <?php } else { ?>
                                                                                    <textarea  name="<?php echo $col['Field'];?>-<?php echo $lang;?>" id="<?php echo $col['Field'];?>-<?php echo $lang;?>" class="textarea col-xs-12 ckeditortextarea" ><?php echo $elemento[$col['Field']][$lang];?></textarea>
                                                                                <?php }?>

																			</div>

																		<?php $ll++;endforeach; ?>
																		</div>
																	</div><!-- chiudo tabbable -->

																	<?php } else {  // (2) if then else campo traducibile ?>

                                                                                <?php if ($col['readonly']=='si') { ?>
                                                                                    <div class="col-xs-12" ><?php echo $elemento[$col['Field']];?></div>
                                                                                <?php } else { ?>
                                                                                    <textarea  name="<?php echo $col['Field'];?>" id="<?php echo $col['Field'];?>" class="textarea col-xs-12 ckeditortextarea"><?php echo $elemento[$col['Field']];?></textarea>
                                                                                <?php }?>

																	<?php } // (3 fine) if then else campo traducibile ?>

																		<?php
																		break;

																		/* (f) ----------------------- TEXTAREA ----------------------- */


																		?>


																		<?php
																		} //end switch


																		endif; ?>
																	</div>
																</div>
																<div style="clear:both;"></div>
																<div class="space-4"></div>
															</div>
														</div>

														<?php
														endforeach;
														?>

                                                    </div><!-- prima colonna -->
                                                    <div class="row col-xs-12 col-sm-1" ></div><!--spazio intermedio-->
                                                    <div class="row col-xs-12 col-sm-<?php echo $colsmsecondacolonna;?>" ><!-- seconda colonna -->

                                                    <?php if (($modulo['note']=='si') or (count($note)>0)) : ?>
                                                    <div class="row border-dashed">
        												<div class="col-xs-12 col-sm-12">
                                                            <div class="box-inner">
                                                            <div class="box-header well">
                                                                <h2><?php echo _("Note");?></h2>
                                                            </div>

                                                            <div class="box-content">
                                                                <div class="row col-xs-12 col-sm-12">
                                                                    <?php if (count($note)>0) : ?>
                                                                        <div id="accordion1" class="accordion-style1 panel-group">
                                                                            <?php foreach ($note as $n) : ?>
                                                                                <div class="panel panel-default">
                                                                                    <div class="panel-heading">
                                                                                        <h4 class="panel-title">
                                                                                            <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse1<?php echo $n['id'];?>">
                                                                                                <i class="ace-icon fa fa-angle-right bigger-110" data-icon-hide="ace-icon fa fa-angle-down" data-icon-show="ace-icon fa fa-angle-right"></i><?php echo _("Nota del ");?> <?php echo $n['data_nota'];?>
                                                                                            </a>
                                                                                        </h4>
                                                                                    </div>

                                                                                    <div class="panel-collapse collapse" id="collapse1<?php echo $n['id'];?>">
                                                                                        <div class="panel-body">
                                                                                            <?php echo $n['nota'];?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php if ($modulo['note']=='si') : ?>
                                                                <?php /* (i) Nuova nota */ ?>
                                                                <div class="form-group">
                                                                    <label><span class="label label-info"><?php echo _("Inserisci una nota");?></span></label>
                                                                    <div>
                                                                        <textarea class="col-sm-12" name="modulo_nota" id="modulo_nota"></textarea>
                                                                    </div>
                                                                </div>
                                                                    <div style="clear:both;"></div>
                                                                    <div class="space-4"><br/></div>
                                                                <?php /* (f) Nuova nota */ ?>
                                                            <?php endif;?>
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>

												<?php /* (i) ---------------------- files ---------------- */ ?>
												<?php if ($idele>0 and $modulo['allegati_possibili']=='si') : ?>
                                                    <?php /* ?>

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
																							<div class="col-xs-3">
																							<img class="img-responsive" style="padding-bottom:5px;" width="150" height="150" alt="150x150" src="<?php echo $sitoweb.$nomefile;?>"/>
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
																							<div class="col-xs-2">
																							<?php if (!($viewmode)) { ?>
                                                                                                <a class="btn btn-danger cancellafile" idmodalmod="<?php echo $idmoduloFile;?>" idmodalele="<?php echo $f['id_file'];?>"><?php echo _("Cancella");?></a>
                                                                                            <?php } ?>
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
																			<div id="ajaxloaderplupload" style="display:none;">
																			<img src="../assets/img/loading.gif"/>
																			</div>
																			<div id="uploader">
																			<div id="filelist"></div>
																			<br/>

                                                                            <?php if (!($viewmode)) : ?>
																			<?php if ($modulo['max_files_immagini']>0 and count($files)>=$modulo['max_files_immagini']) { } else { ?>
                                                                                <div id="responso_upload_immagini" class="alert-success" style="display:none;">OK!</div>
																				<?php
																				// (Cfr.) upload su mobile
																				if ($varBrowser!="SF" or
																				$varBrowser=="SF") {
																					if (count($files)>0) { ?>
																				<a class="btn btn-large btn-info" id="pickfiles" href="#"><i class="glyphicon glyphicon-picture"></i>&nbsp;&nbsp;<?php echo _("Aggiungi altre immagini");?></a>
																				<?php } else { ?>
																				<a class="btn btn-large btn-info" id="pickfiles" href="#"><i class="glyphicon glyphicon-picture"></i>&nbsp;&nbsp;<?php echo _("Aggiungi immagini");?></a>
																				<?php }
																					} else { ?>

																						<input name="filesToUpload[]" id="filesToUpload" type="file" multiple="multiple" capture="camera" accept="image/*" />
																						<input type="file" capture="camera" accept="image/*" id="cameraInput">

																					<? }
																				 ?>
																			<?php } //if ($modulo['max_files']>0 and count($files)>=$modulo['max_files']) ?>
                                                                            <?php endif; //viewmode ?>

																			</div>


																	</div>
																	</div>
																</div>
															</div>
														</div>
                                                        <?php endif; ?>

                                                    <?php */ ?>

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
																							<div class="col-xs-5">
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
                                                                                                <br/>
                                                                                                <?php if ($modulo['nome_modulo']=="CaricamentoDati") { ?>
                                                                                                    <?php $tokenarray['id_azienda']=$elemento['id_azienda'];?>
                                                                                                    <?php $tokenarray['anno']=$elemento['anno'];?>
                                                                                                    <?php $tokenarray['mese']=$elemento['mese'];?>
                                                                                                    <?php $token=base64_encode(json_encode($tokenarray));?>
                                                                                                    <?php $url=$sitedir."bilanciocaricato.php?token=".$token; ?>
                                                                                                    <br/>

                                                                                                    <div class="vedicontoeconomico"><a target="_blank" href="<?php echo $url;?>"><?php echo _("Vedi Conto Economico");?></a></div>

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
																							<div class="col-xs-2">
                                                                                            <?php if (!($viewmode)) { ?>
    																							<a class="btn btn-danger cancellafile" idmodalmod="<?php echo $idmoduloFile;?>" idmodalele="<?php echo $f['id_file'];?>"><?php echo _("Cancella");?></a>
                                                                                            <?php } ?>
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



                                                                            <div id="ajaxloaderplupload_allegati" style="display:none;">
																			<img src="../assets/img/loading.gif"/>
																			</div>
																			<div id="uploader_allegati">
																			<div id="filelist_allegati"></div>
																			<br/>


                                                                        <?php if (!($viewmode)) : ?>

																			<?php if ($modulo['max_files_allegati']>0 and count($files_allegati)>=$modulo['max_files_allegati']) { } else { ?>
                                                                                <div id="responso_upload_allegati" class="alert-success" style="display:none;">OK!</div>
																				<?php
																					if (count($files_allegati)>0) { ?>
																				<a class="btn btn-large btn-info" id="pickfiles_allegati" href="#"><i class="glyphicon glyphicon-paperclip"></i>&nbsp;&nbsp;<?php echo _("Aggiungi altri allegati");?></a>
																				<?php } else { ?>
																				<a class="btn btn-large btn-info" id="pickfiles_allegati" href="#"><i class="glyphicon glyphicon-paperclip"></i>&nbsp;&nbsp;<?php echo _("Aggiungi allegati");?></a>
																				<?php }
																				 ?>
																				<?php } //if ($modulo['max_files']>0 and count($files)>=$modulo['max_files']) ?>
                                                                        <?php endif; //viewmode ?>

																			</div>


																		</div>
																	</div>
																</div>
															</div>
														</div>
                                                        <?php endif; ?>

												<?php endif; ?>
												<?php /* (f) ---------------------- files ---------------- */ ?>

                                                    <?php // aggiungo ora i campi set ?>
                                                    <input type="hidden" name="elencocampiset" id="elencocampiset" value="<?php echo join(",",$campiset);?>" />
                                                </form>
                                                    </div><!-- seconda colonna -->
                                                                    <div style="clear:both;"></div>
                                                                    <div class="space-4"></div>

<?php /* (f) ------------------------------------------ Generazione form in base ai campi della tabella -------------------------------------------- */?>


<?php if ($modulo['nome_modulo']=='ProspettoPrevisionale') : ?>
    <div class="row" style="background-color:#efefef;">
        <div class="col-xs-12 col-sm-8 col-md-6">
                RICAVI PRESUNTI TOTALI: <span id="ricavipresuntitotali"></span><br/>
                SOMMA  RICAVI MENSILI: <span id="sommaricavimensili"></span><br/>
                DIFFERENZA: <span id="differenzaricavi"></span><br/>
        </div>
    </div>
<?php endif; ?>


    <script type="text/javascript">
			jQuery(function($) {


			    <?php if ($modulo['nome_modulo']=='ProspettoPrevisionale') : ?>

                function sommaricavi() {
                    var somma=
                    parseInt(100*$("#ricavi_personalizzati_01").val())+
                    parseInt(100*$("#ricavi_personalizzati_02").val())+
                    parseInt(100*$("#ricavi_personalizzati_03").val())+
                    parseInt(100*$("#ricavi_personalizzati_04").val())+
                    parseInt(100*$("#ricavi_personalizzati_05").val())+
                    parseInt(100*$("#ricavi_personalizzati_06").val())+
                    parseInt(100*$("#ricavi_personalizzati_07").val())+
                    parseInt(100*$("#ricavi_personalizzati_08").val())+
                    parseInt(100*$("#ricavi_personalizzati_09").val())+
                    parseInt(100*$("#ricavi_personalizzati_10").val())+
                    parseInt(100*$("#ricavi_personalizzati_11").val())+
                    parseInt(100*$("#ricavi_personalizzati_12").val());
                    var somma=somma/100;
                    $("#sommaricavimensili").text(somma);
                    $("#ricavipresuntitotali").text($("#ricavi_presunti").val());
                    var differenza=Math.round(100*(somma-parseInt(100*$("#ricavi_presunti").val())/100))/100;
                    if (differenza<0) {
                        $("#differenzaricavi").addClass("red");
                        $("#differenzaricavi").removeClass("green");
                    }else if (differenza>0) {
                        $("#differenzaricavi").addClass("green");
                        $("#differenzaricavi").removeClass("red");
                    } else {
                        $("#differenzaricavi").removeClass("red");
                        $("#differenzaricavi").removeClass("green");
                    }
                    $("#differenzaricavi").text(differenza);
                }

                $("[id^=ricavi_personalizzati_]").change(function(){
                   sommaricavi();
                });

                $("#ricavi_presunti").change(function(){

                    if ($(this).val()>0) {
                        var dodicesimo=parseInt(100*$(this).val()/12)/100;


                        bootbox.confirm("<?php echo _('Vuoi ricalcolare i ricavi mensili?');?>", function(result) {
                            if (result) {
                                $("#ricavi_personalizzati_01").val(dodicesimo);
                                $("#ricavi_personalizzati_02").val(dodicesimo);
                                $("#ricavi_personalizzati_03").val(dodicesimo);
                                $("#ricavi_personalizzati_04").val(dodicesimo);
                                $("#ricavi_personalizzati_05").val(dodicesimo);
                                $("#ricavi_personalizzati_06").val(dodicesimo);
                                $("#ricavi_personalizzati_07").val(dodicesimo);
                                $("#ricavi_personalizzati_08").val(dodicesimo);
                                $("#ricavi_personalizzati_09").val(dodicesimo);
                                $("#ricavi_personalizzati_10").val(dodicesimo);
                                $("#ricavi_personalizzati_11").val(dodicesimo);
                                $("#ricavi_personalizzati_12").val(dodicesimo);
                                sommaricavi();
                            } else {
                                sommaricavi();
                            }
                        });

                    } else {
                        alert("Inserisci un valore numerico!");
                    }

                });
                <?php endif; ?>



                <?php if ($viewmode==1) { ?>
                $("#nuovo_elemento_close").show();
                $("#nuovo_elemento_save").hide();
                <?php } else { ?>
                $("#nuovo_elemento_close").show();
                $("#nuovo_elemento_save").show();
                <?php }  ?>

                $('[data-rel="chosen"],[rel="chosen"]').chosen({disable_search_threshold: 10});
                $('.chosen-select').chosen({disable_search_threshold: 10});


                $("#nuovi_video").click(function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    $("#video_add").show();
                });
                $("#addvideo").click(function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    $(".btn-save").attr('disabled','disabled');
                    var idmod=<?php echo $idmod;?>;
                    var idele="<?php echo $idele;?>";
                    var videorisorsa=$("#videorisorsa-new").val();
                    $.post("ajax_addvideo.php", { idele: idele, idmod: idmod, risorsa:videorisorsa } , function(msg){$("#responso_upload_video").html(msg);} );
                    setTimeout(function(){location.reload();}, 2000);
                });

                $('.tablang').on('shown.bs.tab', function (e) {
                    var lingua=$(this).attr('data-lang');
                    //rimuove active da tutte le linguette
                    $('.nav-tabs > li.active').removeClass('active');
                    //attiva tutte le linguette in lingua giusta
                    $(".tablang-"+lingua).parent().addClass('active');
                });
//                $(".tablang").click(function(){
//                    var lingua=$(this).attr('data-lang');
//                    //rimuove active da tutte le linguette
//                    $('.nav-tabs > li.active').removeClass('active');
//                    //attiva tutte le linguette in lingua giusta
//                    $(".tablang-"+lingua).parent().addClass('active');
//                });


// (i) checkbox all e none per campi set e chiaviesterne
$(".checkbox_all").click(function(){
    var fieldname=$(this).attr('fieldname');
    $(".checkbox_"+fieldname).prop('checked', true);
});
$(".checkbox_none").click(function(){
    var fieldname=$(this).attr('fieldname');
    $(".checkbox_"+fieldname).prop('checked', false);
});
// (f) checkbox all e none per campi set e chiaviesterne

// (i) delete file

				$(".cancellafile").click(function(){
					var idele=$(this).attr("idmodalele");
					var idmod=$(this).attr("idmodalmod");
					bootbox.confirm("<?php echo _('Sicuro di procedere alla cancellazione?');?>", function(result) {
					  if (result) {


					      var params={};
					      params.idele=idele;
					      params.idmod=idmod;

                          $.ajax({
                              type: "POST",
                              url: "ajax_delete_elemento.php",
                              data: params,
                              dataType: 'json',
                              success: function(data){
                                  console.log(data);
                                  if (data.result==true) {
                                      $.notify("Cancellazione avvenuta con successo",'success');

                                      if (data.url!='') {
                                          setTimeout(function(){
                                              location.href=data.url;
                                              }, 1000);
                                      } else {
                                          setTimeout(function() {
                                              location.reload();
                                          }, 2000);
                                      }

                                  } else {
                                      $.notify(data.error);

                                  }
                              },
                              error: function(data) {
                                  console.log(data);
                              }
                          });


					  }
					});
				});

// (f) delete file


// (i) ---------------- ----------------- plupload immagini ------------------- ------------------ -----------------
	var multiselection=true;
	var uagent = navigator.userAgent.toLowerCase();
		if (
		(uagent.match(/ipad/i)) 		||
		(uagent.match(/iphone/i)) 		||
		(uagent.match(/android/i))		||
		(uagent.match(/blackberry/i))	||
		(uagent.match(/webos/i))
		) {
			multiselection=false;
		}
	var uploader = new plupload.Uploader({
		unique_names : true,
		multi_selection: multiselection,
		runtimes : 'gears,html5,flash,silverlight,browserplus',
		browse_button : 'pickfiles',
		container : 'uploader',
		max_file_size : '20mb',
		url : 'upload.php?tipofile=immagine&idele=<?php echo $idele;?>&nome_tabella=<?php echo $modulo['nome_tabella'];?>',
		flash_swf_url : 'plupload/js/plupload.flash.swf',
		silverlight_xap_url : 'plupload/js/plupload.silverlight.xap',
		filters : [
			{title : "Image files", extensions : "jpeg,jpg,gif,png"}
		],
		resize : {width : <?php echo $widthmax;?>, height : <?php echo $heightmax;?>, quality : 90}
	});

	//uploader.bind('Init', function(up, params) {
	//	$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
	//});

//	$('#uploadfiles').click(function(e) {
//		uploader.start();
//		e.preventDefault();
//	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$("#ajaxloaderplupload").show();
			$('#filelist').append(
				'<div id="' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
			'</div>');
		});
		up.refresh(); // Reposition Flash/Silverlight
		//ora le carico subito
		uploader.start();
	});

	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " b").html(file.percent + "%");
	});

	uploader.bind('Error', function(up, err) {
		$('#filelist').append("<div>Error: " + err.code +
			", Message: " + err.message +
			(err.file ? ", File: " + err.file.name : "") +
			"</div>"
		);

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('FileUploaded', function(up, file,info) {
		$('#' + file.id + " b").html("100%");
		var obj = JSON.parse(info.response);
        var filename=obj.cleanFileName;
		//$.get("inserisciFoto.php", { 'filename': filename, 'id': id, 'tipo': tipo });
	});

	uploader.bind('UploadComplete',function(){
		//alert("Caricamento Completato!");
		$("#ajaxloaderplupload").hide();
        $("#responso_upload_immagini").show();
        setTimeout(function() {
            location.reload();
        }, 2000);
   	});
// (f) ---------------- ----------------- plupload immagini ------------------- ------------------ -----------------


// (i) ---------------- ----------------- plupload allegati ------------------- ------------------ -----------------
	var multiselection=true;
	var uagent = navigator.userAgent.toLowerCase();
		if (
		(uagent.match(/ipad/i)) 		||
		(uagent.match(/iphone/i)) 		||
		(uagent.match(/android/i))		||
		(uagent.match(/blackberry/i))	||
		(uagent.match(/webos/i))
		) {
			multiselection=false;
		}
	var uploader_allegati = new plupload.Uploader({
		unique_names : true,
		multi_selection: multiselection,
		runtimes : 'gears,html5,flash,silverlight,browserplus',
		browse_button : 'pickfiles_allegati',
		container : 'uploader_allegati',
		max_file_size : '20mb',
		url : 'upload.php?tipofile=allegato&idele=<?php echo $idele;?>&nome_tabella=<?php echo $modulo['nome_tabella'];?>',
		flash_swf_url : 'plupload/js/plupload.flash.swf',
		silverlight_xap_url : 'plupload/js/plupload.silverlight.xap',
		filters : [
			{title : "Office files", extensions : "csv"},
		],
		resize : {width : <?php echo $widthmax;?>, height : <?php echo $heightmax;?>, quality : 90} //parametri definiti a livello di file _parametri
	});

	//uploader_allegati.bind('Init', function(up, params) {
	//	$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
	//});

//	$('#uploadfiles').click(function(e) {
//		uploader_allegati.start();
//		e.preventDefault();
//	});

	uploader_allegati.init();

	uploader_allegati.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$("#ajaxloaderplupload_allegati").show();
			$('#filelist_allegati').append(
				'<div id="' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
			'</div>');
		});
		up.refresh(); // Reposition Flash/Silverlight
		//ora le carico subito
		uploader_allegati.start();
	});

	uploader_allegati.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " b").html(file.percent + "%");
	});

	uploader_allegati.bind('Error', function(up, err) {
		$('#filelist').append("<div>Error: " + err.code +
			", Message: " + err.message +
			(err.file ? ", File: " + err.file.name : "") +
			"</div>"
		);

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader_allegati.bind('FileUploaded', function(up, file,info) {
		$('#' + file.id + " b").html("100%");
		var obj = JSON.parse(info.response);
        var filename=obj.cleanFileName;
		//$.get("inserisciFoto.php", { 'filename': filename, 'id': id, 'tipo': tipo });
	});

	uploader_allegati.bind('UploadComplete',function(){
		//alert("Caricamento Completato!");
		$("#ajaxloaderplupload_allegati").hide();
        $("#responso_upload_allegati").show();
        setTimeout(function() {
            location.reload();
        }, 2000);
   	});
// (f) ---------------- ----------------- plupload allegati ------------------- ------------------ -----------------


    });


</script>

<?php

exit;