<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package buddypress-birthday
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove plugin settings on uninstall
 *
 * Deletes all options created by the plugin from the database.
 */
function buddy_birthday_uninstall() {
	// Delete plugin options
	delete_option( 'buddy_birthday_field_id' );
	delete_option( 'buddy_birthday_default_range' );
	delete_option( 'buddy_birthday_default_limit' );

	// If multisite, delete options for all sites
	if ( is_multisite() ) {
		global $wpdb;

		// Get all blog IDs
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );

			// Delete options for this site
			delete_option( 'buddy_birthday_field_id' );
			delete_option( 'buddy_birthday_default_range' );
			delete_option( 'buddy_birthday_default_limit' );

			restore_current_blog();
		}
	}

	// Clear any cached data
	wp_cache_flush();
}

// Run the uninstall function
buddy_birthday_uninstall();
