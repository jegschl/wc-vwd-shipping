<?php
/**
 * Admin View: Settings
 *
 * @package jgb-vwds
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$current_tab_label = isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ]['label'] : '';


?>
<div class="wrap jgb-vwds">
	<?php do_action( 'JGB/VWDS/before_settings_' . $current_tab ); ?>
	<form method="<?php echo esc_attr( apply_filters( 'JGB/VWDS/settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php

			foreach ( $tabs as $slug => $t ) {
                $label = $t['label'];
				echo '<a href="' . esc_html( admin_url( 'admin.php?page=jgb-vwds-settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
			}

			do_action( 'JGB/VWDS/settings_tabs' );

			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html( $current_tab_label ); ?></h1>
		<?php
			do_action( 'JGB/VWDS/sections_' . $current_tab );

			do_action( 'JGB/VWDS/settings_' . $current_tab );
			
			do_action( 'JGB/VWDS/settings_tabs_' . $current_tab ); // @deprecated 3.4.0 hook.
		?>
		
		<?php wp_nonce_field( 'jgb-vwds-settings' ); ?>
		
	</form>
	<?php do_action( 'JGB/VWDS/after_settings_' . $current_tab ); ?>
</div>
