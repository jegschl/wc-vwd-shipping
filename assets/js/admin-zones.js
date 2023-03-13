(function( $ ) {
	'use strict';

    let dttblZones = null;

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

    function actions_data_render(data, type){
		if (type === 'display') {
			
            return JGB_VWDS.actionsHtml ;
        }
         
        return data;
	}

    function onDttblCreatedRow( row, data, dataIndex, cells ){
        $(row).data('parent-location-code',data['DT_RowData']['parent-location-code']);
    }

    function confirmRemoveZones(){

    }

    function prepareEditZone(){

    }

    function onDttblDraw(){
        const itemActionReqRemoveZones = '#zones-table .actions .action.remove';
        $(itemActionReqRemoveZones).off('click');
        $(itemActionReqRemoveZones).on('click', confirmRemoveZones);
    
        const itemActionReqEditZone = '#zones-table .actions .action.edit';
        $(itemActionReqEditZone).off('click');
        $(itemActionReqEditZone).on('click', prepareEditZone);
    
    }

    $(document).ready(function () {

        let zonesCols = [];

        zonesCols.push({
            data: null,
            render: selection_data_render
        });

        zonesCols.push({
            data: 'zone_code'
        });

        zonesCols.push({
            data: 'name'
        });

        if( JGB_VWDS.priceMode != 'WR' ){
            zonesCols.push({
                data: 'price_unitary'
            });
        }

        zonesCols.push({
            data: null,
            render: actions_data_render
        });


        dttblZones = $('#jgb-vwds-zones-list #zones-table').DataTable( {
            processing: true,
            serverSide: true,
            ajax: JGB_VWDS.urlGetZones,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es-cl.json'
            },
            columns: zonesCols,
            drawCallback: onDttblDraw,
            createdRow: onDttblCreatedRow
        } );

        $('#price-mode-selection .item-option input').click(function(){
            
            const modePrice = $('#price-mode-selection .item-option input:radio:checked').val();
            const dt = {
                'nm': 'mode_price',
                'vl': modePrice
            }

            const ajxcfg = {
                url: JGB_VWDS.urlSetOpts,
                contentType: "application/json; charset=UTF-8",
                data: JSON.stringify(dt),
                method: 'POST',
                error: function(  jqXHR,  textStatus,  errorThrown){
                    console.log('No se pudo actrualizar la opción.');
                },
                success: function( data,  textStatus,  jqXHR){
                
                    if( data.err_status != undefined && data.err_status == 0){
                        location.reload();
                    } else {
                        console.log('No se pudo actrualizar la opción.');
                    }
                    
                }
            }

            $.ajax(ajxcfg);
        });

    });

})(jQuery);