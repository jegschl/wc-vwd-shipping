<?php

/**
 * Admin View: Locations importer.
 *
 * @package jgb-vwds
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
    <div id="locations-importer-form">
        <h3 class="title">Importar locaciones</h3>
        <div class="container fields">
            <input type="hidden" id="<?= JGB_VWDS_LOCATIONS_NONCE_KEY_NM ?>" value="<?= $nonce ?>">
            <div class="parameters">
                <div class="field-set locations-truncate">
                    <input type="checkbox" id="locations-truncate">
                    <label for="locations-truncate">Eliminar todas las locaciones existentes y crear todo a partir de esta importación.</label>
                </div>
                <div class="field-set locations-create-new">
                    <input type="checkbox" id="locations-create-new">
                    <label for="locations-create-new">Crear nuevas locaciones de códigos inexistentes.</label>
                </div>
                <div class="field-set locations-update">
                    <input type="checkbox" id="locations-update">
                    <label for="locations-update">Actualiar locaciones de códigos existentes.</label>
                </div>
            </div>
            <div class="field locations-data">
                <div class="label"><label for="locations-data">Datos para importar locaciones</label></div>
                <div class="input">
                    <textarea 
                        id="input-locations-import-data"
                        placeholder="Aquí puede pegar el listado de locaciones copiadas desde una hoja de cálculo"
                        style="resize:both; width: 100%"
                    ></textarea>
                </div>
            </div>

        </div>
        <div class="container buttons">
            <div class="button save disabled">Importar</div>
            <div class="uploading">
                <img class="hidden" src="<?= $img_path_spinner ?>">
            </div>
        </div>
        <div class="result-notice notice hidden">
        </div>
    </div>