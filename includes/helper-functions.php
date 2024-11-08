<?php
// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_recipe_have_attached_images' ) ) :

	function boorecipe_is_recipe_have_attached_images() {

		if ( is_singular( 'boo_recipe' ) ) {

			$attached_media = get_attached_media( '', get_the_ID() );
			if ( ! empty( $attached_media ) ) {
				return true;
			}

		}

		return false;

	}

endif;

if ( ! function_exists( 'boorecipe_get_default_language_code' ) ) :

	function boorecipe_get_default_language_code() {

		return Boorecipe_Globals::$default_language_code;

	}

endif;


/**
 * @param string $url
 *
 * @return null|string|string[] $url
 */
if ( ! function_exists( 'boorecipe_get_url_without_http_and_www' ) ) :

	function boorecipe_get_url_without_http_and_www( $url ) {

		$find_h  = '#^http(s)?://#';
		$find_w  = '/^www\./';
		$replace = '';
		$url     = preg_replace( $find_h, $replace, $url );
		$url     = preg_replace( $find_w, $replace, $url );

		return $url;
	}

endif;


/**
 * Get an attachment ID given a URL.
 *
 * @param string $url
 *
 * @return int Attachment ID on success, 0 on failure
 */
if ( ! function_exists( 'boorecipe_get_attachment_id' ) ) :

	function boorecipe_get_attachment_id( $url ) {
		$attachment_id = false;
		$dir           = wp_upload_dir();

		// To handle relative urls
		if ( substr( $url, 0, strlen( '/' ) ) === '/' ) {

			$url = get_site_url() . $url;
		}

		if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?

			$file = basename( $url );

			$query_args = array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'fields'      => 'ids',
				'meta_query'  => array(
					array(
						'value'   => $file,
						'compare' => 'LIKE',
						'key'     => '_wp_attached_file',
					),
				)
			);
			$query      = new WP_Query( $query_args );
			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) : $query->the_post();

					$attachment_id = $query->post;
					break;
				endwhile;
				wp_reset_postdata();
			}
		}

		return $attachment_id;
	}

endif;


/**
 * @param $url
 *
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_local_media_url' ) ) :

	function boorecipe_is_local_media_url( $url ) {

		$url_short      = boorecipe_get_url_without_http_and_www( $url );
		$site_url_short = boorecipe_get_url_without_http_and_www( get_site_url() );

		$pos = strpos( $url_short, $site_url_short );

		return ( $pos === 0 ) ? true : false;
	}

endif;


/**
 * @param $message
 * @param string $type
 *
 * @return string
 */
if ( ! function_exists( 'boorecipe_console_log_message' ) ) :

	function boorecipe_console_log_message( $message, $type = "log" ) {

		$message_types = array( 'log', 'error', 'warn', 'info' );
		$type          = ( in_array( strtolower( $type ), $message_types ) ) ? strtolower( $type ) : $message_types[0];
		$message       = htmlspecialchars( stripslashes( $message ) );
		//Replacing Quotes, so that it does not mess up the script
		$message = str_replace( '"', "-", $message );
		$message = str_replace( "'", "-", $message );

		$script_message = sprintf( "<script>console.%s('%s')</script>", $type, $message );

		return $script_message;
	}

endif;


/**
 * Returns the result of the get_max global function
 *
 * @param $option_id
 *
 * @return mixed
 *
 */
if ( ! function_exists( 'boorecipe_get_options_value' ) ) :

	function boorecipe_get_options_value( $option_id ) {

		return Boorecipe_Globals::get_options_value( $option_id );

	}

endif;


/**
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_recipe_taxonomy' ) ) :

	function boorecipe_is_recipe_taxonomy() {
		if ( is_tax( array(
			'recipe_category',
			'recipe_cuisine',
			'recipe_tags',
			'cooking_method',
			'skill_level',
			'recipe_tool'
		) ) ) {
			return true;
		} else {
			return false;
		}
	}

endif;


/**
 * Check if a shortcode is active
 *
 * @param $shortcode_tag
 *
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_active_shortcode' ) ) :

	function boorecipe_is_active_shortcode( $shortcode_tag ) {

		return Boorecipe_Globals::is_active_shortcode( $shortcode_tag );

	}

endif;


if ( ! function_exists( 'boorecipe_is_old_settings_available' ) ) :

	function boorecipe_is_old_settings_available() {
		// Get old options
		$old_settings = get_option( 'boorecipe-options' );

		// if no old settings found, send error
		if ( $old_settings ) {
			return true;
		} else {
			return false;
		}

	}

endif;


/**
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_active_shortcode_single' ) ) :

	function boorecipe_is_active_shortcode_single() {

		return Boorecipe_Globals::is_active_shortcode_single();

	}

endif;


/**
 * @param $csv_string
 *
 * @return int|string
 */
