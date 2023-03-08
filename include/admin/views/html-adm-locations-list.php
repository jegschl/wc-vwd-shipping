<?php

/**
 * Admin View: Settings Locations
 *
 * @package jgb-vwds
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<section id="jgb-vwds-location-list">
    
    <?php do_action('JGB/VWDS/admin_settings_locations_before_datatable'); ?>
    <table id="locations-table" class="display">
        <thead class="thead">
            <tr class="tr">
                <th>Selección</th>
                <th>Código</th>
                <th>Tipo</th>				
                <th>Título</th>
                <th>Locación superior</th>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <!--body-->
        <tfoot>
            <tr class="tr">
                <th>Selección</th>
                <th>Código</th>
                <th>Tipo</th>				
                <th>Título</th>
                <th>Locación superior</th>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </tfoot>

    </table>
    <?php do_action('JGB/VWDS/admin_settings_locations_after_datatable'); ?>
    
</section>