(function($) {
    var $table = $('table.dm-assets-backup-table');
   
    $table.dataTable({
        "oLanguage": {
            "sUrl": $table.metadata().translation_url
        },
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "aaSorting": [[1,'desc']],
        "aoColumns": [ { "bSortable": false },  null, null, null, null, null, null, null, { "bSortable": false } ]
    }); 
    
    $('table.dm-assets-backup-table input.check-all').click(function(evt){
        evt.stopImmediatePropagation();
        if (!$(this).prop('checked')) {
            $(this).closest('table').find('input[type=checkbox]').removeAttr('checked');
        } else {
            $(this).closest('table').find('input[type=checkbox]').attr('checked', 'checked');
        };
    });
    $('table.dm-assets-backup-table input.assets-backup-file').click(function(evt){
        evt.stopImmediatePropagation();
        if (!$(this).prop('checked')) {
            $(this).closest('table').find('input.check-all').removeAttr('checked');
        };
    });
    
    $('table.dm-assets-backup-table .dm_delete_link').click(function(){        
        var message = $(this).closest('li.sf_admin_action_clean').metadata().message;
        if (confirm(message)) {            
            return true;
        };
        return false;
    });
    
    $('div.dm_form_action_bar.dm_form_action_bar_bottom input.delete-all-button').click(function(){
        var message = $(this).metadata().message;
        var url = $(this).metadata().action;
        if (confirm(message)) {            
            window.location = url;
        };
    });
    
    $('div.dm_form_action_bar.dm_form_action_bar_bottom input.backup-button').click(function(){
        window.location = $(this).metadata().action;
    });
        
    $('div.dm_form_action_bar.dm_form_action_bar_bottom input.batch-delete-button').click(function(){
        if ($(this).closest('form').find('input[type=checkbox]').filter(function(){
            if ($(this).hasClass('check-all')) return false;
            if ($(this).prop('checked')) return true;
            return false;
        }).length == 0) {
            alert($(this).metadata().message)
            return false;
        };
    });    
    
})(jQuery);