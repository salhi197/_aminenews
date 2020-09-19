<?php
/**
 * AnWP_Post_Grid :: Main Class
 *
 * @since   0.1.0
 * @package AnWP_Post_Grid
 */


/**
 * Autoload files with classes when needed.
 *
 * @param string $class_name Name of the class being requested.
 *
 * @since  0.1.0
 */
function anwp_post_grid_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'AnWP_Post_Grid_' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'AnWP_Post_Grid_' ) ) ) );

	// Include our file.
	AnWP_Post_Grid::include_file( 'includes/class-anwp-post-grid-' . $filename );
}

spl_autoload_register( 'anwp_post_grid_autoload_classes' );

/**
 * Main initiation class.
 *
 * @property-read AnWP_Post_Grid_Elements $elements
 * @property-read AnWP_Post_Grid_Template $template
 * @property-read string                  $path     Path of plugin directory
 *
 * @since  0.1.0
 */
final class AnWP_Post_Grid {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	const VERSION = '0.6.3';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    AnWP_Post_Grid
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of AnWP_Post_Grid_Template
	 *
	 * @since 0.1.0
	 * @var AnWP_Post_Grid_Template
	 */
	protected $template;

	/**
	 * Instance of AnWP_Post_Grid_Elements
	 *
	 * @since 0.1.0
	 * @var AnWP_Post_Grid_Elements
	 */
	protected $elements;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.1.0
	 * @return  AnWP_Post_Grid A single instance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( self::dir( 'anwp-post-grid.php' ) );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		/**
		 * Bump init actions
		 *
		 * @since 0.1.0
		 */
		add_action( 'init', [ $this, 'init' ], 0 );

		/**
		 * Add theme name to body classes
		 *
		 * @since 0.1.0
		 */
		add_filter( 'body_class', [ $this, 'add_body_classes' ] );

		/**
		 * Renders notice if Elementor not installed.
		 *
		 * @since 0.1.0
		 */
		add_action( 'admin_notices', [ $this, 'notice_elementor_not_installed' ] );
		add_action( 'admin_notices', [ $this, 'show_rate_notice' ] );

		/**
		 * Add Elementor category.
		 *
		 * @since 0.1.0
		 */
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_category' ] );

