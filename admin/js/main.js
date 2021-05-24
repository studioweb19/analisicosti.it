$(document).ready(function () {

    var msie = navigator.userAgent.match(/msie/i);
    $.browser = {};
    $.browser.msie = {};

    $('.navbar-toggle').click(function (e) {
        e.preventDefault();
        $('.nav-sm').html($('.navbar-collapse').html());
        $('.sidebar-nav').toggleClass('active');
        $(this).toggleClass('active');
    });

    var $sidebarNav = $('.sidebar-nav');

    // Hide responsive navbar on clicking outside
    $(document).mouseup(function (e) {
        if (!$sidebarNav.is(e.target) // if the target of the click isn't the container...
            && $sidebarNav.has(e.target).length === 0
            && !$('.navbar-toggle').is(e.target)
            && $('.navbar-toggle').has(e.target).length === 0
            && $sidebarNav.hasClass('active')
        )// ... nor a descendant of the container
        {
            e.stopPropagation();
            $('.navbar-toggle').click();
        }
    });

    //disbaling some functions for Internet Explorer
    if (msie) {
        $('#is-ajax').prop('checked', false);
        $('#for-is-ajax').hide();
        $('#toggle-fullscreen').hide();
        $('.login-box').find('.input-large').removeClass('span10');

    }


    //highlight current / active link
    $('ul.main-menu li a').each(function () {
        if ($($(this))[0].href == String(window.location))
            $(this).parent().addClass('active');
    });


    $('.accordion > a').click(function (e) {
        e.preventDefault();
        var $ul = $(this).siblings('ul');
        var $li = $(this).parent();
        if ($ul.is(':visible')) $li.removeClass('active');
        else                    $li.addClass('active');
        $ul.slideToggle();
    });

    $('.accordion li.active:first').parents('ul').slideDown();


    //other things to do on document ready, separated for ajax calls
    docReady();
});

function addMonth(datainiziale,n) {
    //funzione che calcola la data finale a n mesi di distanza, con la convenzione che se il mese non coincide, si prende l'ultimo giorno del mese precedente
    //ESEMPIO
    // calcoliamo la prima rata semestrale, ovvero 6 mesi dopo la data di decorrenza
    //con la regola che se il giorno non esiste, si approssima al primo giorno precedente
    //esempio 6 mesi dopo il 30 agosto 2013 farebbe 30 febbraio 2014, allora prendiamo il 28 febbraio 2014
    //esempio 6 mesi dopo il 31 marzo 2013 farebbe il 31 novembre 2013, ovvero il 30 novembre 2013
    //in poche parole, calcolo il mese teorico, se la data non esiste, allora prendo l'ultimo giorno del mese precedente
    var tmpdate=new Date(datainiziale);
    var meseteorico=tmpdate.getMonth()+n;
    if (meseteorico>11) {
        meseteorico=meseteorico-12;
    }
    var datascadenza01 = new Date(new Date(tmpdate).setMonth(tmpdate.getMonth()+n));
    if (datascadenza01.getMonth()!=meseteorico) {
        var lastDay = new Date(datascadenza01.getFullYear(), datascadenza01.getMonth(), 0);
        return lastDay;
    } else {
        return datascadenza01;
    }

}

function calcolascadenze(datadecor,fraz) {
    //in base alla data di deccorrenza e al frazionamento richiesto si calcolano le date
    //la prima data da calcolare è la data di scadenza annuale, quella c'è sempre
    //la data di scadenza è un anno successivo alla data di decorrenza

    var datainiziale=new Date(datadecor);

    var datadecorrenza=new Date(datadecor);

    $('.datepicker').datepicker('update', '');
    $('#decorrenza').datepicker('update', datadecorrenza.toLocaleDateString());


    var datascadenza=new Date(datadecorrenza.setFullYear(datadecorrenza.getFullYear() + 1));
    console.log(datascadenza.toLocaleDateString());

    $('#scadenza').datepicker('update', datascadenza.toLocaleDateString());

    if (fraz=='semestrale') {
        var datascadenza01=addMonth(datainiziale,6);
        $('#scadenza_rata_01').datepicker('update', datascadenza01.toLocaleDateString());
    }

    if (fraz=='quadrimestrale') {
        var datascadenza01=addMonth(datainiziale,4);
        $('#scadenza_rata_01').datepicker('update', datascadenza01.toLocaleDateString());
        var datascadenza02=addMonth(datainiziale,8);
        $('#scadenza_rata_02').datepicker('update', datascadenza02.toLocaleDateString());
    }

    if (fraz=='trimestrale') {
        var datascadenza01=addMonth(datainiziale,3);
        $('#scadenza_rata_01').datepicker('update', datascadenza01.toLocaleDateString());
        var datascadenza02=addMonth(datainiziale,6);
        $('#scadenza_rata_02').datepicker('update', datascadenza02.toLocaleDateString());
        var datascadenza03=addMonth(datainiziale,9);
        $('#scadenza_rata_03').datepicker('update', datascadenza03.toLocaleDateString());
    }

    if (fraz=='mensile') {
        var datascadenza01=addMonth(datainiziale,1);
        $('#scadenza_rata_01').datepicker('update', datascadenza01.toLocaleDateString());
        var datascadenza02=addMonth(datainiziale,2);
        $('#scadenza_rata_02').datepicker('update', datascadenza02.toLocaleDateString());
        var datascadenza03=addMonth(datainiziale,3);
        $('#scadenza_rata_03').datepicker('update', datascadenza03.toLocaleDateString());
        var datascadenza04=addMonth(datainiziale,4);
        $('#scadenza_rata_04').datepicker('update', datascadenza04.toLocaleDateString());
        var datascadenza05=addMonth(datainiziale,5);
        $('#scadenza_rata_05').datepicker('update', datascadenza05.toLocaleDateString());
        var datascadenza06=addMonth(datainiziale,6);
        $('#scadenza_rata_06').datepicker('update', datascadenza06.toLocaleDateString());
        var datascadenza07=addMonth(datainiziale,7);
        $('#scadenza_rata_07').datepicker('update', datascadenza07.toLocaleDateString());
        var datascadenza08=addMonth(datainiziale,8);
        $('#scadenza_rata_08').datepicker('update', datascadenza08.toLocaleDateString());
        var datascadenza09=addMonth(datainiziale,9);
        $('#scadenza_rata_09').datepicker('update', datascadenza09.toLocaleDateString());
        var datascadenza10=addMonth(datainiziale,10);
        $('#scadenza_rata_10').datepicker('update', datascadenza10.toLocaleDateString());
        var datascadenza11=addMonth(datainiziale,11);
        $('#scadenza_rata_11').datepicker('update', datascadenza11.toLocaleDateString());
    }

}

