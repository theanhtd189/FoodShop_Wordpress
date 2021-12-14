<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WOOMULTI_CURRENCY_F_Frontend_Price
 */
class WOOMULTI_CURRENCY_F_Frontend_Price {
	protected static $settings;
	protected $price;

	public function __construct() {

		self::$settings = WOOMULTI_CURRENCY_F_Data::get_ins();
		if ( self::$settings->get_enable() ) {
			/*Simple product*/
			add_filter(
				'woocommerce_product_get_regular_price', array(
				$this,
				'woocommerce_product_get_regular_price'
			), 99, 2 );
			add_filter(
				'woocommerce_product_get_sale_price', array(
				$this,
				'woocommerce_product_get_sale_price'
			), 99, 2
			);
			add_filter( 'woocommerce_product_get_price', array( $this, 'woocommerce_product_get_price' ), 99, 2 );
			//
			/*Variable price*/
			add_filter(
				'woocommerce_product_variation_get_price', array(
				$this,
				'woocommerce_product_variation_get_price'
			), 99, 2
			);
			add_filter(
				'woocommerce_product_variation_get_regular_price', array(
				$this,
				'woocommerce_product_variation_get_regular_price'
			), 99, 2
			);
			add_filter(
				'woocommerce_product_variation_get_sale_price', array(
				$this,
				'woocommerce_product_variation_get_sale_price'
			), 99, 2
			);

			/*Variable Parent min max price*/
			add_filter( 'woocommerce_variation_prices', array( $this, 'get_woocommerce_variation_prices' ), 99, 3 );

			/*Pay with Multi Currencies*/
			add_action( 'template_redirect', array( $this, 'init' ), 99 );

			/*Approximately*/
			add_filter( 'woocommerce_get_price_html', array( $this, 'add_approximately_price' ), 20, 2 );
			if ( self::$settings->get_price_switcher() ) {
				add_action( 'woocommerce_single_product_summary', array( $this, 'add_price_switcher' ), 20, 2 );
			}

			if ( self::$settings->get_param( 'cache_compatible' ) ) {
				add_filter( 'woocommerce_get_price_html', array( $this, 'compatible_cache_plugin' ), 20, 2 );
			}
		}
	}

	/**
	 *
	 */
	public function add_price_switcher() {
		if ( is_product() ) {
			echo do_shortcode( '[woo_multi_currency_product_price_switcher]' );
		}
	}

