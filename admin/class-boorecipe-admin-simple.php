<?php
// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Boorecipe
 * @subpackage Boorecipe/admin
 * @author     Rao Abid <raoabid491@gmail.com>
 */
class Boorecipe_Admin_Simple {

	protected $settings_api;
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->prefix = Boorecipe_Globals::get_meta_prefix();

	}

	/**
	 *
	 */
	public function admin_delete_settings_handler() {

		// Check Admin referrer
//		check_admin_referer( 'delete_existing_settings_using_ajax' );

		// Verify Nonce
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete_existing_settings_using_ajax' ) ) {
			wp_send_json_error( __( 'Security token is invalid' . $_REQUEST['_wpnonce'], 'boo-recipes' ) );
			die();
		}
		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Sorry, You do not have sufficient permissions to do this action.', 'boo-recipes' ) );
			die();
		}

		// Get old options
		$old_settings = get_option( 'boorecipe-options' );

		// if no old settings found, send error
		if ( ! $old_settings ) {
			wp_send_json_error( __( 'Sorry, We could not find any old settings', 'boo-recipes' ) );
			die();
		}

		$result = delete_option( 'boorecipe-options' );

		if ( $result ) {
			$response = array(
				'success' => true,
				'data'    => __( 'Old Settings have been successfully deleted.', 'boo-recipes' ) . " " .
				             sprintf( __( 'Page shall reload automatically after %s seconds', 'boo-recipes' ), 10 ),
			);
		} else {
			$response = array(
				'success' => false,
				'data'    => __( 'Sorry, There was an error while deleting old settings. Please try again later', 'boo-recipes' ),
			);

		}


		wp_send_json( json_encode( $response ) );
		die();

	}

	/**
	 *
	 */
	public function admin_convert_settings_handler() {

		// Check Admin referrer
		check_admin_referer( 'convert_existing_settings_using_ajax' );

		// Verify Nonce
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'convert_existing_settings_using_ajax' ) ) {
			wp_send_json_error( __( 'Security token is invalid' . $_REQUEST['_wpnonce'], 'boo-recipes' ) );
			die();
		}

		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Sorry, You dont have sufficient permissions to do this action.', 'boo-recipes' ) );
			die();
		}


		// Get old options
		$old_settings = get_option( 'boorecipe-options' );

		// if no old settings found, send error
		if ( ! $old_settings ) {
			wp_send_json_error( __( 'Sorry, We could not find any old settings', 'boo-recipes' ) );
			die();
		}


		$site_lang = mb_substr( get_locale(), 0, 2 );

		if ( ! isset( $old_settings[ $site_lang ] ) ) {
			wp_send_json_error( __( 'Sorry, Settings related to site language is not found. Old settings worked on the basis of site language. We cant do much in this regard. Please contact plugin support to resolve this site-specific issue.', 'boo-recipes' ) );
			die();
		}

		if ( ! is_array( $old_settings[ $site_lang ] ) ) {
			wp_send_json_error( __( 'Sorry, Settings related to site language is not found. Old settings worked on the basis of site language. We cant do much in this regard. Please contact plugin support to resolve this site-specific issue.', 'boo-recipes' ) );
			die();
		}

		$lang_specific_settings = $old_settings[ $site_lang ];
