<?php

/**
 * Admin View: Select price mode.
 *
 * @package jgb-vwds
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

    <div id="price-mode-selection">
        <h2 class="title">Modo de tarificación</h2>

        <div class="radio-options">

            <div class="item-option">
                <label>
                    <input type="radio" name="price-mode" value="OU" <?= $mode_price == 'OU'?'checked':'';?>>
                    <span class="option-label">Solo valor unitario</span>
                </label>
                <p class="desc">Se utiliza el valor unitario solo por destino sin considerar pesos.</p>
            </div>

            <div class="item-option">
                <label>
                    <input type="radio" name="price-mode" value="WU" <?= $mode_price == 'WU'?'checked':'';?>>
                    <span class="option-label">Valor unitario por peso</span>
                </label>
                <p class="desc">Se utiliza el valor unitario por destino multiplicándolo por el total de pesos de productos a comprar.</p>
            </div>

            <div class="item-option">
                <label>
                    <input type="radio" name="price-mode" value="WR" <?= $mode_price == 'WR'?'checked':'';?>>
                    <span class="option-label">Utilizando rango de pesos por destino</span>
                </label>
                <p class="desc">Se utiliza el valor unitario multiplicándolo por el peso de todos los productos a comprar según regla de rango de pesos</p>
            </div>

        </div>
    </div>