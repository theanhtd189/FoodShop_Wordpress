<?php


namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;
class WooCommerceCurrency {

	private static $instance   = null;
	public $converted_currency = array();
	private $apply_currency    = null;
	private $is_checkout_different_currency;
	private $currency_update = null;
	public function __construct() {     }

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public function doHooks() {
		$currencies = $this->get_currencies_post_type();
		if ( $currencies ) {
			foreach ( $currencies as $currency ) {
				$currency_meta = get_post_meta( $currency->ID, '', true );

				array_push(
					$this->converted_currency,
					array(
						'ID'                => $currency->ID,
						'currency'          => $currency->post_title,
						'currencyPosition'  => $currency_meta['currency_position'][0],
						'thousandSeparator' => $currency_meta['thousand_separator'][0],
						'decimalSeparator'  => $currency_meta['decimal_separator'][0],
						'numberDecimal'     => $currency_meta['number_decimal'][0],
						'rate'              => $currency_meta['rate'][0],
						'fee'               => maybe_unserialize( $currency_meta['fee'][0] ),
						'status'            => $currency_meta['status'][0],
						'paymentMethods'    => maybe_unserialize( $currency_meta['payment_methods'][0] ),
						'countries'         => maybe_unserialize( $currency_meta['countries'][0] ),
						'symbol'            => get_woocommerce_currency_symbol( $currency->post_title ),
						'roundingType'      => $currency_meta['rounding_type'][0],
						'roundingValue'     => $currency_meta['rounding_value'][0],
						'subtractAmount'    => $currency_meta['subtract_amount'][0],
					)
				);
			}
			$this->apply_currency                 = reset( $this->converted_currency );
			$this->is_checkout_different_currency = get_option( 'yay_currency_checkout_different_currency', 0 );
			add_action(
				'init',
				function () {
					$this->add_woocommerce_filters();
				}
			);
		}
	}

	public function add_woocommerce_filters( $currency_ID = null ) {
		if ( ! is_admin() ) {
			if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
				$currency_ID          = sanitize_key( $_COOKIE['yay_currency_widget'] );
				$this->apply_currency = $this->get_currency_by_ID( $currency_ID ) ? $this->get_currency_by_ID( $currency_ID ) : reset( $this->converted_currency );
			}

			if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {
				if ( isset( $_POST['currency'] ) ) {
					$currency_ID = sanitize_text_field( $_POST['currency'] );

					$this->apply_currency = $this->get_currency_by_ID( $currency_ID );
				}
			}
			$this->set_cookies();
			add_filter( 'woocommerce_product_get_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_product_get_sale_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_variation_prices_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'custom_raw_price' ), 10, 2 );
			add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'custom_variation_price_hash' ) );
			add_filter( 'woocommerce_subscriptions_product_price_string', array( $this, 'custom_subscription_price_string' ), 10, 3 );
			add_filter( 'woocommerce_subscriptions_price_string', array( $this, 'custom_subscription_price_string' ), 10, 3 );

