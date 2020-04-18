<?php
/**
 * Filters manager class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Smart_Filters_Filter_Manager' ) ) {

	/**
	 * Define Jet_Smart_Filters_Filter_Manager class
	 */
	class Jet_Smart_Filters_Filter_Manager {

		private $_filter_types = array();
		private $_active_filters = array();

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			$this->register_filter_types();
			add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'filter_scripts' ) );
			add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'filter_styles' ) );
			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'filter_editor_styles' ) );
			add_action( 'elementor/preview/enqueue_styles', array( $this, 'filter_editor_styles' ) );
		}

		/**
		 * Enqueue filter scripts
		 */
		public function filter_scripts() {

			$dependencies = array( 'jquery' );

			foreach ( $this->get_filter_types() as $filter ) {

				$assets = $filter->get_scripts();

				if ( $assets ) {
					$dependencies = array_merge( $dependencies, $assets );
				}

			}

			wp_enqueue_script(
				'jet-smart-filters',
				jet_smart_filters()->plugin_url( 'assets/js/public.js' ),
				$dependencies,
				jet_smart_filters()->get_version(),
				true
			);

			$localized_data = apply_filters( 'jet-smart-filters/filters/localized-data', array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'selectors' => jet_smart_filters()->data->get_provider_selectors(),
				'queries'   => jet_smart_filters()->query->get_default_queries(),
				'settings'  => jet_smart_filters()->providers->get_provider_settings(),
				'filters'   => $this->get_active_filters(),
				'misc'      => array(
					'week_start' => get_option( 'start_of_week' ),
				),
			) );

			wp_localize_script( 'jet-smart-filters', 'JetSmartFilterSettings', $localized_data );

		}

		/**
		 * Return active filters array
		 *
		 * @return array
		 */
		public function get_active_filters() {
			return $this->_active_filters;
		}

		/**
		 * Store information about rendered filter into active filters list
		 *
		 * @param string $provider  [description]
		 * @param string $query_id  [description]
		 * @param string $filter_id [description]
		 * @param array  $args      [description]
		 */
		public function set_active_filter( $provider = '', $query_id = 'default', $filter_id = '', $args = array() ) {

			if ( ! isset( $this->_active_filters[ $provider ] ) ) {
				$this->_active_filters[ $provider ] = array();
			}

			if ( ! isset( $this->_active_filters[ $provider ][ $query_id ] ) ) {
				$this->_active_filters[ $provider ][ $query_id ] = array();
			}

			$this->_active_filters[ $provider ][ $query_id ][ $filter_id ] = $args;

		}

		/**
		 * Get active filters HTML string
		 *
		 * @return string
		 */
		public function get_active_filters_string() {

			$active_filters = jet_smart_filters()->query->get_active_filters_array();

			if ( ! $active_filters ) {
				return null;
			}

			$this->get_active_filters_string_content( $active_filters, false );

		}

		/**
		 * Renders active filters string from passed array
		 *
		 * @param  array $active_filters [description]
		 * @param  mixed $title [description]
		 *
		 * @return [type]                  [description]
		 */
		public function get_active_filters_string_content( $active_filters = array(), $title = false ) {

			ob_start();

			foreach ( $active_filters as $active_filter ) {

				/*
				 * Ignore grouping options by parents on active filters render
				 * Required to get_verbosed_val work correctly
				 */
				$active_filter['ignore_parents'] = true;

				$filter_id    = $active_filter['id'];
				$filter       = $this->get_filter_instance( $filter_id, $active_filter['type'], $active_filter );
				$hierarchical = isset( $active_filter['hierarchical'] ) ? $active_filter['hierarchical'] : false;
				$hierarchical = filter_var( $hierarchical, FILTER_VALIDATE_BOOLEAN );
				$value        = $active_filter['value'];

				include jet_smart_filters()->get_template( 'common/active-filter.php' );

			}

			$filters = ob_get_clean();

			if ( ! $filters ) {
				return;
			}

			include jet_smart_filters()->get_template( 'common/active-filters-title.php' );

			include jet_smart_filters()->get_template( 'common/active-filters-loop-start.php' );

			echo $filters;

			include jet_smart_filters()->get_template( 'common/active-filters-loop-end.php' );

		}

		/**
		 * Get active tags HTML string
		 *
		 * @return string
		 */
		public function get_active_tags_string() {

			$active_tags = jet_smart_filters()->query->get_active_filters_array();

			if ( ! $active_tags ) {
				return null;
			}

			$this->get_active_tags_string_content( $this->split_multiple_value( $active_tags ), false );

		}

		/**
		 * Renders active tags string from passed array
		 *
		 * @param  array $active_tags
		 * @param  mixed $title
		 *
		 * @return [type][description]
		 */
		public function get_active_tags_string_content( $active_tags = array(), $title = false ) {

			ob_start();

			foreach ( $active_tags as $active_tag ) {

				/*
				 * Ignore grouping options by parents on active filters render
				 * Required to get_verbosed_val work correctly
				 */
				$active_tag['ignore_parents'] = true;

				$filter_id    = $active_tag['id'];
				$filter       = $this->get_filter_instance( $filter_id, $active_tag['type'], $active_tag );
				$hierarchical = isset( $active_tag['hierarchical'] ) ? $active_tag['hierarchical'] : false;
				$hierarchical = filter_var( $hierarchical, FILTER_VALIDATE_BOOLEAN );
				$value        = $active_tag['value'];

				include jet_smart_filters()->get_template( 'common/active-tag.php' );

			}

			$active_tags = ob_get_clean();

			if ( ! $active_tags ) {
				return;
			}

			include jet_smart_filters()->get_template( 'common/active-tags-title.php' );

			include jet_smart_filters()->get_template( 'common/active-tags-loop-start.php' );

			include jet_smart_filters()->get_template( 'common/active-tag-clear.php' );

			echo $active_tags;

			include jet_smart_filters()->get_template( 'common/active-tags-loop-end.php' );

		}

		/**
		 * Split multiple value
		 *
		 * @param  array $active_filters
		 *
		 * @return array
		 */
		public function split_multiple_value( $active_filters ) {

			$output_active_filters = array();

			foreach ( $active_filters as $active_filter ) {
				$value = $active_filter['value'];

				if ( is_array( $value ) && ! filter_var( isset( $active_filter['hierarchical'] ) ? $active_filter['hierarchical'] : false , FILTER_VALIDATE_BOOLEAN ) ) {
					foreach ( $value as $single_value ) {
						$active_filter['value'] = $single_value;
						$output_active_filters[] = $active_filter;
					}
				} else {
					$output_active_filters[] = $active_filter;
				}
			}

			return $output_active_filters;

		}

		/**
		 * Enqueue filter styles
		 */
		public function filter_styles() {

			wp_enqueue_style(
				'jet-smart-filters',
				jet_smart_filters()->plugin_url( 'assets/css/public.css' ),
				array(),
				jet_smart_filters()->get_version()
			);

		}

		/**
		 * Enqueue editor filter styles
		 */
		public function filter_editor_styles() {

			wp_enqueue_style(
				'jet-smart-filters-icons-font',
				jet_smart_filters()->plugin_url( 'assets/css/lib/jet-smart-filters-icons/jet-smart-filters-icons.css' ),
				array(),
				jet_smart_filters()->get_version()
			);

		}

		/**
		 * Register all providers.
		 *
		 * @return void
		 */
		public function register_filter_types() {

			$base_path = jet_smart_filters()->plugin_path( 'includes/filters/' );

			$default_filter_types = array(
				'Jet_Smart_Filters_Checkboxes_Filter'  => $base_path . 'checkboxes.php',
				'Jet_Smart_Filters_Select_Filter'      => $base_path . 'select.php',
				'Jet_Smart_Filters_Range_Filter'       => $base_path . 'range.php',
				'Jet_Smart_Filters_Check_Range_Filter' => $base_path . 'check-range.php',
				'Jet_Smart_Filters_Date_Range_Filter'  => $base_path . 'date-range.php',
				'Jet_Smart_Filters_Radio_Filter'       => $base_path . 'radio.php',
				'Jet_Smart_Filters_Rating_Filter'      => $base_path . 'rating.php',
				'Jet_Smart_Filters_Search_Filter'      => $base_path . 'search.php',
				'Jet_Smart_Filters_Color_Image_Filter' => $base_path . 'color-image.php',
			);

			require $base_path . 'base.php';

			foreach ( $default_filter_types as $filter_class => $filter_file ) {
				$this->register_filter_type( $filter_class, $filter_file );
			}

			/**
			 * Register custom filter types on this hook
			 */
			do_action( 'jet-smart-filters/filter-types/register', $this );

		}

		/**
		 * Register new filter.
		 *
		 * @param  string $filter_class Filter class name.
		 * @param  string $filter_file Path to file with filter class.
		 *
		 * @return void
		 */
		public function register_filter_type( $filter_class, $filter_file ) {

			if ( ! file_exists( $filter_file ) ) {
				return;
			}

			require $filter_file;

			if ( class_exists( $filter_class ) ) {
				$instance                                   = new $filter_class();
				$this->_filter_types[ $instance->get_id() ] = $instance;
			}

		}

		/**
		 * Return all filter types list or specific filter by ID
		 *
		 * @param  string $filter optional, filter ID.
		 *
		 * @return array|filter object|false
		 */
		public function get_filter_types( $filter = null ) {

			if ( $filter ) {
				return isset( $this->_filter_types[ $filter ] ) ? $this->_filter_types[ $filter ] : false;
			}

			return $this->_filter_types;

		}

		/**
		 * Return suffix for query modify
		 *
		 * @param  string $filter, filter ID.
		 *
		 * @return string query_var_suffix for filter
		 */
		public function get_filter_query_var_suffix( $filter ) {

			$query_var_suffix   = array();
			$type               = get_post_meta( $filter, '_filter_type', true );
			$query_var          = get_post_meta( $filter, '_query_var', true );
			$data_source        = get_post_meta( $filter, '_data_source', true );
			$is_hierarchical    = false;
			$is_custom_checkbox = false;

			if ( 'select' === $type ) {
				$is_hierarchical = filter_var( get_post_meta( $filter, '_is_hierarchical', true ), FILTER_VALIDATE_BOOLEAN );
			}

			if ( in_array( $type, ['checkboxes', 'select', 'radio', 'color-image'] ) ) {
				$is_custom_checkbox = filter_var( get_post_meta( $filter, '_is_custom_checkbox', true ), FILTER_VALIDATE_BOOLEAN );
			}

			if ( in_array( $type, ['search', 'range', 'date-range', 'check-range', 'rating'] ) ) {
				$query_var_suffix[] = $type;
			}

			if ( $is_custom_checkbox ) {
				$query_var_suffix[] = 'is_custom_checkbox';
			}

			if ( $query_var && ! $is_hierarchical && ! $is_custom_checkbox ) {
				if ( in_array( $type, ['select', 'radio'] ) && 'taxonomies' !== $data_source ) {
					$query_compare      = get_post_meta( $filter, '_query_compare', true );

					if ( 'equal' !== $query_compare ) {
						$query_var_suffix[] = 'compare::' . $query_compare;
					}
				}
			}

			if ( 'rating' === $type ) {
				$query_compare      = get_post_meta( $filter, '_rating_compare_operand', true );
				$query_var_suffix[] = 'compare::' . $query_compare;
			}

			return $query_var_suffix ? implode( ',', $query_var_suffix ) : false;

		}

		/**
		 * Returns filter instance by filter post ID
		 *
		 * @param  [type] $filter_id [description]
		 * @return [type]            [description]
		 */
		public function get_filter_instance( $filter_id, $type = null, $args = array() ) {

			if ( null === $type ) {
				$type = get_post_meta( $filter_id, '_filter_type', true );
			}

			if ( ! $type ) {
				return false;
			}

			if ( ! class_exists( 'Jet_Smart_Filters_Filter_Instance' ) ) {
				require_once jet_smart_filters()->plugin_path( 'includes/filters/instance.php' );
			}

			return new Jet_Smart_Filters_Filter_Instance( $filter_id, $type, $args );

		}

		/**
		 * Render fiter type template
		 *
		 * @param  int $filter_id filter ID.
		 * @param  array $args arguments.
		 *
		 * @return void
		 */
		public function render_filter_template( $filter_type, $args = array() ) {

			$filter = $this->get_filter_instance( $args['filter_id'], $filter_type, $args );
			$filter->render();

		}

	}

}
