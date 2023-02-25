<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}


class JGBVWDSProductsFields{
    public function emp_add_product_shipping_fields(){
        global $post;
        global $product_object;


        echo '</div><div class="emp_opts_group_vol_weight">'; // New option group
        
        $vwe = $product_object->get_meta('_vol_weight_enabled');
        woocommerce_wp_checkbox( array(
            'id'          => '_vol_weight_enabled',
            'label'       => __( 'Activar peso volumétrico', 'woocommerce' ),
            'desc_tip'    => 'false',
            'value'       => empty($vwe) ? 'no' : $vwe,
            
            
        ) );

        woocommerce_wp_text_input( array(
            'id'          => '_vol_weight_mult_factor',
            'label'       => __( 'Factor de multiplicación', 'woocommerce' ),
            'placeholder' => 'Ingrese el factor de multiplicación para el peso volumétrico',
            'desc_tip'    => 'true',
            'description' => __( 'Ingrese el factor de multiplicación para el peso volumétrico.', 'woocommerce' ),
            'value'       => get_post_meta( $post->ID, '_vol_weight_mult_factor', true ),
            
        ) );

        woocommerce_wp_text_input( array(
            'id'          => '_vol_weight_min_qty',
            'label'       => __( 'Cantidad mínima para altura máxima', 'woocommerce' ),
            'placeholder' => 'Ingrese la cantidad mínima para altura máxima',
            'desc_tip'    => 'true',
            'description' => __( 'Cantidad mínima para altura máxima.', 'woocommerce' ),
            'value'       => get_post_meta( $post->ID, '_vol_weight_min_qty', true ),
        ) );
        
        woocommerce_wp_text_input( array(
            'id'          => '_vol_weight_max_height',
            'label'       => __( 'Altura máxima', 'woocommerce' ),
            'placeholder' => 'Ingrese la altura máxima en cm',
            'desc_tip'    => 'true',
            'description' => __( 'Altura máxima', 'woocommerce' ),
            'value'       => get_post_meta( $post->ID, '_vol_weight_max_height', true ),
        ) );
        
        woocommerce_wp_text_input( array(
            'id'          => '_vol_weight_max_qty_per_pkg',
            'label'       => __( 'Cantidad máxima por unidad de empaque (pallet, bulto, etc.)', 'woocommerce' ),
            'placeholder' => 'Ingrese la cantidad máxima por unidad de empaque para envío',
            'desc_tip'    => 'true',
            'description' => __( 'Cantidad máxima por unidad de empaque para el cálculo del peso volumétrico.', 'woocommerce' ),
            'value'       => get_post_meta( $post->ID, '_vol_weight_max_qty_per_pkg', true ),
        ) );
    }

    public function save_product_vol_weight_shipping_opts( $post_id ){

        $vol_weight_enabled = isset($_POST['_vol_weight_enabled']) ? 'yes' : 'no';
        if( isset( $vol_weight_enabled ) )
            update_post_meta( $post_id, '_vol_weight_enabled', esc_attr( $vol_weight_enabled ) );

        $vol_weight_mult_factor = $_POST['_vol_weight_mult_factor'];
        if( isset( $vol_weight_mult_factor ) )
            update_post_meta( $post_id, '_vol_weight_mult_factor', esc_attr( $vol_weight_mult_factor ) );

        $vol_weight_min_qty = $_POST['_vol_weight_min_qty'];
        if( isset( $vol_weight_min_qty ) )
            update_post_meta( $post_id, '_vol_weight_min_qty', esc_attr( $vol_weight_min_qty ) );

        $vol_weight_max_qty_per_pkg = $_POST['_vol_weight_max_qty_per_pkg'];
        if( isset( $vol_weight_max_qty_per_pkg ) )
            update_post_meta( $post_id, '_vol_weight_max_qty_per_pkg', esc_attr( $vol_weight_max_qty_per_pkg ) );
        
        $vol_weight_max_height = $_POST['_vol_weight_max_height'];
        if( isset( $vol_weight_max_height ) )
            update_post_meta( $post_id, '_vol_weight_max_height', esc_attr( $vol_weight_max_height ) );
    }
}