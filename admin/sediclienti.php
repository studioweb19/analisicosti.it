<?php $pagina="sedi_clienti";?>
<?php include("INC_10_HEADER.php");?>
<?php include("INC_15_SCRIPTS.php");?>
<body>
<?php include("INC_20_NAVBAR.php");?>
<div class="container" id="firstcontainer">
    <?php $_GET[id]=getModuloFrom_nome_modulo('Sedi clienti');?>
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
                                href="get_element.php?debug=<?php echo $_GET['debug']; ?>&idmod=<?php echo $modulo['id_modulo']; ?>&idele=-1"
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

            <table id="sediclienti" class="table table-bordered table-hover display nowrap margin-top-10 bootstrap-datatable datatable responsive" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Azioni</th>
                    <th>Cliente</th>
                    <th>Indirizzo</th>
                    <th>CAP</th>
                    <th>Email</th>
                </tr>
                </thead>
            </table>

    <!-- (f) TABELLA CLIENTI -->
    <!-- custom script -->
<script>
    $(document).ready(function() {

        //(i) ---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------

        $('#sediclienti').DataTable( {
            serverSide: true,
            ajax: "server_side_sediclienti.php?<?php echo $_SERVER['QUERY_STRING'];?>"
        } );

        //(f) ---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------DATATABLE---------------


    } );
</script>
</div>
<hr>
<?php include("INC_90_FOOTER.php");?>

</body>
</html>
