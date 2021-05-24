<?php
        $ftp_server=$ftp_host;
        $ftp_user_name=$ftp_id;
        $ftp_user_pass=$ftp_pw;

	    //ora creo la cartella per gli upload
        $targetDirRemote = $directoryfiles.DIRECTORY_SEPARATOR.$modulo['nome_tabella']."_".$idele;
        $FTPUPLOADDIR=$root_server;

// set up basic connection
        $conn_id = ftp_connect($ftp_server);

// login with username and password
        $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

// try to create the directory $dir
        $dirtobecreated=$FTPUPLOADDIR.$targetDirRemote;
        if (@ftp_mkdir($conn_id, $dirtobecreated)) {
            @ftp_chmod($conn_id, 0777, $targetDirRemote);
            setNotificheCRUD("admWeb","SUCCESS","ajax_modifica_elemento.php - FTPDIR creata ",$dirtobecreated );
        } else {
            setNotificheCRUD("admWeb","ERROR","ajax_modifica_elemento.php - FTPDIR creata o già presente ",$dirtobecreated );
        }
?>
