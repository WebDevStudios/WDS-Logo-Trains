<?php

/**
 * Functions used by templates and Widgets.
 */

if ( ! function_exists( 'wds_logo_train') ) :
function wds_logo_train( $args ) {

	// Plugin instance.
	$instance = wds_logo_trains();

	// Ensure defaults at least.
	$defaults = array(
		'post_id'         => false,
		'no_img'          => false,
		'size'            => 'thumbnail',
		'logos_per_train' => ( is_int( $args['logos_per_train'] ) ) ? $args['logos_per_train'] : false,
	);
	$args = wp_parse_args( $args, $defaults );

	// Logos
	$logos = get_post_meta( $args['post_id'], $instance->meta_prefix( 'logos' ), true );

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

					// Get the desired attachment src for the size we want.
					$src = wp_get_attachment_image_src( $attachment_id, $args['size'] );
					$src = ( isset( $src[0] ) ) ? $src[0] : $src;

					// Meta alt tag.
					$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

					// We want to get the description (have to hack post_content for that).
					$attachment = get_post( $attachment_id );
					$description_as_url = $attachment->post_content;

					?>
						<!-- <a href=... -->
						<?php if ( $description_as_url ) : ?><a href="<?php echo esc_url( $description_as_url ); ?>"><?php endif; ?>

							<li id="logo-<?php echo $logo_count; ?>" class="logo logo-<?php echo sanitize_title_with_dashes( basename( $src ) ); ?>" style="<?php $instance->logo_background_inline_style( $src ); ?>">

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