function docReady() {
    //prevent # links from moving to top
    $('a[href="#"][data-top!=true]').click(function (e) {
        e.preventDefault();
    });

    //notifications
    $('.noty').click(function (e) {
        e.preventDefault();
        var options = $.parseJSON($(this).attr('data-noty-options'));
        noty(options);
    });

    //chosen - improves select
    $('[data-rel="chosen"],[rel="chosen"]').chosen();

    //tabs
    $('#myTab a:first').tab('show');
    $('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });


    //tooltip
    $('[data-toggle="tooltip"]').tooltip();

    //popover
    $('[data-toggle="popover"]').popover();

    $('.btn-close').click(function (e) {
        e.preventDefault();
        $(this).parent().parent().parent().fadeOut();
    });
    $('.btn-minimize').click(function (e) {
        e.preventDefault();
        var $target = $(this).parent().parent().next('.box-content');
        if ($target.is(':visible')) $('i', $(this)).removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
        else                       $('i', $(this)).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
        $target.slideToggle();
    });
    $('.btn-setting').click(function (e) {
        e.preventDefault();
        $('#myModal').modal('show');
    });

}


//additional functions for data table
$.fn.dataTableExt.oApi.fnPagingInfo = function (oSettings) {
    return {
        "iStart": oSettings._iDisplayStart,
        "iEnd": oSettings.fnDisplayEnd(),
        "iLength": oSettings._iDisplayLength,
        "iTotal": oSettings.fnRecordsTotal(),
        "iFilteredTotal": oSettings.fnRecordsDisplay(),
        "iPage": Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
        "iTotalPages": Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
    };
}
$.extend($.fn.dataTableExt.oPagination, {
    "bootstrap": {
        "fnInit": function (oSettings, nPaging, fnDraw) {
            var oLang = oSettings.oLanguage.oPaginate;
            var fnClickHandler = function (e) {
                e.preventDefault();
                if (oSettings.oApi._fnPageChange(oSettings, e.data.action)) {
                    fnDraw(oSettings);
                }
            };

            $(nPaging).addClass('pagination').append(
                '<ul class="pagination">' +
                '<li class="prev disabled"><a href="#">&larr; ' + oLang.sPrevious + '</a></li>' +
                '<li class="next disabled"><a href="#">' + oLang.sNext + ' &rarr; </a></li>' +
                '</ul>'
            );
            var els = $('a', nPaging);
            $(els[0]).bind('click.DT', { action: "previous" }, fnClickHandler);
            $(els[1]).bind('click.DT', { action: "next" }, fnClickHandler);
        },

        "fnUpdate": function (oSettings, fnDraw) {
            var iListLength = 5;
            var oPaging = oSettings.oInstance.fnPagingInfo();
            var an = oSettings.aanFeatures.p;
            var i, j, sClass, iStart, iEnd, iHalf = Math.floor(iListLength / 2);

            if (oPaging.iTotalPages < iListLength) {
                iStart = 1;
                iEnd = oPaging.iTotalPages;
            }
            else if (oPaging.iPage <= iHalf) {
                iStart = 1;
                iEnd = iListLength;
            } else if (oPaging.iPage >= (oPaging.iTotalPages - iHalf)) {
                iStart = oPaging.iTotalPages - iListLength + 1;
                iEnd = oPaging.iTotalPages;
            } else {
                iStart = oPaging.iPage - iHalf + 1;
                iEnd = iStart + iListLength - 1;
            }

            for (i = 0, iLen = an.length; i < iLen; i++) {
                // remove the middle elements
                $('li:gt(0)', an[i]).filter(':not(:last)').remove();

                // add the new list items and their event handlers
                for (j = iStart; j <= iEnd; j++) {
                    sClass = (j == oPaging.iPage + 1) ? 'class="active"' : '';
                    $('<li ' + sClass + '><a href="#">' + j + '</a></li>')
                        .insertBefore($('li:last', an[i])[0])
                        .bind('click', function (e) {
                            e.preventDefault();
                            oSettings._iDisplayStart = (parseInt($('a', this).text(), 10) - 1) * oPaging.iLength;
                            fnDraw(oSettings);
                        });
                }

                // add / remove disabled classes from the static elements
                if (oPaging.iPage === 0) {
                    $('li:first', an[i]).addClass('disabled');
                } else {
                    $('li:first', an[i]).removeClass('disabled');
                }

                if (oPaging.iPage === oPaging.iTotalPages - 1 || oPaging.iTotalPages === 0) {
                    $('li:last', an[i]).addClass('disabled');
                } else {
                    $('li:last', an[i]).removeClass('disabled');
                }
            }
        }
    }
});