//		$updated_options        = array();
		$count = 0;
		foreach ( $lang_specific_settings as $option_id => $option_value ) {
			update_option( 'boorecipe_' . $option_id, $option_value );
//			$updated_options[ 'boorecipe_' . $option_id ] =  $option_value ;
			$count ++;
		}

		/**
		 * Special cases
		 */
		// default image
		$default_image_path = isset( $lang_specific_settings['recipe_default_img_url'] ) ? $lang_specific_settings['recipe_default_img_url'] : false;
		if ( $default_image_path ) {
			update_option( 'boorecipe_recipe_default_img_url', attachment_url_to_postid( $default_image_path ) );
		}

		// Select Filters to include in Search Form
		$search_filters = isset( $lang_specific_settings['search_form_filters'] ) ? $lang_specific_settings['search_form_filters'] : array();
		if ( is_array( $search_filters ) ) {
			$search_filters_combined = array_combine( $search_filters, $search_filters );
			update_option( 'boorecipe_search_form_filters', $search_filters_combined );
		}

		// Update uninstall settings, change default
		update_option( 'boorecipe_uninstall_delete_options', 'no' );


		/**
		 * END special cases
		 */

		// update for default
		$response = array(
			'success' => true,
			'data'    =>
				sprintf( __( 'Settings have been successfully converted. Total changes made to database are %s.', 'boo-recipes' ), $count ) . " " .
				sprintf( __( 'Page shall reload automatically after %s seconds', 'boo-recipes' ), 10 ),
//			'options' => $updated_options
		);

		// Options Processing Done here
		wp_send_json( json_encode( $response ) );
		wp_die();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Boorecipe_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Boorecipe_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/boorecipe-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Boorecipe_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Boorecipe_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/boorecipe-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_add_inline_script( $this->plugin_name, $this->add_custom_js_in_admin() );


		wp_localize_script( $this->plugin_name, 'wp_ajax', array(
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			/**
			 * Create nonce for security.
			 *
			 * @link https://codex.wordpress.org/Function_Reference/wp_create_nonce
			 */
			'_nonce_settings_convert' => wp_create_nonce( 'convert_existing_settings_using_ajax' ),
			'_nonce_settings_delete'  => wp_create_nonce( 'delete_existing_settings_using_ajax' ),
		) );

	}

	/**
	 * @hooked admin_footer
	 */
	public function add_custom_js_in_admin() {

		$custom_js = Boorecipe_Globals::get_options_value( 'admin_custom_js_editor' );

		if ( ! $custom_js ) {
			return null;
		}

		return $custom_js;


	}

	/**
	 * Shall use this to add data update functionality
	 */
	public function data_update_menu() {


	}


	/**
	 *
	 */
	public function admin_menu_simple() {

		$config_array = array(
			'options_id' => $this->plugin_name . '-options-new',
			'tabs'       => true,
			'menu'       => $this->get_settings_menu(),
			'links'      => $this->get_settings_links(),
			'sections'   => $this->get_settings_sections(),
			'fields'     => $this->get_settings_fields()
		);


		$this->settings_api = new Boo_Settings_Helper( $config_array );

		//set menu settings
//			$this->settings_api->set_menu( $this->get_settings_menu() );

		//set the plugin action links
		$this->settings_api->set_links( $this->get_settings_links() );

		//set the settings
//			$this->settings_api->set_sections( $this->get_settings_sections_new() );

		// set fields
//			$this->settings_api->set_fields( $this->get_settings_fields_new() );

		//initialize settings
		$this->settings_api->admin_init();

//			add_options_page( 'WeDevs Settings API', 'WeDevs Settings API', 'delete_posts', 'settings_api_test', array($this, 'plugin_page') );
	}

	function get_settings_menu() {
		$config_menu = array(
			//The name of this page
			'page_title'      => __( 'Settings', 'boo-recipes' ),
			// //The Menu Title in Wp Admin
			'menu_title'      => __( 'Settings', 'boo-recipes' ),
			// The capability needed to view the page
			'capability'      => 'manage_options',
			// Slug for the Menu page
			'slug'            => 'boorecipe-settings',
			// dashicons id or url to icon
			// https://developer.wordpress.org/resource/dashicons/
			'icon'            => 'dashicons-performance',
			// Required for submenu
			'submenu'         => true,
			// position
//			'position'   => 10,
			// For sub menu, we can define parent menu slug (Defaults to Options Page)
			'parent'          => 'edit.php?post_type=boo_recipe',
			// plugin_basename required to add plugin action links
			'plugin_basename' => plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' ),
		);

		return $config_menu;
	}

	function get_settings_links() {
		$links = array(
//				'plugin_basename' => plugin_basename( __FILE__ ),
			'plugin_basename' => plugin_basename( plugin_dir_path( __FILE__ ) . $this->plugin_name . '.php' ),

			// Settings Link in Plugin Action Links


			// Default Settings Links from Menu array
//				'action_links' => true,


			// Admin URL after trailing slash of https://example.com/wp-admin/{url}
//				'action_links' => '?page=boo-helper-slug',

			// array of Settings
			'action_links'    => array(
				array(
					'text' => __( 'Configure', 'boo-helper' ),
					'type' => 'default',
				),
//					array(
//						'text' => __( 'G Forms', 'boo-helper' ),
//						'url'  => 'admin.php?page=gf_edit_forms',
//						'type' => 'internal',
//					),
				array(
					'text' => __( 'Advance Settings', 'boo-helper' ),
					'url'  => 'admin.php?page=boo-helper-slug&tab=wedevs_advanced',
					'type' => 'internal',
				),

				array(
					'text' => __( 'Premium Plugin', 'boo-helper' ),
					'url'  => 'https://boorecipes.com/',
					'type' => 'external',
				),
			),


		);

//			var_dump( $links); die();

		return $links;
	}

	function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'recipe_single',
				'title' => __( 'Recipe Single', 'boo-recipes' ),
