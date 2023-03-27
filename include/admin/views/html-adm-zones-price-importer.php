<?php

/**
 * Admin View: Price importer.
 *
 * @package jgb-vwds
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

    <div id="price-importer">
        <h2 class="title">Importar precios</h2>

        <?php if( $mode_price == 'WR' ) :  ?>
        <div class="prm-field-set col-row-mode">
            <div class="title">Modo de encabezados de columnas y filas</div>
            <div class="prm-field radiobuttons-group ">
                <div class="radiobuttons-group-item">
                    <input 
                        type="radio" 
                        name="rbg-col-row-mode" 
                        value="0" 
                        id="crm-frz-fcw"
                        checked
                    >
                    <label for="crm-frz-fcw">Primera columna: Rangos de pesos / Primera Fila: Nombres de zonas</label>
                </div>
                <div class="radiobuttons-group-item">
                    <input type="radio" name="rbg-col-row-mode" value="1" id="crm-frw-fcz">
                    <label for="crm-frw-fcz">Primera columna: Nombres de zonas / Primera Fila: Rangos de pesos</label>
                </div>
            </div>
        </div>
        <div class="prm-field-set weight-range-mode">
            <div class="title">Modo de rango de pesos</div>
            <div class="prm-field radiobuttons-group">
                <div class="radiobuttons-group-item">
                    <input 
                        type="radio" 
                        name="rbg-weight-range-mode" 
                        value="1" 
                        id="wrm-superior-limit"
                        checked
                    >
                    <label for="wrm-superior-limit">El peso es el límite superior</label>
                </div>
                <div class="radiobuttons-group-item">
                    <input 
                        type="radio" 
                        name="rbg-weight-range-mode" 
                        value="0" 
                        id="wrm-down-superior"
                    >
                    <label for="wrm-down-superior">El peso indica desde - hasta</label>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <textarea 
            id="input-import-data" 
            wrap="off"
            style="resize:both; width: 100%" 
            placeholder="Pegue aquí un set de datos copiados dede una hoja de cálculo..."
        ></textarea>
        
        

        <div class="button import-zone-prices">Importar</div>
    </div>