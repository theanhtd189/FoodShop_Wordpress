<?php

namespace Yay_Currency;

use Yay_Currency\WooCommerceCurrency;
use Yay_Currency\Settings;

defined( 'ABSPATH' ) || exit;

use WP_Widget;

class Widget extends WP_Widget {


	private static $instance = null;

	public $widget_ID;

	public $widget_name;

	public $widget_options = array();

	public $control_options = array();

	public $apply_currencies = array();

	public $all_currencies = array();

	public $selected_currency_ID = null;

	public $country_info;

	public function __construct() {     }

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public function doHooks() {
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

		$this->widget_ID = 'yay_currency_widget';

		$this->widget_name = 'Currency Switcher - YayCurrency';

		$this->widget_options = array(
			'classname'                   => $this->widget_ID,
			'description'                 => $this->widget_name,
			'customize_selective_refresh' => true,
		);

		$this->control_options = array(
			'width'  => 300,
			'height' => 350,
		);
		parent::__construct( $this->widget_ID, $this->widget_name, $this->widget_options, $this->control_options );

		add_action( 'widgets_init', array( $this, 'widgetsInit' ) );
	}

	public function widgetsInit() {
		 register_widget( $this );
	}

	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );

		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {

			if ( isset( $_POST['currency'] ) ) {
				$this->selected_currency_ID = sanitize_text_field( $_POST['currency'] );
			}
		}
		if ( isset( $_COOKIE['yay_currency_widget'] ) ) {
			$this->selected_currency_ID = sanitize_key( $_COOKIE['yay_currency_widget'] );
		}
		$selected_currency_args         = array(
			'p'         => (int) $this->selected_currency_ID,
			'post_type' => 'yay-currency-manage',
		);
		$selected_currency_query_result = new \WP_Query( $selected_currency_args );
		$selected_currency_info         = $selected_currency_query_result->post;
		$woo_currency                   = WooCommerceCurrency::getInstance();
		$settings_data                  = Settings::getInstance();

		$is_show_flag_in_widget            = get_option( 'yay_currency_show_flag_in_widget', 1 );
		$is_show_currency_name_in_widget   = get_option( 'yay_currency_show_currency_name_in_widget', 1 );
		$is_show_currency_symbol_in_widget = get_option( 'yay_currency_show_currency_symbol_in_widget', 1 );
		$is_show_currency_code_in_widget   = get_option( 'yay_currency_show_currency_code_in_widget', 1 );
		$widget_size                       = get_option( 'yay_currency_widget_size', 'small' );

		$no_currency_name_class                 = ! $is_show_currency_name_in_widget ? ' no-currency-name' : '';
		$only_currency_name_class               = $is_show_currency_name_in_widget && ! $is_show_flag_in_widget && ! $is_show_currency_symbol_in_widget && ! $is_show_currency_code_in_widget ? ' only-currency-name' : '';
		$only_currency_name_and_something_class = $is_show_currency_name_in_widget && 2 === Helper::count_display_elements_in_switcher( $is_show_flag_in_widget, $is_show_currency_name_in_widget, $is_show_currency_symbol_in_widget, $is_show_currency_code_in_widget ) ? ' only-currency-name-and-something' : '';
		?>
	<div class='yay-currency-widget-switcher'>
		<h4><?php echo esc_html_e( 'Currency Switcher', 'yay-currency' ); ?></h4>
		<form method='POST' action='' class='yay-currency-form-switcher'>
		<?php wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce' ); ?>
			<select class='yay-currency-switcher' name='currency' onchange='this.form.submit()'>
			<?php
			foreach ( $this->apply_currencies as $currency ) {
				$currency_code = $is_show_currency_code_in_widget ? $currency->post_title : null;
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
			if ( $is_show_flag_in_widget ) {
				$selected_country_code = $settings_data->currency_code_by_country_code[ $selected_currency_info->post_title ];
				$selected_flag_url     = Helper::get_flag_by_country_code( $selected_country_code );
				$selected_html_flag    = '<span style="background-image: url(' . $selected_flag_url . ')" class="yay-currency-flag selected ' . $widget_size . '" data-country_code="' . $selected_country_code . '"></span>';
			}
			$selected_currency_name   = $is_show_currency_name_in_widget ? $this->all_currencies[ $selected_currency_info->post_title ] : null;
			$selected_currency_symbol = $is_show_currency_symbol_in_widget ? ( $is_show_currency_name_in_widget ? ' (' . $woo_currency->get_symbol_by_currency( $selected_currency_info->post_title ) . ')' : $woo_currency->get_symbol_by_currency( $selected_currency_info->post_title ) . ' ' ) : null;
			$hyphen                   = ( $is_show_currency_name_in_widget && $is_show_currency_code_in_widget ) ? ' - ' : null;
			$selected_currency_code   = $is_show_currency_code_in_widget ? $selected_currency_info->post_title : null;
			?>
		</select>
	  </form>
	  <div class="yay-currency-custom-select-wrapper widget
		<?php
			echo esc_attr( $widget_size );
			echo esc_attr( $no_currency_name_class );
			echo esc_attr( $only_currency_name_class );
			echo esc_attr( $only_currency_name_and_something_class );
		?>
		">
				<div class="yay-currency-custom-select">
					<div class="yay-currency-custom-select__trigger
						<?php
						echo esc_attr( $widget_size );
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
						if ( $is_show_flag_in_widget ) {
							$country_code = $settings_data->currency_code_by_country_code[ $currency->post_title ];
							$flag_url     = Helper::get_flag_by_country_code( $selected_country_code );
							$html_flag    = '<span style="background-image: url(' . $flag_url . ')" class="yay-currency-flag ' . $widget_size . '" data-country_code="' . $country_code . '"></span>';
						}
						$currency_name   = $is_show_currency_name_in_widget ? $this->all_currencies[ $currency->post_title ] : null;
						$currency_symbol = $is_show_currency_symbol_in_widget ? ( $is_show_currency_name_in_widget ? ' (' . $woo_currency->get_symbol_by_currency( $currency->post_title ) . ')' : $woo_currency->get_symbol_by_currency( $currency->post_title ) . ' ' ) : null;
						$hyphen          = ( $is_show_currency_name_in_widget && $is_show_currency_code_in_widget ) ? ' - ' : null;
						$currency_code   = $is_show_currency_code_in_widget ? $currency->post_title : null;
						?>
						<li class="yay-currency-custom-option-row <?php echo $currency->ID == $this->selected_currency_ID ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $currency->ID ); ?>">
						<?php echo wp_kses_post( $html_flag ); ?>
						<div class="yay-currency-custom-option <?php echo esc_attr( $widget_size ); ?>">
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
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$is_show_flag_in_widget            = get_option( 'yay_currency_show_flag_in_widget', 1 );
		$is_show_currency_name_in_widget   = get_option( 'yay_currency_show_currency_name_in_widget', 1 );
		$is_show_currency_symbol_in_widget = get_option( 'yay_currency_show_currency_symbol_in_widget', 1 );
		$is_show_currency_code_in_widget   = get_option( 'yay_currency_show_currency_code_in_widget', 1 );

		$widget_size = get_option( 'yay_currency_widget_size', 'small' );
		wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce' );
		// Widget admin form
		?>
	<div class="yay-currency-widget-custom-fields">
			<span class="yay-currency-widget-custom-fields__title">Switcher elements:</span>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-flag" name="show-flag" value="1" <?php echo $is_show_flag_in_widget ? 'checked' : null; ?> />
				<label for="show-flag">Show flag</label>
			</div>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-currency-name" name="show-currency-name" value="1" <?php echo $is_show_currency_name_in_widget ? 'checked' : null; ?> />
				<label for="show-currency-name">Show currency name</label>
			</div>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-currency-symbol" name="show-currency-symbol" value="1" <?php echo $is_show_currency_symbol_in_widget ? 'checked' : null; ?> />
				<label for="show-currency-symbol">Show currency symbol</label>
			</div>
			<div class="yay-currency-widget-custom-fields__field">
				<input class="yay-currency-widget-custom-fields__field--checkbox" type="checkbox" id="show-currency-code" name="show-currency-code" value="1" <?php echo $is_show_currency_code_in_widget ? 'checked' : null; ?> />
				<label for="show-currency-code">Show currency code</label>
			</div>
			<div class="yay-currency-widget-custom-fields">
				<span class="yay-currency-widget-custom-fields__title">Switcher size:</span>
				<div class="yay-currency-widget-custom-field__field-group">
					<div class="yay-currency-widget-custom-field__field">
						<input class="yay-currency-widget-custom-fields__field--radio" type="radio" id="widget-size-small" name="widget-size" value="small" <?php echo 'small' === $widget_size ? 'checked' : null; ?> />
						<label for="widget-size">Small</label>
					</div>
					<div class="yay-currency-widget-custom-field__field">
						<input class="yay-currency-widget-custom-fields__field--radio" type="radio" id="widget-size-medium" name="widget-size" value="medium" <?php echo 'medium' === $widget_size ? 'checked' : null; ?> />
						<label for="widget-size">Medium</label>
					</div>
				</div>
			</div>
		</div>
		<style>

			.yay-currency-widget-custom-fields {
				margin-bottom: 16px;
			}
			.yay-currency-widget-custom-fields__title {
				display: inline-block;
				font-weight: bold;
				margin: 10px 0;
			}

			.yay-currency-widget-custom-fields__field:not(:last-child) {
				display: flex;
				align-items: center;
				margin-bottom: 8px;
			}

			.yay-currency-widget-custom-fields__field--checkbox {
				margin: 0 6px 0 0 !important;
			}

			.yay-currency-widget-custom-field__field-group {
				display: flex;
				gap: 12px;
			}

			.yay-currency-widget-custom-fields__field--radio {
				margin: 2px !important;
			}
		</style>
		<?php
	}

		// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		if ( isset( $_REQUEST['yay-currency-nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['yay-currency-nonce'] ), 'yay-currency-check-nonce' ) ) {
			$is_show_flag_in_widget            = isset( $_POST['show-flag'] ) ? sanitize_text_field( $_POST['show-flag'] ) : 0;
			$is_show_currency_name_in_widget   = isset( $_POST['show-currency-name'] ) ? sanitize_text_field( $_POST['show-currency-name'] ) : 0;
			$is_show_currency_symbol_in_widget = isset( $_POST['show-currency-symbol'] ) ? sanitize_text_field( $_POST['show-currency-symbol'] ) : 0;
			$is_show_currency_code_in_widget   = isset( $_POST['show-currency-code'] ) ? sanitize_text_field( $_POST['show-currency-code'] ) : 0;
			$widget_size                       = isset( $_POST['widget-size'] ) ? sanitize_text_field( $_POST['widget-size'] ) : 'small';

			update_option( 'yay_currency_show_flag_in_widget', $is_show_flag_in_widget );
			update_option( 'yay_currency_show_currency_name_in_widget', $is_show_currency_name_in_widget );
			update_option( 'yay_currency_show_currency_symbol_in_widget', $is_show_currency_symbol_in_widget );
			update_option( 'yay_currency_show_currency_code_in_widget', $is_show_currency_code_in_widget );
			update_option( 'yay_currency_widget_size', $widget_size );
		}
	}

}
