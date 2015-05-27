<?php

/**
 * Functions used by templates and Widgets.
 */

if ( ! function_exists( 'wds_logo_train' ) ) :

/**
 * Adds a logo train to the page.
 *
 * Template tag example (Output):
 *
 *     if ( function_exists( 'wds_logo_train' ) ):
 *         wds_logo_train( array(
 *             'post_id'         => 1390, // (int) ID of the post of the logo train.
 *             'size'            => 'large', // Image size.
 *             'logos_per_train' => false, // (int) Group logo by X into separate <ul>'s.
 *             'before_logos'    => false, // Output right before logos (used by Widget to add heading).
 *             'animate'         => false, // (int) Set to milliseconds to animate logo train.
 *         ) );
 *     endif;
 *
 * Get Logo Train HTML (for a widget, etc):
 *
 *     if ( function_exists( 'wds_logo_train' ) ):
 *         $logo_train_html = wds_logo_train( array(
 *             'post_id'         => 1390, // (int) ID of the post of the logo train.
 *             'size'            => 'large', // Image size.
 *             'logos_per_train' => false, // (int)  Group logo by X into separate <ul>'s.
 *             'before_logos'    => false, // Output right before logos (used by Widget to add heading).
 *             'animate'         => false, // (int) Set to milliseconds to animate logo train.
 *         ), 'return' ); // set to true to return value.
 *     endif;
 *
 * Shortcode usage:
 *
 *     [wds_logo_train size='large' post_id='']
 *
 * @param  array $args  Arguments for logo train.
 *
 * @return void
 */
function wds_logo_train( $args, $return = false ) {

	// Plugin instance.
	$plugin = wds_logo_trains();

	// Ensure defaults at least.
	$defaults = array(
		'post_id'         => false,
		'no_img'          => false,
		'size'            => 'large',
		'logos_per_train' => ( is_int( $args['logos_per_train'] ) ) ? $args['logos_per_train'] : false,
		'before_logos'    => false,
		'animate'         => false,
	);
	$args = wp_parse_args( $args, $defaults );

	// Logos
	$logos = get_post_meta( $args['post_id'], $plugin->meta_prefix( 'logos' ), true );

	// No logo, no use.
	if ( ! is_array( $logos ) ) {
		return;
	}

	// Animation settings (if animate is set).
	if ( $args['animate'] && (int) $args['animate'] != 0 ) {

		// Use this setting to animate
		$logos_per_train = $args['logos_per_train'];

		// Reset logos per train to one train
		$args['logos_per_train'] = 0;

		// Make a unique ID for this train instance.
		$train_animate_id = 'wds-logo-train-animate-id-' . time();
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

	<?php if ( $args['animate'] && (int) $args['animate'] != 0 ) : ?>

	<!-- Animates the following logo train. -->
	<script>
		(function($) {
			$( document ).ready( function(){
				$( '#<?php echo $train_animate_id; ?>' ).slick( {
					slidesToShow: setSlidesToShow(),
					slidesToScroll: 1,
					autoplay: true,
					autoplaySpeed: <?php echo $args['animate']; ?>,
				} );

				//Set the number of slides to show based on screen width
				function setSlidesToShow() {

					var windowWidth = $(window).width();
					var slidesToShow;

					if ( windowWidth < 1024 && windowWidth >= 451 ) {
						slidesToShow = 5;
					} else if ( windowWidth <= 450 ) {
						slidesToShow = 1;
					} else {
						slidesToShow = <?php echo $logos_per_train; ?>;
					}

					return slidesToShow;
				}
			} );
		})(jQuery);
	</script>

	<?php endif; ?>

	<?php if ( is_array( $trains ) ) : ?>
		<div class="wds-logo-train-wrapper">

		<?php echo $args['before_logos']; ?>

			<?php foreach ( $trains as $train_id => $logos ) : ?>
				<ul id="<?php echo ( isset( $train_animate_id ) ) ? $train_animate_id : ''; ?>" class="wds-logo-train train-<?php echo $train_id; ?>">

					<?php foreach ( $logos as $attachment_id => $src ) :

						$logo_details = $plugin->get_logo_details( $attachment_id, $args['size'] );
						$src = $logo_details['src']; // A better URL
						$alt = $logo_details['alt'];
						$description_as_url = $logo_details['url'];

						?>
							<!-- <a href=... -->
							<a href="<?php echo ( $description_as_url ) ? esc_url( $description_as_url ) : '#'; ?>">

								<li id="logo-<?php echo $logo_count; ?>" class="logo logo-<?php echo sanitize_title_with_dashes( basename( $src ) ); ?>" style="<?php $plugin->logo_background_inline_style( $src ); ?>">

									<?php if ( ! $args['no_img'] ) : ?>
										<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo ( $alt ) ? $alt : __( 'Logo', 'wds-logo-train' ); ?>" />
									<?php endif; ?>

								</li>

							</a>
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

	// Return or echo?
	if ( $return ) {
		return $html;
	} else {
		echo $html;
	}

}
endif;