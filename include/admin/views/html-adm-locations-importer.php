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
            <div class="field locations-data">
                <div class="label"><label for="locations-data">Código</label></div>
                <div class="input">
                    <textarea 
                        id="locations-data" 
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