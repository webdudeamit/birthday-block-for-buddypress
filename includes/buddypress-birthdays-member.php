<?php
/**
 * Birthday Block for BuddyPress Members REST API
 *
 * Handles REST API endpoints for birthday data
 *
 * @package buddypress-birthday
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Buddy_Birthday_Members {

	/**
	 * Holds the class instance.
	 *
	 * @var array
	 */
	private static $instance = array();

	/**
	 * Constructor.
	 *
	 * Initializes the class and calls the init method.
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * The method you use to get the Buddy_Birthday_Members's instance.
	 *
	 * This method ensures that only one instance of the class is created,
	 * reducing memory usage and improving performance.
	 *
	 * @return Buddy_Birthday_Members The instance of the class.
	 */
	public static function get_instance() {
		$cls = static::class;
		if ( ! isset( self::$instance[ $cls ] ) ) {
			self::$instance[ $cls ] = new static();
		}

		return self::$instance[ $cls ];
	}

	/**
	 * Initializes the class.
	 *
	 * Registers a custom REST API route using the 'rest_api_init' action hook.
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'buddy_birthday_register_custom_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function buddy_birthday_register_custom_routes() {
		register_rest_route(
			'buddypress-birthday/v1',
			'/birthdays',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_birthdays' ),
				'permission_callback' => array( $this, 'get_birthdays_permissions_check' ),
				'args'                => array(
					'range'    => array(
						'default'           => 'upcoming',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function( $param ) {
							return in_array( $param, array( 'today', 'weekly', 'monthly', 'upcoming' ), true );
						},
					),
					'limit'    => array(
						'default'           => 5,
						'sanitize_callback' => 'absint',
						'validate_callback' => function( $param ) {
							return $param > 0 && $param <= 50;
						},
					),
					'scope'    => array(
						'default'           => 'all',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function( $param ) {
							return in_array( $param, array( 'all', 'friends' ), true );
						},
					),
					'field_id' => array(
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Permission check for birthdays endpoint
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if allowed, WP_Error otherwise.
	 */
	public function get_birthdays_permissions_check( $request ) {
		$scope = $request->get_param( 'scope' );

		// Public data - anyone can see all members' birthdays
		if ( 'all' === $scope ) {
			return true;
		}

		// Friends scope - must be logged in
		if ( 'friends' === $scope && ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to view friends birthdays.', 'birthday-block-for-buddypress' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Get birthdays REST endpoint callback
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function get_birthdays( $request ) {
		$range    = $request->get_param( 'range' );
		$limit    = $request->get_param( 'limit' );
		$scope    = $request->get_param( 'scope' );
		$field_id = $request->get_param( 'field_id' );

		// Get birthday field ID from settings if not provided
		if ( ! $field_id ) {
			$field_id = get_option( 'buddy_birthday_field_id', 0 );
		}

		if ( ! $field_id ) {
			return new WP_Error(
				'no_birthday_field',
				__( 'No birthday field configured. Please configure it in Settings > BP Birthday.', 'birthday-block-for-buddypress' ),
				array( 'status' => 400 )
			);
		}

		// Get birthdays using helper function
		$birthdays = $this->fetch_birthdays( $field_id, $range, $limit, $scope );

		return new WP_REST_Response( $birthdays, 200 );
	}

	/**
	 * Fetch birthdays data
	 *
	 * @param int    $field_id Birthday field ID.
	 * @param string $range Date range.
	 * @param int    $limit Number of results.
	 * @param string $scope Scope (all or friends).
	 * @return array Birthday data.
	 */
	private function fetch_birthdays( $field_id, $range, $limit, $scope ) {
		$user_id = ( 'friends' === $scope ) ? get_current_user_id() : 0;

		// Use helper function if available, otherwise return empty array
		if ( function_exists( 'buddy_birthday_get_upcoming_birthdays' ) ) {
			return buddy_birthday_get_upcoming_birthdays( $field_id, $range, $limit, $scope, $user_id );
		}

		return array();
	}
}

Buddy_Birthday_Members::get_instance();
