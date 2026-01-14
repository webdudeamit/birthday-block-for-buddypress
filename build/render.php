<?php
/**
 * Dynamic block render callback for Birthday Block for BuddyPress
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @package buddypress-birthday
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get attributes with defaults.
$title        = isset( $attributes['title'] ) ? $attributes['title'] : __( 'Upcoming Birthdays', 'birthday-block-for-buddypress' );
$display_age  = isset( $attributes['displayAge'] ) ? $attributes['displayAge'] : true;
$send_message = isset( $attributes['sendMessage'] ) ? $attributes['sendMessage'] : true;
$date_format  = isset( $attributes['dateFormat'] ) ? $attributes['dateFormat'] : 'F d';
$range_limit  = isset( $attributes['rangeLimit'] ) ? $attributes['rangeLimit'] : 'upcoming';
$birthdays_of = isset( $attributes['birthdaysOf'] ) ? $attributes['birthdaysOf'] : 'all';
$name_type    = isset( $attributes['nameType'] ) ? $attributes['nameType'] : 'display_name';
$limit        = isset( $attributes['limit'] ) ? intval( $attributes['limit'] ) : 5;
$emoji        = isset( $attributes['emoji'] ) ? $attributes['emoji'] : true;

// Get birthday field from settings.
$field_id = get_option( 'bp_birthday_field_id', 0 );

if ( ! $field_id ) {
	?>
	<div <?php echo get_block_wrapper_attributes( array( 'class' => 'bp-birthday-block-error' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<p>
			<?php
			printf(
				/* translators: %s: Settings link */
				esc_html__( 'Please configure the birthday field in %s.', 'birthday-block-for-buddypress' ),
				'<a href="' . esc_url( admin_url( 'options-general.php?page=bp-birthday-settings' ) ) . '">' .
				esc_html__( 'Settings > BP Birthday', 'birthday-block-for-buddypress' ) .
				'</a>'
			);
			?>
		</p>
	</div>
	<?php
	return;
}

// Get birthdays.
$user_id   = ( 'friends' === $birthdays_of ) ? get_current_user_id() : 0;
$birthdays = bp_birthday_get_upcoming_birthdays( $field_id, $range_limit, $limit, $birthdays_of, $user_id );

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'bp-birthday-block',
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( ! empty( $title ) ) : ?>
		<h2 class="bp-birthday-title"><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>

	<?php if ( ! empty( $birthdays ) ) : ?>
		<ul class="bp-birthday-list item-list">
			<?php foreach ( $birthdays as $birthday ) : ?>
				<li class="bp-birthday-item">
					<div class="item-avatar">
						<a href="<?php echo esc_url( $birthday['profile_url'] ); ?>">
							<img src="<?php echo esc_url( $birthday['avatar'] ); ?>"
								 alt="<?php echo esc_attr( $birthday['name'] ); ?>"
								 class="avatar" />
						</a>
					</div>

					<div class="item">
						<div class="item-title">
							<a href="<?php echo esc_url( $birthday['profile_url'] ); ?>">
								<?php
								// Display name based on type.
								$display_name = $birthday['name'];
								if ( 'username' === $name_type ) {
									$user         = get_userdata( $birthday['user_id'] );
									$display_name = $user ? $user->user_login : $birthday['name'];
								}
								echo esc_html( $display_name );

								// Add birthday emoji.
								if ( $emoji ) {
									echo ' 🎂';
								}
								?>
							</a>
						</div>

						<div class="item-meta">
							<span class="bp-birthday-date">
								<?php echo esc_html( bp_birthday_format_date( $birthday['next_birthday'], $date_format ) ); ?>
							</span>

							<?php if ( 0 === $birthday['days_until'] ) : ?>
								<span class="bp-birthday-today">
									<?php esc_html_e( 'Today!', 'birthday-block-for-buddypress' ); ?>
								</span>
							<?php elseif ( 1 === $birthday['days_until'] ) : ?>
								<span class="bp-birthday-soon">
									<?php esc_html_e( 'Tomorrow', 'birthday-block-for-buddypress' ); ?>
								</span>
							<?php else : ?>
								<span class="bp-birthday-days">
									<?php
									printf(
										/* translators: %d: Number of days */
										esc_html( _n( 'in %d day', 'in %d days', $birthday['days_until'], 'birthday-block-for-buddypress' ) ),
										absint( $birthday['days_until'] )
									);
									?>
								</span>
							<?php endif; ?>

							<?php if ( $display_age && $birthday['age'] > 0 ) : ?>
								<span class="bp-birthday-age">
									<?php
									printf(
										/* translators: %d: Age */
										esc_html__( 'Turning %d', 'birthday-block-for-buddypress' ),
										absint( $birthday['age'] + 1 )
									);
									?>
								</span>
							<?php endif; ?>
						</div>

						<?php if ( $send_message && is_user_logged_in() && bp_is_active( 'messages' ) && ! empty( $birthday['message_url'] ) ) : ?>
							<div class="action">
								<a href="<?php echo esc_url( $birthday['message_url'] ); ?>"
								   class="button bp-birthday-message">
									<?php esc_html_e( 'Send Wishes', 'birthday-block-for-buddypress' ); ?>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="bp-birthday-empty">
			<?php
			switch ( $range_limit ) {
				case 'today':
					esc_html_e( 'No birthdays today', 'birthday-block-for-buddypress' );
					break;
				case 'weekly':
					esc_html_e( 'No birthdays this week', 'birthday-block-for-buddypress' );
					break;
				case 'monthly':
					esc_html_e( 'No birthdays this month', 'birthday-block-for-buddypress' );
					break;
				default:
					esc_html_e( 'No upcoming birthdays', 'birthday-block-for-buddypress' );
			}
			?>
		</p>
	<?php endif; ?>
</div>
