(function( $ ) {
	'use strict';

    let dttbl = null;

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

    $(document).ready(function () {
        
        dttbl = $('#tabla').DataTable( {
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

    });

})(jQuery);