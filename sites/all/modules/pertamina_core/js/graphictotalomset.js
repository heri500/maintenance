jQuery(function ($) {
    $('#daily-report').on('click', function(){
        window.location = Drupal.settings.basePath + 'graphtotalomset/daily';
    });
    $('#weekly-report').on('click', function(){
        window.location = Drupal.settings.basePath + 'graphtotalomset/weekly';
    });
    $('#month-before').on('click', function(){
        var monthValue = $('#month_before').val();
        var splitMonth = monthValue.split('_');
        window.location = Drupal.settings.basePath + 'graphtotalomset/monthly/'+ splitMonth[0] +'/'+ splitMonth[1];
    });
    $('#curr-month').on('click', function(){
        var monthValue = $('#curr_month').val();
        var splitMonth = monthValue.split('_');
        window.location = Drupal.settings.basePath + 'graphtotalomset/monthly/'+ splitMonth[0] +'/'+ splitMonth[1];
    });
    $('#year-before').on('click', function(){
        window.location = Drupal.settings.basePath + 'graphtotalomset/yearly/'+ $('#year_before').val();
    });
    $('#curr-year').on('click', function(){
        window.location = Drupal.settings.basePath + 'graphtotalomset/yearly/'+ $('#curr_year').val();
    });
    $('#view-report').on('click', function(){
        window.location = Drupal.settings.basePath + 'graphtotalomset?tglawal='+ $('#start_date').val() +'&tglakhir='+ $('#end_date').val();
    });
    /*if (Drupal.settings.report_type == 'daily'){
        setTimeout(function(){
            window.location.reload(1);
        }, 5000);
    }*/
})