if ( ! function_exists( 'boorecipe_get_clean_csv' ) ) :

	function boorecipe_get_clean_csv( $csv_string ) {

		if ( empty( $csv_string ) || ! is_string( $csv_string ) ) {
			return '';
		}

		if ( ctype_digit( str_replace( ",", "", $csv_string ) ) ) {
			//all ok. very strict. input can only contain numbers and commas. not even spaces
			$clean_csv = $csv_string;
		} else {
			// Try to Get abs int from string
			$clean_csv = absint( $csv_string );
		}

		return $clean_csv;
	}

endif;


/**
 * @param $csv_string
 *
 * @return array|bool
 */
if ( ! function_exists( 'boorecipe_get_array_from_csv' ) ) :

	function boorecipe_get_array_from_csv( $csv_string ) {

		$clean_csv = boorecipe_get_clean_csv( $csv_string );

		if ( ! $clean_csv ) {
			return false;
		}

		$values_array = explode( ',', $clean_csv );

		$values_array = array_map( 'absint', $values_array );

		return $values_array;
	}

endif;


/**
 * @param $var
 */
//function rao_var_dump( $var ) {
//	echo "<pre>";
//	var_dump( $var );
//	echo "<pre>";
//	die();
//}


/**
 * @param $var
 */
if ( ! function_exists( 'var_dump_pretty' ) ) :

	function var_dump_pretty( $var, $color = 'black' ) {
		echo "<pre color:{$color}>";
		var_export( $var );
		echo "<pre>";
//	die();
	}

endif;


/**
 * @param $slugs_csv
 *
 * @return array
 */
if ( ! function_exists( 'boorecipe_get_array_from_slugs_csv' ) ) :

	function boorecipe_get_array_from_slugs_csv( $slugs_csv ) {

		if ( empty( $slugs_csv ) || ! is_string( $slugs_csv ) ) {
			return array();
		}

		$slugs_array = explode( ',', $slugs_csv );

		$slugs_array = array_map( 'sanitize_key', $slugs_array );

		return $slugs_array;

	}

endif;


/**
 * @param $color
 *
 * @return null|string
 */
if ( ! function_exists( 'boorecipe_sanitize_color' ) ) :

	function boorecipe_sanitize_color( $color ) {

		if ( '' === $color ) {
			return '';
		}

		// If string does not start with 'rgba', then treat as hex
		// sanitize the hex color and finally convert hex to rgba
		if ( false === strpos( $color, 'rgba' ) ) {
			return sanitize_hex_color( $color );
		}

		// By now we know the string is formatted as an rgba color so we need to further sanitize it.
		$color = trim( $color, ' ' );
		$red   = $green = $blue = $alpha = '';

		sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );

		return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';

	}

endif;


/**
 * @param null $widget_id_optional
 *
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_recipe_widget_active' ) ) :

	function boorecipe_is_recipe_widget_active( $widget_id_optional = null ) {


		if ( $widget_id_optional !== null ) {
			// We have received some widget_id to check
			if ( is_active_widget( false, false, $widget_id_optional, true ) ) {
				return true;
			}

		} else {


			$recipe_widget_ids_array = apply_filters( 'boorecipe_widgets_id_array', array(
				'boorecipe_recipe_cat',
				'boorecipe_recipe_skill_level',
				'boorecipe_recipe_cuisines',
				'boorecipe_recipe_tag_cloud',
				'boorecipe_recipes',
				'boorecipe-widget-search-recipes'
			) );

			$is_active_widget = false;
			foreach ( $recipe_widget_ids_array as $id ) {
				if ( is_active_widget( false, false, $id, true ) ) {

//				var_dump( $id );
					$is_active_widget = true;
//				break;
				}
			}

			return $is_active_widget;
		}

		return false;

	}
endif;


///**
// * Returns the result of the get_max global function
// *
// * @param string $meta_key
// *
// * @return string
// *
// */
//function boorecipe_get_meta_key_label( $meta_key ) {
//
//	return Boorecipe_Globals::get_meta_key_label( $meta_key );
//
//}


