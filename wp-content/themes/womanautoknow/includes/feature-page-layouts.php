<?php

/**
 * Feature: Page Layouts
 * @version 1.0
 */
add_action( 'init', 'wak_theme_feature_page_layouts' );
function wak_theme_feature_page_layouts() {

	add_action( 'add_meta_boxes',    'wak_theme_page_layout_metaboxes' );
	add_action( 'save_post',         'wak_theme_save_page_layout' );
	add_filter( 'wp_nav_menu_items', 'wak_theme_adjust_top_nav', 50, 2 );

}

/**
 * Adjust Top Navigation
 * @version 1.0
 */
function wak_theme_adjust_top_nav( $items, $args ) {

	if ( $args->theme_location != 'primary' )
		return $items;

	$items = str_replace( 'page menu-item-13', 'page menu-item-13 toggle-driver-menu', $items );

	if ( is_author() )
		$items = str_replace( 'toggle-driver-menu', 'toggle-driver-menu current_page_item', $items );

	if ( is_post_type_archive( 'autoshops' ) || is_singular( 'autoshops' ) )
		$items = str_replace( 'page menu-item-14', 'page menu-item-14 current_page_item', $items );

	$prefs = wak_theme_prefs();
	$base_url = get_template_directory_uri() . '/images/';

	/*if ( $prefs['facebook_link'] != '' )
		$items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page social-media-item"><a href="' . $prefs['facebook_link'] . '" target="_blank" class="pink"><i class="fa fa-facebook"></i></a></li>';

	if ( $prefs['twitter_link'] != '' )
		$items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page social-media-item"><a href="' . $prefs['twitter_link'] . '" target="_blank" class="pink"><i class="fa fa-twitter"></i></a></li>';*/

	return $items;

}

/**
 * Register Metabox
 * @version 1.0
 */
function wak_theme_page_layout_metaboxes() {

	add_meta_box(
		'wak-page-layout-box',
		'Page Layout',
		'wak_theme_metabox_layout',
		'page',
		'side',
		'high'
	);

}

/**
 * Metabox: Page Layout
 * @version 1.0
 */
function wak_theme_metabox_layout( $post ) {

	$options = array(
		'none'  => 'No Sidebar',
		'right' => 'Sidebar on right side',
		'left'  => 'Sidebar on the left side'
	);

	$layout = get_post_meta( $post->ID, 'layout', true );

?>
<label for="wak-theme-layout">Sidebar:</label><br />
<select name="wak_theme_layout" id="wak-theme-layout">
<?php

	foreach ( $options as $value => $label ) {

		echo '<option value="' . $value . '"';
		if ( $layout == $value ) echo ' selected="selected"';
		echo '>' . $label . '</option>';

	}

?>
</select>
<?php
}

/**
 * Save Layout
 * @version 1.0
 */
function wak_theme_save_page_layout( $post_id ) {

	if ( ! current_user_can( 'edit_users' ) ) return;

	if ( isset( $_POST['wak_theme_layout'] ) ) {

		$layout = sanitize_key( $_POST['wak_theme_layout'] );
		if ( $layout == '' ) $layout = 'full';

		update_post_meta( $post_id, 'layout', $layout );

	}

}

/**
 * Get Theme Layout
 * @version 1.0
 */
function wak_theme_get_layout( $object_id = NULL ) {

	global $post;

	if ( $object_id === NULL && is_object( $post ) )
		$object_id = $post->ID;

	if ( $object_id === NULL ) return 'none';

	$layout = get_post_meta( $object_id, 'layout', true );
	if ( $layout == '' )
		$layout = 'none';

	if ( $layout == 'full' ) {
		update_post_meta( $object_id, 'layout', 'none' );
		$layout = 'none';
	}

	return $layout;

}

/**
 * Theme Slider
 * @version 1.0
 */
function wak_theme_slider() {

	if ( ! function_exists( 'putRevSlider' ) || ! is_singular() ) return;

?>
<div class="full">
	<?php putRevSlider( 'videofront' ); ?>
</div>
<?php

}

?>