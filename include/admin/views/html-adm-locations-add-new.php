<?php

/**
 * Admin View: Locations add new.
 *
 * @package jgb-vwds
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

    <div id="new-location-form">
        <div id="remove-confirm-dlg" class="hidden" title="Eliminación de locación">
            <p>
                <span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
                Por favor confirmar eliminación de locación.
            </p>
        </div>  
        <h3 class="title">Agrege una nueva locación</h3>
        <div class="container fields">
            <input type="hidden" id="<?= JGB_VWDS_LOCATIONS_NONCE_KEY_NM ?>" value="<?= $nonce ?>">
            <div class="field location-code">
                <div class="label"><label for="location-code">Código</label></div>
                <div class="input"><input type="text" id="location-code" /></div>
            </div>

            <div class="field location-type">
                <div class="label"><label for="location-type">Tipo</label></div>
                <div class="input"><input type="text" id="location-type" /></div>
            </div>

            <div class="field location-title">
                <div class="label"><label for="location-title">Título</label></div>
                <div class="input"><input type="text" id="location-title" /></div>
            </div>

            <div class="field location-parent">
                <div class="label"><label for="location-parent">Código de Locación superior</label></div>
                <div class="input"><input type="text" id="location-parent" /></div>
            </div>
        </div>
        <div class="container buttons">
            <div class="button save disabled">Guardar</div>
            <div class="uploading">
                <img class="hidden" src="<?= $img_path_spinner ?>">
            </div>
        </div>
        <div class="result-notice hidden">
        </div>
    </div>