		/**
		 * Enqueue Styles
		 *
		 * @since 0.1.0
		 */
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 9 );

		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'public_enqueue_scripts' ] );

		/**
		 * Add svg icons to the public side
		 *
		 * @since 0.1.0
		 */
		add_action( 'wp_footer', [ $this, 'include_public_svg_icons' ], 99 );

		add_action( 'wp_ajax_anwp_post_grid_rate_notice', [ $this, 'process_rate_notice' ] );
	}

	/**
	 * Handle rate notice.
	 *
	 * @since 0.6.2
	 */
	public function process_rate_notice() {

		// Check if our nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'anwp_post_grid_rate' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'Error : Unauthorized action' );
		}

		$action = isset( $_POST['process'] ) ? sanitize_key( $_POST['process'] ) : '';

		switch ( $action ) {
			case 'did':
			case 'rate':
				update_option( 'anwp_post_grid_rate_notice', 'hide' );
				break;

			case 'later':
				update_option( 'anwp_post_grid_rate_notice', time() );
				break;
		}

		wp_send_json_success();
	}

	/**
	 * Show Rate Plugin notice.
	 *
	 * @since 0.6.0
	 */
	public function show_rate_notice() {
		$option        = get_option( 'anwp_post_grid_installed_time' );
		$notice_status = get_option( 'anwp_post_grid_rate_notice' );

		if ( ! $option ) {
			update_option( 'anwp_post_grid_installed_time', time() );
		} elseif ( current_user_can( 'install_plugins' ) && $option < strtotime( '-30 days' ) && 'hide' !== $notice_status ) {

			// Check later
			if ( $notice_status && $notice_status > strtotime( '-10 days' ) ) {
				return;
			}
			?>
			<div class="notice notice-info anwp-post-grid__rate">
				<img alt="plugin image" style="float: left; width: 90px; margin-right: 15px; margin-top: 15px;" src="<?php echo esc_url( self::url( 'public/img/anwp-post-grid.png' ) ); ?>">
				<p>
					<img alt="plugin image" style="float: left; width: 55px; margin-right: 10px; margin-top: 5px; border-radius: 20px;" src="<?php echo esc_url( self::url( 'public/img/me.jpg' ) ); ?>">

					Hello! My name is Andrei Strekozov. I am the developer of the "AnWP Post Grid and Post Carousel Slider for Elementor" plugin.<br>
					If you like my plugin and happy with it, please help me spread the word by leaving a 5-star rating on WordPress.org.
					This would help other users, and boost my motivation to further plugin updates and new features.<br>
					﻿﻿Have a nice day and stay safe!
				</p>
				<p style="margin-top: 8px; margin-left: 168px;">
					<a href="https://wordpress.org/support/plugin/anwp-post-grid-for-elementor/reviews/?filter=5" data-action="rate" target="_blank" class="button anwp-post-grid__rate-button" style="margin-right: 5px"><span style="line-height: 1.4; margin-right: 5px;" class="dashicons dashicons-star-filled"></span><?php echo esc_html__( 'Rate it', 'anwp-post-grid' ); ?></a>
					<a href="#" class="anwp-post-grid__rate-button button" data-action="later" style="margin-right: 5px"><span style="line-height: 1.4; margin-right: 5px;" class="dashicons dashicons-clock"></span><?php echo esc_html__( 'Maybe later', 'anwp-post-grid' ); ?></a>
					<a href="#" class="anwp-post-grid__rate-button button" data-action="did" style="margin-right: 5px"><span style="line-height: 1.4; margin-right: 5px;" class="dashicons dashicons-yes"></span><?php echo esc_html__( "I've already rated", 'anwp-post-grid' ); ?></a>
				</p>
				<p style="clear: both; margin-bottom: 5px;"></p>
			</div>

			<script>
				( function( $ ) {
					'use strict';
					$( function() {
						$( '.anwp-post-grid__rate-button' ).on( 'click', function( e ) {
							var $this = $( this );

							$.ajax( {
								url: ajaxurl,
								type: 'POST',
								dataType: 'json',
								data: {
									action: 'anwp_post_grid_rate_notice',
									process: $this.data( 'action' ),
									nonce: '<?php echo esc_attr( wp_create_nonce( 'anwp_post_grid_rate' ) ); ?>',
								}
							} );

							$this.closest( '.anwp-post-grid__rate' ).hide();

							if ( 'rate' !== $this.data( 'action' ) ) {
								e.preventDefault();
							}
						} );
					} );
				}( jQuery ) );
			</script>
			<?php
		}
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 */
	public function init() {

		// Load translated strings for plugin.
		load_plugin_textdomain( 'anwp-post-grid', false, dirname( $this->basename ) . '/languages/' );

		// Include Gamajo_Template_Loader - http://github.com/GaryJones/Gamajo-Template-Loader
		require_once self::dir( 'vendor/class-gamajo-template-loader.php' );

		// Initialize plugin classes.
		$this->plugin_classes();
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 */
	public function plugin_classes() {

		// Others
		$this->template = new AnWP_Post_Grid_Template( $this );
		$this->elements = new AnWP_Post_Grid_Elements( $this );

	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add body classes.
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 0.1.0
	 */
	public function add_body_classes( $classes ) {
		global $is_IE;

		// If it's IE, add a class.
		if ( $is_IE ) {
			$classes[] = 'ie';
		}

		$classes[] = 'theme--' . wp_get_theme()->get_template();

		return $classes;
	}

	/**
	 * Add Elementor categories.
	 *
	 * @param Elementor\Elements_Manager $elements_manager
	 *
	 * @since 0.1.0
	 */
	public function add_elementor_category( $elements_manager ) {

		$elements_manager->add_category(
			'anwp-pg',
			[
				'title' => __( 'AnWP Post Grid', 'anwp-post-grid' ),
				'icon'  => 'fa fa-plug',
			]
		);
	}

	/**
	 * Add SVG definitions to the public footer.
	 *
	 * @since 0.1.0
	 */
	public function include_public_svg_icons() {

		// Define SVG sprite file.
		$svg_icons = self::dir( 'public/img/svg-icons.svg' );

		// If it exists, include it.
		if ( file_exists( $svg_icons ) ) {
			require_once $svg_icons;
		}
	}

	/**
	 * Load public scripts and styles
	 *
	 * @since 0.5.2
	 */
	public function public_enqueue_scripts() {
		/*
		|--------------------------------------------------------------------------
		| Plugin Scripts
		|--------------------------------------------------------------------------
		*/
		wp_enqueue_script( 'anwp-pg-scripts', self::url( 'public/js/plugin.min.js' ), [ 'jquery', 'elementor-frontend' ], self::VERSION, false );

		wp_localize_script(
			'anwp-pg-scripts',
			'anwpPostGridElementorData',
			[
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'public_nonce'   => wp_create_nonce( 'anwp-pg-public-nonce' ),
				'premium_active' => class_exists( 'AnWP_Post_Grid_Premium' ) ? 'yes' : '',
			]
		);
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @since 0.1.0
	 */
	public function enqueue_styles() {

		// Load styles
		if ( is_rtl() ) {
			wp_enqueue_style( 'anwp-pg-styles-rtl', self::url( 'public/css/styles-rtl.css' ), [], self::VERSION );
		} else {
			wp_enqueue_style( 'anwp-pg-styles', self::url( 'public/css/styles.css' ), [], self::VERSION );
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $field Field to get.
	 *
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'template':
			case 'elements':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once $file;
		}

		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );

		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path.
	 *
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );

		return $url . $path;
	}

	/**
	 * Load template partial.
	 * Proxy for template rendering class method.
	 *
	 * @param array|object $atts
	 * @param string       $slug
	 * @param string       $layout
	 *
	 * @since 0.6.1
	 * @return string
	 */
	public function load_partial( $atts, $slug, $layout = '' ) {

		$layout = empty( $layout ) ? '' : ( '-' . sanitize_key( $layout ) );
		return $this->template->set_template_data( $atts )->get_template_part( $slug, $layout );
	}

	/**
	 * Renders notice if Elementor not installed.
	 *
	 * @since 0.1.0
	 */
	public function notice_elementor_not_installed() {

		if ( ! did_action( 'elementor/loaded' ) && current_user_can( 'install_plugins' ) ) {

			// Check Elementor installed
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$all_plugins = get_plugins();

			$elementor_installed = isset( $all_plugins['elementor/elementor.php'] );
			?>
			<div class="notice notice-error">
				<img alt="plugin image" style="float: left; width: 75px; margin-right: 15px; margin-top: 10px;" src="<?php echo esc_url( self::url( 'public/img/anwp-post-grid.png' ) ); ?>">
				<p>
					<?php echo wp_kses_post( __( "<strong>AnWP Post Grid for Elementor</strong> doesn't work without <strong>Elementor Page Builder</strong> plugin.", 'anwp-post-grid' ) ); ?><br>
					<?php echo $elementor_installed ? esc_html__( 'Please activate Elementor to continue.', 'anwp-post-grid' ) : esc_html__( 'Please install Elementor to continue.', 'anwp-post-grid' ); ?>
				</p>

				<?php if ( $elementor_installed && current_user_can( 'activate_plugins' ) ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=activate&plugin=' . rawurlencode( 'elementor/elementor.php' ), 'activate-plugin_elementor/elementor.php' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Activate Elementor', 'anwp-post-grid' ); ?></a>
				<?php elseif ( current_user_can( 'install_plugins' ) ) : ?>
					<a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Install Elementor', 'anwp-post-grid' ); ?></a>
				<?php endif; ?>

				<p style="clear: both; margin-bottom: 5px;"></p>
			</div>
			<?php
		}
	}

	/**
	 * Converts a string to a bool.
	 * From WOO
	 *
	 * @param string $string String to convert.
	 *
	 * @return bool
	 * @since 0.1.0
	 */
	public static function string_to_bool( $string ) {
		return is_bool( $string ) ? $string : ( 1 === $string || 'yes' === $string || 'true' === $string || '1' === $string );
	}

	/**
	 * Function checks if Post Views Counter plugin is active
	 *
	 * @return bool
	 * @since 0.1.0
	 */
	public static function is_pvc_active() {
		static $is_active = null;

		if ( null === $is_active ) {
			$is_active = function_exists( 'pvc_get_post_views' );
		}

		return $is_active;
	}
}