			add_filter( 'woocommerce_currency', array( $this, 'change_woocommerce_currency' ), 10, 1 );
			add_filter( 'woocommerce_currency_symbol', array( $this, 'change_existing_currency_symbol' ), 10, 2 );
			add_filter( 'pre_option_woocommerce_currency_pos', array( $this, 'change_currency_position' ) );
			add_filter( 'wc_get_price_thousand_separator', array( $this, 'change_thousand_separator' ) );
			add_filter( 'wc_get_price_decimal_separator', array( $this, 'change_decimal_separator' ) );
			add_filter( 'wc_get_price_decimals', array( $this, 'change_number_decimals' ) );
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'conditional_payment_gateways' ), 10, 1 );
			add_action( 'woocommerce_before_mini_cart', array( $this, 'custom_mini_cart_price' ), 10 );

			if ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
				add_filter( 'woocommerce_cart_product_subtotal', array( $this, 'custom_checkout_product_subtotal' ), 10, 4 );
				add_action( 'woocommerce_checkout_before_order_review', array( $this, 'add_notice_checkout_payment_methods' ), 1000 );
				add_filter( 'woocommerce_cart_subtotal', array( $this, 'custom_checkout_order_subtotal' ), 10, 3 );
				add_filter( 'woocommerce_cart_total', array( $this, 'custom_checkout_order_total' ) );
				add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'custom_shipping_fee' ), 10, 2 );
				add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'custom_discount_coupon' ), 10, 3 );
			}
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'add_order_currency_meta' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'custom_cart_item_subtotal' ), 10, 3 );
		}
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'change_format_order_line_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'change_format_order_item_totals' ), 10, 3 );
		add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'get_formatted_order_total' ), 10, 2 );
		add_filter( 'woocommerce_order_subtotal_to_display', array( $this, 'get_formatted_order_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_order_shipping_to_display', array( $this, 'get_formatted_order_shipping' ), 10, 3 );
		add_filter( 'woocommerce_order_discount_to_display', array( $this, 'get_formatted_order_discount' ), 10, 2 );
		add_filter( 'woocommerce_package_rates', array( $this, 'change_shipping_cost' ), 10, 2 );
		add_filter( 'woocommerce_coupon_get_amount', array( $this, 'change_coupon_amount' ), 10, 2 );

		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_currency', array( $this, 'update_currency_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_currency_pos', array( $this, 'update_currency_meta_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_price_thousand_sep', array( $this, 'update_currency_meta_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_price_decimal_sep', array( $this, 'update_currency_meta_option' ), 10, 3 );
		add_filter( 'woocommerce_admin_settings_sanitize_option_woocommerce_price_num_decimals', array( $this, 'update_currency_meta_option' ), 10, 3 );
		add_action( 'current_screen', array( $this, 'get_current_screen' ) );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'custom_subscription_signup_fee_for_cart' ), 10, 1 );
		add_filter( 'woocommerce_stripe_request_body', array( $this, 'custom_stripe_request_total_amount' ), 10, 2 );
		add_filter( 'woocommerce_paypal_args', array( $this, 'custom_request_paypal' ), 10, 2 );
		// custom price for Woocommerce product addon plugin
		add_filter( 'woocommerce_product_addons_option_price_raw', array( $this, 'custom_product_addons_option_price' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'custom_cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'custom_order_meta_fee' ), 10, 3 );
		// Compatible with Table Rate Shipping plugin
		add_filter( 'woocommerce_table_rate_package_row_base_price', array( $this, 'custom_table_rate_shipping_plugin_row_base_price' ), 10, 3 );

		// Display an friendly error message for WooCommerce PayPal Checkout Gateway && WooCommerce PayPal Payments plugin error when turn of Checkout in different currency
		add_filter( 'woocommerce_after_checkout_validation', array( $this, 'handle_woocommerce_paypal_payments_plugin_error' ), 10, 1 );
	}

	public function handle_woocommerce_paypal_payments_plugin_error( $data ) {
		$default_currency = get_option( 'woocommerce_currency' );
		if ( is_checkout() && ( ( 'ppcp-gateway' === $data['payment_method'] ) || ( 'ppec_paypal' === $data['payment_method'] ) ) && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) && $this->apply_currency['currency'] !== $default_currency ) {
			wc_add_notice( __( 'Sorry! This Paypal payment method for ' . $this->apply_currency['currency'] . ' is not supported in your location. Please cancel and start payment again with ' . $default_currency . '.', 'yay-currency' ), 'error' );
		}
		return $data;
	}

	public function custom_table_rate_shipping_plugin_row_base_price( $row_base_price, $_product, $qty ) {
		$row_base_price = $_product->get_data()['price'] * $qty;
		return $row_base_price;
	}

	public function custom_order_meta_fee( $item, $cart_item_key, $values ) {
		if ( ! empty( $values['addons'] ) ) {
			foreach ( $values['addons'] as $addon ) {
				$key = $addon['name'];
				if ( 'percentage_based' !== $addon['price_type'] ) {
					$item_fee            = $addon['price'];
					$format              = $this->format_currency_position( $this->apply_currency['currencyPosition'] );
					$converted_item_fee  = $this->calculate_price_by_currency( $item_fee, true );
					$formatted_item_fee  = wc_price(
						$converted_item_fee,
						array(
							'currency'           => $this->apply_currency['currency'],
							'decimal_separator'  => $this->apply_currency['decimalSeparator'],
							'thousand_separator' => $this->apply_currency['thousandSeparator'],
							'decimals'           => (int) $this->apply_currency['numberDecimal'],
							'price_format'       => $format,
						)
					);
					$item_meta_value     = $item->get_meta_data( $addon['value'] );
					$item_meta_data      = $item_meta_value[0];
					$item_meta_data->key = $key . ' (' . $formatted_item_fee . ')';
				}
			}
		}
	}

	public function custom_cart_item_data( $other_data, $cart_item ) {
		if ( ! empty( $cart_item['addons'] ) ) {
			foreach ( $cart_item['addons'] as $item ) {
				if ( 'percentage_based' !== $item['price_type'] ) {
					$item_fee           = $item['price'];
					$format             = $this->format_currency_position( $this->apply_currency['currencyPosition'] );
					$converted_item_fee = $this->calculate_price_by_currency( $item_fee, true );
					$formatted_item_fee = wc_price(
						$converted_item_fee,
						array(
							'currency'           => $this->apply_currency['currency'],
							'decimal_separator'  => $this->apply_currency['decimalSeparator'],
							'thousand_separator' => $this->apply_currency['thousandSeparator'],
							'decimals'           => (int) $this->apply_currency['numberDecimal'],
							'price_format'       => $format,
						)
					);
					foreach ( $other_data as &$data ) {
						$data['name'] = 'Fee (' . $formatted_item_fee . ')';
					}
					return $other_data;
				}
			}
		}
		return $other_data;
	}

	public function custom_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
		$format   = $this->format_currency_position( $this->apply_currency['currencyPosition'] );
		$subtotal = wc_price(
			$cart_item['line_total'],
			array(
				'currency'           => $this->apply_currency['currency'],
				'decimal_separator'  => $this->apply_currency['decimalSeparator'],
				'thousand_separator' => $this->apply_currency['thousandSeparator'],
				'decimals'           => (int) $this->apply_currency['numberDecimal'],
				'price_format'       => $format,
			)
		);
		return $subtotal;
	}

	public function custom_product_addons_option_price( $price, $option ) {
		if ( 'percentage_based' !== $option['price_type'] ) {
			$price = $this->calculate_price_by_currency( $price );
		}
		return $price;
	}

	public function custom_request_paypal( $args, $order ) {
		if ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
			return $args;
		}
		$args['currency_code'] = $this->apply_currency['currency'];
		return $args;
	}

	public function custom_stripe_request_total_amount( $request, $api ) {

		global $wpdb;
		if ( isset( $request['currency'] ) && isset( $request['metadata'] ) && isset( $request['metadata']['order_id'] ) ) {
			$array_zero_decimal_currencies = array(
				'BIF',
				'CLP',
				'DJF',
				'GNF',
				'JPY',
				'KMF',
				'KRW',
				'MGA',
				'PYG',
				'RWF',
				'UGX',
				'VND',
				'VUV',
				'XAF',
				'XOF',
				'XPF',
			);
			if ( in_array( strtoupper( $request['currency'] ), $array_zero_decimal_currencies ) ) {
				$orderID = $request['metadata']['order_id'];
				$result  = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_value FROM {$wpdb->postmeta} WHERE (post_id = %d AND meta_key = '_order_total')",
						$orderID
					)
				);

				if ( empty( $result ) ) {
					return $request;
				}

				$order_total = $result;

				$request['amount'] = (int) $order_total;
			}
		}
			return $request;
	}

	public function custom_subscription_price_string( $price_string, $product, $args ) {

		if ( is_checkout() ) {
			return $price_string;
		}

		$quantity = 1;

		if ( is_cart() ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {

				$item = $cart_item['data'];

				if ( ! empty( $item ) ) {
						$quantity = $cart_item['quantity'];
				}
			}
		}

		$signup_fee_original = get_post_meta( $product->get_id(), '_subscription_sign_up_fee', true );

		$converted_signup_fee = $this->calculate_price_by_currency( $signup_fee_original, true ) * $quantity;

		$format = $this->format_currency_position( $this->apply_currency['currencyPosition'] );

		$formatted_signup_fee = wc_price(
			$converted_signup_fee,
			array(
				'currency'           => $this->apply_currency['currency'],
				'decimal_separator'  => $this->apply_currency['decimalSeparator'],
				'thousand_separator' => $this->apply_currency['thousandSeparator'],
				'decimals'           => (int) $this->apply_currency['numberDecimal'],
				'price_format'       => $format,
			)
		);

		$custom_sign_up_fee = ( isset( $args['sign_up_fee'] ) && 0 != $signup_fee_original ) ? __( ' and a ' . wp_kses_post( $formatted_signup_fee ) . ' sign-up fee', 'woocommerce' ) : '';

		if ( in_array( $product->get_type(), array( 'variable-subscription' ) ) ) {
			$formatted_price            = wc_price(
				$product->get_price(),
				array(
					'currency'           => $this->apply_currency['currency'],
					'decimal_separator'  => $this->apply_currency['decimalSeparator'],
					'thousand_separator' => $this->apply_currency['thousandSeparator'],
					'decimals'           => (int) $this->apply_currency['numberDecimal'],
					'price_format'       => $format,
				)
			);
			$price_string_no_html       = strip_tags( $price_string );
			$price_string_no_fee_string = substr( $price_string_no_html, 0, strpos( $price_string_no_html, 'and' ) ); // remove default sign-up fee string
			$start_index_to_cut_string  = strpos( $price_string_no_html, ' /' ) ? strpos( $price_string_no_html, ' /' ) : ( strpos( $price_string_no_html, ' every' ) ? strpos( $price_string_no_html, ' every' ) : strpos( $price_string_no_html, ' for' ) );
			$interval_subscrition       = substr( empty( $price_string_no_fee_string ) ? $price_string_no_html : $price_string_no_fee_string, $start_index_to_cut_string ); // get default interval subscrition (ex: /month or every x days...)
			$price_string               = __( 'From: ', 'woocommerce' ) . $formatted_price . $interval_subscrition . $custom_sign_up_fee;
		} else {
			$price_string_no_fee_string = strpos( $price_string, 'and' ) ? substr( $price_string, 0, strpos( $price_string, 'and' ) ) : $price_string; // remove default sign-up fee string
			$price_string               = $price_string_no_fee_string . $custom_sign_up_fee;
		}

		return $price_string;
	}

	public function custom_subscription_signup_fee_for_cart( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		if ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			if ( in_array( $cart_item['data']->get_type(), array( 'subscription', 'subscription_variation' ) ) ) {
				$signup_fee_original  = $cart_item['data']->get_meta( '_subscription_sign_up_fee' );
				$converted_signup_fee = $this->calculate_price_by_currency( $signup_fee_original, true );
				$cart_item['data']->update_meta_data( '_subscription_sign_up_fee', $converted_signup_fee );
			}
		}
	}

	public function get_current_screen() {
		$screen = get_current_screen();
		if ( 'shop_order' === $screen->id ) {
			$order_id = isset( $_GET['post'] ) ? sanitize_key( $_GET['post'] ) : null;
			if ( $order_id ) {
				$order_data                     = wc_get_order( $order_id );
				$yay_currency_checkout_currency = $order_data->get_currency();
				$convert_currency               = array();

				foreach ( $this->converted_currency as $key => $value ) {
					if ( $value['currency'] == $yay_currency_checkout_currency ) {
						$convert_currency = $value;
					}
				}
				if ( $convert_currency ) {
					$this->apply_currency = $convert_currency;
					add_filter( 'woocommerce_product_get_price', array( $this, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_get_sale_price', array( $this, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_get_regular_price', array( $this, 'custom_raw_price' ), 10, 2 );

					add_filter( 'woocommerce_product_variation_get_price', array( $this, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_product_variation_get_sale_price', array( $this, 'custom_raw_price' ), 10, 2 );

					add_filter( 'woocommerce_variation_prices_price', array( $this, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'custom_raw_price' ), 10, 2 );
					add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'custom_variation_price_hash' ) );

					add_filter( 'woocommerce_currency_symbol', array( $this, 'change_existing_currency_symbol' ), 10, 2 );
					add_filter( 'pre_option_woocommerce_currency_pos', array( $this, 'change_currency_position' ) );
					add_filter( 'wc_get_price_thousand_separator', array( $this, 'change_thousand_separator' ) );
					add_filter( 'wc_get_price_decimal_separator', array( $this, 'change_decimal_separator' ) );
					add_filter( 'wc_get_price_decimals', array( $this, 'change_number_decimals' ) );
				}
			}
		}
	}

	public function custom_variation_price_hash( $hash ) {
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$hash[] = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		return $hash;
	}

	public function change_format_order_item_totals( $total_rows, $order, $tax_display ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format = $this->format_currency_position( $convert_currency['currencyPosition'] );

			$fees = $order->get_fees();
			if ( $fees ) {
				foreach ( $fees as $id => $fee ) {
					if ( apply_filters( 'woocommerce_get_order_item_totals_excl_free_fees', empty( $fee['line_total'] ) && empty( $fee['line_tax'] ), $id ) ) {
						continue;
					}
					$total_rows[ 'fee_' . $fee->get_id() ] = array(
						'label' => $fee->get_name() . ':',
						'value' => wc_price(
							'excl' === $tax_display ? $fee->get_total() : $fee->get_total() + $fee->get_total_tax(),
							array(
								'currency'           => $yay_currency_checkout_currency,
								'decimal_separator'  => $convert_currency['decimalSeparator'],
								'thousand_separator' => $convert_currency['thousandSeparator'],
								'decimals'           => (int) $convert_currency['numberDecimal'],
								'price_format'       => $format,
							)
						),
					);
				}
			}

			if ( 'excl' === $tax_display && wc_tax_enabled() ) {
				if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
					foreach ( $order->get_tax_totals() as $code => $tax ) {
						$total_rows[ sanitize_title( $code ) ] = array(
							'label' => $tax->label . ':',
							'value' => $tax->formatted_amount,
						);
					}
				} else {
					$total_rows['tax'] = array(
						'label' => WC()->countries->tax_or_vat() . ':',
						'value' => wc_price(
							$order->get_total_tax(),
							array(
								'currency'           => $yay_currency_checkout_currency,
								'decimal_separator'  => $convert_currency['decimalSeparator'],
								'thousand_separator' => $convert_currency['thousandSeparator'],
								'decimals'           => (int) $convert_currency['numberDecimal'],
								'price_format'       => $format,
							)
						),
					);
				}
			}

			$refunds = $order->get_refunds();
			if ( $refunds ) {
				foreach ( $refunds as $id => $refund ) {
					$total_rows[ 'refund_' . $id ] = array(
						'label' => $refund->get_reason() ? $refund->get_reason() : __( 'Refund', 'woocommerce' ) . ':',
						'value' => wc_price(
							'-' . $refund->get_amount(),
							array(
								'currency'           => $yay_currency_checkout_currency,
								'decimal_separator'  => $convert_currency['decimalSeparator'],
								'thousand_separator' => $convert_currency['thousandSeparator'],
								'decimals'           => (int) $convert_currency['numberDecimal'],
								'price_format'       => $format,
							)
						),
					);
				}
			}
		}
		return $total_rows;
	}

	public function change_format_order_line_subtotal( $subtotal, $item, $order ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			if ( 'excl' === $tax_display ) {
				$ex_tax_label = $order->get_prices_include_tax() ? 1 : 0;

				$subtotal = wc_price(
					$order->get_line_subtotal( $item ),
					array(
						'ex_tax_label'       => $ex_tax_label,
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);
			} else {
				$subtotal = wc_price(
					$order->get_line_subtotal( $item, true ),
					array(
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);
			}
		}
		return $subtotal;
	}

	public function get_formatted_order_total( $formatted_total, $order ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$total            = get_post_meta( $order->get_id(), '_order_total', true );
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format          = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$formatted_total = wc_price(
				$total,
				array(
					'currency'           => $yay_currency_checkout_currency,
					'decimal_separator'  => $convert_currency['decimalSeparator'],
					'thousand_separator' => $convert_currency['thousandSeparator'],
					'decimals'           => (int) $convert_currency['numberDecimal'],
					'price_format'       => $format,
				)
			);
		}
		return $formatted_total;
	}

	protected function get_cart_subtotal_for_order( $order ) {
		return wc_remove_number_precision(
			$order->get_rounded_items_total(
				$this->get_values_for_total( 'subtotal', $order )
			)
		);
	}

	protected function get_values_for_total( $field, $order ) {
		$items = array_map(
			function ( $item ) use ( $field ) {
				return wc_add_number_precision( $item[ $field ], false );
			},
			array_values( $order->get_items() )
		);
		return $items;
	}

	protected static function round_at_subtotal() {
		return 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' );
	}

	protected static function round_line_tax( $value, $in_cents = true ) {
		if ( ! self::round_at_subtotal() ) {
			$value = wc_round_tax_total( $value, $in_cents ? 0 : null );
		}
		return $value;
	}

	public function get_formatted_order_subtotal( $subtotal, $compound, $order ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
			$subtotal    = $this->get_cart_subtotal_for_order( $order );

			if ( ! $compound ) {

				if ( 'incl' === $tax_display ) {
					$subtotal_taxes = 0;
					foreach ( $order->get_items() as $item ) {
						$subtotal_taxes += self::round_line_tax( $item->get_subtotal_tax(), false );
					}
					$subtotal += wc_round_tax_total( $subtotal_taxes );
				}

				$subtotal = wc_price(
					$subtotal,
					array(
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);

				if ( 'excl' === $tax_display && $order->get_prices_include_tax() && wc_tax_enabled() ) {
					$subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			} else {
				if ( 'incl' === $tax_display ) {
					return '';
				}

				$subtotal += $order->get_shipping_total();

				foreach ( $order->get_taxes() as $tax ) {
					if ( $tax->is_compound() ) {
						continue;
					}
					$subtotal = $subtotal + $tax->get_tax_total() + $tax->get_shipping_tax_total();
				}

				$subtotal = $subtotal - $order->get_total_discount();
				$subtotal = wc_price(
					$subtotal,
					array(
						'currency'           => $yay_currency_checkout_currency,
						'decimal_separator'  => $convert_currency['decimalSeparator'],
						'thousand_separator' => $convert_currency['thousandSeparator'],
						'decimals'           => (int) $convert_currency['numberDecimal'],
						'price_format'       => $format,
					)
				);
			}
		}
		return $subtotal;
	}

	public function get_formatted_order_shipping( $shipping, $order, $tax_display ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = $tax_display ? $tax_display : get_option( 'woocommerce_tax_display_cart' );
			if ( 0 < abs( (float) $order->get_shipping_total() ) ) {

				if ( 'excl' === $tax_display ) {
					$shipping = wc_price(
						$order->get_shipping_total(),
						array(
							'currency'           => $yay_currency_checkout_currency,
							'decimal_separator'  => $convert_currency['decimalSeparator'],
							'thousand_separator' => $convert_currency['thousandSeparator'],
							'decimals'           => (int) $convert_currency['numberDecimal'],
							'price_format'       => $format,
						)
					);

					if ( (float) $order->get_shipping_tax() > 0 && $order->get_prices_include_tax() ) {
						$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>', $order, $tax_display );
					}
				} else {
					$shipping = wc_price(
						$order->get_shipping_total() + $order->get_shipping_tax(),
						array(
							'currency'           => $yay_currency_checkout_currency,
							'decimal_separator'  => $convert_currency['decimalSeparator'],
							'thousand_separator' => $convert_currency['thousandSeparator'],
							'decimals'           => (int) $convert_currency['numberDecimal'],
							'price_format'       => $format,
						)
					);

					if ( (float) $order->get_shipping_tax() > 0 && ! $order->get_prices_include_tax() ) {
						$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>', $order, $tax_display );
					}
				}

				/* translators: %s: method */
				$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_shipped_via', '&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $order->get_shipping_method() ) . '</small>', $order );

			} elseif ( $order->get_shipping_method() ) {
				$shipping = $order->get_shipping_method();
			} else {
				$shipping = __( 'Free!', 'woocommerce' );
			}
		}
		return $shipping;
	}

	public function get_formatted_order_discount( $tax_display, $order ) {
		$yay_currency_checkout_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		if ( ! empty( $yay_currency_checkout_currency ) ) {
			$convert_currency = $this->apply_currency;
			foreach ( $this->converted_currency as $key => $value ) {
				if ( $value['currency'] == $yay_currency_checkout_currency ) {
					$convert_currency = $value;
				}
			}
			$format      = $this->format_currency_position( $convert_currency['currencyPosition'] );
			$tax_display = wc_price(
				$order->get_total_discount( 'excl' === $tax_display && 'excl' === get_option( 'woocommerce_tax_display_cart' ) ),
				array(
					'currency'           => $yay_currency_checkout_currency,
					'decimal_separator'  => $convert_currency['decimalSeparator'],
					'thousand_separator' => $convert_currency['thousandSeparator'],
					'decimals'           => (int) $convert_currency['numberDecimal'],
					'price_format'       => $format,
				)
			);
		}
		return $tax_display;
	}

	protected function evaluate_cost( $sum, $args = array() ) {
		if ( ! is_array( $args ) || ! array_key_exists( 'qty', $args ) || ! array_key_exists( 'cost', $args ) ) {
			wc_doing_it_wrong( __FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1' );
		}

		include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';
		$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
		$locale         = localeconv();
		$decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
		$this->fee_cost = $args['cost'];
		add_shortcode( 'fee', array( $this, 'fee' ) );

		$sum = do_shortcode(
			str_replace(
				array(
					'[qty]',
					'[cost]',
				),
				array(
					$args['qty'],
					$args['cost'],
				),
				$sum
			)
		);
		remove_shortcode( 'fee', array( $this, 'fee' ) );
		$sum = preg_replace( '/\s+/', '', $sum );
		$sum = str_replace( $decimals, '.', $sum );
		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );
		return $sum ? \WC_Eval_Math::evaluate( $sum ) : 0;
	}

	public function change_shipping_cost( $methods, $package ) {
		if ( count( array_filter( $methods ) ) ) {
			foreach ( $methods as $key => $method ) {
				if ( 'betrs_shipping' == $method->method_id || 'printful_shipping' == $method->method_id || 'easyship' == $method->method_id ) {
					continue;
				}
				if ( 'flat_rate' == $method->method_id ) {
					$shipping  = new \WC_Shipping_Flat_Rate( $method->instance_id );
					$has_costs = false;
					$cost      = $shipping->get_option( 'cost' );

					if ( '' !== $cost ) {
						$has_costs    = true;
						$rate['cost'] = $this->evaluate_cost(
							$cost,
							array(
								'qty'  => $shipping->get_package_item_qty( $package ),
								'cost' => $package['contents_cost'],
							)
						);
					}

					$shipping_classes = WC()->shipping->get_shipping_classes();

					if ( ! empty( $shipping_classes ) ) {
						$product_shipping_classes = $shipping->find_shipping_classes( $package );
						$shipping_classes_cost    = 0;

						foreach ( $product_shipping_classes as $shipping_class => $products ) {
							$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
							$class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $shipping->get_option( 'class_cost_' . $shipping_class_term->term_id, $shipping->get_option( 'class_cost_' . $shipping_class, '' ) ) : $shipping->get_option( 'no_class_cost', '' );

							if ( '' === $class_cost_string ) {
								continue;
							}

							$has_costs  = true;
							$class_cost = $this->evaluate_cost(
								$class_cost_string,
								array(
									'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
									'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
								)
							);

							if ( 'class' === $shipping->type ) {
								$rate['cost'] += $class_cost;
							} else {
								$shipping_classes_cost = $class_cost > $shipping_classes_cost ? $class_cost : $shipping_classes_cost;
							}
						}

						if ( 'order' === $shipping->type && $shipping_classes_cost ) {
							$rate['cost'] += $shipping_classes_cost;
						}
					}
					if ( $has_costs ) {
						if ( is_checkout() && 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
							$method->set_cost( $rate['cost'] );
						} else {
							$method->set_cost( $this->calculate_price_by_currency( $rate['cost'], true ) );
						}
					}
				} elseif ( 'table_rate' === $method->method_id ) {

					if ( ( is_checkout() ) && 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
						return $methods;
					}

					$method->cost = $this->calculate_price_by_currency( $method->cost, true );
					return $methods;
				} elseif ( 'per_product' === $method->method_id ) {

					if ( ( is_checkout() ) && 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
						return $methods;
					}

					$method->cost = $this->calculate_price_by_currency( $method->cost, true );
					return $methods;
				} else {
					$data = get_option( 'woocommerce_' . $method->method_id . '_' . $method->instance_id . '_settings' );
					$method->set_cost( isset( $data['cost'] ) ? $this->calculate_price_by_currency( $data['cost'], true ) : $this->calculate_price_by_currency( $method->get_cost(), true ) );
				}

				if ( count( $method->get_taxes() ) ) {
					$tax_new = array();
					foreach ( $method->get_taxes() as $key => $tax ) {
						$tax_new[ $key ] = $this->calculate_price_by_currency( $tax, true );
					}
					$method->set_taxes( $tax_new );
				}
			}
		}

		return $methods;
	}

	public function change_coupon_amount( $price, $coupon ) {
		if ( $coupon->is_type( array( 'percent' ) ) ) {
			return $price;
		}
		if ( is_checkout() && 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
			return $price;
		}
		// Coupon type != 'percent' calculate price
		// if ( is_checkout() ) {
			$converted_coupon_price = $this->calculate_price_by_currency( $price, true );
			return $converted_coupon_price;
		// }

		// return $price;
	}

	public function get_symbol_by_currency( $currency_name ) {
		foreach ( $this->converted_currency as $key => $currency ) {
			if ( $currency['currency'] == $currency_name ) {
				return $currency['symbol'];
			}
		}
	}

	public function update_currency_option( $value, $option, $raw_value ) {
		$post_type_args = array(
			'numberposts' => -1,
			'post_type'   => 'yay-currency-manage',
			'post_status' => 'publish',
			'order'       => 'ASC',
		);

		$currencies = get_posts( $post_type_args );
		$flag       = false;
		if ( $currencies ) {
			foreach ( $currencies as $currency ) {
				if ( $currency->post_title == $value ) {
					$flag = true;
					break;
				}
			}
		}
		if ( count( $currencies ) < 3 || true == $flag ) {
			$this->currency_update = $value;
			$currency_update       = get_page_by_title( $value, OBJECT, 'yay-currency-manage' );
			if ( empty( $currency_update ) ) {
				$new_currency    = array(
					'post_title'  => $value,
					'post_type'   => 'yay-currency-manage',
					'post_status' => 'publish',
				);
				$new_currency_ID = wp_insert_post( $new_currency );
				if ( $new_currency_ID ) {
					update_post_meta( $new_currency_ID, 'rate', '1' );
					update_post_meta( $new_currency_ID, 'rate_type', 'auto' );
					update_post_meta(
						$new_currency_ID,
						'fee',
						array(
							'value' => '0',
							'type'  => 'fixed',
						)
					);
					update_post_meta( $new_currency_ID, 'status', '1' );
					update_post_meta( $new_currency_ID, 'payment_methods', array( 'all' ) );
					update_post_meta( $new_currency_ID, 'countries', array( 'default' ) );
					update_post_meta( $new_currency_ID, 'rounding_type', 'disabled' );
					update_post_meta( $new_currency_ID, 'rounding_value', 1 );
					update_post_meta( $new_currency_ID, 'subtract_amount', 0 );
				}
			} else {
				update_post_meta( $currency_update->ID, 'rate', '1' );
				update_post_meta(
					$currency_update->ID,
					'fee',
					array(
						'value' => '0',
						'type'  => get_post_meta(
							$currency_update->ID,
							'fee'
						)[0]['type'],
					)
				);
			}
			$this->update_exchange_rate_currency( $currencies, $value );
		} else {
			$this->currency_update = get_woocommerce_currency();
			$value                 = get_woocommerce_currency();
			add_action( 'admin_notices', array( $this, 'show_notice_notification' ) );
		}
		return $value;
	}


	public function show_notice_notification() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'You\'re using the maximum number of currencies in the YayCurrency lite version. Please delete one of them so that you can add another.', 'yay-currency' ); ?></p>
		</div>
		<?php
	}


	public function update_currency_meta_option( $value, $option, $raw_value ) {
		if ( null != $this->currency_update ) {
			$currency_update = get_page_by_title( $this->currency_update, OBJECT, 'yay-currency-manage' );
			if ( $currency_update ) {
				if ( 'woocommerce_currency_pos' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'currency_position', $value );
				}
				if ( 'woocommerce_price_thousand_sep' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'thousand_separator', $value );
				}
				if ( 'woocommerce_price_decimal_sep' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'decimal_separator', $value );
				}
				if ( 'woocommerce_price_num_decimals' == $option['id'] ) {
					update_post_meta( $currency_update->ID, 'number_decimal', $value );
				}
			}
		}

		return $value;
	}

	public function update_exchange_rate_currency( $currencies, $value ) {
		if ( '' != $value ) {

			if ( $currencies ) {
				foreach ( $currencies as $currency ) {
					if ( $currency->post_title !== $value ) {
						$currency_params_template = array(
							'$src'  => $value,
							'$dest' => $currency->post_title,
						);
						$url_template             = 'https://query1.finance.yahoo.com/v8/finance/chart/$src$dest=X?interval=2m';
						$url                      = strtr( $url_template, $currency_params_template );
						$json_data                = wp_remote_get( $url );
						if ( 200 !== $json_data['response']['code'] ) {
							update_post_meta( $currency->ID, 'rate', 'N/A' );
							continue;
						}
						$decoded_json_data = json_decode( $json_data['body'] );
						update_post_meta( $currency->ID, 'rate', $decoded_json_data->chart->result ? $decoded_json_data->chart->result[0]->meta->previousClose : 'N/A' );
					} else {
						update_post_meta( $currency->ID, 'rate', 1 );
					}
				}
			}
		}
	}

	public function custom_raw_price( $price, $product ) {
		if ( ( is_checkout() ) && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return $price;
		}
		if ( is_null( $this->apply_currency ) ) {
			return $price;
		}
		if ( empty( $price ) ) {
			return $price;
		}
		// Fix for manual renewal subscription product and still keep old code works well
		if ( is_checkout() || is_cart() ) {
			$product_price = $product->get_data()['price'];
			$price         = $this->calculate_price_by_currency( $product_price );
			return $price;
		}

		$price = $this->calculate_price_by_currency( $price );
		return $price;
	}

	public function get_currency_by_ID( $currency_ID ) {
		$currency = get_post( $currency_ID );
		if ( empty( $currency ) ) {
			return null;
		}
		$currency_meta      = get_post_meta( $currency_ID, '', true );
		$converted_currency = array(
			'ID'                => $currency->ID,
			'currency'          => $currency->post_title,
			'currencyPosition'  => $currency_meta['currency_position'][0],
			'thousandSeparator' => $currency_meta['thousand_separator'][0],
			'decimalSeparator'  => $currency_meta['decimal_separator'][0],
			'numberDecimal'     => $currency_meta['number_decimal'][0],
			'roundingType'      => $currency_meta['rounding_type'][0],
			'roundingValue'     => $currency_meta['rounding_value'][0],
			'subtractAmount'    => $currency_meta['subtract_amount'][0],
			'rate'              => $currency_meta['rate'][0],
			'fee'               => maybe_unserialize( $currency_meta['fee'][0] ),
			'status'            => $currency_meta['status'][0],
			'paymentMethods'    => maybe_unserialize( $currency_meta['payment_methods'][0] ),
			'countries'         => maybe_unserialize( $currency_meta['countries'][0] ),
			'symbol'            => get_woocommerce_currency_symbol( $currency->post_title ),
		);
		return $converted_currency;
	}

	public function get_currencies_post_type() {
		$post_type_args = array(
			'numberposts' => -1,
			'post_type'   => 'yay-currency-manage',
			'post_status' => 'publish',
			'order'       => 'ASC',
			'orderby'     => 'menu_order',
		);

		$currencies = get_posts( $post_type_args );

		return $currencies;
	}

	public function get_current_and_default_currency() {
		$current_currency_ID   = $this->apply_currency['ID'];
		$current_currency      = $this->get_currency_by_ID( $current_currency_ID );
		$default_currency_code = get_option( 'woocommerce_currency' );
		$default_currency      = null;

		foreach ( $this->converted_currency as $currency ) {
			if ( $currency['currency'] === $default_currency_code ) {
				$default_currency = $currency;
				break;
			}
		}
		return array(
			'current_currency' => $current_currency,
			'default_currency' => $default_currency,
		);
	}

	public function add_notice_checkout_payment_methods() {
		$currencies_data = $this->get_current_and_default_currency();
		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			// only for admin
			echo "<div class='yay-currency-checkout-notice'><span>" . esc_html__( 'The current payment method for ', 'yay-currency' ) . '<strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['current_currency']['currency'], 'yay-currency' ) ) ) . '</strong></span><span>' . esc_html__( ' is not supported in your location. ', 'yay-currency' ) . '</span><span>' . esc_html__( 'So your payment will be recorded in ', 'yay-currency' ) . '</span><strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['default_currency']['currency'], 'yay-currency' ) ) ) . '.</strong></span></div>';
			echo "<div class='yay-currency-checkout-notice-admin'><span>" . esc_html__( 'Are you the admin? You can change the checkout options for payment methods ', 'yay-currency' ) . '<a href=' . esc_url( admin_url( '/admin.php?page=yay_currency&tabID=1' ) ) . '>' . esc_html__( 'here', 'yay-currency' ) . '</a>.</span><br><span><i>' . esc_html__( '(Only logged in admin can see this.)', 'yay-currency' ) . '</i></span></div>';
		} else {
			echo "<div class='yay-currency-checkout-notice user'><span>" . esc_html__( 'The current payment method for ', 'yay-currency' ) . '<strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['current_currency']['currency'], 'yay-currency' ) ) ) . '</strong></span><span>' . esc_html__( ' is not supported in your location. ', 'yay-currency' ) . '</span><span>' . esc_html__( 'So your payment will be recorded in ', 'yay-currency' ) . '</span><strong>' . wp_kses_post( html_entity_decode( esc_html__( $currencies_data['default_currency']['currency'], 'yay-currency' ) ) ) . '.</strong></span></div>';
		}
	}

	public function calculate_price_by_currency( $price, $exclude = false ) {
		if ( 'percentage' === $this->apply_currency['fee']['type'] ) {
			$rate_after_fee = (float) $this->apply_currency['rate'] + ( (float) $this->apply_currency['rate'] * ( (float) $this->apply_currency['fee']['value'] / 100 ) );
		} else {
			$rate_after_fee = (float) $this->apply_currency['rate'] + (float) $this->apply_currency['fee']['value'];
		}
		$price = ( (float) $price * $rate_after_fee );

		if ( $exclude ) {
			return $price;
		}

		if ( 'disabled' !== $this->apply_currency['roundingType'] ) {

			$rounding_type   = $this->apply_currency['roundingType'];
			$rounding_value  = $this->apply_currency['roundingValue'];
			$subtract_amount = $this->apply_currency['subtractAmount'];

			switch ( $rounding_type ) {
				case 'up':
					$price = ceil( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					return $price;
				case 'down':
					$price = floor( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					return $price;
				case 'nearest':
					$price = round( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					return $price;
				default:
					return;
			}
		}
		return $price;
	}

	public function calculate_price_by_currency_html( $currency, $original_price, $quantity = 1 ) {
		$price = $original_price * $quantity * $currency['rate'];

		if ( 'disabled' !== $currency['roundingType'] ) {

			$rounding_type   = $currency['roundingType'];
			$rounding_value  = $currency['roundingValue'];
			$subtract_amount = $currency['subtractAmount'];

			switch ( $rounding_type ) {
				case 'up':
					$price = ceil( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					break;
				case 'down':
					$price = floor( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					break;
				case 'nearest':
					$price = round( $price / $rounding_value ) * $rounding_value - $subtract_amount;
					break;
				default:
					return;
			}
		}

		$formatted_price            = number_format( $price, $currency['numberDecimal'], $currency['decimalSeparator'], $currency['thousandSeparator'] );
		$currency_symbol            = $currency['symbol'];
		$is_currency_position_right = strpos( $currency['currencyPosition'], 'right' );

		if ( 0 === $is_currency_position_right ) {
			$price = $formatted_price . $currency_symbol;
		} else {
			$price = $currency_symbol . $formatted_price;
		}
		return wp_kses_post( html_entity_decode( $price ) );
	}

	public function custom_checkout_product_subtotal( $product_subtotal, $product, $quantity, $cart ) {
		$currencies_data = $this->get_current_and_default_currency();
		$product_price   = $product->get_price();

		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] || is_cart() ) {
			$format           = $this->format_currency_position( $currencies_data['current_currency']['currencyPosition'] );
			$product_subtotal = wc_price(
				$product_price,
				array(
					'currency'           => $currencies_data['current_currency']['currency'],
					'decimal_separator'  => $currencies_data['current_currency']['decimalSeparator'],
					'thousand_separator' => $currencies_data['current_currency']['thousandSeparator'],
					'decimals'           => (int) $currencies_data['current_currency']['numberDecimal'],
					'price_format'       => $format,
				)
			);
			return $product_subtotal;
		}
		if ( is_checkout() ) {
			remove_filter( 'woocommerce_cart_item_subtotal', array( $this, 'custom_cart_item_subtotal' ) );
			$original_product_subtotal  = $this->calculate_price_by_currency_html( $currencies_data['default_currency'], $product_price, $quantity );
			$converted_product_subtotal = $this->calculate_price_by_currency_html( $currencies_data['current_currency'], $product_price, $quantity );
			$product_subtotal           = $original_product_subtotal . ' (~' . $converted_product_subtotal . ')';
			return $product_subtotal;
		}

		return $product_subtotal;
	}

	public function custom_checkout_order_subtotal( $price ) {
		$currencies_data = $this->get_current_and_default_currency();
		$subtotal_price  = WC()->cart->get_displayed_subtotal();

		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
			$format   = $this->format_currency_position( $currencies_data['current_currency']['currencyPosition'] );
			$subtotal = wc_price(
				$subtotal_price,
				array(
					'currency'           => $currencies_data['current_currency']['currency'],
					'decimal_separator'  => $currencies_data['current_currency']['decimalSeparator'],
					'thousand_separator' => $currencies_data['current_currency']['thousandSeparator'],
					'decimals'           => (int) $currencies_data['current_currency']['numberDecimal'],
					'price_format'       => $format,
				)
			);
			return $subtotal;
		}
		if ( is_checkout() ) {
			$original_subtotal  = $this->calculate_price_by_currency_html( $currencies_data['default_currency'], $subtotal_price );
			$converted_subtotal = $this->calculate_price_by_currency_html( $currencies_data['current_currency'], $subtotal_price );
			$subtotal           = $original_subtotal . ' (~' . $converted_subtotal . ')';
			return $subtotal;
		}
		return $price;
	}

	public function custom_checkout_order_total( $price ) {
		$currencies_data = $this->get_current_and_default_currency();
		$total_price     = WC()->cart->total;
		$format          = $this->format_currency_position( $currencies_data['current_currency']['currencyPosition'] );

		if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
			$total = wc_price(
				$total_price,
				array(
					'currency'           => $currencies_data['current_currency']['currency'],
					'decimal_separator'  => $currencies_data['current_currency']['decimalSeparator'],
					'thousand_separator' => $currencies_data['current_currency']['thousandSeparator'],
					'decimals'           => (int) $currencies_data['current_currency']['numberDecimal'],
					'price_format'       => $format,
				)
			);
			return $total;
		}

		if ( is_checkout() ) {
			$original_total  = $this->calculate_price_by_currency_html( $currencies_data['default_currency'], $total_price );
			$converted_total = $this->calculate_price_by_currency_html( $currencies_data['current_currency'], $total_price );
			$total           = $original_total . ' (~' . $converted_total . ')';
			return $total;
		}
		return $price;
	}

	public function custom_shipping_fee( $label, $method ) {
		if ( is_checkout() ) {
			$currencies_data = $this->get_current_and_default_currency();
			if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
				return $label;
			}
			$shipping_fee           = (float) $method->cost;
			$format                 = $this->format_currency_position( $this->apply_currency['currencyPosition'] );
			$converted_shipping_fee = $this->calculate_price_by_currency( $shipping_fee, true );
			$formatted_shipping_fee = wc_price(
				$converted_shipping_fee,
				array(
					'currency'           => $this->apply_currency['currency'],
					'decimal_separator'  => $this->apply_currency['decimalSeparator'],
					'thousand_separator' => $this->apply_currency['thousandSeparator'],
					'decimals'           => (int) $this->apply_currency['numberDecimal'],
					'price_format'       => $format,
				)
			);
				$label             .= ' (~' . $formatted_shipping_fee . ')';
				return $label;
		}
		return $label;
	}

	public function custom_discount_coupon( $coupon_html, $coupon, $discount_amount_html ) {

		if ( is_checkout() ) {

			$currencies_data = $this->get_current_and_default_currency();

			if ( $currencies_data['current_currency']['currency'] == $currencies_data['default_currency']['currency'] ) {
				return $coupon_html;
			}

			$discount_amount           = (float) $coupon->get_amount();
			$format                    = $this->format_currency_position( $this->apply_currency['currencyPosition'] );
			$converted_discount_amount = $this->calculate_price_by_currency( $discount_amount, true );
			$formatted_discount_amount = wc_price(
				$converted_discount_amount,
				array(
					'currency'           => $this->apply_currency['currency'],
					'decimal_separator'  => $this->apply_currency['decimalSeparator'],
					'thousand_separator' => $this->apply_currency['thousandSeparator'],
					'decimals'           => (int) $this->apply_currency['numberDecimal'],
					'price_format'       => $format,
				)
			);

			$custom_discount_amount_html = $discount_amount_html . ' (~' . $formatted_discount_amount . ')';
			$coupon_html                 = str_replace( $discount_amount_html, $custom_discount_amount_html, $coupon_html );

			return $coupon_html;
		}

		return $coupon_html;
	}

	public function custom_mini_cart_price() {
		if ( is_checkout() && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return false;
		}
		if ( is_cart() || is_checkout() ) {
			return false;
		}
		WC()->cart->calculate_totals();
		do_action( 'woocommerce_widget_shopping_cart_total' );
	}

	public function change_woocommerce_currency( $currency ) {
		if ( is_checkout() && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return $currency;
		}
		$currency = $this->apply_currency['currency'];
		return $currency;
	}

	public function change_existing_currency_symbol( $currency_symbol, $currency ) {
		if ( is_checkout() && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return $currency_symbol;
		}
		if ( is_null( $this->apply_currency ) ) {
			return $currency_symbol;
		}
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return $currency_symbol;
		}

		$currency_symbol = $currency === $this->apply_currency['currency'] ? $this->apply_currency['symbol'] : $this->get_symbol_by_currency( $currency );
		return wp_kses_post( html_entity_decode( $currency_symbol ) );
	}

	public function change_currency_position() {
		if ( is_null( $this->apply_currency ) ) {
			return false;
		}
		return $this->apply_currency['currencyPosition'];
	}

	public function change_thousand_separator() {
		if ( is_null( $this->apply_currency ) ) {
			return;
		}
		return wp_kses_post( html_entity_decode( $this->apply_currency['thousandSeparator'] ) );
	}

	public function change_decimal_separator() {
		if ( is_null( $this->apply_currency ) ) {
			return;
		}
		return wp_kses_post( html_entity_decode( $this->apply_currency['decimalSeparator'] ) );
	}

	public function change_number_decimals() {
		if ( is_null( $this->apply_currency ) ) {
			return;
		}
		return wp_kses_post( html_entity_decode( $this->apply_currency['numberDecimal'] ) );
	}

	public function conditional_payment_gateways( $available_gateways ) {
		$total_price = WC()->cart ? WC()->cart->total : 0;
		apply_filters( 'order_in_default_price', $total_price );

		if ( is_checkout() && ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) ) {
			return $available_gateways;
		}
		if ( is_null( $this->apply_currency ) ) {
			return;
		} else {
			if ( array( 'all' ) === $this->apply_currency['paymentMethods'] ) {
				return $available_gateways;
			}
			$allowed_payment_methods = $this->apply_currency['paymentMethods'];
			$filtered                = array_filter(
				$available_gateways,
				function ( $key ) use ( $allowed_payment_methods ) {
					return in_array( $key, $allowed_payment_methods );
				},
				ARRAY_FILTER_USE_KEY
			);
			$available_gateways      = $filtered;
			return $available_gateways;
		}
	}

	public function add_order_currency_meta( $order_id, $data ) {
		if ( 0 == $this->is_checkout_different_currency || 0 == $this->apply_currency['status'] ) {
			return;
		}
		$order_data            = wc_get_order( $order_id );
		$order_total           = $order_data->get_total();
		$currency_rate         = $this->apply_currency['rate'];
		$currency_fee          = 'percentage' === $this->apply_currency['fee']['type'] ? ( $order_total ) / ( $this->apply_currency['fee']['value'] / 100 ) : $this->apply_currency['fee']['value'];
		$converted_order_total = ( ( $order_total - $currency_fee ) / $currency_rate );
		update_post_meta( $order_id, '_order_currency', $this->apply_currency['currency'] );
		update_post_meta( $order_id, 'yay_currency_checkout_original_total', $converted_order_total );
	}
	public function format_currency_position( $currency_position ) {
		$format = '%1$s%2$s';
		switch ( $currency_position ) {
			case 'left':
				$format = '%1$s%2$s';
				break;
			case 'right':
				$format = '%2$s%1$s';
				break;
			case 'left_space':
				$format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space':
				$format = '%2$s&nbsp;%1$s';
				break;
		}
		return $format;
	}

	public function set_cookies() {
		$cookie_name  = 'yay_currency_widget';
		$cookie_value = $this->apply_currency['ID'];
		setcookie( $cookie_name, $cookie_value, time() + ( 86400 * 30 ), '/' );
		$_COOKIE[ $cookie_name ] = $cookie_value;
	}
}
