<?php
/**
 * BuddyPress Birthday
 *
 * @package           buddypress-birthday
 * @author            Amit Kumar Agrahari
 * @copyright         2025 Amit Kumar Agrahari
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       BuddyPress Birthday
 * Plugin URI:        https://wordpress.org/plugins/buddypress-birthday/
 * Description:       Display upcoming birthdays of your BuddyPress members with a beautiful, customizable Gutenberg block. Perfect for building community engagement!
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Amit Kumar Agrahari
 * Author URI:        https://profiles.wordpress.org/amitkumarahari/
 * Text Domain:       buddypress-birthday
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

class BuddyBirthday {

	private static $instance = array();

	/**
	 * Absolute path to this plugin directory.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Absolute url to this plugin directory.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private $basename;


	protected function __construct() {
		$this->path     = plugin_dir_path( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );

		$this->buddy_birthday_init();
	}

	/**
	 * The method you use to get the BuddyBirthday's instance.
	 */
	public static function get_instance() : BuddyBirthday {
		$cls = static::class;
		if ( ! isset( self::$instance[ $cls ] ) ) {
			self::$instance[ $cls ] = new static();
		}

		return self::$instance[ $cls ];
	}

	public function buddy_birthday_init() {
		add_action( 'plugins_loaded', array( $this, 'check_dependencies' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'buddy_birthday_setup_constants' ), 2 );
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 2 );
		add_action( 'bp_init', array( $this, 'load_includes' ), 3 );
		add_action( 'init', array( $this, 'buddy_birthday_block_init' ) );
		add_filter( 'block_categories_all', array( $this, 'buddy_birthday_add_block_category' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Check plugin dependencies
	 *
	 * @return bool True if dependencies are met, false otherwise.
	 */
	public function check_dependencies() {
		// Check if BuddyPress is active
		if ( ! function_exists( 'buddypress' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_buddypress_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * Load plugin includes
	 */
	public function load_includes() {
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Load helper functions
		require_once $this->path . 'includes/buddypress-birthdays-functions.php';

		// Load REST API handler
		require_once $this->path . 'includes/buddypress-birthdays-member.php';

		// Load admin settings (only in admin)
		if ( is_admin() ) {
			require_once $this->path . 'includes/buddypress-birthdays-admin.php';

			// Load dummy data tool only in debug mode
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				require_once $this->path . 'includes/populate-dummy-birthdays.php';
			}
		}
	}

	/**
	 * Display admin notices
	 */
	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if birthday field is configured
		$field_id = get_option( 'bp_birthday_field_id', 0 );
		if ( ! $field_id && function_exists( 'buddypress' ) ) {
			$this->no_birthday_field_notice();
		}
	}

	/**
	 * Admin notice for missing BuddyPress
	 */
	public function missing_buddypress_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: BuddyPress */
					esc_html__( 'BuddyPress Birthday requires %s to be installed and active.', 'buddypress-birthday' ),
					'<strong>' . esc_html__( 'BuddyPress', 'buddypress-birthday' ) . '</strong>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Admin notice for missing birthday field configuration
	 */
	public function no_birthday_field_notice() {
		$screen = get_current_screen();

		// Don't show on the settings page itself
		if ( isset( $screen->id ) && 'settings_page_bp-birthday-settings' === $screen->id ) {
			return;
		}

		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %s: Settings link */
					esc_html__( 'BuddyPress Birthday: Please configure the birthday field in %s.', 'buddypress-birthday' ),
					'<a href="' . esc_url( admin_url( 'options-general.php?page=bp-birthday-settings' ) ) . '">' .
					esc_html__( 'Settings > BP Birthday', 'buddypress-birthday' ) .
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Define constants
	 */
	public function buddy_birthday_setup_constants() {
		if ( ! defined( 'BUDDY_BIRTHDAY_FILE' ) ) {
			define( 'BUDDY_BIRTHDAY_FILE', __FILE__ );
		}
		if ( ! defined( 'BUDDY_BIRTHDAY_URL' ) ) {
			define( 'BUDDY_BIRTHDAY_URL', $this->get_url() );
		}
		if ( ! defined( 'BUDDY_BIRTHDAY_PATH' ) ) {
			define( 'BUDDY_BIRTHDAY_PATH', $this->get_path() );
		}
		if ( ! defined( 'BUDDY_BIRTHDAY_TEMPLATE_PATH' ) ) {
			define( 'BUDDY_BIRTHDAY_TEMPLATE_PATH', $this->get_path() . 'templates/' );
		}
	}

	/**
	 * Get plugin url
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get Plugin Path
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Load translation files
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'buddypress-birthday', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function buddy_birthday_block_init() {
		register_block_type( __DIR__ . '/build' );
	}

	public function buddy_birthday_add_block_category( $block_categories, $block_editor_context ) {

		if ( ! ( $block_editor_context instanceof WP_Block_Editor_Context ) ) {
			return $block_categories;
		}
		return array_merge(
			$block_categories,
			array(
				array(
					'slug'  => 'wbcom-designs',
					'title' => esc_html__( 'WBCOM Designs', 'buddypress-birthday' ),
					'icon'  => 'lightbulb', // Slug of a WordPress Dashicon or custom SVG
				),
			)
		);
	}

}

function buddy_birthday() {
	return BuddyBirthday::get_instance();
}

buddy_birthday();
