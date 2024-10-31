<?php
/**
 *
 * @package   Advanced Responsive Video Embedder
 * @author    Nicolas Jonas
 * @license   GPL-3.0
 * @link      https://nextgenthemes.com
 * @copyright Copyright (c) 2013-2017 Nicolas Jonas
 *
 * @wordpress-plugin
 * Plugin Name:       Regenerate Thumbnails Reminder
 * Plugin URI:        http://nextgenthemes.com/plugins/regenerate-thumbnails-reminder/
 * Description:       Checks if your image sizes has changed or there was a new one added, if so it reminds you to go regenerate them. Redirects you to the "Regenerate Thumbnails" plugin's tool page, but you can use whatever plugin you prefer to regenerate thumbnails (images).
 * Version:           2.0.0
 * Author:            Nicolas Jonas
 * Author URI:        http://nextgenthemes.com
 * Text Domain:       regenerate-thumbnails-reminder
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/nextgenthemes/regenerate-thumbnails-reminder/
 * GitHub Branch:     master
 *
 **********************************************************************************
 * _  _ ____ _  _ ___ ____ ____ _  _ ___ _  _ ____ _  _ ____ ____  ____ ____ _  _ *
 * |\ | |___  \/   |  | __ |___ |\ |  |  |__| |___ |\/| |___ [__   |    |  | |\/| *
 * | \| |___ _/\_  |  |__] |___ | \|  |  |  | |___ |  | |___ ___] .|___ |__| |  | *
 *                                                                                *
 **********************************************************************************
 */

namespace nextgenthemes\regenerate_thumbnails_reminder;

if ( ! defined( 'ABSPATH' ) ) {
	die( "Can't load this file directly" );
}

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
}

function init() {
	add_action( 'admin_init', __NAMESPACE__ . '\\admin_init' );
	add_action( 'admin_post_reg_thumb_reminder_apa', __NAMESPACE__ . '\\admin_post_callback' );
}

function admin_init() {
	// only if we're in the admin panel, and the current user has permission
	// to edit options
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$all_image_sizes = get_all_image_sizes();

	$options = get_option( 'regenerate_thumbs_reminder_options', array() );

	if( ! isset ( $options['all_image_sizes'] ) ) {
		$options['all_image_sizes'] = $all_image_sizes;
		update_option( 'regenerate_thumbs_reminder_options', $options, '', 'yes' );
		return;
	}

	if( $all_image_sizes == $options['all_image_sizes'] ) {
		return;
	} else {
		add_action( 'admin_notices', __NAMESPACE__ . '\\action_admin_notice' );
	}
}

function action_admin_notice() {

?>
<div class="updated">
	<p>
		<?php
		printf(
			__( 'Your image sizes have been changed, you might want to <a href="%s">regenerate them now.</a> <a href="%s">Dismiss</a>', 'regenerate-thumbnails-reminder' ),
			admin_url( 'admin-post.php?action=reg_thumb_reminder_apa' ),
			admin_url( 'admin-post.php?action=reg_thumb_reminder_apa&dismiss=true' )
		);
		?>
		<span class="description"><?php _e( '(in case a image size was removed ignore this and click Dismiss)', 'regenerate-thumbnails-reminder' ); ?></span>
	</p>
</div>
<?php
}

function get_all_image_sizes() {
	$all_image_sizes = array();

	foreach ( get_intermediate_image_sizes() as $size ) {

		global $_wp_additional_image_sizes;

		if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {

			$all_image_sizes[ $size ]['width']  = (int) $_wp_additional_image_sizes[ $size ]['width'];
			$all_image_sizes[ $size ]['height'] = (int) $_wp_additional_image_sizes[ $size ]['height'];

		} else {

			$all_image_sizes[ $size ]['width']  = (int) get_option( "{$size}_size_w" );
			$all_image_sizes[ $size ]['height'] = (int) get_option( "{$size}_size_h" );
		}
	}

	return $all_image_sizes;
}

function admin_post_callback() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options['all_image_sizes'] = get_all_image_sizes();

	update_option( 'regenerate_thumbs_reminder_options', $options );

	// if just dismiss was clicked
	if ( isset( $_GET["dismiss"] ) ) {

		if ( wp_get_referer() )
			wp_redirect( wp_get_referer() );
		else
			wp_redirect( admin_url() );
		exit;
	}

	wp_redirect( admin_url( 'tools.php?page=regenerate-thumbnails' ) );
	exit;
}