//				'desc'  => 'this is sweet'
			),
			array(
				'id'    => 'recipe_archive',
				'title' => __( 'Recipe Archive', 'boo-recipes' ),
			),
			array(
				'id'    => 'recipe_search_form',
				'title' => __( 'Search Form', 'boo-recipes' ),
			),
			array(
				'id'    => 'recipe_widgets',
				'title' => __( 'Widget Settings', 'boo-recipes' ),
			),
//			array(
//				'id'    => 'recipe_options_backup_restore',
//				'title' => __( 'Settings Backup', 'boo-recipes' ),
//			),
			'recipe_plugin_activation' => array(
				'id'    => 'recipe_plugin_activation',
				'title' => __( 'Premium Plugin', 'boo-recipes' ),
			),
			array(
				'id'    => 'special_section',
				'title' => __( 'Special', 'boo-recipes' ),
			),
			array(
				'id'    => 'uninstall_section',
				'title' => __( 'Uninstall', 'boo-recipes' ),
			)
		);

		return apply_filters( 'boorecipe_filter_options_sections_array', $sections );
	}

	function get_settings_fields() {
		$options_fields = array();
		/*
		* Recipe Individual
		*/
		$options_fields['recipe_single'] = apply_filters( 'boorecipe_filter_options_fields_array_single', array(

			array(
				'id'          => $this->prefix . 'color_accent',
				'type'        => 'color',
				'label'       => __( 'Accent Color', 'boo-recipes' ),
				'description' => __( 'This will the theme color for the recipe', 'boo-recipes' ),
				'default'     => '#71A866',
			),

			array(
				'id'          => $this->prefix . 'color_secondary',
				'type'        => 'color',
				'label'       => __( 'Secondary Color', 'boo-recipes' ),
				'description' => __( 'This will be the color for secondary elements (usually in contrast of accent)', 'boo-recipes' ),
				'default'     => '#e8f1e6',
				'rgba'        => true,
			),

			array(
				'id'                => $this->prefix . 'color_icon',
				'type'              => 'color',
				'label'             => __( 'Icon Color', 'boo-recipes' ),
				'label_description' => __( 'This will be the color for icons', 'boo-recipes' ),
				'default'           => '#71A866',
				'rgba'              => true,
			),

			array(
				'id'                => $this->prefix . 'color_border',
				'type'              => 'color',
				'label'             => __( 'Border Color', 'boo-recipes' ),
				'label_description' => __( 'This will be the color for borders in elements', 'boo-recipes' ),
				'default'           => '#e5e5e5',
				'rgba'              => true,
			),

			array(
				'id'      => $this->prefix . 'recipe_style',
				'type'    => 'select',
				'label'   => __( 'Recipe Style', 'boo-recipes' ),
				'options' => apply_filters( 'boorecipe_filter_options_fields_array_single_style', array(
					'style1' => sprintf( __( 'Style %s', 'boo-recipes' ), 1 )
				) ),
				'radio'   => true,
				'default' => 'style1',
				'desc'    => __( 'More Styles in Premium Version', 'boo-recipes' ),
			),

			array(
				'id'      => $this->prefix . 'enable_wysiwyg_editor',
				'type'    => 'select',
				'label'   => __( 'Enable WYSIWYG Editor?', 'boo-recipes' ),
				'options' => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
				'radio'   => true,
				'default' => 'no',
				'desc'    => __( 'This will only be available for Short Description and Additional Notes', 'boo-recipes' ),
			),

			array(
				'id'                => $this->prefix . 'show_nutrition',
				'type'              => 'select',
				'label'             => __( 'Show Nutrition? (Global)', 'boo-recipes' ),
				'label_description' => __( 'Do you want to show Nutrition info in individual Recipe?', 'boo-recipes' ),
				'default'           => 'yes',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_icons',
				'type'              => 'select',
				'label'             => __( 'Show Icons?', 'boo-recipes' ),
				'label_description' => __( 'Do you want to show icons in individual Recipe?', 'boo-recipes' ),
				'default'           => 'yes',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_key_point_label',
				'type'              => 'select',
				'label'             => __( 'Show Labels for Key Points?', 'boo-recipes' ),
				'label_description' => __( 'Do you want to show labels for key points in individual Recipe?', 'boo-recipes' ),
				'default'           => 'yes',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'      => $this->prefix . 'ingredients_editor',
				'type'    => 'select',
				'label'   => __( 'Ingredients Editor', 'boo-recipes' ),
				'desc'    => __( 'More Styles in Premium Version', 'boo-recipes' ),
				'default' => 'textarea',
				'options' => apply_filters( 'boorecipe_filter_options_field_ingredients_editor', array(
					'textarea' => __( 'Simple Textarea', 'boo-recipes' )
				) )

			),

			array(
				'id'                => $this->prefix . 'ingredient_side',
				'type'              => 'select',
				'label'             => __( 'Ingredients by the Side', 'boo-recipes' ),
				'label_description' => __( 'Do you Want to show ingredients by the side?', 'boo-recipes' ),
				'default'           => 'no',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'nutrition_side',
				'type'              => 'select',
				'label'             => __( 'Nutrition by the Side', 'boo-recipes' ),
				'label_description' => __( 'Do you Want to show nutrition by the side?', 'boo-recipes' ),
				'default'           => 'yes',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'hide_empty_nutrition',
				'type'              => 'select',
				'label'             => __( 'Hide Empty Nutrition Info', 'boo-recipes' ),
				'label_description' => __( 'Do you want to hide nutrition info if value not provided?', 'boo-recipes' ),
				'default'           => 'no',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_featured_image',
				'type'              => 'select',
				'label'             => __( 'Show Featured Image?', 'boo-recipes' ),
				'label_description' => __( 'Some Themes add this to header, you may want to hide the one added by this plugin to avoid duplicated contents', 'boo-recipes' ),
				'default'           => $this->get_default_options( 'show_featured_image' ),
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_recipe_title',
				'type'              => 'select',
				'label'             => __( 'Show Recipe Title?', 'boo-recipes' ),
				'label_description' => __( 'Some Themes add this to header, you may want to hide the one added by this plugin to avoid duplicated contents', 'boo-recipes' ),
				'default'           => $this->get_default_options( 'show_recipe_title' ),
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_recipe_publish_info',
				'type'              => 'select',
				'label'             => __( 'Show Recipe Publish info?', 'boo-recipes' ),
				'label_description' => __( 'Some Themes add this to header, you may want to hide the one added by this plugin to avoid duplicated contents', 'boo-recipes' ),
				'default'           => $this->get_default_options( 'show_recipe_publish_info' ),
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_share_buttons',
				'type'              => 'select',
				'label'             => __( 'Show Share Buttons?', 'boo-recipes' ),
				'label_description' => __( 'Do you Want to show share buttons on recipe page?', 'boo-recipes' ),
				'default'           => $this->get_default_options( 'show_share_buttons' ),
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_author',
				'type'              => 'select',
				'label'             => __( 'Show Author', 'boo-recipes' ),
				'label_description' => __( 'Do you Want to show author name on recipe page?', 'boo-recipes' ),
				'default'           => 'yes',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_published_date',
				'type'              => 'select',
				'label'             => __( 'Show Published Date', 'boo-recipes' ),
				'label_description' => __( 'Do you want to show published date on recipe page?', 'boo-recipes' ),
				'default'           => 'no',
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'          => $this->prefix . 'featured_image_height',
				'type'        => 'text',
				'label'       => __( 'Featured image height', 'boo-recipes' ),
//					'after'       => __("You will need to re-generate thumbnails after changing this value for existing recipes", "boorecipe"),
				'description' => __( 'Maximum height of the recipe image', 'boo-recipes' ),
				'default'     => '576',
				'sanitize'    => 'boorecipe_sanitize_absint',

			),

			array(
				'id'          => $this->prefix . 'recipe_default_img_url',
				'type'        => 'media',
				'label'       => __( 'Recipe default image', 'boo-recipes' ),
				'description' => __( 'Paste the full url to the image you want to use', 'boo-recipes' ),
				'width'       => 768,
				'height'      => 768,
				'max_width'   => 768
			),

			array(
				'id'          => $this->prefix . 'layout_max_width',
				'type'        => 'number',
				'label'       => __( 'Layout Max Width', 'boo-recipes' ),
//					'after'       => __("You will need to re-generate thumbnails after changing this value for existing recipes", "boorecipe"),
				'description' => __( 'in pixels', 'boo-recipes' ),
				'default'     => '1048',
				'sanitize'    => 'boorecipe_sanitize_absint',

			),

			array(
				'id'      => $this->prefix . 'recipe_layout',
				'type'    => 'select',
				'label'   => __( 'Recipe Layout', 'boo-recipes' ),
				'options' => array(
					'full'  => __( 'Full', 'boo-recipes' ),
					'left'  => __( 'Left', 'boo-recipes' ),
					'right' => __( 'Right', 'boo-recipes' ),
				),
				'radio'   => true,
				'default' => 'full',
			),

			array(
				'id'          => $this->prefix . 'recipe_slug',
				'type'        => 'text',
				'label'       => __( 'Recipe Slug', 'boo-recipes' ),
				'desc'        => sprintf( __( "You will need to re-save %spermalinks%s after changing this value", "boorecipe" ), '<a href=' . get_admin_url() . "options-permalink.php" . ' target="_blank">', '</a>' ),
				'class'       => 'text-class',
				'description' => __( 'the term that appears in url', 'boo-recipes' ),
				'default'     => 'recipe',
				'attributes'  => array(
					'rows' => 10,
					'cols' => 5,
				),
				'help'        => 'only use small letters and underscores or dashes',
				'sanitize'    => 'sanitize_key',

			),

			array(
				'id'      => $this->prefix . 'external_link_type',
				'type'    => 'select',
				'label'   => __( 'External Author Link Type', 'boorecipe-premium' ),
				'default' => 'link_to_name',
				'options' => array(
					'link_to_name'    => esc_html__( 'Link to External Author Name', 'boorecipe-premium' ),
					'show_under_name' => esc_html__( 'Show Under External Author Name', 'boorecipe-premium' )
				),
			)


		) );
		/*
		 * Recipe Archive
		 */
		$options_fields['recipe_archive'] = apply_filters( 'boorecipe_filter_options_fields_array_archive', array(

			array(
				'id'       => $this->prefix . 'recipes_per_page',
				'type'     => 'number',
				'label'    => __( 'Recipes Per Page', 'boo-recipes' ),
				'default'  => $this->get_default_options( 'recipes_per_page' ),
				'sanitize' => 'boorecipe_sanitize_absint',
				'options'  => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'       => $this->prefix . 'recipes_per_row',
				'type'     => 'select',
				'label'    => __( 'Recipes Per Row', 'boo-recipes' ),
				'options'  => array(
					'1' => __( '1', 'boo-recipes' ),
					'2' => __( '2', 'boo-recipes' ),
					'3' => __( '3', 'boo-recipes' ),
					'4' => __( '4', 'boo-recipes' ),
					'5' => __( '5', 'boo-recipes' ),
				),
				'after'    => __( 'This option will not take affect for ALL archie layouts', 'boo-recipes' ),
				'default'  => $this->get_default_options( 'recipes_per_row' ),
				'sanitize' => 'boorecipe_sanitize_absint'
			),

			array(
				'id'      => $this->prefix . 'recipe_archive_layout',
				'type'    => 'select',
				'label'   => __( 'Recipes Archive Layout', 'boo-recipes' ),
				'options' => apply_filters( 'boorecipe_filter_options_fields_array_archive_layout', array(
					'grid' => __( 'Grid', 'boo-recipes' ),
					'list' => __( 'List', 'boo-recipes' ),
				) ),
				'default' => $this->get_default_options( 'recipe_archive_layout' ),
			),

			array(
				'id'          => 'show_in_masonry',
				'type'        => 'select',
				'label'       => __( 'Show Recipe cards in Masonry?', 'boo-recipes' ),
				'default'     => $this->get_default_options( 'show_in_masonry' ),
				'description' => __( 'If enabled, Layout Switcher will auto disable on front end', 'boo-recipes' ),
				'options'     => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'          => $this->prefix . 'show_layout_switcher',
				'type'        => 'select',
				'label'       => __( 'Show Layout Switcher?', 'boo-recipes' ),
				'description' => __( 'This option only available for List and Grid view', 'boo-recipes' ),
				'default'     => $this->get_default_options( 'show_layout_switcher' ),
				'options'     => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'      => $this->prefix . 'heading_for_archive_title',
				'type'    => 'select',
				'label'   => __( 'Heading Tag for Recipes Archive', 'boo-recipes' ),
				'options' => array(
					'h2' => __( 'h2', 'boo-recipes' ),
					'h3' => __( 'h3', 'boo-recipes' ),
					'h4' => __( 'h4', 'boo-recipes' ),
					'h5' => __( 'h5', 'boo-recipes' ),
					'h6' => __( 'h6', 'boo-recipes' ),
				),
				'default' => $this->get_default_options( 'heading_for_archive_title' ),
			),


			array(
				'id'          => $this->prefix . 'color_archive_title',
				'type'        => 'color',
				'label'       => __( 'Recipe Title Color', 'boo-recipes' ),
				'description' => __( 'This will default to theme link color', 'boo-recipes' ),
				'default'     => $this->get_default_options( 'color_archive_title' ),
			),

			array(
				'id'      => $this->prefix . 'color_archive_excerpt',
				'type'    => 'color',
				'label'   => __( 'Recipe Excerpt Color', 'boo-recipes' ),
				'default' => $this->get_default_options( 'color_archive_excerpt' ),

			),

			array(
				'id'      => $this->prefix . 'color_card_bg',
				'type'    => 'color',
				'label'   => __( 'Cards Background Color', 'boo-recipes' ),
				'default' => $this->get_default_options( 'color_card_bg' ),
				'rgba'    => true,
			),

			array(
				'id'      => '$this->prefix .color_archive_keys',
				'type'    => 'color',
				'label'   => __( 'Key Points Text Color', 'boo-recipes' ),
				'default' => $this->get_default_options( 'color_archive_keys' ),

			),

			// Expected insertion of premium options

			array(
				'id'          => $this->prefix . 'archive_layout_max_width',
				'type'        => 'number',
				'label'       => __( 'Archive Layout Max Width', 'boo-recipes' ),
				'description' => __( 'in pixels', 'boo-recipes' ),
				'default'     => $this->get_default_options( 'archive_layout_max_width' ),
				'sanitize'    => 'boorecipe_sanitize_absint',

			),


			array(
				'id'                => $this->prefix . 'override_theme_pagination_style',
				'type'              => 'select',
				'label'             => __( 'Override Pagination Styling?', 'boo-recipes' ),
				'label_description' => __( 'Do you want to override theme styling for pagination?', 'boo-recipes' ),
				'default'           => $this->get_default_options( 'override_theme_pagination_style' ),
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_archive_excerpt',
				'type'              => 'select',
				'label'             => __( 'Show Archive Excerpt', 'boo-recipes' ),
				'label_description' => __( 'Do you want to show archive excerpt?', 'boo-recipes' ),
				'default'           => $this->get_default_options( 'show_archive_excerpt' ),
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'                => $this->prefix . 'show_search_form',
				'type'              => 'select',
				'label'             => __( 'Show Search Form on archive page?', 'boo-recipes' ),
				'label_description' => __( 'If enabled, Search form will be added to recipes archive page ', 'boo-recipes' ),
				'default'           => $this->get_default_options( 'show_search_form' ),
				'options'           => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'      => $this->prefix . 'recipe_category_slug',
				'type'    => 'text',
				'label'   => __( 'Recipe Category Slug', 'boorecipe-premium' ),
				'default' => $this->get_default_options( 'recipe_category_slug' ),
				'desc'    => sprintf( __( "You will need to re-save %spermalinks%s after changing this value", "boorecipe" ), '<a href=' . get_admin_url() . "options-permalink.php" . ' target="_blank">', '</a>' ),
			),

			array(
				'id'      => $this->prefix . 'skill_level_slug',
				'type'    => 'text',
				'label'   => __( 'Skill Level Slug', 'boorecipe-premium' ),
				'default' => $this->get_default_options( 'skill_level_slug' ),
				'desc'    => sprintf( __( "You will need to re-save %spermalinks%s after changing this value", "boorecipe" ), '<a href=' . get_admin_url() . "options-permalink.php" . ' target="_blank">', '</a>' ),
			),

			array(
				'id'      => $this->prefix . 'recipe_tags_slug',
				'type'    => 'text',
				'label'   => __( 'Recipe Category Slug', 'boorecipe-premium' ),
				'default' => $this->get_default_options( 'recipe_tags_slug' ),
				'desc'    => sprintf( __( "You will need to re-save %spermalinks%s after changing this value", "boorecipe" ), '<a href=' . get_admin_url() . "options-permalink.php" . ' target="_blank">', '</a>' ),
			)


		) );
		/*
	 * Search Form
	 */
		$options_fields['recipe_search_form'] = apply_filters( 'boorecipe_filter_options_fields_array_search', array(

			array(
				'id'      => $this->prefix . 'form_bg_color',
				'type'    => 'color',
				'label'   => __( 'Form background Color', 'boo-recipes' ),
				'default' => $this->get_default_options( 'form_bg_color' ),
				'rgba'    => true,
//					'description'   => __('This will default to theme link color','boo-recipes'),
			),

			array(
				'id'      => $this->prefix . 'form_button_bg_color',
				'type'    => 'color',
				'label'   => __( 'Button background color', 'boo-recipes' ),
				'default' => $this->get_default_options( 'form_button_bg_color' ),
				'rgba'    => true,
			),

			array(
				'id'      => $this->prefix . 'form_button_text_color',
				'type'    => 'color',
				'label'   => __( 'Button text color', 'boo-recipes' ),
				'default' => $this->get_default_options( 'form_button_text_color' ),
			),

		) );
		/*
		 * Widget Settings
		 */
		$options_fields['recipe_widgets'] = apply_filters( 'boorecipe_filter_options_fields_array_widgets', array(

			array(
				'id'          => $this->prefix . 'recipe_widget_img_width',
				'type'        => 'number',
				'label'       => __( 'Recipe Widget: Image width', 'boo-recipes' ),
				'after'       => __( "in pixels", "boorecipe" ),
				'description' => __( 'its for widget area', 'boo-recipes' ),
				'default'     => $this->get_default_options( 'recipe_widget_img_width' ),
				'sanitize'    => 'boorecipe_sanitize_absint',

			),

			array(
				'id'      => $this->prefix . 'recipe_widget_bg_color',
				'type'    => 'color',
				'label'   => __( 'Recipe Widget: Background color', 'boo-recipes' ),
				'default' => $this->get_default_options( 'recipe_widget_bg_color' ),
				'rgba'    => true,
			),

		) );
//		/*
//		 * Settings Backup
//		 */
//		$options_fields['recipe_options_backup_restore'] = apply_filters( 'boorecipe_filter_options_fields_array_backup', array(
//
//			array(
//				'id'    => $this->prefix . 'boorecipe_options_backup_restore',
//				'type'  => 'backup',
//				'label' => __( 'Settings Backup and/or Restore', 'boo-recipes' ),
//			),
//
//		) );

		/*
		 * Premium Plugin
		 */
		$options_fields['recipe_plugin_activation'] = apply_filters( 'boorecipe_filter_options_fields_array_activation', array(
			array(
				'id'    => $this->prefix . 'plugin_activation_content',
				'type'  => 'html',
				'class' => 'class-name', // for all fields
				'desc'  => '<div>
								<p>Future Updates. 6 Months Support.</p>
								<p>Key Features include:</p>
								<ul>
									<li>2 single recipe styles: style1 and style2</li>
									<li>2 more recipe index styles: modern and overlay</li>
									<li>Change Labels to suit your needs</li>
									<li>Recipe Cuisines Taxonomy</li>
									<li>Cooking Method Taxonomy</li>
									<li>Recipes with image sliders</li>
									<li>Video Recipes</li>
									<li>Show/embed recipes in posts or pages</li>
								</ul>
							</div><br/>'
				           .
				           sprintf( '<a href="%s" target="_blank">%s</a>',
					           'https://boospot.com/product/boorecipes-premium-plugin/',
					           esc_html__( 'Buy Premium Plugin', 'boo-recipes' )
				           ),
			),

		) );
		/*
		 * Special
		 */
		$special_section_fields = array(

			array(
				'id'    => $this->prefix . 'custom_css_editor',
				'type'  => 'textarea',
				'label' => __( 'Your Custom CSS', 'boo-recipes' ),
				'desc'  => __( 'Add your custom CSS here', 'boo-recipes' ),
			),

			array(
				'id'    => $this->prefix . 'admin_custom_js_editor',
				'type'  => 'textarea',
				'label' => __( 'Your Custom JS for Admin', 'boo-recipes' ),
				'desc'  => __( 'Add your custom JS here', 'boo-recipes' ),
			),

		);

		if ( boorecipe_is_old_settings_available() ) {

			$special_section_fields[] = array(
				'id'    => $this->prefix . 'settings_converter',
				'type'  => 'html',
				'label' => esc_html__( 'Convert Old Settings', 'boo-recipes' ),
				'desc'  => '<input type="button" name="boorecipes-convert-settings" id="boorecipes-convert-settings" class="button button-secondary" value="' . esc_html__( 'Convert Old Settings', 'boo-recipes' ) . '"><div id="boorecipes-convert-settings-response"></div>'
			);

			$special_section_fields[] = array(
				'id'    => $this->prefix . 'update_recipes_meta',
				'type'  => 'html',
				'label' => __( 'Update Recipes Meta', 'boo-recipes' ),
				'desc'  => sprintf( '<input 
				type="button" 
				class="button button-secondary" 
				value="' . esc_html__( 'Update Recipes Meta', 'boo-recipes' ) . '"
				onclick="window.location.href=\'%s\'"
				>', admin_url( 'edit.php?post_type=boo_recipe&page=boorecipe-update-meta' ) )
			);
//			' . . '

			$special_section_fields[] = array(
				'id'    => $this->prefix . 'settings_delete_old',
				'type'  => 'html',
				'label' => __( 'Delete Old Settings', 'boo-recipes' ),
				'desc'  => '<input type="button" name="boorecipes-delete-old-settings" id="boorecipes-delete-old-settings" class="button button-secondary" value="' . esc_html__( 'Delete Old Settings', 'boo-recipes' ) . '"><div id="boorecipes-delete-old-settings-response"></div>'
			);
		}


		$options_fields['special_section'] = apply_filters( 'boorecipe_filter_options_fields_array_special', $special_section_fields );

		/*
		 * Uninstall
		 */
		$options_fields['uninstall_section'] = apply_filters( 'boorecipe_filter_options_fields_array_uninstall', array(

			array(
				'id'          => $this->prefix . 'uninstall_delete_options',
				'type'        => 'select',
				'label'       => __( 'Delete Plugin Options', 'boo-recipes' ),
				'description' => __( 'Delete all plugin options data at uninstall?', 'boo-recipes' ),
				'help'        => __( 'green = Yes & red = No', 'boo-recipes' ),
				'default'     => $this->get_default_options( 'uninstall_delete_options' ),
				'options'     => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),

			array(
				'id'          => $this->prefix . 'uninstall_delete_meta',
				'type'        => 'select',
				'label'       => __( 'Delete Recipes Data', 'boo-recipes' ),
				'description' => __( 'Delete all recipes meta data at uninstall?', 'boo-recipes' ),
				'help'        => __( 'green = Yes & red = No', 'boo-recipes' ),
				'default'     => $this->get_default_options( 'uninstall_delete_mata' ),
				'options'     => array(
					'yes' => esc_html__( 'Yes', 'boo-recipes' ),
					'no'  => esc_html__( 'No', 'boo-recipes' )
				),
			),


		) );

		return apply_filters( 'boorecipe_filter_options_fields_array', $options_fields );
	}


	/*
	 * Adding Function for Plugin Menu and options page
	 */

	public function get_default_options( $key ) {

		return Boorecipe_Globals::get_default_options( $key );

	}


	public function register_sidebar_widgets() {

		// Single Recipe Sidebar
		register_sidebar( array(
			'name'        => __( 'Recipe Single Sidebar', 'boo-recipes' ),
			'id'          => 'recipe-single-sidebar',
			'description' => __( 'Widgets in this area will be shown on Single Recipe', 'boo-recipes' ),
		) );

		// Archive Recipe Sidebar
		register_sidebar( array(
			'name'        => __( 'Recipe Archive Sidebar', 'boo-recipes' ),
			'id'          => 'recipe-archive-sidebar',
			'description' => __( 'Widgets in this area will be shown on Recipe Archive pages', 'boo-recipes' ),
		) );

	}


}
