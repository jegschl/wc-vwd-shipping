<?php

/**
 * Admin View: Locations list actions.
 *
 * @package jgb-vwds
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="actions">

    <div class="action edit">
        <span class="dashicons dashicons-edit"></span>
    </div>

    <div class="action remove">
        <span class="dashicons dashicons-trash"></span>
    </div>

    <div class="status removing hidden">
        <img src="<?= $img_path_spinner ?>">
        <span>Eliminando...</span>
    </div>

</div>