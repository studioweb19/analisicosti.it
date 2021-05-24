<div class="container">
    <!-- Example row of columns -->
<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-4">
            <div id="chart1" style="height:96%; width:96%;"></div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4">
            <div id="chart2" style="height:96%; width:96%;"></div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-4">
            <div id="chart3" style="height:96%; width:96%;"></div>
    </div>
</div>


    <hr>
    <script>
        $(document).ready(function(){
            var data = [
                ['Heavy Industry', 12],['Retail', 9], ['Light Industry', 14],
                ['Out of home', 16],['Commuting', 7], ['Orientation', 9]
            ];
            var plot1 = jQuery.jqplot ('chart1', [data],
                {
                    title: 'Portafoglio clienti 1',
                    seriesDefaults: {
                        // Make this a pie chart.
                        renderer: jQuery.jqplot.PieRenderer,
                        rendererOptions: {
                            // Put data labels on the pie slices.
                            // By default, labels show the percentage of the slice.
                            showDataLabels: true
                        }
                    },
                    legend: { show:true, location: 'e' }
                }
            );
            var plot2 = jQuery.jqplot ('chart2', [data],
                {
                    title: 'Portafoglio clienti 2',
                    seriesDefaults: {
                        renderer: jQuery.jqplot.PieRenderer,
                        rendererOptions: {
                            // Turn off filling of slices.
                            fill: false,
                            showDataLabels: true,
                            // Add a margin to seperate the slices.
                            sliceMargin: 4,
                            // stroke the slices with a little thicker line.
                            lineWidth: 5
                        }
                    },
                    legend: { show:true, location: 'e' }
                }
            );
            var plot3 = $.jqplot('chart3', [data], {
                title: 'Portafoglio clienti 3',
                seriesDefaults: {
                    // make this a donut chart.
                    renderer:$.jqplot.DonutRenderer,
                    rendererOptions:{
                        // Donut's can be cut into slices like pies.
                        sliceMargin: 3,
                        // Pies and donuts can start at any arbitrary angle.
                        startAngle: -90,
                        showDataLabels: true,
                        // By default, data labels show the percentage of the donut/pie.
                        // You can show the data 'value' or data 'label' instead.
                        dataLabels: 'value',
                        // "totalLabel=true" uses the centre of the donut for the total amount
                        totalLabel: true
                    }
                }
            });

        });
    </script>
    <?php include("INC_90_FOOTER.php");?>
</div> <!-- /container -->