	/**
	 * Get Product variation price
	 *
	 * @param $product
	 *
	 * @return int|string
	 */
	public static function get_variation_max_price( $product, $currency_code = false, $raw = false ) {
		$variation_ids = $product->get_visible_children();
		$price_max     = 0;
		foreach ( $variation_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );

			if ( $variation ) {
				$price = 0;
				if ( ! $currency_code ) {
					$currenct_currency = self::$settings->get_current_currency();
				} elseif ( ! $raw ) {
					$currenct_currency = $currency_code;
				}
				if ( self::$settings->check_fixed_price() && ! $raw ) {
					$product_id    = $variation_id;
					$product_price = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true ) );
					$sale_price    = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true ) );
					if ( isset( $product_price[ $currenct_currency ] ) && ! $product->is_on_sale() && $product_price[ $currenct_currency ] > 0 ) {
						$price = $product_price[ $currenct_currency ];
					} elseif ( isset( $sale_price[ $currenct_currency ] ) && $sale_price[ $currenct_currency ] > 0 ) {
						$price = $sale_price[ $currenct_currency ];
					}
				}

				if ( ! $price ) {
					$price = $variation->get_price( 'edit' );
					if ( ! $raw ) {
						$price = wmc_get_price( $price, $currency_code );
					}
				}
				if ( $price > $price_max ) {
					$price_max = $price;
				}
			}
		}


		return $price_max;
	}
	/**
	 * @param $product WC_Product|WC_Product_Variable
	 * @param bool $currency_code
	 * @param bool $raw
	 *
	 * @return bool|float|int|mixed|string
	 */
	public static function get_variation_min_price( $product, $currency_code = false, $raw = false ) {
		$variation_ids     = $product->get_visible_children();
		$price_min         = false;
		$check_fixed_price = self::$settings->check_fixed_price();
		foreach ( $variation_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation ) {
				$price = 0;
				if ( ! $currency_code ) {
					$current_currency = self::$settings->get_current_currency();
				} elseif ( ! $raw ) {
					$current_currency = $currency_code;
				}
				if ( $check_fixed_price && ! $raw ) {
					$product_id    = $variation_id;
					$product_price = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true ) );
					$sale_price    = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true ) );
					if ( isset( $product_price[ $current_currency ] ) && ! $product->is_on_sale( 'edit' ) && $product_price[ $current_currency ] > 0 ) {
						$price = $product_price[ $current_currency ];
					} elseif ( isset( $sale_price[ $current_currency ] ) && $sale_price[ $current_currency ] > 0 ) {
						$price = $sale_price[ $current_currency ];
					}
				}

				if ( ! $price ) {
					$price = $variation->get_price( 'edit' );
					if ( ! $raw ) {
						$price = wmc_get_price( $price, $currency_code );
					}
				}
				if ( $price_min === false ) {
					$price_min = $price;
				}
				if ( $price < $price_min ) {
					$price_min = $price;
				}
			}
		}

		return $price_min;
	}
	/**
	 * @param $html_price    default price
	 * @param $a
	 *
	 * @return string
	 */
	public function add_approximately_price( $html_price, $product ) {

		if ( is_admin() ) {
			return $html_price;
		}
		if ( self::$settings->get_auto_detect() == 2 ) {
			if ( '' === $product->get_price() || ! $product->is_in_stock() ) {
				return $html_price;
			}
			if ( ! self::$settings->getcookie( 'wmc_currency_rate' ) || ! self::$settings->getcookie( 'wmc_currency_symbol' ) || ! self::$settings->getcookie( 'wmc_ip_info' ) ) {
				return $html_price;
			}
			$geoplugin_arg = json_decode( base64_decode( self::$settings->getcookie( 'wmc_ip_info' ) ), true );

			$detect_currency_code = isset( $geoplugin_arg['currency_code'] ) ? $geoplugin_arg['currency_code'] : '';
			if ( $detect_currency_code == self::$settings->get_current_currency() ) {
				return $html_price;
			}
			$list_currencies    = self::$settings->get_list_currencies();
			$default_currency   = self::$settings->get_default_currency();
			$decimal_separator  = wc_get_price_decimal_separator();
			$thousand_separator = wc_get_price_thousand_separator();
			if ( $detect_currency_code && isset( $list_currencies[ $detect_currency_code ] ) ) {
				$decimals    = $list_currencies[ $detect_currency_code ]['decimals'];
				$current_pos = $list_currencies[ $detect_currency_code ]['pos'];
			} else {
				$decimals    = $list_currencies[ $default_currency ]['decimals'];
				$current_pos = $list_currencies[ $default_currency ]['pos'];
			}
			$rate   = self::$settings->getcookie( 'wmc_currency_rate' );
			$symbol = self::$settings->getcookie( 'wmc_currency_symbol' );
			switch ( $current_pos ) {
				case 'left' :
					$format = '%1$s%2$s';
					break;
				case 'right' :
					$format = '%2$s%1$s';
					break;
				case 'left_space' :
					$format = '%1$s&nbsp;%2$s';
					break;
				case 'right_space' :
					$format = '%2$s&nbsp;%1$s';
					break;
			}


			$price = number_format( wc_get_price_to_display( $product, array(
					'qty'   => 1,
					'price' => $product->get_price( 'edit' )
				) ) * $rate, $decimals, $decimal_separator, $thousand_separator );
			$pos   = strpos( $symbol, '#PRICE#' );
			if ( $pos === false ) {
				$formatted_price = sprintf( $format, $symbol, $price );
			} else {
				$formatted_price = str_replace( '#PRICE#', $price, $symbol );
			}
			$max_price = '';
			if ( $product->get_type() == 'variable' ) {
				$price_max = self::get_variation_max_price( $product, false, true );
				if ( $price_max != $product->get_price( 'edit' ) ) {
					$price_max = number_format( wc_get_price_to_display( $product, array(
							'qty'   => 1,
							'price' => $price_max
						) ) * $rate, $decimals, $decimal_separator, $thousand_separator );
					if ( $pos === false ) {
						$max_price = ' - ' . sprintf( $format, $symbol, $price_max );
					} else {
						$max_price = ' - ' . str_replace( '#PRICE#', $price_max, $symbol );
					}
				}
			}
			$html_price .= '<div class="wmc-approximately">' . esc_html__( 'Approximately', 'woo-multi-currency' ) . ': ' . $formatted_price . $max_price . '</div>';

		}

		return $html_price;
	}

	/**
	 * Check on checkout page
	 */
	public static function init() {

		if ( is_admin() && ! is_ajax() ) {
			return;
		}
		/*Fix UX Builder of Flatsome*/
		if ( isset( $_GET['uxb_iframe'] ) ) {
			return;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		$settings    = WOOMULTI_CURRENCY_F_Data::get_ins();
		$allow_multi = $settings->get_enable_multi_payment();

		$old_currency = $settings->getcookie( 'wmc_current_currency_old' );
		$is_checkout  = is_checkout();
		/*Checkout && Cartpage*/
		if ( ! $allow_multi ) {
			$cur = $is_checkout ? $settings->get_default_currency() : $old_currency;
			$settings->set_current_currency( $cur, false );
		}
	}


	/**Variable Parent min max price
	 * @param $price_arr
	 * @param $product
	 * @param $for_display
	 *
	 * @return array
	 */
	public function get_woocommerce_variation_prices( $price_arr, $product, $for_display ) {
		$temp_arr = $price_arr;
		if ( is_array( $price_arr ) && ! empty( $price_arr ) ) {
			$fixed_price = self::$settings->check_fixed_price();

			foreach ( $price_arr as $price_type => $values ) {
				foreach ( $values as $key => $value ) {

					if ( $fixed_price ) {
						$current_currency = self::$settings->get_current_currency();
						if ( $temp_arr['regular_price'][ $key ] != $temp_arr['price'][ $key ] ) {
							if ( $price_type == 'regular_price' ) {
								$regular_price_wmcp = wmc_adjust_fixed_price( json_decode( get_post_meta( $key, '_regular_price_wmcp', true ), true ) );

								if ( isset( $regular_price_wmcp[ $current_currency ] ) && $regular_price_wmcp[ $current_currency ] > 0 ) {
									$price_arr[ $price_type ][ $key ] = $for_display ? $this->tax_handle( $regular_price_wmcp[ $current_currency ], $product ) : $regular_price_wmcp[ $current_currency ];
								} else {
									$price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
								}
							}

							if ( $price_type == 'price' || $price_type == 'sale_price' ) {
								$sale_price_wmcp = wmc_adjust_fixed_price( json_decode( get_post_meta( $key, '_sale_price_wmcp', true ), true ) );

								if ( isset( $sale_price_wmcp[ $current_currency ] ) && $sale_price_wmcp[ $current_currency ] > 0 ) {
									$price_arr[ $price_type ][ $key ] = $for_display ? $this->tax_handle( $sale_price_wmcp[ $current_currency ], $product ) : $sale_price_wmcp[ $current_currency ];
								} elseif ( $temp_arr['regular_price'][ $key ] != $temp_arr['price'][ $key ] ) {
									$price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
								} else {
									$price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
								}
							}
						} else {
							$regular_price_wmcp = wmc_adjust_fixed_price( json_decode( get_post_meta( $key, '_regular_price_wmcp', true ), true ) );
							if ( isset( $regular_price_wmcp[ $current_currency ] ) && $regular_price_wmcp[ $current_currency ] > 0 ) {
								$price_arr[ $price_type ][ $key ] = $for_display ? $this->tax_handle( $regular_price_wmcp[ $current_currency ], $product ) : $regular_price_wmcp[ $current_currency ];
							} else {
								$price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
							}
						}

					} else {
						$price_arr[ $price_type ][ $key ] = wmc_get_price( $value );
					}
				}
			}
		}


		return $price_arr;
	}

	/**
	 * @param $price
	 * @param $product
	 *
	 * @return float|string
	 */
	public function tax_handle( $price, $product ) {
		if ( ! $price ) {
			return $price;
		}

		$data = array( 'qty' => 1, 'price' => $price, );

		return 'incl' === get_option( 'woocommerce_tax_display_shop' ) ? wc_get_price_including_tax( $product, $data ) : wc_get_price_excluding_tax( $product, $data );
	}

	/**Sale price with product variable
	 * @param $price
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public function woocommerce_product_variation_get_sale_price( $price, $product ) {
		if ( ! $price ) {
			return $price;
		}
		$product_id = $product->get_id();
		if ( isset( $this->price[ $product_id ][ $price ] ) ) {
			return $this->price[ $product_id ][ $price ];
		}
		$changes = $product->get_changes();

		if ( self::$settings->check_fixed_price() && ( is_array( $changes ) ) && count( $changes ) < 1 ) {

			$currenct_currency = self::$settings->get_current_currency();
			$product_id        = $product->get_id();
			$product_price     = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true ) );
			if ( isset( $product_price[ $currenct_currency ] ) ) {
				if ( $product_price[ $currenct_currency ] > 0 ) {
					return $this->set_cache( $product_price[ $currenct_currency ], $product_id, $price );
				}
			}
		}

		return $this->set_cache( wmc_get_price( $price ), $product_id, $price );
		//Do nothing to remove prices hash to alway get live price.
	}

	/**Regular price with product variable
	 * @param $price
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public function woocommerce_product_variation_get_regular_price( $price, $product ) {
		if ( ! $price ) {
			return $price;
		}
		$product_id = $product->get_id();
		if ( isset( $this->price[ $product_id ][ $price ] ) ) {
			return $this->price[ $product_id ][ $price ];
		}
		$changes = $product->get_changes();

		if ( self::$settings->check_fixed_price() && ( is_array( $changes ) ) && count( $changes ) < 1 ) {

			$currenct_currency = self::$settings->get_current_currency();
			$product_id        = $product->get_id();
			$product_price     = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true ) );
			if ( isset( $product_price[ $currenct_currency ] ) ) {
				if ( $product_price[ $currenct_currency ] > 0 ) {
					return $this->set_cache( $product_price[ $currenct_currency ], $product_id, $price );

				}
			}
		}

		return $this->set_cache( wmc_get_price( $price ), $product_id, $price );
		//Do nothing to remove prices hash to alway get live price.
	}


	/**Sale product variable price
	 *
	 * @param $price
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public function woocommerce_product_variation_get_price( $price, $product ) {
		if ( ! $price ) {
			return $price;
		}
		$product_id = $product->get_id();
		if ( isset( $this->price[ $product_id ][ $price ] ) ) {
			return $this->price[ $product_id ][ $price ];
		}
		$changes = $product->get_changes();

		if ( self::$settings->check_fixed_price() && ( is_array( $changes ) ) && count( $changes ) < 1 ) {

			$currenct_currency = self::$settings->get_current_currency();
			$product_id        = $product->get_id();
			$product_price     = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true ) );
			$sale_price        = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true ) );
			if ( isset( $product_price[ $currenct_currency ] ) && ! $product->is_on_sale() ) {
				if ( $product_price[ $currenct_currency ] > 0 ) {
					return $this->set_cache( $product_price[ $currenct_currency ], $product_id, $price );
				}
			} elseif ( isset( $sale_price[ $currenct_currency ] ) ) {
				if ( $sale_price[ $currenct_currency ] > 0 ) {
					return $this->set_cache( $sale_price[ $currenct_currency ], $product_id, $price );

				}
			}
		}

		return $this->set_cache( wmc_get_price( $price ), $product_id, $price );
	}

	/**
	 * @param $price
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public function woocommerce_product_get_price( $price, $product ) {
		if ( ! $price ) {
			return $price;
		}
		$product_id = $product->get_id();
		if ( isset( $this->price[ $product_id ][ $price ] ) ) {
			return $this->price[ $product_id ][ $price ];
		}
		$changes = $product->get_changes();

		if ( self::$settings->check_fixed_price() && ( is_array( $changes ) ) && count( $changes ) < 1 ) {
			$currenct_currency = self::$settings->get_current_currency();
			$product_id        = $product->get_id();
			$product_price     = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true ) );
			$sale_price        = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true ) );
			if ( isset( $product_price[ $currenct_currency ] ) && ! self::is_on_sale( $product ) ) {
				if ( $product_price[ $currenct_currency ] > 0 ) {
					return $this->set_cache( $product_price[ $currenct_currency ], $product_id, $price );

				}
			} elseif ( isset( $sale_price[ $currenct_currency ] ) ) {
				if ( $sale_price[ $currenct_currency ] > 0 ) {
					return $this->set_cache( $sale_price[ $currenct_currency ], $product_id, $price );

				}
			}
		}

		return $this->set_cache( wmc_get_price( $price ), $product_id, $price );
	}

	/**
	 * @param $price
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public function woocommerce_product_get_sale_price( $price, $product ) {
		if ( ! $price ) {
			return $price;
		}
		$product_id = $product->get_id();
		if ( isset( $this->price[ $product_id ][ $price ] ) ) {
			return $this->price[ $product_id ][ $price ];
		}
		$changes = $product->get_changes();

		if ( self::$settings->check_fixed_price() && ( is_array( $changes ) ) && count( $changes ) < 1 ) {

			$currenct_currency = self::$settings->get_current_currency();
			$product_id        = $product->get_id();
			$product_price     = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_sale_price_wmcp', true ), true ) );
			if ( isset( $product_price[ $currenct_currency ] ) ) {
				if ( $product_price[ $currenct_currency ] > 0 ) {
					return $this->set_cache( $product_price[ $currenct_currency ], $product_id, $price );

				}
			}
		}

		return $this->set_cache( wmc_get_price( $price ), $product_id, $price );
	}

	/**
	 * @param $price
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public function woocommerce_product_get_regular_price( $price, $product ) {
		if ( ! $price ) {
			return $price;
		}
		$product_id = $product->get_id();
		if ( isset( $this->price[ $product_id ][ $price ] ) ) {
			return $this->price[ $product_id ][ $price ];
		}
		$changes = $product->get_changes();

		if ( self::$settings->check_fixed_price() && ( is_array( $changes ) ) && count( $changes ) < 1 ) {

			$currenct_currency = self::$settings->get_current_currency();
			$product_id        = $product->get_id();
			$product_price     = wmc_adjust_fixed_price( json_decode( get_post_meta( $product_id, '_regular_price_wmcp', true ), true ) );
			if ( isset( $product_price[ $currenct_currency ] ) ) {
				if ( $product_price[ $currenct_currency ] > 0 ) {

					return $this->set_cache( $product_price[ $currenct_currency ], $product_id, $price );
				}
			}
		}

		return $this->set_cache( wmc_get_price( $price ), $product_id, $price );
	}

	/**Set price to global. It will help more speedy.
	 * @param $price
	 * @param $id
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function set_cache( $price, $id, $key ) {
		$ajax = isset( $_GET['wc-ajax'] ) ? sanitize_text_field( $_GET['wc-ajax'] ) : '';
		if ( $ajax === 'ppc-create-order' ) {
			/**
			 * Fix bug with WooCommerce PayPal Payments
			 *
			 * 'HUF', 'JPY' and 'TWD' do not support decimals, the rest must have 2 decimals
			 */
			$current_currency = self::$settings->get_current_currency();
			$args             = array( 'decimals' => 2 );
			if ( in_array( $current_currency, array( 'HUF', 'JPY', 'TWD' ) ) ) {
				$args['decimals'] = 0;
			}
			$price = WOOMULTI_CURRENCY_F_Data::convert_price_to_float( $price, $args );
		}
		if ( $price && $id && $key ) {
			/*Default decimal is "."*/
			$this->price[ $id ][ $key ] = str_replace( ',', '.', $price );

			return $this->price[ $id ][ $key ];
		} else {
			return $price;
		}
	}

	/**
	 * @param $price
	 * @param $product WC_Product
	 *
	 * @return string
	 */
	public function compatible_cache_plugin( $price, $product ) {
		return "<span class='wmc-cache-pid' data-wmc_product_id='{$product->get_id()}'>" . $price . '</span>';
	}

	/**Fix error 500 with Subscriptions for WooCommerce plugin from WebToffee if using $product->is_on_sale('edit') for variable_subscription product
	 *
	 * @param $product WC_Product
	 *
	 * @return bool
	 */
	public static function is_on_sale( $product ) {
		$context = 'edit';
		if ( '' !== (string) $product->get_sale_price( $context ) && $product->get_regular_price( $context ) > $product->get_sale_price( $context ) ) {
			$on_sale = true;

			if ( $product->get_date_on_sale_from( $context ) && $product->get_date_on_sale_from( $context )->getTimestamp() > time() ) {
				$on_sale = false;
			}

			if ( $product->get_date_on_sale_to( $context ) && $product->get_date_on_sale_to( $context )->getTimestamp() < time() ) {
				$on_sale = false;
			}
		} else {
			$on_sale = false;
		}

		return $on_sale;
	}
}