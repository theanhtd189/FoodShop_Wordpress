<?php

namespace Yay_Currency;

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;

defined( 'ABSPATH' ) || exit;
class Shortcode {

	protected static $instance = null;

	public $apply_currencies = array();

	public $all_currencies = array();

	public $selected_currency_ID = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	private function doHooks() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		$post_type_args = array(
			'numberposts' => -1,
			'post_type'   => 'yay-currency-manage',
			'post_status' => 'publish',
			'order'       => 'ASC',
			'orderby'     => 'menu_order',
		);

		$this->apply_currencies = get_posts( $post_type_args );

		$this->all_currencies        = get_woocommerce_currencies();
		$this->all_currencies['USD'] = 'United States dollar';

		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}

		add_shortcode( 'yaycurrency-switcher', array( $this, 'currency_dropdown_shortcode' ) );
	}

	public function currency_dropdown_shortcode( $content = null ) {
		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {

			if ( isset( $_POST['currency'] ) ) {
				$this->selected_currency_ID = sanitize_key( wp_unslash( $_POST['currency'] ) );
			}
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		$selected_currency_args              = array(
			'p'         => (int) $this->selected_currency_ID,
			'post_type' => 'yay-currency-manage',
		);
		$selected_currency_query_result      = new \WP_Query( $selected_currency_args );
		$selected_currency_info              = $selected_currency_query_result->post;
		$woo_currency                        = WooCommerceCurrency::getInstance();
		$settings_data                       = Settings::getInstance();
		$is_show_flag_in_switcher            = get_option( 'yay_currency_show_flag_in_switcher', 1 );
		$is_show_currency_name_in_switcher   = get_option( 'yay_currency_show_currency_name_in_switcher', 1 );
		$is_show_currency_symbol_in_switcher = get_option( 'yay_currency_show_currency_symbol_in_switcher', 1 );
		$is_show_currency_code_in_switcher   = get_option( 'yay_currency_show_currency_code_in_switcher', 1 );
		$switcher_size                       = get_option( 'yay_currency_switcher_size', 'medium' );

		$no_currency_name_class                 = ! $is_show_currency_name_in_switcher ? ' no-currency-name' : '';
		$only_currency_name_class               = $is_show_currency_name_in_switcher && ! $is_show_flag_in_switcher && ! $is_show_currency_symbol_in_switcher && ! $is_show_currency_code_in_switcher ? ' only-currency-name' : '';
		$only_currency_name_and_something_class = $is_show_currency_name_in_switcher && 2 === Helper::count_display_elements_in_switcher( $is_show_flag_in_switcher, $is_show_currency_name_in_switcher, $is_show_currency_symbol_in_switcher, $is_show_currency_code_in_switcher ) ? ' only-currency-name-and-something' : '';
		$yay_currency_nonce                     = wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce', true, false );
		$file                                   = YAY_CURRENCY_PLUGIN_DIR . 'includes/templates/ShortCodeTemplate.php';
		ob_start();
		require $file;
		$content = ob_get_clean();
		return $content;
	}
}
