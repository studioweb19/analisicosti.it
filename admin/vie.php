<?php $pagina="Vie";?>
<?php include_once ("INC_10_script.php"); ?>
<html>
<?php include_once ("INC_20_head.php"); ?>
<body>
<?php include_once ("INC_30_navbar.php"); ?>

<div class="container-fluid" id="firstcontainer">

    <div class="panel panel-primary">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                Vie
            </h4>
        </div>
        <div class="panel-body">
            <div class="pull-right">
                <button id="aggiungistrada" class="btn btn-success"><i class="fa fa-plus" aria-hidden="true"></i> NEW </button><br/><br/>
            </div>
                <table id="strade" class="table table-bordered table-hover display nowrap margin-top-10 bootstrap-datatable datatable responsive" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>codice_via</th>
                        <th>Comune</th>
                        <th>Nome Via</th>
                        <th>Azioni</th>
                    </tr>
                    </thead>
                </table>

        </div>
    </div>
    <?php $comuni=getComuni();?>
    <div id="strade-detail" class="panel panel-warning" style="display:none;">
        <div class="panel-heading">
            <h4 class="panel-title" class="modifica">
                <span class="modifica">Modifica Strada</span>
                <span class="inserimento">Inserimento Nuova Strada</span>
            </h4>
        </div>
        <div class="panel-body">
            <form id="insertmodstrada">
                <input type="hidden" id="strada_id" name="strada_id" value="-1"/>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="codice_via">Codice via</label>
                            <input type="text" class="form-control" id="codice_via" name="codice_via" placeholder="Codice via" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="comune_id">Comune</label>
                            <select name="comune_id" id="comune_id" class="form-control" required>
                                <?php foreach ($comuni as $c) : ?>
                                    <option value="<?php echo $c['comune_id'];?>"><?php echo $c['comune_nome'];?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="comune_nome">Nome della strada</label>
                            <input type="text" class="form-control" id="strada_nome" name="strada_nome" placeholder="Nome della strada" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <input type="submit" class="btn btn-success" value="Salva">
                    </div>
                </div>
            </form>
        </div>
    </div>


</div>
<!-- http://lokeshdhakar.com/projects/lightbox2/ -->

<?php include_once ("INC_90_script.php"); ?>
<!-- custom script -->

<script>
    $(document).ready(function() {

        var offsetscroll=$("#firstcontainer").offset().top;
        var navheight=$("nav").height();
        $('html,body').animate({scrollTop: offsetscroll-navheight},'slow');
        //alert(navheight);
        //$("#firstcontainer").css("margin-top",navheight-50);

        $('#strade').DataTable( {
            "pagingType":"simple_numbers",
            "processing": true,
            "serverSide": true,
            "ajax": "lib/server_side_strade.php?<?php echo $_SERVER['QUERY_STRING'];?>",
            "language": {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Italian.json"
            },
            "aoColumns": [{ "bSortable": true , sWidth: '15%'},{ "bSortable": true , sWidth: '15%'},{ "bSortable": true , sWidth: '15%'},{ "bSortable": false , sWidth: '5%'}],
        } );

        $(document).on('click', '.btn-delete', function() {
            var idele= $( this ).attr('data-elem') ;
            bootbox.confirm("Sicuro di voler eliminare questa strada?", function(result) {
                if (result) {
                    var params={};
                    params.idele=idele;
                    $.ajax({
                        type: "POST",
                        url: "ajax_delete_strada.php",
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
                }
            });
        });

        $(document).on('click', '.btn-edit', function() {
            $("#insertmodstrada").formValidation();
            $("#insertmodstrada").data('formValidation').resetForm();

            var idele= $( this ).attr('data-elem') ;
            var params={};
            params.idele=idele;
            $.ajax({
                type: "POST",
                url: "ajax_get_strada.php",
                data: params,
                dataType: 'json',
                success: function(data){
                    console.log(data);
                    if (data.result==true) {

                        //riempio i campi
                        $("#strada_id").val(data.strada.strada_id);
                        $("#comune_id").val(data.strada.comune_id);
                        $("#strada_nome").val(data.strada.strada_nome);
                        $("#codice_via").val(data.strada.codice_via);

                        $(".modifica").show();
                        $(".inserimento").hide();

                        $("#strade-detail").removeClass('panel-danger');
                        $("#strade-detail").addClass('panel-warning');

                        $("#strade-detail").show();
                        var navheight=$("nav").height();
                        var offsetscroll=$("#strade-detail").offset().top;
                        $('html,body').animate({scrollTop: offsetscroll-navheight},'slow');
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

        $( "#aggiungistrada" ).on( "click", function() {
            $("#insertmodstrada").formValidation();
            $("#insertmodstrada").data('formValidation').resetForm();

            //riempio i campi
            $("#strada_id").val('-1');
            $("#strada_nome").val('');
            $("#comune_id").val('');
            $("#codice_via").val('');

            $(".modifica").hide();
            $(".inserimento").show();

            $("#strade-detail").removeClass('panel-warning');
            $("#strade-detail").addClass('panel-danger');

            $("#strade-detail").show();
            var navheight=$("nav").height();
            var offsetscroll=$("#strade-detail").offset().top;
            $('html,body').animate({scrollTop: offsetscroll-navheight},'slow');
        });

        $("#insertmodstrada").formValidation({
            // Indicate the framework
            // It can be: bootstrap, bootstrap4, foundation5, foundation, pure, semantic, uikit
            framework: 'bootstrap',
            locale: 'it_IT',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            }

        })
            .on('success.form.fv', function(e) {
                // Prevent form submission
                e.preventDefault();

                // Some instances you can use are
                var $form = $(e.target),        // The form instance
                    fv    = $(e.target).data('formValidation'); // FormValidation instance

                var params={};
                params.idele=$("#strada_id").val();
                params.comune_id=$("#comune_id").val();
                params.strada_nome=$("#strada_nome").val();
                params.codice_via=$("#codice_via").val();

                console.log(params);

                $.ajax({
                    type: "POST",
                    url: "ajax_modifica_strada.php",
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



                // Do whatever you want here ...
            });
    } );
</script>
</body>
</html>
