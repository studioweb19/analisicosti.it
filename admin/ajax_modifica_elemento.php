<?php
session_start();
if($_SESSION['sitosospeso'] == "1"){
    @header("Location:utente-sospeso.php");
}
include("config.php");

$saveandreload=$_REQUEST['saveandreload'];

$backlist=$_REQUEST['backlist'];

$testifile=array();

sanitate($_POST);

foreach ($_POST as $key=>$value) {
    //cerco i campi dei testi dei files
    if (substr($key,0,9)=="file-nome") {
        list($trash1,$trash2,$fileid,$lang)=explode("-",$key);
        $testifile[$fileid][$lang]=$value;
    }
}

if ($_POST['elencocampiset']) {
    $campiset=explode(",",$_POST['elencocampiset']);
    if (count($campiset)>0) {
        foreach ($campiset as $cs) {
			if ($_POST['cs']) {
				$valori=join(",",$_POST[$cs]);
				$_POST[$cs]=$valori;
			}
        }
    }
}

$campiobbligatori=explode(",",$_POST['elencocampiobbligatori']);

$idmod=$_REQUEST['idmod'];
$idele=$_REQUEST['idele'];

if ($idmod>0) {
	$modulo=getModulo($idmod);
	$campi_readonly=explode(",",$modulo['campi_readonly']);
	$campi_nascosti=explode(",",$modulo['campi_nascosti']);
} else {
		echo "Modulo vuoto!";
		return false;
		exit;
}

$errore=array();

//ORA BISOGNA CREARE l'ELENCO DEI CAMPI!
$elencocampikeys=explode(",",$_POST['elencocampi']);
foreach ($elencocampikeys as $field) {
	$elencocampivalues[$field]=$_POST[$field];
	if ($elencocampivalues[$field]=='' AND in_array($field,$campiobbligatori)) : 
		$err['nome']=$field;
		$errore[]=$err;
	endif;
}

//ora verifico i campi traducibili obbligatori
		if ($_POST['elencocampitraducibili']!='') : 
			$campitraducibili=explode(",",$_POST['elencocampitraducibili']);
			foreach ($campitraducibili as $nome_campo) :
				if (in_array($nome_campo,$campiobbligatori)):
				foreach ($lingue as $lang) :
					$tmp=$nome_campo."-".$lang;
					$valore=$_POST[$tmp];
					$err=array();
					$err['nome']=$nome_campo;
					$err['lang']=$lang;
					if ($valore=='') $errore[]=$err;
				endforeach;	
				endif;
			endforeach;	
		endif;
 /* 
if (count($errore)>0) :
	?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<strong>Attenzione!</strong>Campi obbligatori non compilati!<br/>
  		I seguenti campi devo essere compilati correttamente:
  		<ul>
  		<?php foreach ($errore as $err) { ?>
  		<li>
  		<?php echo $err['nome'];?>
  		<?php if ($err['lang']) echo "(".$err['lang'].")"; ?>
  		</li>
  		<?php } ?>
  		</ul>
  		</div>
</div>
<?php
exit;
endif;
 */ 

$permessi=permessi($idmod,$utente['id_ruolo'],$superuserOverride);

//mysql_insert_id()

if ($idele==-1) { //Crea nuovo elemento
	if ($permessi['Can_create']=='si') {
	} else { ?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
        <?php echo _("<strong>Attenzione!</strong>Non hai i permessi per creare questo elemento!");?>
  		</div>
</div>
    <script>
	setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
	<?php
		exit;
	}

} else { //Update Elemento
	if ($permessi['Can_update']=='si') {

	} else { ?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<?php echo _("<strong>Attenzione!</strong>Problema modifica elemento (1)!");?>
  		</div>
</div>
    <script>
	setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
	<?php
		exit;
	}
}

