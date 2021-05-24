<div class="container" id="firstcontainer">
    <?php
    $query="SELECT *,pcs_elenco_pagine_doc.pagina as nomepagina FROM pcs_inpage_doc JOIN pcs_elenco_pagine_doc ON pcs_inpage_doc.pagina=pcs_elenco_pagine_doc.id WHERE ruolo=?";
    $stmt=$dbh->prepare($query);
    $stmt->execute(array($utente['id_ruolo']));
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $documentazione[]=$row;
    } ?>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-responsive">
                <thead>
                <tr>
                    <th>Pagina</th>
                    <th>Note</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($documentazione as $d) : ?>
                    <tr>
                        <td><?php echo $d['nomepagina'];?></td>

                        <td><?php echo $d['testo'];?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
    <hr>
    <?php include("INC_90_FOOTER.php");?>

    <script>
        $(document).ready(function(){




        });
    </script>

</div> <!-- /container -->