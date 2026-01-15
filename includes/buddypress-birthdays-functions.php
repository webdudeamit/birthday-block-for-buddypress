<?php
/**
 * Birthday Block for BuddyPress Helper Functions
 *
 * Core birthday calculation and retrieval functions
 *
 * @package buddypress-birthday
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculate age from birthday
 *
 * @param string $birthday Date in Y-m-d format.
 * @return int Age in years.
 */
function buddy_birthday_calculate_age( $birthday ) {
	if ( empty( $birthday ) || ! buddy_birthday_validate_date( $birthday ) ) {
		return 0;
	}

	try {
		$birth_date = new DateTime( $birthday );
		$today      = new DateTime( 'today' );
		return $birth_date->diff( $today )->y;
	} catch ( Exception $e ) {
		return 0;
	}
}

/**
 * Get upcoming birthdays within a date range
 *
 * @param int    $field_id xProfile field ID.
 * @param string $range Range type: 'today', 'weekly', 'monthly', 'upcoming'.
 * @param int    $limit Number of results.
 * @param string $scope 'all' or 'friends'.
 * @param int    $user_id User ID (for friends scope).
 * @return array Birthday data.
 */
function buddy_birthday_get_upcoming_birthdays( $field_id, $range = 'upcoming', $limit = 5, $scope = 'all', $user_id = 0 ) {
	global $wpdb;

	$field_id = absint( $field_id );
	$limit    = absint( $limit );

	if ( ! $field_id || ! $limit ) {
		return array();
	}

	// Get BuddyPress table prefix
	$bp_prefix = bp_core_get_table_prefix();

	// Base query for birthday data
	$sql = "SELECT DISTINCT u.ID, u.display_name, pd.value as birthday
			FROM {$wpdb->users} u
			INNER JOIN {$bp_prefix}bp_xprofile_data pd ON u.ID = pd.user_id
			WHERE pd.field_id = %d
			AND pd.value IS NOT NULL
			AND pd.value != ''
			AND pd.value != '0000-00-00'";

	// Add friends filter if needed
	$prepare_args = array( $field_id );
	if ( 'friends' === $scope && $user_id > 0 ) {
		$sql .= " AND u.ID IN (
			SELECT friend_user_id FROM {$bp_prefix}bp_friends
			WHERE initiator_user_id = %d AND is_confirmed = 1
			UNION
			SELECT initiator_user_id FROM {$bp_prefix}bp_friends
			WHERE friend_user_id = %d AND is_confirmed = 1
		)";
		$prepare_args[] = $user_id;
		$prepare_args[] = $user_id;
	}

	// Add ORDER BY based on range
	$sql .= ' ORDER BY ' . buddy_birthday_get_upcoming_sort_sql();
	$sql .= ' LIMIT %d';
	$prepare_args[] = $limit * 3; // Get more results for filtering
	// Prepare and execute query
	// Use call_user_func_array to pass dynamic number of parameters
	$prepare_params = array_merge( array( $sql ), $prepare_args );
	$prepared       = call_user_func_array( array( $wpdb, 'prepare' ), $prepare_params ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$results        = $wpdb->get_results( $prepared ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	if ( empty( $results ) ) {
		return array();
	}

	// Process and filter results
	$birthdays = array();
	foreach ( $results as $row ) {
		// Validate date
		if ( ! buddy_birthday_validate_date( $row->birthday ) ) {
			continue;
		}

		// Check if birthday is in the specified range
		if ( ! buddy_birthday_is_in_range( $row->birthday, $range ) ) {
			continue;
		}

		// Stop if we have enough results
		if ( count( $birthdays ) >= $limit ) {
			break;
		}

		$user_id          = absint( $row->ID );
		$age              = buddy_birthday_calculate_age( $row->birthday );
		$next_birthday    = buddy_birthday_get_next_birthday_date( $row->birthday );
		$days_until       = buddy_birthday_get_days_until( $row->birthday );
		$formatted_date   = buddy_birthday_format_date( $next_birthday );

		// Get avatar URL
		$avatar_args = array(
			'item_id' => $user_id,
			'type'    => 'thumb',
			'html'    => false,
		);
		$avatar_url = bp_core_fetch_avatar( $avatar_args );

		// Get profile URL
		$profile_url = bp_core_get_user_domain( $user_id );

		// Get message URL if messages component is active
		$message_url = '';
		if ( bp_is_active( 'messages' ) && is_user_logged_in() ) {
			// Construct the compose message URL for this specific user
			$message_url = wp_nonce_url(
				bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( $user_id )
			);
		}

		$birthdays[] = array(
			'user_id'        => $user_id,
			'name'           => $row->display_name,
			'birthday'       => $row->birthday,
			'age'            => $age,
			'next_birthday'  => $next_birthday,
			'days_until'     => $days_until,
			'formatted_date' => $formatted_date,
			'avatar'         => $avatar_url,
			'profile_url'    => $profile_url,
			'message_url'    => $message_url,
		);
	}

	return $birthdays;
}

/**
 * Get complex SQL for sorting by upcoming birthday
 *
 * @return string SQL ORDER BY clause.
 */
function buddy_birthday_get_upcoming_sort_sql() {
	// This sorts birthdays by the next occurrence (handling year wrap-around)
	// Note: %% is used to escape % for wpdb->prepare()
	return "CASE
		WHEN DATE_FORMAT(pd.value, '%%m-%%d') >= DATE_FORMAT(CURDATE(), '%%m-%%d')
		THEN DATE_FORMAT(pd.value, '%%m%%d')
		ELSE CONCAT('13', DATE_FORMAT(pd.value, '%%m%%d'))
	END ASC";
}

/**
 * Check if birthday is in specified range
 *
 * @param string $birthday Birthday date string.
 * @param string $range Range type.
 * @return bool True if in range, false otherwise.
 */
function buddy_birthday_is_in_range( $birthday, $range ) {
	if ( empty( $birthday ) ) {
		return false;
	}

	try {
		$birth_date = new DateTime( $birthday );
		$today      = new DateTime( 'today' );

		// Get month and day
		$birth_md = $birth_date->format( 'm-d' );
		$today_md = $today->format( 'm-d' );

		switch ( $range ) {
			case 'today':
				return $birth_md === $today_md;

			case 'weekly':
				$next_week    = ( new DateTime( 'today' ) )->modify( '+7 days' );
				$next_week_md = $next_week->format( 'm-d' );

				// Handle year boundary
				if ( $next_week_md < $today_md ) {
					return $birth_md >= $today_md || $birth_md <= $next_week_md;
				}
				return $birth_md >= $today_md && $birth_md <= $next_week_md;

			case 'monthly':
				return $birth_date->format( 'm' ) === $today->format( 'm' ) && $birth_md >= $today_md;

			default: // upcoming - all future birthdays
				return true;
		}
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Get next birthday date for display
 *
 * @param string $birthday Original birthday date.
 * @return string Next birthday date in Y-m-d format.
 */
function buddy_birthday_get_next_birthday_date( $birthday ) {
	if ( empty( $birthday ) ) {
		return '';
	}

	try {
		$birth_date   = new DateTime( $birthday );
		$today        = new DateTime( 'today' );
		$current_year = $today->format( 'Y' );

		// Set to current year
		$next_birthday = new DateTime( $current_year . '-' . $birth_date->format( 'm-d' ) );

		// If already passed this year, use next year
		if ( $next_birthday < $today ) {
			$next_birthday->modify( '+1 year' );
		}

		return $next_birthday->format( 'Y-m-d' );
	} catch ( Exception $e ) {
		return '';
	}
}

/**
 * Get days until next birthday
 *
 * @param string $birthday Original birthday date.
 * @return int Number of days until next birthday.
 */
function buddy_birthday_get_days_until( $birthday ) {
	$next_birthday = buddy_birthday_get_next_birthday_date( $birthday );
	if ( empty( $next_birthday ) ) {
		return 0;
	}

	try {
		$next  = new DateTime( $next_birthday );
		$today = new DateTime( 'today' );

		return (int) $today->diff( $next )->days;
	} catch ( Exception $e ) {
		return 0;
	}
}

/**
 * Format birthday date for display
 *
 * @param string $date Date to format.
 * @param string $format PHP date format (default: 'F d').
 * @return string Formatted date.
 */
function buddy_birthday_format_date( $date, $format = 'F d' ) {
	if ( empty( $date ) ) {
		return '';
	}

	try {
		$date_obj = new DateTime( $date );
		return date_i18n( $format, $date_obj->getTimestamp() );
	} catch ( Exception $e ) {
		return '';
	}
}

/**
 * Validate a date string
 *
 * @param string $date Date string to validate.
 * @return bool True if valid, false otherwise.
 */
function buddy_birthday_validate_date( $date ) {
	if ( empty( $date ) ) {
		return false;
	}

	// Check for invalid date
	if ( '0000-00-00' === $date ) {
		return false;
	}

	// Try to parse the date
	$parsed = date_parse( $date );

	if ( false === $parsed || $parsed['error_count'] > 0 ) {
		return false;
	}

	// Check if it's a valid date
	if ( ! checkdate( $parsed['month'], $parsed['day'], $parsed['year'] ) ) {
		return false;
	}

	return true;
}