/**
 * Returns the result of the get_max global function
 */
if ( ! function_exists( 'boorecipe_is_single_recipe' ) ) :

	function boorecipe_is_single_recipe() {

		return Boorecipe_Globals::is_single_recipe();

	}
endif;


/**
 * Returns the result of the get_max global function
 *
 * @param array
 *
 * @return string
 *
 */
if ( ! function_exists( 'boorecipe_get_max' ) ) :

	function boorecipe_get_max( $array ) {

		return Boorecipe_Globals::get_max( $array );

	}

endif;


/**
 * Returns the result of the get_svg global function
 *
 * @param string $svg
 *
 * @return mixed svg code
 *
 */
if ( ! function_exists( 'boorecipe_get_svg' ) ) :

	function boorecipe_get_svg( $svg ) {

		return Boorecipe_Globals::get_svg( $svg );

	}

endif;


/**
 * Returns the result of the get_template global function
 *
 * @param string filename_without_extension
 * @param string directory name
 *
 * @return string template path
 *
 */
if ( ! function_exists( 'boorecipe_get_template' ) ) :

	function boorecipe_get_template( $name, $sub_directory = null ) {

		return Boorecipe_Globals::get_template( $name, $sub_directory );

	}

endif;


/**
 * @return string
 */
if ( ! function_exists( 'boorecipe_return_markup_author_name_with_link' ) ) :

	function boorecipe_return_markup_author_name_with_link() {
		global $post;
		$html = '';

		if ( isset( $post->post_author ) ) {
			$display_name = get_the_author_meta( 'display_name', $post->post_author );

			if ( empty( $display_name ) ) {
				$display_name = get_the_author_meta( 'nickname', $post->post_author );
			}

			// Get author's website URL
			$user_website = get_the_author_meta( 'url', $post->post_author );

			if ( ! empty( $user_website ) ) {
				$html .= "<a href='{$user_website}' target='_blank' rel='nofollow'>{$display_name}</a>";
			} else {
				$html .= "{$display_name}";
			}

		}

		return $html;
	}

endif;


/**
 * @param $number_input
 *
 * @return mixed|string
 */
