<div class='yay-currency-single-page-switcher'>
  <form method='POST' action='' class='yay-currency-form-switcher'>
	<?php
		use Yay_Currency\Helper;
		wp_nonce_field( 'yay-currency-check-nonce', 'yay-currency-nonce' );
	?>
		<select class='yay-currency-switcher' name='currency' onchange='this.form.submit()'>
		<?php
		foreach ( $this->apply_currencies as $currency ) {
			$currency_code = $is_show_currency_code_in_menu_item ? $currency->post_title : null;
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
		if ( $is_show_flag_in_menu_item ) {
			$selected_country_code = $settings_data->currency_code_by_country_code[ $selected_currency_info->post_title ];
			$selected_flag_url     = Helper::get_flag_by_country_code( $selected_country_code );
			$selected_html_flag    = '<span style="background-image: url(' . $selected_flag_url . ')" class="yay-currency-flag selected ' . $menu_item_size . '" data-country_code="' . $selected_country_code . '"></span>';
		}
		$selected_currency_name   = $is_show_currency_name_in_menu_item ? $this->all_currencies[ $selected_currency_info->post_title ] : null;
		$selected_currency_symbol = $is_show_currency_symbol_in_menu_item ? ( $is_show_currency_name_in_menu_item ? ' (' . $woo_currency->get_symbol_by_currency( $selected_currency_info->post_title ) . ')' : $woo_currency->get_symbol_by_currency( $selected_currency_info->post_title ) . ' ' ) : null;
		$hyphen                   = ( $is_show_currency_name_in_menu_item && $is_show_currency_code_in_menu_item ) ? ' - ' : null;
		$selected_currency_code   = $is_show_currency_code_in_menu_item ? $selected_currency_info->post_title : null;
		?>
		</select>
  </form>
  <div class="yay-currency-custom-select-wrapper 
  <?php
	echo esc_attr( $menu_item_size );
	echo esc_attr( $no_currency_name_class );
	echo esc_attr( $only_currency_name_class );
	echo esc_attr( $only_currency_name_and_something_class );
	?>
	">
	<div class="yay-currency-custom-select">
	  <div class="yay-currency-custom-select__trigger 
	  <?php
			echo esc_attr( $menu_item_size );
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
			if ( $is_show_flag_in_menu_item ) {
				$country_code = $settings_data->currency_code_by_country_code[ $currency->post_title ];
				$flag_url     = Helper::get_flag_by_country_code( $country_code );
				$html_flag    = '<span style="background-image: url(' . $flag_url . ')" class="yay-currency-flag ' . $menu_item_size . '" data-country_code="' . $country_code . '"></span>';
			}
			$currency_name   = $is_show_currency_name_in_menu_item ? $this->all_currencies[ $currency->post_title ] : null;
			$currency_symbol = $is_show_currency_symbol_in_menu_item ? ( $is_show_currency_name_in_menu_item ? ' (' . $woo_currency->get_symbol_by_currency( $currency->post_title ) . ')' : $woo_currency->get_symbol_by_currency( $currency->post_title ) . ' ' ) : null;
			$hyphen          = ( $is_show_currency_name_in_menu_item && $is_show_currency_code_in_menu_item ) ? ' - ' : null;
			$currency_code   = $is_show_currency_code_in_menu_item ? $currency->post_title : null;
			?>
		<li class="yay-currency-custom-option-row <?php echo $currency->ID == $this->selected_currency_ID ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $currency->ID ); ?>">
			<?php echo wp_kses_post( $html_flag ); ?>
	  <div class="yay-currency-custom-option <?php echo esc_attr( $menu_item_size ); ?>">
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
