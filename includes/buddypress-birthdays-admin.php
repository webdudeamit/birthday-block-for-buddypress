<?php
/**
 * Birthday Block for BuddyPress Admin Settings
 *
 * Admin interface for plugin configuration
 *
 * @package buddypress-birthday
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Buddy_Birthday_Admin {

	/**
	 * Singleton instance
	 *
	 * @var Buddy_Birthday_Admin
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Buddy_Birthday_Admin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'buddy_birthday_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'buddy_birthday_register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'buddy_birthday_enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu item
	 */
	public function buddy_birthday_add_admin_menu() {
		add_options_page(
			__( 'Birthday Block for BuddyPress Settings', 'birthday-block-for-buddypress' ),
			__( 'BP Birthday', 'birthday-block-for-buddypress' ),
			'manage_options',
			'buddy-birthday-settings',
			array( $this, 'buddy_birthday_render_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function buddy_birthday_register_settings() {
		// Register settings
		register_setting(
			'buddy_birthday_settings',
			'buddy_birthday_field_id',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);

		register_setting(
			'buddy_birthday_settings',
			'buddy_birthday_default_range',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'upcoming',
			)
		);

		register_setting(
			'buddy_birthday_settings',
			'buddy_birthday_default_limit',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 5,
			)
		);

		// Add settings section
		add_settings_section(
			'buddy_birthday_main_section',
			__( 'Birthday Field Configuration', 'birthday-block-for-buddypress' ),
			array( $this, 'buddy_birthday_render_section_description' ),
			'buddy-birthday-settings'
		);

		// Add settings fields
		add_settings_field(
			'buddy_birthday_field_id',
			__( 'Birthday Profile Field', 'birthday-block-for-buddypress' ),
			array( $this, 'buddy_birthday_render_field_select' ),
			'buddy-birthday-settings',
			'buddy_birthday_main_section'
		);

		add_settings_field(
			'buddy_birthday_default_range',
			__( 'Default Birthday Range', 'birthday-block-for-buddypress' ),
			array( $this, 'buddy_birthday_render_range_select' ),
			'buddy-birthday-settings',
			'buddy_birthday_main_section'
		);

		add_settings_field(
			'buddy_birthday_default_limit',
			__( 'Default Number to Display', 'birthday-block-for-buddypress' ),
			array( $this, 'buddy_birthday_render_limit_field' ),
			'buddy-birthday-settings',
			'buddy_birthday_main_section'
		);
	}

	/**
	 * Render settings page
	 */
	public function buddy_birthday_render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if settings have been saved
		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_settings_error(
				'buddy_birthday_messages',
				'buddy_birthday_message',
				__( 'Settings Saved', 'birthday-block-for-buddypress' ),
				'updated'
			);
		}

		settings_errors( 'buddy_birthday_messages' );
		?>
		<div class="wrap bp-birthday-admin-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'buddy_birthday_settings' );
				do_settings_sections( 'buddy-birthday-settings' );
				submit_button( __( 'Save Settings', 'birthday-block-for-buddypress' ) );
				?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'How to Use', 'birthday-block-for-buddypress' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Select the xProfile field that contains member birthdays above.', 'birthday-block-for-buddypress' ); ?></li>
				<li><?php esc_html_e( 'Add the "Birthday Block for BuddyPress" block to any page or post.', 'birthday-block-for-buddypress' ); ?></li>
				<li><?php esc_html_e( 'Customize the block settings in the editor sidebar.', 'birthday-block-for-buddypress' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'Block Attributes', 'birthday-block-for-buddypress' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'Title:', 'birthday-block-for-buddypress' ); ?></strong> <?php esc_html_e( 'Widget heading text', 'birthday-block-for-buddypress' ); ?></li>
				<li><strong><?php esc_html_e( 'Display Age:', 'birthday-block-for-buddypress' ); ?></strong> <?php esc_html_e( 'Show member age', 'birthday-block-for-buddypress' ); ?></li>
				<li><strong><?php esc_html_e( 'Send Message:', 'birthday-block-for-buddypress' ); ?></strong> <?php esc_html_e( 'Show "Send Wishes" button', 'birthday-block-for-buddypress' ); ?></li>
				<li><strong><?php esc_html_e( 'Date Format:', 'birthday-block-for-buddypress' ); ?></strong> <?php esc_html_e( 'PHP date format string', 'birthday-block-for-buddypress' ); ?></li>
				<li><strong><?php esc_html_e( 'Range:', 'birthday-block-for-buddypress' ); ?></strong> <?php esc_html_e( 'Today, weekly, monthly, or upcoming', 'birthday-block-for-buddypress' ); ?></li>
				<li><strong><?php esc_html_e( 'Scope:', 'birthday-block-for-buddypress' ); ?></strong> <?php esc_html_e( 'All members or friends only', 'birthday-block-for-buddypress' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render section description
	 */
	public function buddy_birthday_render_section_description() {
		echo '<p>' . esc_html__( 'Select which xProfile field contains member birthdays. Only date-type fields are shown.', 'birthday-block-for-buddypress' ) . '</p>';
	}

	/**
	 * Render field select
	 */
	public function buddy_birthday_render_field_select() {
		$current_field = get_option( 'buddy_birthday_field_id', 0 );
		$date_fields   = $this->buddy_birthday_get_date_fields();

		if ( empty( $date_fields ) ) {
			echo '<p class="description">';
			esc_html_e( 'No date fields found. Please create a date-type xProfile field first.', 'birthday-block-for-buddypress' );
			echo ' <a href="' . esc_url( admin_url( 'users.php?page=bp-profile-setup' ) ) . '">';
			esc_html_e( 'Create Profile Field', 'birthday-block-for-buddypress' );
			echo '</a></p>';
			return;
		}

		?>
		<select name="buddy_birthday_field_id" id="buddy_birthday_field_id">
			<option value="0"><?php esc_html_e( '-- Select a Field --', 'birthday-block-for-buddypress' ); ?></option>
			<?php foreach ( $date_fields as $field ) : ?>
				<option value="<?php echo esc_attr( $field->id ); ?>" <?php selected( $current_field, $field->id ); ?>>
					<?php echo esc_html( $field->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'This field will be used to calculate upcoming birthdays.', 'birthday-block-for-buddypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render range select
	 */
	public function buddy_birthday_render_range_select() {
		$current_range = get_option( 'buddy_birthday_default_range', 'upcoming' );
		?>
		<select name="buddy_birthday_default_range" id="buddy_birthday_default_range">
			<option value="upcoming" <?php selected( $current_range, 'upcoming' ); ?>>
				<?php esc_html_e( 'Upcoming (All Future)', 'birthday-block-for-buddypress' ); ?>
			</option>
			<option value="today" <?php selected( $current_range, 'today' ); ?>>
				<?php esc_html_e( 'Today Only', 'birthday-block-for-buddypress' ); ?>
			</option>
			<option value="weekly" <?php selected( $current_range, 'weekly' ); ?>>
				<?php esc_html_e( 'Next 7 Days', 'birthday-block-for-buddypress' ); ?>
			</option>
			<option value="monthly" <?php selected( $current_range, 'monthly' ); ?>>
				<?php esc_html_e( 'This Month', 'birthday-block-for-buddypress' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Default range for birthday blocks. Can be overridden in each block.', 'birthday-block-for-buddypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render limit field
	 */
	public function buddy_birthday_render_limit_field() {
		$current_limit = get_option( 'buddy_birthday_default_limit', 5 );
		?>
		<input type="number" name="buddy_birthday_default_limit" id="buddy_birthday_default_limit"
			   value="<?php echo esc_attr( $current_limit ); ?>" min="1" max="50" />
		<p class="description">
			<?php esc_html_e( 'Default number of birthdays to display in the block.', 'birthday-block-for-buddypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Get date fields from xProfile
	 *
	 * @return array Date fields.
	 */
	private function buddy_birthday_get_date_fields() {
		if ( ! function_exists( 'bp_xprofile_get_groups' ) ) {
			return array();
		}

		global $wpdb;
		$bp_prefix = bp_core_get_table_prefix();

		$sql = "SELECT id, name, type FROM {$bp_prefix}bp_xprofile_fields
				WHERE type IN ('datebox', 'birthdate')
				ORDER BY name ASC";

		return $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function buddy_birthday_enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_bp-birthday-settings' !== $hook ) {
			return;
		}

		// Enqueue admin CSS if file exists
		$css_file = BUDDY_BIRTHDAY_PATH . 'assets/css/admin.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'bp-birthday-admin',
				BUDDY_BIRTHDAY_URL . 'assets/css/admin.css',
				array(),
				filemtime( $css_file )
			);
		}
	}
}

Buddy_Birthday_Admin::get_instance();
