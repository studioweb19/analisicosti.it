<div class="container" id="firstcontainer">
    <!-- Example row of columns -->
        <div class="row">
            <div>
                <ul class="breadcrumb">
                    <li>
                        <a href="index.php">Home</a>
                    </li>
                    <li>
                        QRCode
                    </li>
                </ul>
            </div>
            <div class="page-header">
                <h2>
                    <i class="fa fa-qrcode"></i> Genera QR CODE
                </h2>
            </div>

            <div class="panel panel-primary">
                <div class="panel-body">


                        <!-- put your content here -->
                        <form method="post">
                            <div class="form-group col-md-4">
                                <label class="control-label" for="precodice">Precodice</label>
                                <input type="text" class="form-control" id="precodice" name="precodice" placeholder="<?php echo $prefissoqrcode;?>">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label" for="inizionumerazione">Inizio Numerazione</label>
                                <input type="text" class="form-control" id="inizionumerazione" name="inizionumerazione" placeholder="1001">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label" for="numerocodici">Numero di Codici da Generare</label>
                                <input type="text" class="form-control" id="numerocodici" name="numerocodici" placeholder="81">
                            </div>
                            <div class="form-group col-md-4">
                                <input type="submit" class="btn btn-success" id="stampa" name="stampa" value="Genera Codici">
                            </div>

                        </form>
                </div>
            </div>
        </div><!--/row-->
    <hr>
    <?php include("INC_90_FOOTER.php");?>
</div> <!-- /container -->