$(document).ready(function(){
    var app = $('.flow-instance.standard---sms');

    $('.radio-table .radio-cell input', app).live('click', function(event) {
        var table = $(event.target).closest('.radio-table');
        var table_row = $(event.target).closest('.radio-table-row');
        $('.radio-table-row', table).removeClass('on').addClass('off');
        table_row.removeClass('off').addClass('on');
    });
});