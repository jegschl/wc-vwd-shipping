<?php

/**
 * Admin View: Settings Zones
 *
 * @package jgb-vwds
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<section id="jgb-vwds-zones-list">
    <?php do_action('JGB/VWDS/admin_settings_zones_before_datatable'); ?>
    <table id="zones-table" class="display">
        <thead class="thead">
            <tr class="tr">
                <th>Selección</th>
                <th>Código</th>
                <th>Nombre</th>
                <?php if( $mode_price != 'WR' ) : ?>
                <th>Tarifa estándar</th>
                <?php endif; ?>
                <th>Acciones</th>
            </tr>
        </thead>
        <!--body-->
        <tfoot>
            <tr class="tr">
                <th>Selección</th>
                <th>Código</th>
                <th>Nombre</th>
                <?php if( $mode_price != 'WR' ) : ?>
                <th>Tarifa estándar</th>
                <?php endif; ?>
                <th>Acciones</th>
            </tr>
        </tfoot>
    </table>
</section>