if ( ! function_exists( 'boorecipe_sanitize_float' ) ) :

	function boorecipe_sanitize_float( $number_input ) {

		return $number_input = ( isset( $number_input ) && ! empty( $number_input ) ) ? filter_var( $number_input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';

	}

endif;


/**
 * @param $value
 *
 * @return string
 */
if ( ! function_exists( 'boorecipe_sanitize_textarea' ) ) :

	function boorecipe_sanitize_textarea( $value ) {

		// $value = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $value ) ) );


		$value = wp_kses_post( $value );

		return $value;
	}

endif;


/**
 * @param $number_input
 *
 * @return int|null
 */
if ( ! function_exists( 'boorecipe_sanitize_absint' ) ) :

	function boorecipe_sanitize_absint( $number_input ) {
		return $number_input = ( isset( $number_input ) && ! empty( $number_input ) ) ? absint( $number_input ) : null;
	}

endif;


/**
 * @param $number_input
 *
 * @return int|null
 */
if ( ! function_exists( 'boorecipe_sanitize_int' ) ) :

	function boorecipe_sanitize_int( $number_input ) {
		return $number_input = ( isset( $number_input ) && ! empty( $number_input ) ) ? (int) ( $number_input ) : null;
	}

endif;


/*
 * The Following Set of Functions are specific to recipes
 */

/**
 * @param null $key
 *
 * @return bool|mixed
 */
if ( ! function_exists( 'boorecipe_get_recipe_meta' ) ) :

	function boorecipe_get_recipe_meta( $key = null ) {

		$recipe_meta_array = Boorecipe_Globals::get_recipe_meta( get_the_ID() );


		if ( $key == null ) {
			return $recipe_meta_array;
		}

		if ( isset( $recipe_meta_array[ $key ] ) ) {
			return $recipe_meta_array[ $key ];
		} else {
			return false;
		}


	}

endif;


/**
 * @return object
 */
if ( ! function_exists( 'boorecipe_get_recipe_meta_object' ) ) :

	function boorecipe_get_recipe_meta_object() {

		return $recipe_meta_object = (object) boorecipe_get_recipe_meta();

	}

endif;


/**
 * @return string
 */
if ( ! function_exists( 'boorecipe_return_markup_external_author' ) ) :

	function boorecipe_return_markup_external_author() {

//	$post_id = get_the_ID();

		$external_author_name = boorecipe_get_recipe_meta( 'external_author_name' );
		$external_author_url  = boorecipe_get_recipe_meta( 'external_author_link' );


		$html = '';

		if ( ! empty( $external_author_name ) ) {
			$display_name = $external_author_name;


			if ( ! empty( $external_author_url ) ) {
				$html .= "<a href='{$external_author_url}' target='_blank' rel='nofollow'>{$display_name}</a>";
			} else {
				$html .= "{$display_name}";
			}

		} else {
			$html .= __( 'Anonymous', 'boo-recipes' );
		}

		return $html;
	}

endif;


/**
 * @return string
 */
if ( ! function_exists( 'boorecipe_return_markup_recipe_author_name' ) ) :

	function boorecipe_return_markup_recipe_author_name() {


		$is_external_author = boorecipe_get_recipe_meta( 'is_external_author' );


		if ( $is_external_author == 'yes' ) {
			$posttype_author = boorecipe_return_markup_external_author();
		} else {
			$posttype_author = boorecipe_return_markup_author_name_with_link();
		}

		return $posttype_author;
	}

endif;


/**
 * @param $post_type
 *
 * @return string
 */
if ( ! function_exists( 'boorecipe_default_posttype_image' ) ) :

	function boorecipe_default_posttype_image( $post_type ) {

		$options = get_option( 'boorecipe-options' );

		if ( isset( $options['recipe_default_img_url'] ) && ! empty( $options['recipe_default_img_url'] ) ) {
			return esc_url_raw( $options['recipe_default_img_url'] );
		}

		return BOORECIPE_PLUGIN_URL . "assets/images/{$post_type}-default-image.png";
	}

endif;

/**
 * @param $post_type
 *
 * @return string
 */
if ( ! function_exists( 'boorecipe_default_taxonomy_image_recipe_tool' ) ) :

	function boorecipe_default_taxonomy_image( $taxonomy ) {

		$options   = get_option( 'boorecipe-options' );
		$option_id = $taxonomy . '_default_img_url';

		if ( isset( $options[ $option_id ] ) && ! empty( $options[ $option_id ] ) ) {
			return esc_url_raw( $options[ $option_id ] );
		}

		return BOORECIPE_PLUGIN_URL . "assets/images/{$taxonomy}-default-image.jpg";
	}

endif;

/**
 * @param null $post_id
 * @param null $size
 *
 * @return false|string
 */
if ( ! function_exists( 'boorecipe_get_posttype_image_url' ) ) :

	function boorecipe_get_posttype_image_url( $post_id = null, $size = null ) {

		$image_size = ( $size != null ) ? $size : 'recipe_image';

		$post_id = ( $post_id != null ) ? $post_id : get_the_ID();

		$posttype_featured_image = get_the_post_thumbnail_url( $post_id, $image_size );

		if ( ! empty( $posttype_featured_image ) && ! false ) {
			return $posttype_featured_image;
		} else {
			return boorecipe_default_posttype_image( get_post_type( $post_id ) );
		}

	}

endif;


/**
 * @return mixed
 */
if ( ! function_exists( 'boorecipe_get_recipe_archive_layouts_array' ) ) :

	function boorecipe_get_recipe_archive_layouts_array() {

		$archive_layout_array = apply_filters( 'boorecipe_recipe_archive_layouts', array(
			'list'    => boorecipe_get_options_value( 'archive_layout_list_label' ),
			'grid'    => boorecipe_get_options_value( 'archive_layout_grid_label' ),
			'modern'  => boorecipe_get_options_value( 'archive_layout_modern_label' ),
			'overlay' => boorecipe_get_options_value( 'archive_layout_overlay_label' ),
		) );

		return $archive_layout_array;
	}

endif;


/**
 * @return mixed
 */
if ( ! function_exists( 'boorecipe_get_recipe_registered_taxonomy_array' ) ) :

	function boorecipe_get_recipe_registered_taxonomy_array() {

		$registered_taxonomy_array = apply_filters( 'boorecipe_recipe_registered_taxonomy', array(
			'recipe_category' => boorecipe_get_options_value( 'recipe_category_label' ),
			'recipe_cuisine'  => boorecipe_get_options_value( 'recipe_cuisine_label' ),
			'recipe_tags'     => boorecipe_get_options_value( 'recipe_tags_label' ),
			'skill_level'     => boorecipe_get_options_value( 'skill_level_label' ),
			'cooking_method'  => boorecipe_get_options_value( 'cooking_method_label' ),
			'recipe_tool'     => boorecipe_get_options_value( 'recipe_tool_label' ),
		) );

		return $registered_taxonomy_array;
	}

endif;


/**
 * @param $taxonomy
 *
 * @return false|string
 */
if ( ! function_exists( 'boorecipe_get_taxonomy_terms' ) ) :

	function boorecipe_get_taxonomy_terms( $taxonomy ) {

// Get the term IDs assigned to post.
		$post_terms = wp_get_object_terms( get_the_ID(), $taxonomy, array( 'fields' => 'ids' ) );

// Separator between links.
		$separator = ', ';

		if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {

			$term_ids = implode( ',', $post_terms );

			$terms = wp_list_categories( array(
				'title_li' => '',
				'style'    => 'none',
				'echo'     => false,
				'taxonomy' => $taxonomy,
				'include'  => $term_ids
			) );


			$terms = rtrim( trim( str_replace( '<br />', $separator, $terms ) ), $separator );

			// Display post categories.
			return $terms;
		} else {
			return false;
		}
	}

endif;


/**
 * @param $taxonomy_name_or_args_array
 *
 * @return bool|string
 */
if ( ! function_exists( 'boorecipe_get_taxonomy_terms_options_markup' ) ) :

	function boorecipe_get_taxonomy_terms_options_markup( $taxonomy_name_or_args_array ) {

		if ( is_array( $taxonomy_name_or_args_array ) ) {
			$taxonomy_name  = isset( $taxonomy_name_or_args_array['taxonomy'] ) ? $taxonomy_name_or_args_array['taxonomy'] : 0;
			$taxonomy_terms = get_terms( $taxonomy_name_or_args_array );

		} else {
			$taxonomy_name  = $taxonomy_name_or_args_array;
			$taxonomy_terms = get_terms( array(
				'taxonomy'   => $taxonomy_name_or_args_array,
				'hide_empty' => true
			) );
		}

		// Get Variables from GET array
		$get_taxonomy_term_from_url = ( isset( $_GET[ $taxonomy_name ] ) ) ? sanitize_key( $_GET[ $taxonomy_name ] ) : '';
		// If the GET variable not defined, try to get it from the queried object
		if ( empty( $get_taxonomy_term_from_url ) ) {
			$queried_object = get_queried_object();
			if ( get_class( $queried_object ) === 'WP_Term' ) {
				$get_taxonomy_term_from_url = $queried_object->slug;
			}
		}

		$output = false;

		if ( ! empty( $taxonomy_terms ) ) :
			foreach ( $taxonomy_terms as $term ) {
				if ( $term->slug == $get_taxonomy_term_from_url ) {

					$output .= "<option value='$term->slug' selected=selected>{$term->name}</option>";
				} else {
					$output .= "<option value='$term->slug'>{$term->name}</option>";
				}
			}

		endif;

		return $output;
	}

endif;


/**
 * @param $field_args
 *
 * @return string
 */
if ( ! function_exists( 'boorecipe_get_meta_terms_options_markup' ) ) :

	function boorecipe_get_meta_terms_options_markup( $field_args ) {

		$get_meta_from_url = ( isset( $_GET[ $field_args['id'] ] ) ) ? sanitize_key( $_GET[ $field_args['id'] ] ) : '';

		$option_markup = '';

		if ( is_array( $field_args['options'] ) ) {

			foreach ( $field_args['options'] as $meta_value => $meta_name ) {

				$selected = '';

				if ( $meta_value == $get_meta_from_url ) {
					$selected = "selected=selected";
				}
				$option_markup .= "<option value='$meta_value' {$selected}>{$meta_name}</option>";
			}
		}

		return $option_markup;

	}

endif;


/**
 * @return bool|string
 */
if ( ! function_exists( 'boorecipe_get_skill_level_options_markup' ) ) :

	function boorecipe_get_skill_level_options_markup() {

		// Get Variables from GET array
		$get_skill_level_from_url = ( isset( $_GET['skill_level'] ) ) ? sanitize_key( $_GET['skill_level'] ) : '';


		$skill_levels = array(
			'easy'   => __( 'Easy', 'boo-recipes' ),
			'medium' => __( 'Medium', 'boo-recipes' ),
			'hard'   => __( 'Hard', 'boo-recipes' ),
		);


		$output = false;

		foreach ( $skill_levels as $skill => $skill_name ) {

			$selected_skill = '';

			if ( $skill == $get_skill_level_from_url ) {
				$selected_skill = "selected=selected";
			}
			$output .= "<option value='$skill' {$selected_skill}>{$skill_name}</option>";
		}


		return $output;
	}

endif;


/**
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_tax_query' ) ) :

	function boorecipe_is_tax_query() {


		$is_tax = false;

		$recipe_taxonomy_array = boorecipe_get_recipe_registered_taxonomy_array();

		foreach ( $recipe_taxonomy_array as $taxonomy => $label ) {

			$is_tax = ( is_tax( $taxonomy ) ) ? true : false;

			if ( $is_tax ) {
				break;
			}

		}


		return $is_tax;

	}

endif;


/**
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_archive_query' ) ) :

	function boorecipe_is_archive_query() {

		$queried_object = get_queried_object();

		$taxonomy_template = false;
		if ( get_class( $queried_object ) == 'WP_Term' ) {
			$taxonomy_template = (
				$queried_object->taxonomy == 'recipe_category' ||
				$queried_object->taxonomy == 'recipe_cuisine' ||
				$queried_object->taxonomy == 'recipe_tags' ||
				$queried_object->taxonomy == 'skill_level' ||
				$queried_object->taxonomy == 'cooking_method'
			) ? true : false;
		}

		return $taxonomy_template;

	}

endif;


/**
 * @param $meta_key
 *
 * @return array
 */
if ( ! function_exists( 'boorecipe_get_all_meta_values' ) ) :

	function boorecipe_get_all_meta_values( $meta_key ) {
		global $wpdb;
		$result = $wpdb->get_col(
			$wpdb->prepare( "
			SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s' 
			AND p.post_status = 'publish'
			ORDER BY pm.meta_value",
				$meta_key
			)
		);

		return $result;
	}


endif;


/**
 * @return bool
 */
if ( ! function_exists( 'boorecipe_is_search_form_submitted' ) ) :

	function boorecipe_is_search_form_submitted() {

		$is_search_form_submitted = (
			isset( $_GET['recipe_search'] )
			&& ! empty( ( sanitize_key( $_GET['recipe_search'] ) ) )
		)
			? true : false;

		return $is_search_form_submitted;
	}

endif;


/**
 * @return bool
 */
if ( ! function_exists( 'boorecipe_show_search_form' ) ) :

	function boorecipe_show_search_form() {

		$options                    = get_option( 'boorecipe-options' );
		$recipe_submit_button_label = ( isset( $options['show_search_form'] ) ) ? $options['show_search_form'] : 'yes';

		$is_show_search_form = ( 'yes' == $recipe_submit_button_label ) ? true : false;

		return $is_show_search_form;
	}

endif;


/**
 * @param $key
 *
 * @return bool|mixed
 */
if ( ! function_exists( 'boorecipe_get_default_options' ) ) :
	function boorecipe_get_default_options( $key ) {

		return Boorecipe_Globals::get_default_options( $key );

	}
endif;
/**
 * @return array
 */
if ( ! function_exists( 'boorecipe_get_headings_array' ) ) :
	function boorecipe_get_headings_array() {
		return array(
			'h1' => __( 'h1', 'boo-recipes' ),
			'h2' => __( 'h2', 'boo-recipes' ),
			'h3' => __( 'h3', 'boo-recipes' ),
			'h4' => __( 'h4', 'boo-recipes' ),
			'h5' => __( 'h5', 'boo-recipes' ),
			'h6' => __( 'h6', 'boo-recipes' ),
		);
	}
endif;


/**
 * @return array
 */
if ( ! function_exists( 'boorecipe_get_nutrition_meta' ) ) :
	function boorecipe_get_nutrition_meta() {

		$nutrition_meta = array(

			'nutrition_servingSize'           => array(
				'itemprop'    => 'servingSize',
				'measurement' => '',
				'display'     => __( 'Serving Size', 'boo-recipes' ),
				'parent'      => true,
				'description' => __( 'The serving size, in terms of the number of volume or mass.', 'boo-recipes' ),
			),
			'nutrition_calories'              => array(
				'itemprop'    => 'calories',
				'measurement' => '',
				'display'     => __( 'Calories', 'boo-recipes' ),
				'parent'      => true,
				'description' => __( 'The number of calories.', 'boo-recipes' ),
			),
			'nutrition_fatContent'            => array(
				'itemprop'       => 'fatContent',
				'measurement'    => 'g',
				'display'        => __( 'Total Fat', 'boo-recipes' ),
				'cal_total'      => true,
				'2000_cal_total' => 65,
				'2500_cal_total' => 80,
				'parent'         => true,
				'description'    => __( 'The number of grams of fat.', 'boo-recipes' ),
			),
			'nutrition_saturatedFatContent'   => array(
				'itemprop'       => 'saturatedFatContent',
				'measurement'    => 'g',
				'display'        => __( 'Saturated Fat', 'boo-recipes' ),
				'cal_total'      => true,
				'2000_cal_total' => 20,
				'2500_cal_total' => 25,
				'parent'         => false,
				'description'    => __( 'The number of grams of saturated fat.', 'boo-recipes' ),
			),
			'nutrition_transFatContent'       => array(
				'itemprop'    => 'transFatContent',
				'measurement' => 'g',
				'display'     => __( 'Trans Fat', 'boo-recipes' ),
				'parent'      => false,
				'description' => __( 'The number of grams of trans fat.', 'boo-recipes' ),
			),
			'nutrition_unsaturatedFatContent' => array(
				'itemprop'    => 'unsaturatedFatContent',
				'measurement' => 'g',
				'display'     => __( 'Unsaturated Fat', 'boo-recipes' ),
				'parent'      => false,
				'description' => __( 'The number of grams of unsaturated fat.', 'boo-recipes' ),
			),
			'nutrition_cholesterolContent'    => array(
				'itemprop'       => 'cholesterolContent',
				'measurement'    => 'mg',
				'display'        => __( 'Cholesterol', 'boo-recipes' ),
				'cal_total'      => true,
				'2000_cal_total' => 300,
				'2500_cal_total' => 300,
				'parent'         => true,
				'description'    => __( 'The number of milligrams of cholesterol.', 'boo-recipes' ),
			),
			'nutrition_sodiumContent'         => array(
				'itemprop'       => 'sodiumContent',
				'measurement'    => 'mg',
				'display'        => __( 'Sodium', 'boo-recipes' ),
				'cal_total'      => true,
				'2000_cal_total' => 2400,
				'2500_cal_total' => 2400,
				'parent'         => true,
				'description'    => __( 'The number of milligrams of sodium.', 'boo-recipes' ),
			),

			'nutrition_carbohydrateContent' => array(
				'itemprop'       => 'carbohydrateContent',
				'measurement'    => 'g',
				'display'        => __( 'Total Carbohydrate', 'boo-recipes' ),
				'cal_total'      => true,
				'2000_cal_total' => 300,
				'2500_cal_total' => 375,
				'parent'         => true,
				'description'    => __( 'The number of grams of carbohydrates.', 'boo-recipes' ),

			),

			'nutrition_fiberContent'   => array(
				'itemprop'       => 'fiberContent',
				'measurement'    => 'g',
				'display'        => __( 'Dietary Fiber', 'boo-recipes' ),
				'cal_total'      => true,
				'2000_cal_total' => 25,
				'2500_cal_total' => 30,
				'parent'         => false,
				'description'    => __( 'The number of grams of fiber.', 'boo-recipes' ),
			),
			'nutrition_sugarContent'   => array(
				'itemprop'    => 'sugarContent',
				'measurement' => 'g',
				'display'     => __( 'Sugar', 'boo-recipes' ),
				'parent'      => false,
				'description' => __( 'The number of grams of sugar.', 'boo-recipes' ),
			),
			'nutrition_proteinContent' => array(
				'itemprop'    => 'proteinContent',
				'measurement' => 'g',
				'display'     => __( 'Protein', 'boo-recipes' ),
				'parent'      => true,
				'description' => __( 'The number of grams of protein.', 'boo-recipes' ),
			),


		);

		return apply_filters( 'boorecipe_nutrition_meta_helper_array', $nutrition_meta );

	}
endif;