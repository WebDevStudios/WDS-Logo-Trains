<?php

if ( ! function_exists( 'wds_logo_train') ) :
function wds_logo_train( $args ) {

	// Plugin instance.
	$instance = wds_logo_trains();

	// Ensure defaults at least.
	$defaults = array(
		'post_id'       => false,
		'before'        => '<div class = "wds-logo-train">',
		'after'         => '</div>',
	);
	$args = wp_parse_args( $args, $defaults );

	// Make sure we slap the ID on there.
	if ( is_int( $args['post_id'] ) ) {
		$args['before'] = '<div class="wds-logo-train wds-logo-train-' . $args['id'];
	} else {

		// Exit out of here if there is no ID set.
		return;
	}

	// Logos
	$logos = get_post_meta( $args['post_id'], $instance->meta_prefix( 'logos' ), true );

	ob_start();
	?>

	<?php if ( is_array( $logos ) ) : ?>
		<ul class="wds-logo-train">
			<?php foreach ( $logos as $logo ): ?>
				<li class="logo" style="<?php $instance->logo_background_inline_style( $logo ); ?>">
					<img src="<?php echo esc_url( $logo ); ?>" alt="<?php _e( 'Logo', 'wds-logo-train' ); ?>" />
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php
	$html = ob_get_contents();
	ob_end_clean();
	echo $html;

}
endif;