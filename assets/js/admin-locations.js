(function( $ ) {
	'use strict';

    let dttbl = null;
    let isFormValid;
    let isFormSaving;
    let remConfirmDlg;
    let locationToRemove;

    const btn_save = '#new-location-form .buttons .button.save';
    const img_spinner = '#new-location-form .buttons .uploading img';

    function actions_data_render(data, type){
		if (type === 'display') {
			
            return JGB_VWDS.actionsHtml ;
        }
         
        return data;
	}

    function selection_data_render(data, type) {
        if (type === 'display') {
            let selection = '';
            if(data == true){
                selection = 'checked';
            }

            return '<input type="checkbox" ' + selection + ' />' ;
        }
         
        return data;
    }

    function confirmRemoveLocation(){
        locationToRemove = [ $(this).closest('tr').attr('id') ];
        remConfirmDlg.dialog( "open" );
    }

    function setLocationInRomevingMode(){
        let i,ltrc,trslctr;
        ltrc = locationToRemove.length;

        trslctr = '';
        for(i = 0; i < ltrc; i++){
            if( i>=1 ){
                trslctr += ', ';
            }
            trslctr += '#locations-table tbody tr#' + locationToRemove[i];
            trslctr += ' .actions';
        }

        if( !$(trslctr + ' .action').hasClass('hidden') ){
            $(trslctr + ' .action').addClass('hidden');
        }

        if( $(trslctr + ' .status').hasClass('hidden') ){
            $(trslctr + ' .status').removeClass('hidden');
        }
    }

    function prepareEditLocation(){

    }

    function onDttblDraw(){
        const itemActionReqRemoveLocation = '#locations-table .actions .action.remove';
        $(itemActionReqRemoveLocation).off('click');
        $(itemActionReqRemoveLocation).on('click', confirmRemoveLocation);
    
        const itemActionReqEditLocation = '#locations-table .actions .action.edit';
        $(itemActionReqEditLocation).off('click');
        $(itemActionReqEditLocation).on('click', prepareEditLocation);
    
    }

    function add_new_row(){
        dttbl.fnAddData([
            null,
            '<input type="text" id="newRowLocationCode" />',
            '<input type="text" id="newRowDescription" />',
            '<input type="text" id="newRowParent" />',
            null
        ]);
    }

    function button_save_set_disabled(){
        if( !$(btn_save).hasClass('disabled') ){
            $(btn_save).addClass('disabled');
        }
    }

    function button_save_set_enabled(){
        if($(btn_save).hasClass('disabled')){
            $(btn_save).removeClass('disabled');
        }
    }

    function activate_spinner_img(){
        
        if($(img_spinner).hasClass('hidden')){
            $(img_spinner).removeClass('hidden');
        }
    }

    function deactivate_spinner_img(){
        if(!$(img_spinner).hasClass('hidden')){
            $(img_spinner).addClass('hidden');
        }
    }
    
    function validate_form_fields(){
        isFormValid = true;
        if( $('#location-code').val() == '' )
            isFormValid = false;

        if( $('#location-type').val() == '' )
            isFormValid = false;

        if( $('#location-title').val() == '' )
            isFormValid = false;

        if( isFormValid ){
            button_save_set_enabled();
        } else {
            button_save_set_disabled();
        }
    }

    function set_form_add_new_saving(){
        isFormSaving = true;
        button_save_set_disabled();
        activate_spinner_img();
    }

    function set_form_add_new_no_saving(){
        isFormSaving = false;
        button_save_set_enabled();
        deactivate_spinner_img();
    }

    function reset_form_add_new_fields(){
        $('#location-code').val('');
        $('#location-type').val('');
        $('#location-title').val('');
        $('#location-parent').val('');
    }

    function reset_form_add_new_result(){
        $('.result-notice').removeClass('success');
        $('.result-notice').removeClass('error');
        $('.result-notice').addClass('hidden');
    }

    function set_form_add_new_result_error(){
        $('.result-notice').removeClass('success');
        $('.result-notice').removeClass('hidden');
        $('.result-notice').addClass('error');
        $('.result-notice').text('No se ha podido guardara.');
    }

    function set_form_add_new_result_success(){
        $('.result-notice').removeClass('error');
        $('.result-notice').removeClass('hidden');
        $('.result-notice').addClass('success');
        $('.result-notice').text('Locación actualizada exitosamente.');
    }

    function inputChange(){
        reset_form_add_new_result();
        validate_form_fields();
            
    }

    function prepare_data_to_send(){
        const locationData = {
            'code': $('#location-code').val(),
            'type': $('#location-type').val(),
            'title': $('#location-title').val(),
            'parent': $('#location-parent').val(),
            'vwds-locations-nonce': $('#vwds-locations').val()
        }

        return locationData;
    }

    function try_send_del_location_req(){
        if( locationToRemove.length > 0 ){
            const dt = JSON.stringify(locationToRemove);

            const ajxConfig = {
                url: JGB_VWDS.urlDelLocations,
                contentType: "application/json; charset=UTF-8",
                data: dt,
                method: 'POST',
                error: function(  jqXHR,  textStatus,  errorThrown){
                    console.log('No se pudo eliminar la locación.');
                },
                success: function( data,  textStatus,  jqXHR){
                
                    if( data.err_status != undefined && data.err_status == 0){
                        dttbl.ajax.reload();
                    } else {
                        console.log('No se pudo eliminar la locación.');
                    }
                    
                }
            };

            $.ajax(ajxConfig);
        }
    }

    function try_send_new_location_req(){
        if( isFormValid ){
            set_form_add_new_saving();

            const dt = JSON.stringify(prepare_data_to_send());

            const ajxConfig = {
                url: JGB_VWDS.urlGetLocations,
                contentType: "application/json; charset=UTF-8",
                data: dt,
                method: 'POST',
                error: function(  jqXHR,  textStatus,  errorThrown){
                    set_form_add_new_result_error();
                },
                success: function( data,  textStatus,  jqXHR){
                
                    if( data.err_status != undefined && data.err_status == 0){
                        set_form_add_new_result_success();
                        //reload datatable
                        dttbl.ajax.reload();
                        reset_form_add_new_fields();
                    } else {
                        set_form_add_new_result_error();
                    }
                    
                },
                complete: function( jqXHR,  textStatus){
                    set_form_add_new_no_saving();
                    
                }
            };

            $.ajax(ajxConfig);

        }
    }

    $(document).ready(function () {
        $('#add_row').click(add_new_row);
        
        dttbl = $('#jgb-vwds-location-list #locations-table').DataTable( {
            processing: true,
            serverSide: true,
            ajax: JGB_VWDS.urlGetLocations,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es-cl.json'
            },
            columns: [
                {
                    data: 'selection',
                    render: selection_data_render
                },
                {
                    data: 'location_code'
                },
                {
                    data: 'type'
                },
                {
                    data: 'title'
                },
                {
                    data: 'parent'
                },
                {
                    data: 'active'
                },
                {
                    data: 'actions',
                    render: actions_data_render
                }
            ],
            drawCallback: onDttblDraw
        } );

        $('#location-code').change(inputChange);
        $('#location-type').change(inputChange);
        $('#location-title').change(inputChange);
        $('#location-parent').change(inputChange);

        $('#new-location-form .buttons .button.save').click(try_send_new_location_req);

        remConfirmDlg = $( "#remove-confirm-dlg" ).dialog({
            autoOpen: false,
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            closeText: "",
            buttons: {
              "Eliminar": function() {
                $( this ).dialog( "close" );
                setLocationInRomevingMode();
                try_send_del_location_req();
              },
              Cancelar: function() {
                $( this ).dialog( "close" );
              }
            }
        }); 
    });

})(jQuery);