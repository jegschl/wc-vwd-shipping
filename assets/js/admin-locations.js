(function( $ ) {
	'use strict';

    let dttbl = null;
    let isFormValid;
    let isFormSaving;

    const btn_save = '#new-location-form .buttons .button.save';
    const img_spinner = '#new-location-form .buttons .uploading img';

    function actions_data_render(data, type){
		if (type === 'display') {
			var output = '';
			output += '<div class="actions">';

			output += '<div class="action edit">';
			output += '<i class="fas fa-edit"></i>'; // icono de edición 
			output += '</div>';

			output += '<div class="action remove">';
			output += '<i class="fas fa-minus-circle"></i>'; // icono de eliminación
			output += '</div>';

            output += '<div class="action update_ok">';
			output += '<i class="fas fa-minus-circle"></i>'; // icono de Ok, guardar cambios.
			output += '</div>';

            output += '<div class="action update_cancel">';
			output += '<i class="fas fa-minus-circle"></i>'; // icono de Cancelar cambios.
			output += '</div>';

			output += '</div>';
            return output ;
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

    function onDttblDraw(){
        const itemActionReqSendDwldCodeSelector = '.action.send-dosf-download-code';
        /* $(itemActionReqSendDwldCodeSelector).off('click');
        $(itemActionReqSendDwldCodeSelector).on('click',dttblItemActionReqSendDownloadCodeEmail); */
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
        $('.result-notice .error').addClass('hidden');
        $('.result-notice .success').addClass('hidden');
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
            'nonce': $('#vwds-locations').val()
        }

        return locationData;
    }

    function try_send_new_location(){
        if( isFormValid ){
            set_form_add_new_saving();

            const dt = prepare_data_to_send();

            const ajxConfig = {
                url: JGB_VWDS.urlGetLocations,
                contentType: "application/json; charset=UTF-8",
                data: dt,
                method: 'POST',
                error: function(  jqXHR,  textStatus,  errorThrown){
                    $('.result-notice .error').removeClass('hidden');
                },
                success: function( data,  textStatus,  jqXHR){
                    $('.result-notice .success').removeClass('hidden');
                    reset_form_add_new_fields();
                    //reload datatable
                    dttbl.ajax.reload();
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

        $('#new-location-form .buttons .button.save').click(try_send_new_location);

    });

})(jQuery);