if (1) {
//iniziamo una transazione
    $dbh->beginTransaction();

    //vediamo se ci sono i campi "unici"
    $campi_unici=explode(",",$modulo['campi_unici']);
    if (count($campi_unici)>0) {

    }

    $INSERIMENTO=false;
    if ($idele==-1) {
        $INSERIMENTO=true;
        //il campo ordine deve essere incrementato di 1
        $newordine=getLastPosition($modulo['nome_tabella']);
        $newordine++;
        $chiavicampi[0]='ordine';
        $valoricampi[0]=$newordine;
        $valoricampivuoti[0]='?';

        //inserimento
        foreach ($elencocampivalues as $key=>$value) {
            if ($key=='') continue;
            $value=validateDate($value); //se è una data in formato d/m/Y la converto in Y-m-d altrimenti rimane la stringa che ho passato
            $chiavicampi[]=$key;
            if (in_array($key,$campiobbligatori) or $value!='') {
                $valoricampi[]=$value;
            } else {
                $valoricampi[]=NULL;
            }
            $valoricampivuoti[]='?';
        }
        $listachiavicampi=join(",",$chiavicampi);
        $listavaloricampivuoti=join(",",$valoricampivuoti);



        //controllo caso caricamento dati
        if ($modulo['nome_modulo']=='CaricamentoDati') {
            $querypp="SELECT * FROM pcs_caricamento_dati WHERE anno='".$elencocampivalues['anno']."' and mese = '".$elencocampivalues['mese']."' and id_azienda='".$elencocampivalues['id_azienda']."'";
            //setNotificheCRUD("admWeb","controllo caricamento dati","ajax_modifica_elemento.php - INSERT",$querypp);
            $stmtpp=$dbh->query($querypp);
            if ($giapresente=$stmtpp->fetch(PDO::FETCH_ASSOC)) {
                //gia presente

                    $k='id_azienda-'.$elencocampivalues['id_azienda'];
                    $k1="anno-".$elencocampivalues['anno'];
                    $k2="mese-".$elencocampivalues['mese'];

                ?>
                <div class="registrazionerror alert alert-danger" role="alert">
                    <div class="center">
                        <?php echo _("<strong>Attenzione!</strong> Anno e mese già inseriti! <br/> ");?>
                    </div>
                </div>
                <!--<script>
                    var url="get_element.php?k1=<?php echo $k1;?>&k2=<?php echo $k2;?>&k=<?php echo $k;?>&debug=<?php echo $_GET['debug']; ?>&idmod=<?php echo $modulo['id_modulo']; ?>&idele=<?php echo $giapresente['id'];?>&backlist=<?php echo base64_encode($backlist);?>";
                    setTimeout(function(){$(".registrazionerror").hide(); window.location.href = url; }, 3000);
                </script>-->
                <?php
                    exit;
                } else {

            }

        }

        $queryinsert="INSERT INTO ".$modulo['nome_tabella']." (".$listachiavicampi.") VALUES(".$listavaloricampivuoti.")";

        $stmt=$dbh->prepare($queryinsert);
        $stmt->execute($valoricampi);

        if (!($stmt)) {
            setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - INSERT",$queryinsert . "---". json_encode($valoricampi));
            $dbh->rollBack();



            ?>
            <div class="registrazionerror alert alert-danger" role="alert">
                <div class="center">
                    <?php echo _("<strong>Attenzione!</strong>Problema salvataggio elemento!");?>
                </div>
            </div>
            <script>
                setTimeout(function(){$(".registrazionerror").hide();}, 2000);
            </script>
            <?php
            exit;
        } else {

            $idele=$dbh->lastInsertId();

        }

    } else {
        //modifica
        $set = array();
        foreach ($elencocampivalues as $key => $value) {
            if ($key == '') continue;
            $value=validateDate($value); //se è una data in formato d/m/Y la converto in Y-m-d altrimenti rimane la stringa che ho passato
            $setnuovo[]="$key=?";

            if (in_array($key,$campiobbligatori) or $value!='') {
                $valorisetnuovo[]=$value;
            } else {
                $valorisetnuovo[]=NULL;
            }
        }
        $comandoset = join(",", $setnuovo);
        if ($comandoset != '') {

            $queryupdate = "UPDATE " . $modulo['nome_tabella'] . " SET " . $comandoset . " WHERE " . $modulo['chiaveprimaria'] . "='" . $idele . "'";

            $stmt = $dbh->prepare($queryupdate);
            $stmt->execute($valorisetnuovo);
            setNotificheCRUD("admWeb","INFO","ajax_modifica_elemento.php",json_encode($queryupdate)."----".$dbh->errorInfo());

            if (!($stmt)) {
                setNotificheCRUD("admWeb", "ERROR", "ajax_modifica_elemento.php - UPDATE", $queryupdate . "---". json_encode($valorisetnuovo));
                $dbh->rollBack();
                ?>
                <div class="registrazionerror alert alert-danger" role="alert">
                    <div class="center">
                        <?php echo _("<strong>Attenzione!</strong>Problema modifica elemento!"); ?>
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
        } else {
            $stmt=true;
        }
    }

	if ($stmt) {
	    include("creadirectoryallegati.php");

		setNotificheCRUD("admWeb","SUCCESS","ajax_modifica_elemento.php",$queryinsert.$queryupdate."INSERT:".json_encode($valoricampi)."UPDATE:".json_encode($valorisetnuovo));
		$erroreTransazione=false;
		//vediamo se ci sono i campi traducibili

		if ($_POST['elencocampitraducibili']) :
			$campitraducibili=explode(",",$_POST['elencocampitraducibili']);
			foreach ($campitraducibili as $nome_campo) :
				foreach ($lingue as $lang) :
					$tmp=$nome_campo."-".$lang;
					$valore=$_POST[$tmp];
					$queryTesti="REPLACE into ".$GLOBAL_tb['testi']." SET id_ext=$idele, table_ext='".$modulo['nome_tabella']."', lang='".$lang."',chiave='".$nome_campo."', valore=?";
                    $stmt = $dbh->prepare($queryTesti);
                    $stmt->execute(array($valore));
                    if (!($stmt)) {
						setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - REPLACE",$queryTesti);
						$erroreTransazione=true;
					}
				endforeach;	
			endforeach;	
		endif;

        //salviamo la nota se c'è
        if ($_POST['modulo_nota']) :
            $queryNote="INSERT INTO ".$GLOBAL_tb['note']." (id_user,table_ext,id_ext,nota,data_nota) VALUES (".$utente['id_user'].",'".$modulo['nome_tabella']."',".$idele.",'".$_POST['modulo_nota']."',NOW())";
            if (!($stmt=$dbh->query($queryNote))) {
                setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - INSERT NOTE:",$queryNote);
                $erroreTransazione=true;
            }
        endif;

		//salviamo anche i testi delle immagini e degli allegati 
		if (count($testifile)>0) :
			foreach ($testifile as $fileid=>$tf) :
				foreach ($tf as $lang=>$valore):
					$queryTesti="REPLACE into ".$GLOBAL_tb['testi']." SET id_ext=$fileid, table_ext='".$GLOBAL_tb['files']."', lang='".$lang."',chiave='nome', valore=?";
                    $stmt = $dbh->prepare($queryTesti);
                    $stmt->execute(array($valore));
					if (!($stmt)) {
						setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - REPLACE",$queryTesti);
						$erroreTransazione=true;
					}
				endforeach;
			endforeach;
		endif;
        //veririchiamo anche il post process update
        $post_process_update=json_decode($modulo['post_process_update'],true);
        setNotificheCRUD("admWeb","INFO","ajax_modifica_elemento.php - post process update:","--->".$modulo['post_process_update']."<---");

		if (count($post_process_update)>0 && $post_process_update!='') {
			foreach ($post_process_update as $ppuquery) {
				$ppuquery=str_replace("%chiaveprimaria%",$idele,$ppuquery);
				$ppuquery=str_replace("%TIMEZONE%",$TIMEZONE,$ppuquery);
                $ppuquery=str_replace("%id_user%",$utente['id_user'],$ppuquery);
                $ppuquery=str_replace("%id_ruolo%",$utente['id_ruolo'],$ppuquery);
                $ppuquery=str_replace("%id_cliente%",$utente['id_cliente'],$ppuquery);
                if (!($stmt=$dbh->query($ppuquery))) {
					setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - post process update:",$ppuquery);
					$erroreTransazione=true;
				} else {
					setNotificheCRUD("admWeb","SUCCESS","ajax_modifica_elemento.php - post process update:",$ppuquery);
				}
			}
		} else {
            setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - post process update non conteggiato!",$modulo['post_process_update']);
        }

        if ($INSERIMENTO) :
            $post_process_insert=json_decode($modulo['post_process_insert'],true);
            setNotificheCRUD("admWeb","INFO","ajax_modifica_elemento.php - post process insert:",$modulo['post_process_insert']);
            if (count($post_process_insert)>0) {
                foreach ($post_process_insert as $ppuquery) {
                    $ppuquery=str_replace("%chiaveprimaria%",$idele,$ppuquery);
					$ppuquery=str_replace("%TIMEZONE%",$TIMEZONE,$ppuquery);
                    $ppuquery=str_replace("%id_user%",$utente['id_user'],$ppuquery);
                    $ppuquery=str_replace("%id_ruolo%",$utente['id_ruolo'],$ppuquery);
                    $ppuquery=str_replace("%id_cliente%",$utente['id_cliente'],$ppuquery);
                    if (!($stmt=$dbh->query($ppuquery))) {
                        setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - post process insert:",$ppuquery);
                        $erroreTransazione=true;
                    } else {
                        setNotificheCRUD("admWeb","SUCCESS","ajax_modifica_elemento.php - post process insert:",$ppuquery);
                    }
                }
            }
        endif;

		if ($erroreTransazione==true) {
			$dbh->rollBack();
		?>
		<div class="registrazionerror alert alert-danger" role="alert">
				<div class="center">
				<?php echo _("<strong>Attenzione!</strong> Problema modifica testi oppure post process update!");?>
				</div>
		</div>
			<script>
			setTimeout(function(){$(".registrazionerror").hide();}, 2000);
			</script>
		<?php
			exit;
		} else {
            $dbh->commit();
        }
?>

<div class="space6"></div>
<div class="registrazionesuccess alert alert-success" role="alert">
		<div class="center">
            <?php echo _("<strong>Complimenti!</strong> Inserimento effettuato con successo!");?><br/>
			<script>
				setTimeout(function(){
					$(".registrazionesuccess").hide();
					<?php if ($saveandreload==1) { ?>
					var url='https://<?php echo $_SERVER[HTTP_HOST].$sitedir;?>get_element.php?idmod=<?php echo $idmod;?>&idele=<?php echo $idele;?>&backlist=<?php echo $backlist;?>';
					window.location.href = url;
					<?php  } else { ?>
                    var url='<?php echo base64_decode($backlist);?>';
					window.location.href = url;
                    //alert(url);
					<?php } ?>
				}, 2000);
			</script>
  		</div>
</div>
<?php
	} 
/*-------------------------------------------------------------------------------------------------*/
	else { //if ($rv)
?>
<div class="registrazionerror alert alert-danger" role="alert">
		<div class="center">
  		<?php echo _("<strong>Warning!</strong> You have to fill all the fields!");?>
  		</div>
</div>
    <script>
	//setTimeout(function(){$(".registrazionerror").hide();}, 2000);
	</script>
<?php
	}
		
}
exit;
?>
