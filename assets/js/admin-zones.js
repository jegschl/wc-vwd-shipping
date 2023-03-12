(function( $ ) {
	'use strict';

    let dttblZones = null;

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

    });

})(jQuery);