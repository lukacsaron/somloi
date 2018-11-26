<?php

class LscfCacheController {

	/**
	 * The meta name of the price variations cache.
	 *
	 * @access public
	 * @var string
	 */
	public $woocommerce_price_variation_meta_name = 'lscf_woocommerce_price_variations_cache';

	/**
	 * Return variations prices for variable products
	 *
	 * @param array $variations The variations array.
	 * @access public
	 * @var array
	 */
	function return_woo_variation_prices( $variations ) {

		$prices = array();

		foreach ( $variations as $variation ) {

			if ( ! isset( $variation['display_price'] ) || ! isset( $variation['display_regular_price'] ) ) { continue; }

			if ( 0 !== (int) $variation['display_price'] ) {
				$prices[] = $variation['display_price'];
			};

			if ( 0 !== (int) $variation['display_regular_price'] ) {
				$prices[] =  $variation['display_regular_price'];
			}

		}

		if ( 0 === count( $prices ) ) {
			return array(
				'min' => '',
				'max' => '',
			);
		};

		return array(
			'min' => min( $prices ),
			'max' => max( $prices ),
		);

	}


	/**
	 * Save the Min Max values of the WooCommerce product variations.
	 *
	 * @access public
	 * @param int $post_id The post ID.
	 * @var function
	 */
	public function save_variations_min_max_price( $post_id ) {

		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		$woo_product = wc_get_product( $post_id );

		if ( ! $woo_product ) {
			return;
		}
		if ( ! $woo_product->is_type( 'variable' ) ) { return; }

		$variations = $woo_product->get_available_variations();
		$variation_prices = $this->return_woo_variation_prices( $variations );

		update_post_meta( $post_id, $this->woocommerce_price_variation_meta_name, array( 'variations_price' => $variation_prices ) );

	}

	/**
	 * Init the class hooks.
	 *
	 * @access public
	 * @var function
	 */
	public function init_hooks() {
		add_action( 'save_post', array( $this, 'save_variations_min_max_price' ) );
	}

}
