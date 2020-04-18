(function( $ ) {

	"use strict";

	var JetSmartFilterIndexerData = window.JetSmartFilterSettings.jetFiltersIndexedData;
	var xhr = null;

	var JetSmartFiltersIndexer = {

		is_ajax_action:  false,
		currentWidgetId: null,

		init: function() {

			var self = JetSmartFiltersIndexer;

			$( document ).ready( self.initFiltersIndexer );

			$( document )
				.on( 'jet-filter-before-loaded', self.updateFiltersIndexer )
				.on( 'jet-filter-hierarchy-updated', self.updateHierarchyIndexer );

			// init filters indexer in popup
			$( window )
				.on( 'jet-popup/render-content/ajax/success', function(evt, popup) {
					var $indexableFilters = $( '#jet-popup-' + popup.popup_id + ' .jet-filter[data-indexer-rule="show"]' );

					if ( $indexableFilters.length ) {
						JetSmartFiltersIndexer.initFiltersIndexer();
					}
				} );

		},

		initFiltersIndexer: function() {

			if ( $( 'body' ).hasClass( 'elementor-editor-active' ) ) {
				return;
			}

			var $filters = $( '.jet-filter.jet-filter-indexed' ),
				groupedFiltersCount = 0,
				emptyGroupedFiltersCount = 0;

			$.each( $filters, function() {
				var $this          = $( this ),
					$indexerRule   = $this.data( 'indexer-rule' ),
					$showCounter   = $this.data( 'show-counter' ),
					$changeCounter = $this.data( 'change-counter' ),
					widgetId       = $this.closest( '.elementor-widget' ).data( 'id' );

				if ( JetSmartFiltersIndexer.is_ajax_action ) {

					if ( 'never' === $changeCounter ) {
						return;
					}

					if ( 'other_changed' === $changeCounter && widgetId === JetSmartFiltersIndexer.currentWidgetId ) {
						return;
					}

				}
				var itemsCount = 0,
					hiddenItemsCount = 0;
				
				$.each( $this.find( 'input' ), function() {
					var $input  = $( this ),
						$item   = $input.closest( '.jet-filter-row' ),
						$args   = [$input.data( 'apply-provider' ), $input.data( 'query-id' ), $input.attr( 'name' ), $input.attr( 'value' )],
						$row    = $args.join( '/' ),
						$counts = 0;

					$row = $row.toLowerCase();

					if ( 'undefined' !== typeof JetSmartFilterIndexerData[$row] ) {
						$counts = JetSmartFilterIndexerData[$row];
					}

					$input.parent().find( '.jet-filters-counter .value' ).replaceWith( '<span class="value">' + $counts + '</span>' );

					if ( 'hide' === $indexerRule || 'disable' === $indexerRule ) {
						if ( 0 === $counts ) {
							$item.addClass( 'jet-filter-row-' + $indexerRule );
						} else {

							$item.removeClass( 'jet-filter-row-' + $indexerRule );
						}
					}
					
					if ( 'hide' === $indexerRule && 0 === $counts ) {
						hiddenItemsCount++;
					}
					
					itemsCount++;

				} );

				$.each( $this.find( 'option' ), function() {
					var $option = $( this ),
						$select = $option.closest( '.jet-select__control' ),
						$args   = [$select.data( 'apply-provider' ), $select.data( 'query-id' ), $select.attr( 'name' ), $option.attr( 'value' )],
						$row    = $args.join( '/' ),
						$counts = 0;

					if ( 'undefined' !== typeof JetSmartFilterIndexerData[$row] ) {
						$counts = JetSmartFilterIndexerData[$row];
					}

					if ( '' !== $option.attr( 'value' ) ) {
						if ( 'yes' === $showCounter ) {
							$option.html( $option.data( 'label' ) + ' ' + $option.data( 'counter-prefix' ) + $counts + $option.data( 'counter-suffix' ) );
						}

						if ( 'disable' === $indexerRule ) {
							if ( 0 === $counts ) {
								$option.attr( 'disabled', "" );
							} else {
								$option.removeAttr( 'disabled' );
							}
						}

						if ( 'hide' === $indexerRule || 'disable' === $indexerRule ) {
							if ( 0 === $counts ) {
								$option.addClass( 'jet-filter-row-' + $indexerRule );
							} else {
								$option.removeClass( 'jet-filter-row-' + $indexerRule );
							}
						}
						
						if ( 'hide' === $indexerRule && 0 === $counts ) {
							hiddenItemsCount++;
						}
						
						itemsCount++;
					}

				} );
				
				if ( itemsCount === hiddenItemsCount ) {
					$this.hide();
					if ( ! $this.parent().hasClass( 'jet-filters-group' ) ) {
						$this.parent().hide();
					}
				} else {
					$this.show();
					if ( ! $this.parent().hasClass( 'jet-filters-group' ) ) {
						$this.parent().show();
					}
				}
				
				if ( $this.parent().hasClass( 'jet-filters-group' ) && $this.css( 'display' ) === 'none' ) {
					emptyGroupedFiltersCount++;
				}
				
				groupedFiltersCount++;

			} );
			
			if ( $filters.parent().hasClass( 'jet-filters-group' ) && groupedFiltersCount === emptyGroupedFiltersCount ) {
				$filters.closest( '.elementor-widget-container' ).hide();
			} else {
				$filters.closest( '.elementor-widget-container' ).show();
			}

		},

		updateHierarchyIndexer: function( e, $scope, JetSmartFilters, response ) {
			JetSmartFiltersIndexer.updateIndexerHandler( $scope, response );
		},

		updateFiltersIndexer: function( e, $scope, JetSmartFilters, provider, query, queryID, response ) {
			JetSmartFiltersIndexer.updateIndexerHandler( $scope, response );
		},

		updateIndexerHandler: function( $scope, response ) {

			if ( ! response.jetFiltersIndexedData ) {
				return;
			}

			JetSmartFiltersIndexer.is_ajax_action = true;

			if ( false !== $scope ){
				JetSmartFiltersIndexer.currentWidgetId = $scope.closest( '.elementor-widget' ).data( 'id' );
			}

			JetSmartFilterIndexerData = response.jetFiltersIndexedData;

			JetSmartFiltersIndexer.initFiltersIndexer();

			JetSmartFiltersIndexer.is_ajax_action = false;

		},

	};

	JetSmartFiltersIndexer.init();

	window.JetSmartFiltersIndexer = JetSmartFiltersIndexer;

}( jQuery ));
