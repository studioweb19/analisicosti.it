<?php 
session_start();

include("config.php");

$matricola=strtoupper($_REQUEST['matricolalogin']);
$pswd=$_REQUEST['passwordlogin'];
if ($matricola and $pswd) {

    //proviamo prima il cliente
    $query="SELECT * FROM ".$GLOBAL_tb['clienti']." WHERE (Mail=?) AND Password=? LIMIT 0,1";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($matricola, $pswd));
    $nome="";
    if ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        $trovato=true;
        $nome=$row['RagioneSociale'];
        $_SESSION['pcs_id_user']=11; //user fittizio per loggare il cliente
        $_SESSION['pcs_id_cliente']=$row['id'];
        $_SESSION['pcs_nome']=$row['RagioneSociale'];
        $_SESSION['pcs_cognome']='';
        //mettere riga nella tabella pcs_login
        //con id_cliente e data e ora di login
        $querylog="INSERT INTO pcs_log (id_cliente, data_login, ora_login) VALUES (?,?,?)";
        $stmtlog=$dbh->prepare($querylog);
        $stmtlog->execute(array($_SESSION['pcs_id_cliente'],date("Y-m-d"),date("H:i:s")));
    } else {
        $query="SELECT * FROM ".$GLOBAL_tb['users']." WHERE (username=? OR email=?) AND password=? LIMIT 0,1";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($matricola, $matricola, $pswd));
        if ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            $trovato=true;
            $nome=$row['Nome']." ".$row['Cognome'];
            $_SESSION['pcs_id_user']=$row['id_user'];
            $_SESSION['pcs_nome']=$row['Nome'];
            $_SESSION['pcs_cognome']=$row['Cognome'];

            $querylog="INSERT INTO pcs_log_studi (id_user, data_login, ora_login) VALUES (?,?,?)";
            $stmtlog=$dbh->prepare($querylog);
            $stmtlog->execute(array($_SESSION['pcs_id_user'],date("Y-m-d"),date("H:i:s")));


        } else {
            $trovato=false;
        }
    }
} else {
    $trovato=false;
}
if ($trovato) { ?>

<div class="alert alert-success" role="alert">
		<div class="center">
			Login corretto, benvenuto <strong><?php echo $nome;?><br/></strong>
			<?php
			$msgheader = "From: no-reply@studioweb19.it\r\n" .
				"Reply-To: info@studioweb19.it\r\n" .
				"X-Mailer: PHP/" . phpversion();
			mail('fabio.franci@gmail.com','analisicosti.it - Login effettuato su '.$projecttitle, "Login effettuato su $projecttitle: ".$nome, $msgheader);
			?>

  		</div>
</div>
    <script>
	setTimeout(function(){document.location='<?php echo $sitedir;?>';}, 2000);
	</script>
    <?php
}
else
{ ?>
<div id="loginerror" class="alert alert-danger alert-dismissible" role="alert">
		<div class="center">
  <strong><?php echo _("Errore!");?></strong> <?php echo _("Credenziali errate! Riprova!");?>
  		</div>
</div>
    <script>
	setTimeout(function(){$("#loginerror").hide();}, 2000);
	</script>
<?php  } ?>
<?php
function check_password($password, $hash)
{
	if ($hash == '') // no password
	{
		//echo "No password";
		return FALSE;
	}

	if ($hash{0} != '{') // plaintext password
	{
		if ($password == $hash)
			return TRUE;
		return FALSE;
	}

	if (substr($hash,0,7) == '{crypt}')
	{
		if (crypt($password, substr($hash,7)) == substr($hash,7))
			return TRUE;
		return FALSE;
	}
	elseif (substr($hash,0,5) == '{MD5}')
	{
		$encrypted_password = '{MD5}' . base64_encode(md5( $password,TRUE));
	}
	elseif (substr($hash,0,6) == '{SHA1}')
	{
		$encrypted_password = '{SHA}' . base64_encode(sha1( $password, TRUE ));
	}
	elseif (substr($hash,0,6) == '{SSHA}')
	{
		$salt = substr(base64_decode(substr($hash,6)),20);
		$encrypted_password = '{SSHA}' . base64_encode(sha1( $password.$salt, TRUE ). $salt);
	}
	else
	{
		//echo "Unsupported password hash format";
		return FALSE;
	}

	if ($hash == $encrypted_password)
		return TRUE;

	return FALSE;
}
?>
