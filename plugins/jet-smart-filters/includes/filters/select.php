<?php
/**
 * Select filter class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Select_Filter' ) ) {

	/**
	 * Define Jet_Smart_Filters_Select_Filter class
	 */
	class Jet_Smart_Filters_Select_Filter extends Jet_Smart_Filters_Filter_Base {

		/**
		 * Get provider name
		 *
		 * @return string
		 */
		public function get_name() {
			return __( 'Select', 'jet-smart-filters' );
		}

		/**
		 * Get provider ID
		 *
		 * @return string
		 */
		public function get_id() {
			return 'select';
		}

		/**
		 * Get provider wrapper selector
		 *
		 * @return string
		 */
		public function get_scripts() {
			return false;
		}

		/**
		 * Return arguments
		 * @return [type] [description]
		 */
		public function get_args() {
			return $this->args;
		}

		/**
		 * Return filter value in human-readable format
		 *
		 * @param  string $input     Filter value to format.
		 * @param  int    $filter_id Filter ID.
		 * @return string
		 */
		public function get_verbosed_val( $input, $filter_id, $args = false ) {

			if ( 'false' === $input ) {
				return;
			}

			if ( false === $args ) {
				$args = $this->prepare_args( array(
					'filter_id' => $filter_id
				) );
			}

			if ( empty( $args['options'] ) ) {
				return;
			}

			$options = $args['options'];

			if ( is_array( $input ) ) {
				$input = $input[0];
			}

			return isset( $options[ $input ] ) ? $options[ $input ] : false;
		}

		/**
		 * Prepare filter template argumnets
		 *
		 * @param  [type] $args [description]
		 * @return [type]       [description]
		 */
		public function prepare_args( $args ) {

			$filter_id        = $args['filter_id'];
			$content_provider = isset( $args['content_provider'] ) ? $args['content_provider'] : false;
			$apply_type       = isset( $args['apply_type'] ) ? $args['apply_type'] : false;

			if ( ! $filter_id ) {
				return false;
			}

			$source                  = get_post_meta( $filter_id, '_data_source', true );
			$use_exclude_include     = get_post_meta( $filter_id, '_use_exclude_include', true );
			$exclude_include_options = get_post_meta( $filter_id, '_data_exclude_include', true );
			$filter_label            = get_post_meta( $filter_id, '_filter_label', true );
			$is_hierarchical         = get_post_meta( $filter_id, '_is_hierarchical', true );
			$is_hierarchical         = filter_var( $is_hierarchical, FILTER_VALIDATE_BOOLEAN );
			$options                 = array();
			$query_type              = false;
			$query_var               = '';
			$current_value           = false;

			if ( $is_hierarchical ) {
				$source = false;
			}

			switch ( $source ) {

				case 'taxonomies':
					$tax        = get_post_meta( $filter_id, '_source_taxonomy', true );
					$query_type = 'tax_query';
					$query_var  = $tax;

					$only_child = get_post_meta( $filter_id, '_only_child', true );
					$only_child = filter_var( $only_child, FILTER_VALIDATE_BOOLEAN );

					$options = jet_smart_filters()->data->get_terms_for_options( $tax, $only_child );

					if ( is_tax( $tax ) ) {
						$current_value = get_queried_object_id();
					}

					break;

				case 'posts':

					$post_type = get_post_meta( $filter_id, '_source_post_type', true );
					$args      = array(
						'post_type' => $post_type,
						'post_status' => 'publish',
						'posts_per_page' => -1
					);

					$args = apply_filters( 'jet-smart-filters/filters/posts-source/args', $args );

					$posts      = get_posts( $args );
					$query_type = 'meta_query';
					$query_var  = get_post_meta( $filter_id, '_query_var', true );

					if ( ! empty( $posts ) ) {
						$options = wp_list_pluck( $posts, 'post_title', 'ID' );
					}

					break;

				case 'custom_fields':

					$custom_field   = get_post_meta( $filter_id, '_source_custom_field', true );
					$get_from_field = get_post_meta( $filter_id, '_source_get_from_field_data', true );
					$get_from_field = filter_var( $get_from_field, FILTER_VALIDATE_BOOLEAN );

					if ( $get_from_field ) {
						$options = jet_smart_filters()->data->get_choices_from_field_data( array(
							'field_key' => $custom_field,
							'source'    => get_post_meta( $filter_id, '_custom_field_source_plugin', true ),
						) );
					} else {
						$options = get_post_meta( get_the_ID(), $custom_field, true );
						$options = jet_smart_filters()->data->maybe_parse_repeater_options( $options );
					}

					$query_type = 'meta_query';
					$query_var  = get_post_meta( $filter_id, '_query_var', true );

					break;

				case 'manual_input':

					$options    = get_post_meta( $filter_id, '_source_manual_input', true );
					$query_type = 'meta_query';
					$query_var  = get_post_meta( $filter_id, '_query_var', true );

					if ( ! empty( $options ) ) {
						$options = wp_list_pluck( $options, 'label', 'value' );
					}

					break;

			}

			if ( $is_hierarchical ) {
				$query_type = 'tax_query';
			}

			$options = jet_smart_filters()->data->maybe_include_exclude_options( $use_exclude_include, $exclude_include_options, $options );

			$placeholder = get_post_meta( $filter_id, '_placeholder', true );

			if ( !empty( $options ) ){
				$options = array( '' => $placeholder ) + $options;
			}

			$result = array(
				'options'          => $options,
				'query_type'       => $query_type,
				'query_var'        => $query_var,
				'query_var_suffix' => jet_smart_filters()->filter_types->get_filter_query_var_suffix( $filter_id ),
				'content_provider' => $content_provider,
				'apply_type'       => $apply_type,
				'filter_id'        => $filter_id,
				'filter_label'     => $filter_label,
				'is_hierarchical'  => $is_hierarchical,
				'query_id'         => ! empty( $args['query_id'] ) ? $args['query_id'] : false,
			);

			if ( $current_value ) {
				$result['current_value'] = $current_value;
			}

			return $result;

		}

	}

}
