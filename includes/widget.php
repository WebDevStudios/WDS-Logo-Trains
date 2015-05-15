<?php

// Exit if accessed directly
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

class WDS_Logo_Train extends WP_Widget {

	/**
	 * Unique identifier for this widget.
	 *
	 * Will also serve as the widget class.
	 *
	 * @var string
	 */
	protected $widget_slug = 'wds-logo-train';

	/**
	 * Widget name displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var string
	 */
	protected $widget_name = '';

	/**
	 * Default widget title displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var string
	 */
	protected $default_widget_title = '';

	/**
	 * Shortcode name for this widget
	 *
	 * @var string
	 */
	protected static $shortcode = 'wds_logo_train';

	/**
	 * The instance of the plugin.
	 *
	 * @var object
	 */
	protected $plugin;

	/**
	 * Contruct widget.
	 */
	public function __construct() {

		$this->widget_name          = esc_html__( 'Logo Train', 'wds-logo-train' );
		$this->default_widget_title = esc_html__( 'Logo Train', 'wds-logo-train' );

		parent::__construct(
			$this->widget_slug,
			$this->widget_name,
			array(
				'classname'   => $this->widget_slug,
				'description' => esc_html__( 'Adds a train of logos.', 'wds-logo-train' ),
			)
		);

		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		add_shortcode( self::$shortcode, array( __CLASS__, 'get_widget' ) );

		// Plugin instance.
		$this->plugin = wds_logo_trains();
	}

	/**
	 * Delete this widget's cache.
	 *
	 * Note: Could also delete any transients
	 * delete_transient( 'some-transient-generated-by-this-widget' );
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->widget_slug, 'widget' );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param  array  $args      The widget arguments set up when a sidebar is registered.
	 * @param  array  $instance  The widget settings as set by user.
	 */
	public function widget( $args, $instance ) {

		echo self::get_widget( array(
			'before_widget'   => $args['before_widget'],
			'after_widget'    => $args['after_widget'],

			// Our Widget settings.
			'size'            => $instance['size'],
			'post_id'         => $instance['post_id'],
			'logos_per_train' => $instance['logos_per_train'],
			'train_title'     => $instance['train_title'],
		) );

	}

	/**
	 * Return the widget/shortcode output
	 *
	 * @param  array  $atts Array of widget/shortcode attributes/args
	 * @return string       Widget output
	 */
	public static function get_widget( $atts ) {
		$widget = '';

		// Set up default values for attributes
		// TODO: Defaults
		$atts = shortcode_atts(
			array(
				// Ensure variables
				'size'            => 'large',
				'post_id'         => false,
				'logos_per_train' => 'infinite',
				'train_title'     => '',
			),
			(array) $atts,
			self::$shortcode
		);

		// No post id, no train.
		if ( ! $atts['post_id'] ) {
			return;
		}

		// Before widget hook
		$widget .= ( isset( $atts['before_widget'] ) ) ? $atts['before_widget'] : '';

		if ( function_exists( 'wds_logo_train' ) ):
			$widget .= wds_logo_train( array(
				'post_id'         => $atts['post_id'],
				'size'            => $atts['size'],
				'logos_per_train' => $atts['logos_per_train'],
				'train_title'     => $atts['train_title'],
				'before_logos'     => ( isset( $atts['train_title'] ) ) ? self::maybe_add_heading( $atts['train_title'] ) : ''
			), 'return' );
		endif;

		// After widget hook
		$widget .= ( isset( $atts['after_widget'] ) ) ? $atts['after_widget'] : '';

		return $widget;
	}

	/**
	 * Checks if HTML is present, if not add a heading.
	 * @param  string $maybe_html Saved DB value.
	 * @return string             String with heading added if no HTML detected.
	 */
	protected function maybe_add_heading( $maybe_html ) {
		if ( esc_html( $maybe_html ) == $maybe_html ) {

			// If we have no HTML assume they need a heading.
			return "<h2 class='train-title'>$maybe_html</h2>";
		} else {

			// If there is HTML, just use what they have.
			return $maybe_html;
		}
	}

	/**
	 * Update form values as they are saved.
	 *
	 * @param  array  $new_instance  New settings for this instance as input by the user.
	 * @param  array  $old_instance  Old settings for this instance.
	 * @return array  Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {

		// Previously saved values
		$instance = $old_instance;

		// Save the chosen Logo Train.
		$instance['post_id'] = (int) $new_instance['post_id'];
		$instance['size'] = (string) $new_instance['size'];
		$instance['logos_per_train'] = absint( $new_instance['logos_per_train'] );
		$instance['train_title'] = wp_kses( $new_instance['train_title'], wp_kses_allowed_html( array(
			'h1'   => array(),
			'h2'   => array(),
			'h3'   => array(),
			'h4'   => array(),
			'h5'   => array(),
			'h6'   => array(),
			'div'  => array(),
			'span' => array(),
		) ) );

		// Flush cache
		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Back-end widget form with defaults.
	 *
	 * @param  array  $instance  Current settings.
	 */
	public function form( $instance ) {

		$logo_trains = get_posts( array(
			'posts_per_page'   => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => $this->plugin->post_type(),
			'post_status'      => 'publish',
			'suppress_filters' => true,
			'fields'           => 'ids',
		) );

		$sizes = array(
			'large' => __( 'Large', 'wds-logo-train' ), // At top because it's default.
			'medium' => __( 'Medium', 'wds-logo-train' ),
			'thumbnail' => __( 'Thumbnail', 'wds-logo-train' ),
		);

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'train_title' ) ); ?>">
				<?php _e( 'Heading:', 'wds-logo-train' ); ?>
			</label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'train_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'train_title' ) ); ?>" class="widefat" value="<?php echo (string) $instance['train_title']; ?>">
		</p>
		<p class="description"><?php _e( 'Heading right above logos. Heading HTML allowed. E.g. <code>&lt;h1&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;div&gt;</code>, etc.', 'wds-logo-train' ); ?></p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_id' ) ); ?>">
				<?php _e( 'Choose Logo Train:', 'wds-logo-train' ); ?>
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'post_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_id' ) ); ?>" style="max-width: 100%; min-width: 100%;">
				<option><?php _e( '&mdash; None &mdash;', 'wds-logo-train' ); ?></option>

				<!-- Logo Train ID -->
				<?php if ( is_array( $logo_trains ) ) : ?>
					<?php foreach( $logo_trains as $post_id ) :

					// Get the logos.
					$logos = get_post_meta( $post_id, $this->plugin->meta_prefix( 'logos' ), true );

					?>
						<option value="<?php echo $post_id; ?>" <?php echo ( $post_id == $instance['post_id'] ) ? 'selected="selected"' : ''; ?>><?php echo get_the_title( $post_id ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>">
				<?php _e( 'Image Size:', 'wds-logo-train' ); ?>
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>" style="max-width: 100%; min-width: 100%;">
				<?php foreach ( $sizes as $size => $label ) : ?>
					<option value="<?php echo $size; ?>" <?php echo ( $size == $instance['size'] ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logos_per_train' ) ); ?>">
				<?php _e( 'Split Logos Into Sections of:', 'wds-logo-train' ); ?>
			</label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'logos_per_train' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logos_per_train' ) ); ?>" class="widefat" value="<?php echo (int) $instance['logos_per_train']; ?>">
		</p>
		<p class="description"><?php _e( 'This will create a separate <code>ul</code> for each <code>&times;</code> number of logos.', 'wds-logo-train' ); ?></p>

		<?php
	}
}