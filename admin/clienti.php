<?php $pagina="clienti";?>
<?php $backlist=$_SERVER['SCRIPT_URI']."?".$_SERVER['QUERY_STRING'];?>
<?php include("INC_10_HEADER.php");?>
<?php include("INC_15_SCRIPTS.php");?>
<body>
<?php include("INC_20_NAVBAR.php");?>
<div class="container" id="firstcontainer">
    <?php if ($_SESSION['ClienteSceltoDaUtente']>0) {

        //include("barranavigazione.php");
    }?>


    <?php $_GET[id]=getModuloFrom_nome_modulo('Clienti');?>
    <?php $modulo=getModulo($_GET[id]);?>
    <?php $pars=$_GET['p']; ?>
<?php    $permessi_modulo=permessi($_GET['id'],$utente['id_ruolo'],$superuserOverride); ?>
    <div>
        <ul class="breadcrumb">
            <li>
                <a href="index.php">Home</a>
            </li>
            <li>
                <?php echo _($modulo['nome_modulo']);?>
            </li>
        </ul>
    </div>

    <div class="page-header">
        <h2>
            <i class="<?php echo $modulo['font_icon'];?>"></i> <?php echo _($modulo['nome_modulo']);?>
            <div class="pull-right">
                <?php if ($permessi_modulo['Can_delete']=='si') { ?>
                    <a id="multiplerowdelete" class="tooltip-danger btn btn-app btn-danger btn-xs" style="display:none;"
                       data-rel="tooltip" data-placement="left" title="Delete all selected items" >
                        <i class="glyphicon glyphicon-trash bigger-160"></i>
                    </a>
                <?php } ?>
                <?php
                if ($modulo['aprimodal'] == 'si') {
                    if (($permessi_modulo['Can_create'] == 'si') && ($modulo['abilita_bottone_new'] == 'si')) { ?>
                        <a class="tooltip-success btn btn-app btn-success btn-xs aprimodal-ele"
                           idmodalmod="<?php echo $modulo['id_modulo']; ?>" idmodalele="-1"
                           data-rel="tooltip" data-placement="left"
                           title="Aggiungi elemento di <?php echo _($modulo[nome_modulo]); ?>">
                            <span style="font-size:2.5em;" class="glyphicon glyphicon-plus"></span>
                        </a>
                    <?php } ?>
                <?php } else {
                    if (($permessi_modulo['Can_create'] == 'si') && ($modulo['abilita_bottone_new'] == 'si')) { ?>
                        <a
                                href="get_element.php?debug=<?php echo $_GET['debug']; ?>&idmod=<?php echo $modulo['id_modulo']; ?>&idele=-1&backlist=<?php echo base64_encode($backlist);?>"
                                class="tooltip-success btn btn-app btn-success btn-xs"
                                data-rel="tooltip" data-placement="left"
                                title="Aggiungi elemento di <?php echo $modulo['nome_modulo']; ?>">
                            <i class="ace-icon glyphicon glyphicon-plus bigger-160"></i>
                        </a>
                    <?php }
                }
                ?>

                <?php if ($permessi_modulo['Can_update']=='si') {
                    foreach ($_GET as $key=>$value) {
                        if ($key=="reordering") continue;
                        $get[]=$key."=".$value;
                    }
                    $querystring=join("&",$get);
                    $url="http://".$_SERVER[HTTP_HOST].$_SERVER[SCRIPT_NAME]."?".$querystring;
                    ?>
                <?php } ?>
            </div>
        </h2>

    </div><!-- /.page-header -->
    <!-- (i) TABELLA CLIENTI -->
            <table id="clienti" class="table table-bordered table-hover display nowrap margin-top-10 bootstrap-datatable datatable responsive" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Azioni</th>
                    <th>RagSociale</th>
                    <th>Cognome</th>
                    <th>Nome</th>
                    <th>Indirizzo</th>
                    <th>MAIL</th>
                    <th>Telefono</th>
                    <th>Cell</th>
                    <th>Credenziali<br/>inviate</th>
                    <th>INVIA</th>
                </tr>
                </thead>
            </table>

    <!-- (f) TABELLA CLIENTI -->
    <!-- custom script -->
<script>
    $(document).ready(function() {





        //(i) ---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------

        //var url="server_side_clienti.php?backlist=<?php echo base64_encode($backlist);?>&<?php echo $_SERVER['QUERY_STRING'];?>";
        var url="server_side_clienti.php?backlist=<?php echo base64_encode($backlist);?>";
        console.log(url);
        $('#clienti').DataTable( {
            serverSide: true,
            ajax: url
        } );

        //(f) ---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------

        $(document).on("click",".delete-elemento",function(){
            var idele=$(this).attr("idmodalele");
            var idmod=$(this).attr("idmodalmod");
            bootbox.confirm("<?php echo _('Sicuro di voler eliminare questo elemento?');?>", function(result) {
                if (result) {
                    $.post("ajax_delete_elemento.php", { idmod: idmod, idele: idele } , function(msg){$("#responso").html(msg);} );
                    setTimeout(function(){location.reload();}, 2000);
                }
            });
        });

        $(document).on("click",".inviacredenzialicliente",function(){
            var idcliente=$(this).attr("idcliente");

            var params={};
            params.idcliente=idcliente;
            $.ajax({
                type: "POST",
                url: "ajax_invia_credenziali_cliente.php",
                data: params,
                dataType: 'json',
                success: function(data){
                    console.log(data);
                    if (data.result==true) {
                        $.notify(data.msg,'success');
                        setTimeout(function(){location.reload();}, 2000);
                    } else {
                        $.notify(data.error);
                        console.log(data);
                    }
                },
                error: function(data) {
                    console.log(data);
                }
            });
        });


    } );
</script>
<hr>
<?php include("INC_90_FOOTER.php");?>
</div>

</body>
</html>
