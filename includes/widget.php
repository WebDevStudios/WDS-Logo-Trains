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
			'before_widget' => $args['before_widget'],
			'after_widget'  => $args['after_widget'],
			'before_title'  => $args['before_title'],
			'after_title'   => $args['after_title'],
			'title'         => $instance['title'],
			'text'          => $instance['text'],
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
		$atts = shortcode_atts(
			array(
				// Ensure variables
				'before_widget' => '',
				'after_widget'  => '',
				'before_title'  => '',
				'after_title'   => '',
				'title'         => '',
				'text'          => '',
			),
			(array) $atts,
			self::$shortcode
		);

		// Before widget hook
		$widget .= $atts['before_widget'];

		// Title
		$widget .= ( $atts['title'] ) ? $atts['before_title'] . esc_html( $atts['title'] ) . $atts['after_title'] : '';

		$widget .= wpautop( wp_kses_post( $atts['text'] ) );

		// After widget hook
		$widget .= $atts['after_widget'];

		return $widget;
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
		$instance['logo_train_id'] = (int) $new_instance['logo_train_id'];

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

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'logo_train_id' ) ); ?>">
				<?php _e( 'Choose Logo Train:', 'wds-logo-train' ); ?>
			</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'logo_train_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logo_train_id' ) ); ?>" style="max-width: 100%; min-width: 100%;">
				<option><?php _e( '&mdash; None &mdash;', 'wds-logo-train' ); ?></option>

				<?php if ( is_array( $logo_trains ) ) : ?>
					<?php foreach( $logo_trains as $post_id ) :

					// Get the logos.
					$logos = get_post_meta( $post_id, $this->plugin->meta_prefix( 'logos' ), true );

					?>
						<option value="<?php echo $post_id; ?>" <?php echo ( $post_id == $instance['logo_train_id'] ) ? 'selected="selected"' : ''; ?>><?php echo get_the_title( $post_id ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>

		<?php
	}
}