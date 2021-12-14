<?php

namespace Yay_Currency;

use WP_Query;
use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;

defined( 'ABSPATH' ) || exit;

class SingleProductDropdown {

	private static $instance = null;

	public $apply_currencies = array();

	public $all_currencies = array();

	public $selected_currency_ID = null;

	public function __construct() {     }

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
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

		$is_show_on_single_product_page = get_option( 'yay_currency_show_single_product_page', 1 );

		if ( $is_show_on_single_product_page ) {
			add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'dropdown_price_in_different_currency' ) );
		}
	}

	public function dropdown_price_in_different_currency() {
		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {
			if ( isset( $_POST['currency'] ) ) {
				$this->selected_currency_ID = sanitize_text_field( $_POST['currency'] );
			}
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		$selected_currency_args              = array(
			'p'         => (int) $this->selected_currency_ID,
			'post_type' => 'yay-currency-manage',
		);
		$selected_currency_query_result      = new WP_Query( $selected_currency_args );
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

		?>
	<div class='yay-currency-single-page-switcher'>
	  <span><?php echo esc_html_e( 'Currency:', 'yay-currency' ); ?></span>
	  <form method='POST' action='' class='yay-currency-form-switcher'>
		<?php wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce' ); ?>
			<select class='yay-currency-switcher' name='currency' onchange='this.form.submit()'>
				<?php
				foreach ( $this->apply_currencies as $currency ) {
					$currency_code = $is_show_currency_code_in_switcher ? $currency->post_title : null;
					?>
					<option value='<?php echo esc_attr( $currency->ID ); ?>' 
												<?php
												if ( $currency->ID == $this->selected_currency_ID ) {
													echo 'selected';}
												?>
					></option>
					<?php
				}
				$selected_country_code = null;
				$selected_html_flag    = null;
				if ( $is_show_flag_in_switcher ) {
					$selected_country_code = $settings_data->currency_code_by_country_code[ $selected_currency_info->post_title ];
					$selected_flag_url     = Helper::get_flag_by_country_code( $selected_country_code );
					$selected_html_flag    = '<span style="background-image: url(' . $selected_flag_url . ')" class="yay-currency-flag selected ' . $switcher_size . '" data-country_code="' . $selected_country_code . '"></span>';
				}
				$selected_currency_name   = $is_show_currency_name_in_switcher ? $this->all_currencies[ $selected_currency_info->post_title ] : null;
				$selected_currency_symbol = $is_show_currency_symbol_in_switcher ? ( $is_show_currency_name_in_switcher ? ' (' . $woo_currency->get_symbol_by_currency( $selected_currency_info->post_title ) . ')' : $woo_currency->get_symbol_by_currency( $selected_currency_info->post_title ) . ' ' ) : null;
				$hyphen                   = ( $is_show_currency_name_in_switcher && $is_show_currency_code_in_switcher ) ? ' - ' : null;
				$selected_currency_code   = $is_show_currency_code_in_switcher ? $selected_currency_info->post_title : null;
				?>
			</select>
	  </form>
	  <div class="yay-currency-custom-select-wrapper 
		<?php
		echo esc_attr( $switcher_size );
		echo esc_attr( $no_currency_name_class );
		echo esc_attr( $only_currency_name_class );
		echo esc_attr( $only_currency_name_and_something_class );
		?>
		">
				<div class="yay-currency-custom-select">
					<div class="yay-currency-custom-select__trigger 
						<?php
						echo esc_attr( $switcher_size );
						?>
					">
						<div class="yay-currency-custom-selected-option">
						<?php echo wp_kses_post( $selected_html_flag ); ?>
						<span class="yay-currency-selected-option">
						<?php
						echo wp_kses_post(
							html_entity_decode(
								esc_html__( $selected_currency_name, 'yay-currency' ) . esc_html__( $selected_currency_symbol, 'yay-currency' ) . esc_html( $hyphen ) . esc_html__(
									$selected_currency_code,
									'yay-currency'
								)
							)
						);
						?>
						</span>
						</div>
						<div class="yay-currency-custom-arrow"></div>
					</div>
					<ul class="yay-currency-custom-options">
						<?php
						$country_code = null;
						$html_flag    = null;
						foreach ( $this->apply_currencies as $currency ) {
							if ( $is_show_flag_in_switcher ) {
								$country_code = $settings_data->currency_code_by_country_code[ $currency->post_title ];
								$flag_url     = Helper::get_flag_by_country_code( $country_code );
								$html_flag    = '<span style="background-image: url(' . $flag_url . ')" class="yay-currency-flag ' . $switcher_size . '" data-country_code="' . $country_code . '"></span>';
							}
							$currency_name   = $is_show_currency_name_in_switcher ? $this->all_currencies[ $currency->post_title ] : null;
							$currency_symbol = $is_show_currency_symbol_in_switcher ? ( $is_show_currency_name_in_switcher ? ' (' . $woo_currency->get_symbol_by_currency( $currency->post_title ) . ')' : $woo_currency->get_symbol_by_currency( $currency->post_title ) . ' ' ) : null;
							$hyphen          = ( $is_show_currency_name_in_switcher && $is_show_currency_code_in_switcher ) ? ' - ' : null;
							$currency_code   = $is_show_currency_code_in_switcher ? $currency->post_title : null;
							?>
						<li class="yay-currency-custom-option-row <?php echo $currency->ID == $this->selected_currency_ID ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $currency->ID ); ?>">
							<?php echo wp_kses_post( $html_flag ); ?>
							<div class="yay-currency-custom-option <?php echo esc_attr( $switcher_size ); ?>">
									<?php
									echo wp_kses_post(
										html_entity_decode(
											esc_html__( $currency_name, 'yay-currency' ) . esc_html__( $currency_symbol, 'yay-currency' ) . esc_html( $hyphen ) . esc_html__(
												$currency_code,
												'yay-currency'
											)
										)
									);
									?>
							</div>
						</li>
					<?php } ?>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}
}
