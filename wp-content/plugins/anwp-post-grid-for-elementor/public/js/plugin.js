
window.anwpPostGridElementor = {};
( function( window, $, app, l10n ) {

	// Constructor.
	app.init = function() {
		app.cache();
		app.bindEvents();
	};

	// Cache document elements.
	app.cache = function() {
		app.$c = {
			body: $( document.body )
		};
	};

	// Combine all events.
	app.bindEvents = function() {
		$( window ).on( 'elementor/frontend/init', function() {

			elementorFrontend.hooks.addAction( 'frontend/element_ready/anwp-pg-simple-slider.default', function( $scope ) {
				app.initSwiper( $scope );
			} );

			elementorFrontend.hooks.addAction( 'frontend/element_ready/anwp-pg-classic-slider.default', function( $scope ) {
				app.initSwiper( $scope );
			} );

			app.initLoadMore();
			app.initPromotionTooltip();

			app.$c.body.addClass( 'anwp-pg-ready' );

		} );
	};

	app.initPromotionTooltip = function() {

		if ( l10n.premium_active ) {
			return false;
		}

		$( parent.document ).on( 'mousedown', function( e ) {
			var wrapper = $( e.target ).closest( '.elementor-element--promotion' );
			var tooltip = $( parent.document.body ).find( '#elementor-element--promotion__dialog' );

			if ( wrapper.length && wrapper.find( '.anwp-pg-pro-promotion-icon' ).length ) {
				tooltip.find( 'button.dialog-action.dialog-buttons-action.elementor-button-success' ).hide();

				if ( ! tooltip.find( 'a.dialog-button.dialog-action.dialog-buttons-action.elementor-button-success' ).length ) {
					var classes = wrapper.find( '.anwp-pg-pro-promotion-icon' ).attr( 'class' ).split( ' ' );
					var link    = 'https://anwp.pro/grid-promo';

					$.each( classes, function( index, c ) {
						if ( 0 < c.indexOf( '__admin-icon' ) ) {
							link += c.replace( '__admin-icon', '' ).replace( 'anwp-pg-pro', '' );
						}
					} );

					tooltip.find( '.dialog-action.dialog-buttons-action.elementor-button-success' ).after( '<a class="dialog-button dialog-action dialog-buttons-action elementor-button elementor-button-success" style="width: 100%; display: block; text-align: center;" href="' + link + '" target="_blank">See it in Action</a>' );
				}
			} else if ( wrapper.length ) {
				tooltip.find( 'button.dialog-action.dialog-buttons-action.elementor-button-success' ).show();
				tooltip.find( 'a.dialog-action.dialog-buttons-action.elementor-button-success' ).remove();
			}
		} );
	};

	app.initLoadMore = function() {
		var $btn = app.$c.body.find( '.anwp-pg-load-more__btn' );

		if ( ! $btn.length ) {
			return false;
		}

		$btn.on( 'click', function( e ) {
			e.preventDefault();

			var $this = $( this );

			if ( $this.hasClass( 'anwp-pg-load-more--active' ) ) {
				return false;
			}

			$this.addClass( 'anwp-pg-load-more--active disabled' );
			$this.prop( 'disabled', true );

			$.ajax( {
				url: l10n.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'anwp_pg_load_more_posts',
					args: $this.data( 'anwp-load-more' ),
					loaded: $this.data( 'anwp-loaded-qty' ),
					qty: $this.data( 'anwp-posts-per-load' ),
					_ajax_nonce: l10n.public_nonce
				}
			} ).done( function( response ) {
				if ( response.success ) {

					$( response.data.html ).appendTo( $this.closest( '.anwp-pg-wrap' ).find( '.anwp-pg-posts-wrapper' ) );
					$this.data( 'anwp-loaded-qty', response.data.offset );

					if ( ! response.data.next ) {
						$this.closest( '.anwp-pg-load-more' ).remove();
					}

					$( document.body ).trigger( 'post-load' );
					$( document ).trigger( 'resize' );
				}
			} ).always( function() {
				$this.removeClass( 'anwp-pg-load-more--active disabled' );
				$this.prop( 'disabled', false );
			} );

		} );
	};

	app.initSwiper = function( $scope ) {
		var $slider = $scope.find( '.anwp-pg-swiper-wrapper' );

		if ( ! $slider.length ) {
			return false;
		}

		// Get Swiper options
		var swiperOptions = {
			autoHeight: 'yes' !== $slider.data( 'pg-show-read-more' ),
			roundLengths: true,
			effect: $slider.data( 'pg-effect' ),
			spaceBetween: $slider.data( 'pg-space-between' ),
			slidesPerView: $slider.data( 'pg-slides-per-view-mobile' ),
			slidesPerGroup: $slider.data( 'pg-slides-per-group-mobile' ),
			breakpoints: {
				576: {
					slidesPerView: $slider.data( 'pg-slides-per-view-tablet' ),
					slidesPerGroup: $slider.data( 'pg-slides-per-group-tablet' )
				},
				768: {
					slidesPerView: $slider.data( 'pg-slides-per-view' ),
					slidesPerGroup: $slider.data( 'pg-slides-per-group' )
				}
			}
		};

		if ( 'yes' === $slider.data( 'pg-autoplay' ) ) {
			swiperOptions.autoplay = {
				delay: $slider.data( 'pg-autoplay-delay' )
			};
		}

		if ( 'fade' === swiperOptions.effect ) {
			swiperOptions.fadeEffect = {
				crossFade: true
			};
		}

		if ( $scope.find( '.swiper-pagination' ).length ) {
			swiperOptions.pagination = {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			};
		}

		if ( $scope.find( '.elementor-swiper-button-prev' ).length ) {
			swiperOptions.navigation = {
				prevEl: '.elementor-swiper-button-prev',
				nextEl: '.elementor-swiper-button-next'
			};
		}

		new Swiper( $slider[ 0 ], swiperOptions );
	};

	// Engage!
	app.init();
}( window, jQuery, window.anwpPostGridElementor, window.anwpPostGridElementorData ) );
