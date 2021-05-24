<div class="container" id="firstcontainer">
    <?php include("mostrainfo.php");?>
    <!-- INTESTAZIONE REPORT-->
<?php include("barranavigazione.php");?>
    <?php if ($token['anno']=='') : //capita se non ho ancora inserito niente!!! ?>
        <div class="row">
            <div class="col col-xs-12">
                <div class="jumbotron">
                    <h3>Nessun dato per questo cliente!</h3>
                    <p class="lead">Al momento non è presente nessun dato associato a questo cliente!</p>

                    <p class="lead">
                        <a class="btn btn-warning btn-lg" href="module.php?modname=CaricamentoDati&p[id_azienda]=<?php echo $token['id_azienda'];?>" role="button">Procedi con caricamento dati!</a>
                    </p>
                </div>
            </div>
        </div>

   <?php else : ?>
        <!--<div class="panel panel-default">-->
        <div class="panel-body">
            <table class="table">
                <!-- RICAVI -->
                <tr class="bg-success"><th style="text-align:center;" colspan="13"><?php echo _("Ricavi");?></th></tr>
                <tr>
                    <th>&nbsp;</th>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <th>
                            <a href="reportmese.php?token=<?php echo $tokenmese[sprintf("%02d",$i)];?>"><?php echo $mese[sprintf("%02d",$i)];?></a>
                        </th>
                    <?php endfor; ?>
                </tr>
                <?php if ($maxmeseprec==12) { ?>
                    <tr>
                        <td><i><?php echo _("Ricavi Mensili")." "; printf("%d",$token['anno']-1);?></i></td>
                        <?php for ($i=1;$i<13;$i++) : ?>
                            <td >
                                <i><?php echo soldi($campiprec[sprintf("%02d",$i)]['ricavi_mese']);?></i>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php echo _("Ricavi Mensili")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <td >
                            <?php echo soldi($campi[sprintf("%02d",$i)]['ricavi_mese']);?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr class="bg-warning">
                    <td><?php echo _("Ricavi Previsionali")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : $meseindex=sprintf("%02d",$i);

                        if ($PROGETTO['ricavi_personalizzati_'.$meseindex]) {
                            $ricaviprogettomensile=$PROGETTO['ricavi_personalizzati_'.$meseindex];
                        } else {
                            $ricaviprogettomensile=$PROGETTO['ricavi_presunti']/12;
                        } ?>

                        <td >
                            <?php echo soldi($ricaviprogettomensile);?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td><?php echo _("Ricavi Totali")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <td >
                            <?php echo soldi($campi[sprintf("%02d",$i)]['ricavi_totali']);?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr><td colspan="13">&nbsp;</td></tr>

                <!-- ACQUISTI -->
                <tr class="bg-warning"><th style="text-align:center;" colspan="13"><?php echo _("Acquisti");?></th></tr>
                <tr>
                    <th>&nbsp;</th>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <th>
                            <a href="reportmese.php?token=<?php echo $tokenmese[sprintf("%02d",$i)];?>"><?php echo $mese[sprintf("%02d",$i)];?></a>
                        </th>
                    <?php endfor; ?>
                </tr>
                <?php if ($maxmeseprec==12) { ?>
                    <tr>
                        <td><i><?php echo _("Acquisti Mensili")." "; printf("%d",$token['anno']-1);?></i></td>
                        <?php for ($i=1;$i<13;$i++) : ?>
                            <td >
                                <i><?php echo soldi($campiprec[sprintf("%02d",$i)]['acquisti_mese']);?></i>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php echo _("Acquisti Mensili")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <td>
                            <?php echo soldi($campi[sprintf("%02d",$i)]['acquisti_mese']);?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td><?php echo _("Acquisti Totali")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <td>
                            <?php echo soldi($campi[sprintf("%02d",$i)]['acquisti_totali']);?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr><td colspan="13">&nbsp;</td></tr>

                <!-- COSTI GENERALI -->
                <tr class="bg-danger"><th style="text-align:center;" colspan="13"><?php echo _("Costi Generali");?></th></tr>
                <tr>
                    <th>&nbsp;</th>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <th>
                            <a href="reportmese.php?token=<?php echo $tokenmese[sprintf("%02d",$i)];?>"><?php echo $mese[sprintf("%02d",$i)];?></a>
                        </th>
                    <?php endfor; ?>
                </tr>
                <?php if ($maxmeseprec==12) { ?>
                    <tr>
                        <td><i><?php echo _("Costi Generali Mensili")." "; printf("%d",$token['anno']-1);?></i></td>
                        <?php for ($i=1;$i<13;$i++) : ?>
                            <td >
                                <i><?php echo soldi($campiprec[sprintf("%02d",$i)]['costigenerali_mese']);?></i>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php echo _("Costi Generali Mensili")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <td>
                            <?php echo soldi($campi[sprintf("%02d",$i)]['costigenerali_mese']);?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td><?php echo _("Costi Generali Totali")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <td>
                            <?php echo soldi($campi[sprintf("%02d",$i)]['costigenerali_totali']);?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr><td colspan="13">&nbsp;</td></tr>

                <!-- COSTI TOTALI -->
                <tr class="bg-info"><th style="text-align:center;" colspan="13"><?php echo _("Costi Totali");?></th></tr>
                <tr>
                    <th>&nbsp;</th>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <th>
                            <a href="reportmese.php?token=<?php echo $tokenmese[sprintf("%02d",$i)];?>"><?php echo $mese[sprintf("%02d",$i)];?></a>
                        </th>
                    <?php endfor; ?>
                </tr>
                <?php if ($maxmeseprec==12) { ?>
                    <tr>
                        <td><i><?php echo _("Costi Totali Mensili")." "; printf("%d",$token['anno']-1);?></i></td>
                        <?php for ($i=1;$i<13;$i++) : ?>
                            <td style="text-align:right;">
                                <i><?php echo soldi($campiprec[sprintf("%02d",$i)]['acquisti_mese']+$campiprec[sprintf("%02d",$i)]['costigenerali_mese']);?>
                                </i>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php echo _("Costi Totali Mensili")." ".$token['anno'];?></td>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <td>
                            <?php echo soldi($campi[sprintf("%02d",$i)]['acquisti_mese']+$campi[sprintf("%02d",$i)]['costigenerali_mese']);?>
                        </td>
                    <?php endfor; ?>
                </tr>

                <tr><td colspan="13">&nbsp;</td></tr>

                <!-- RICAVI DA BUDGET -->
                <tr class="bg-primary"><th style="text-align:center;" colspan="13"><?php echo _("Ricavi da budget");?></th></tr>
                <tr>
                    <th>&nbsp;</th>
                    <?php for ($i=1;$i<13;$i++) : ?>
                        <th>
                            <a href="reportmese.php?token=<?php echo $tokenmese[sprintf("%02d",$i)];?>"><?php echo $mese[sprintf("%02d",$i)];?></a>
                        </th>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <td><?php echo _("Ricavi da fare ad inizio mese");?></td>
                    <?php
                    $ricaviprogetto=0;
                    for ($i=1;$i<13;$i++) : $meseindex=sprintf("%02d",$i); $meseprecedenteindex=sprintf("%02d",$i-1);?>
                        <td>
                            <?php


                            if ($PROGETTO['ricavi_personalizzati_'.$meseindex]) {
                                $ricaviprogettomensile=$PROGETTO['ricavi_personalizzati_'.$meseindex];
                            } else {
                                $ricaviprogettomensile=$PROGETTO['ricavi_presunti']/12;
                            }

                            //ricavi da fare a inizio mese sono
                            // - ricavi effettivi totali del mese precedente
                            // + ricavi da fare fino a quel mese + quelli del mese in corso

                            $ricaviprogetto+=$ricaviprogettomensile;

                            if ($i==1) {
                                $ricavidafareiniziomese=$ricaviprogetto;
                            } else {
                                $ricavidafareiniziomese=$ricaviprogetto-$campi[$meseprecedenteindex]['ricavi_totali'];
                            }

                            if ($i==$maxmese+1) {
                                echo "<b>".soldi($ricavidafareiniziomese)."</b>";
                            } else if ($i>$maxmese+1) {
                                echo "-";
                            } else {
                                echo soldi($ricavidafareiniziomese);
                            }
                            ?>
                        </td>
                    <?php endfor; ?>
                </tr>
                <tr><td colspan="13">&nbsp;</td></tr>

                <!-- ACQUISTI DA BUDGET -->

                <?php if ($PROGETTO['incidenza_acquisti']!='') : ?>
                    <tr class="bg-info"><th style="text-align:center;" colspan="13"><?php echo _("Acquisti da budget");?></th></tr>
                    <tr>
                        <th>&nbsp;</th>
                        <?php for ($i=1;$i<13;$i++) : ?>
                            <th>
                                <a href="reportmese.php?token=<?php echo $tokenmese[sprintf("%02d",$i)];?>"><?php echo $mese[sprintf("%02d",$i)];?></a>
                            </th>
                        <?php endfor; ?>
                    </tr>
                    <tr>
                        <td><?php echo _("Acquisti da fare ad inizio mese");?></td>
                        <?php
                        $acquistiprogetto=0;
                        for ($i=1;$i<13;$i++) : $meseindex=sprintf("%02d",$i); $meseprecedenteindex=sprintf("%02d",$i-1);?>
                            <td>
                                <?php


                                $acquistiprogettomensile=$PROGETTO['incidenza_acquisti']*$PROGETTO['ricavi_presunti']/1200;

                                //ricavi da fare a inizio mese sono
                                // - ricavi effettivi totali del mese precedente
                                // + ricavi da fare fino a quel mese + quelli del mese in corso

                                $acquistiprogetto+=$acquistiprogettomensile;

                                if ($i==1) {
                                    $acquistidafareiniziomese=$acquistiprogetto;
                                } else {
                                    $acquistidafareiniziomese=$acquistiprogetto-$campi[$meseprecedenteindex]['acquisti_totali'];
                                }

                                if ($i==$maxmese+1) {
                                    echo "<b>".soldi($acquistidafareiniziomese)."</b>";
                                } else if ($i>$maxmese+1) {
                                    echo "-";
                                } else {
                                    echo soldi($acquistidafareiniziomese);
                                }
                                ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endif; ?>

            </table>
        </div>
        <!--</div>-->



        <!-- GRAFICI -->

        <div class="row">
            <div class="col-xs-12">
                <div id="ricavisucosti" style="height:96%; width:96%;"></div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div id="ricavisuacquisti" style="height:96%; width:96%;"></div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div id="ricavisugenerali" style="height:96%; width:96%;"></div>
            </div>
        </div>
    <?php endif; ?>



    <hr>
    <?php include("INC_90_FOOTER.php");?>

    <script>
        $(document).ready(function(){



        $.jqplot.config.enablePlugins = true;

        var ticks=[];
        var serie=[];
        var legenda=[];

            serie['ricavi']=[];
            serie['costi']=[];
            serie['acquisti']=[];
            serie['generali']=[];
            <?php
            $seriephp1[]="serie['ricavi']";
            $seriephp2[]="serie['ricavi']";
            $seriephp3[]="serie['ricavi']";

            $seriephp1[]="serie['costi']";
            $seriephp2[]="serie['acquisti']";
            $seriephp3[]="serie['generali']";

            $serielegendphp1[]="Ricavi Totali";
            $serielegendphp2[]="Ricavi Totali";
            $serielegendphp3[]="Ricavi Totali";

            $serielegendphp1[]="Costi Totali";
            $serielegendphp2[]="Costi Acquisti";
            $serielegendphp3[]="Costi Generali";
            ?>

        //devo avere subito tutte le chiavi per generare le serie, altrimenti si rischia di saltare

        <?php
            $ii=array();
            for ($i=1;$i<13;$i++) :
            $ii[]=sprintf("%02d",$i);
        endfor; ?>

        <?php foreach ($ii as $valoreii) :

                $data=$mese[$valoreii];
                $valore=$campi[$valoreii];

            ?>
            ticks.push('<?php echo $data;?>');
            //ora riempio le serie di dati, tanto so già quali legende ho, se il dato non esiste, metto 0
            var valore=0; //ricavi

            <?php if ($valore['ricavi_mese']>0) { ?>
            valore=<?php echo $valore['ricavi_mese']; ?>;
            serie['ricavi'].push(valore);
            <?php }  else { ?> serie['ricavi'].push(0); <?php } ?>

            var valore1=0; //acquisti
            var valore2=0; //generali
            var valore3=0; //totali
            <?php if ($valore['acquisti_mese']>0) { ?>
            valore1=<?php echo $valore['acquisti_mese']; ?>;
            serie['acquisti'].push(valore1);
            <?php }  else { ?> serie['acquisti'].push(0); <?php } ?>
            <?php if ($valore['costigenerali_mese']>0) { ?>
            valore2=<?php echo $valore['costigenerali_mese']; ?>;
            serie['generali'].push(valore2);
            <?php }  else { ?> serie['generali'].push(0); <?php } ?>
            valore3=valore1+valore2;
            serie['costi'].push(valore3);
        <?php endforeach; ?>

            console.log(serie);

        var plot1 = $.jqplot('ricavisucosti', [<?php echo join(",",$seriephp1);?>], {
        // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
        title: 'Ricavi su Totale Costi ',
        seriesColors: ["#4f80bd", "#c0504d"],
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
                pointLabels: { show: true },
                rendererOptions:{
                    animation: {
                        speed: 1000
                    }
                }
            },
        animate: !$.jqplot.use_excanvas,
        series:[
        <?php foreach ($serielegendphp1 as $s) : ?>
            {label:'<?php echo $s;?>'},
        <?php endforeach; ?>
        ],
        legend: {
        show: true,
        placement: 'outsideGrid',
        location: 'e'
        },

        axesDefaults: {
        tickRenderer: $.jqplot.CanvasAxisTickRenderer
        },
        axes: {
            xaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
                ticks: ticks,
                tickOptions: {
                    angle: -90
                }
            }
        },
        highlighter: { show: false }
        });


        var plot2 = $.jqplot('ricavisuacquisti', [<?php echo join(",",$seriephp2);?>], {
                // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
                title: 'Ricavi su Acquisti mensili ',
                seriesColors: ["#4f80bd", "#c0504d"],
                seriesDefaults:{
                    renderer:$.jqplot.BarRenderer,
                    pointLabels: { show: false },
                    rendererOptions:{
                        animation: {
                            speed: 1000
                        }
                    }
                },
                animate: !$.jqplot.use_excanvas,
                series:[
                    <?php foreach ($serielegendphp2 as $s) : ?>
                    {label:'<?php echo $s;?>'},
                    <?php endforeach; ?>
                ],
                legend: {
                    show: true,
                    placement: 'outsideGrid',
                    location: 'e'
                },

                axesDefaults: {
                    tickRenderer: $.jqplot.CanvasAxisTickRenderer
                },
                axes: {
                    xaxis: {
                        renderer: $.jqplot.CategoryAxisRenderer,
                        ticks: ticks,
                        tickOptions: {
                            angle: -90
                        }
                    }
                },
                highlighter: { show: false }
            });

        var plot3 = $.jqplot('ricavisugenerali', [<?php echo join(",",$seriephp3);?>], {
                // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
                title: 'Ricavi su Costi generali ',
                seriesColors: ["#4f80bd", "#c0504d"],
                seriesDefaults:{
                    renderer:$.jqplot.BarRenderer,
                    pointLabels: { show: false },
                    rendererOptions:{
                        animation: {
                            speed: 1000
                        }
                    }
                },
                animate: !$.jqplot.use_excanvas,
                series:[
                    <?php foreach ($serielegendphp3 as $s) : ?>
                    {label:'<?php echo $s;?>'},
                    <?php endforeach; ?>
                ],
                legend: {
                    show: true,
                    placement: 'outsideGrid',
                    location: 'e'
                },

                axesDefaults: {
                    tickRenderer: $.jqplot.CanvasAxisTickRenderer
                },
                axes: {
                    xaxis: {
                        renderer: $.jqplot.CategoryAxisRenderer,
                        ticks: ticks,
                        tickOptions: {
                            angle: -90
                        }
                    }
                },
                highlighter: { show: false }
            });


        });
/*
        $('#Chart1').bind('jqplotDataClick', function (ev, seriesIndex, pointIndex, data) {
            window.parent.location.href = 'YOURURL.aspx?id=' + arrBranchId[pointIndex];
        });
*/
    </script>

</div> <!-- /container -->