<?php

/**
 * Functions used by templates and Widgets.
 */

if ( ! function_exists( 'wds_logo_train') ) :

/**
 * Adds a logo train to the page.
 *
 * @param  array $args  Arguments for logo train.
 *
 * @return void
 */
function wds_logo_train( $args ) {

	// Plugin instance.
	$plugin = wds_logo_trains();

	// Ensure defaults at least.
	$defaults = array(
		'post_id'         => false,
		'no_img'          => false,
		'size'            => 'thumbnail',
		'logos_per_train' => ( is_int( $args['logos_per_train'] ) ) ? $args['logos_per_train'] : false,
	);
	$args = wp_parse_args( $args, $defaults );

	// Logos
	$logos = get_post_meta( $args['post_id'], $plugin->meta_prefix( 'logos' ), true );

	// No logo, no use.
	if ( ! is_array( $logos ) ) {
		return;
	}

	// Sort logos by logos_per_train.
	if ( is_array( $logos ) ) {
		$train_count = 0;
		$logo_count = 0;
		foreach ( $logos as $attachment_id => $src ) {

			if ( $args['logos_per_train'] == $logo_count ) {
				$train_count++;
				$logo_count = 0;
			}

			$trains[$train_count][$attachment_id] = $src;
			$logo_count++;
		}
	}

	// Count our logos to give them an ID.
	$logo_count = 0;

	// Start output.
	ob_start();
	?>

	<?php if ( is_array( $trains ) ) : ?>
		<div class="wds-logo-train-wrapper">

		<?php foreach ( $trains as $train_id => $train ) : ?>
			<ul class="wds-logo-train train-<?php echo $train_id; ?>">

				<?php foreach ( $train as $attachment_id => $src ) :

					$logo_details = $plugin->get_logo_train_details( $attachment_id );
					$src = $logo_details['src'];
					$alt = $logo_details['alt'];
					$description_as_url = $logo_details['url'];

					?>
						<!-- <a href=... -->
						<?php if ( $description_as_url ) : ?><a href="<?php echo esc_url( $description_as_url ); ?>"><?php endif; ?>

							<li id="logo-<?php echo $logo_count; ?>" class="logo logo-<?php echo sanitize_title_with_dashes( basename( $src ) ); ?>" style="<?php $plugin->logo_background_inline_style( $src ); ?>">

								<?php if ( ! $args['no_img'] ) : ?>
									<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo ( $alt ) ? $alt : __( 'Logo', 'wds-logo-train' ); ?>" />
								<?php endif; ?>

							</li>

						<?php if ( $description_as_url ) : ?></a><?php endif; ?>
						<!-- /a -->

					<?php $logo_count++; // Next logo ID. ?>

				<?php endforeach; ?>

			</ul>
		<?php endforeach; ?>

		</div>
	<?php endif; ?>

	<?php

	// Echo output.
	$html = ob_get_contents();
	ob_end_clean();
	echo $html;

}
endif;