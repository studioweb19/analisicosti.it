<script>
    $( window ).load(function() {
        var navheight=$("nav").height();
        //alert(navheight);
        $("#firstcontainer").css("margin-top",navheight-50);

        //http://bootsnipp.com/snippets/50me8
        $('#preloader').fadeOut('slow');
        $('body').css({'overflow':'visible'});
    });
</script>
<?php $monitoraggio=1; //smartlook ?>
<?php if ($monitoraggio==1) : ?>
    <script type="text/javascript">
        window.smartlook||(function(d) {
            var o=smartlook=function(){ o.api.push(arguments)},h=d.getElementsByTagName('head')[0];
            var c=d.createElement('script');o.api=new Array();c.async=true;c.type='text/javascript';
            c.charset='utf-8';c.src='//rec.smartlook.com/recorder.js';h.appendChild(c);
        })(document);
        smartlook('init', 'e8d4fefb9dcdd67731e8147cf251a6b57e4a9f56');
    </script>
<?php endif; ?>
<footer>
    <p>analisicosti.it 2019 - copyright Studio La Rosa e Associati </p>
</footer>
