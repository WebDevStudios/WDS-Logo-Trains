<?php

if ( ! function_exists( 'wds_logo_train') ) :
function wds_logo_train( $args ) {

	// Plugin instance.
	$instance = wds_logo_trains();

	// Ensure defaults at least.
	$defaults = array(
		'post_id'         => false,
		'before'          => '<div class = "wds-logo-train">',
		'after'           => '</div>',
		'no_img'          => false,
		'size'            => 'thumbnail',
		'logos_per_train' => 5,
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

	// Count logos
	$logo_count = 1;
	$train_count = 1;

	ob_start();
	?>

	<?php if ( is_array( $logos ) ) : ?>
		<div class="wds-logo-train-wrapper">
			<ul class="wds-logo-train">
				<?php foreach ( $logos as $attachment_id => $src ):

				// Get the desired attachment src.
				$src = wp_get_attachment_image_src( $attachment_id, $args['size'] );
				$src = ( isset( $src[0] ) ) ? $src[0] : $src;

				// Meta
				$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

				// Which logo and train.
				if ( $args['logos_per_train'] + 1 == $logo_count ) {
					$train_count ++;
					$logo_count = 1;
				}

				?>
					<li class="logo logo-<?php echo sanitize_title_with_dashes( basename( $src ) ); ?> logo-<?php echo $logo_count; ?> train-<?php echo $train_count; ?>" style="<?php $instance->logo_background_inline_style( $src ); ?>">
						<?php if ( ! $args['no_img'] ) : ?>
							<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo ( $alt ) ? $alt : __( 'Logo', 'wds-logo-train' ); ?>" />
						<?php endif; ?>
					</li>
				<?php $logo_count++; // Next logo. ?>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php
	$html = ob_get_contents();
	ob_end_clean();
	echo $html;

}
endif;