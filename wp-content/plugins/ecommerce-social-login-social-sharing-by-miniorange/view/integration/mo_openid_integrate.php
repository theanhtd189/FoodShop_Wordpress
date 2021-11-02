<?php
function mo_wc_openid_integrations(){
    ?>
        <form style="margin-left: 1%">
            <table id="woocommerce_integration"><tr><td>
                        <p><b><?php echo mo_wc_sl('If enabled, first name, last name and email are pre-filled in billing details of a user and on the Woocommerce checkout page.');?></b></p>
                        <label class="mo_openid_checkbox_container_disable"><strong><?php echo mo_wc_sl('Sync Woocommerce checkout fields');?></strong>
                            <input  type="checkbox"/>
                            <span class="mo_openid_checkbox_checkmark_disable"></span>
                        </label>
                        <br>
                        <input disabled type="button" value="<?php echo mo_wc_sl('Save');?> " class="button button-primary button-large" />
                        <br>
                    </td></tr></table>
        </form>
    <br/>
    <div class="mo_openid_highlight">
        <h3 style="margin-left: 2%;line-height: 210%;color: white;" id="mo_openid_send_email_user_collapse"><?php echo mo_wc_sl('WooCommerce Display Options');?></h3>
    </div><br/>
    <div style="height: auto; min-height: 350px">
        <form method="post" action="">
            <input type="hidden" name="option" value="mo_openid_enable_woocommerce_display" />
            <input type="hidden" name="mo_openid_enable_woocommerce_display_nonce" value="<?php echo wp_create_nonce( 'mo-openid-enable-woocommerce-display-nonce' ); ?>"/>
            <div style="width:40%; float:left; margin-left: 1%; background:white; border: 1px transparent;">
                <b style="font-size:17px;"><?php echo mo_wc_sl('Login display options');?> </b><br><br>
                <label class="mo_openid_checkbox_container">
                    <input type="checkbox" id="woocommerce_before_login_form" name="mo_openid_woocommerce_before_login_form" value="1" <?php checked( get_option('mo_openid_woocommerce_before_login_form') == 1 );?> /><?php echo mo_wc_sl("Before WooCommerce Login Form");?>
                    <span class="mo_openid_checkbox_checkmark"></span>
                </label>

                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl("Before 'Remember Me' of WooCommerce Login Form");?>
                    <input type="checkbox"  /><a style="left: 1%; position: relative; text-decoration: none" class="mo-openid-premium" href="<?php echo add_query_arg( array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI'] ); ?>"><?php echo mo_wc_sl('PRO');?></a><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>
                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl('After WooCommerce Login Form');?>
                    <input type="checkbox"  /><a style="left: 1%; position: relative; text-decoration: none" class="mo-openid-premium" href="<?php echo add_query_arg( array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI'] ); ?>"><?php echo mo_wc_sl('PRO');?></a><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>

                <br/><b style="font-size:17px;"><?php echo mo_wc_sl('Checkout display options');?> </b><a style="left: 1%; position: relative; text-decoration: none" class="mo-openid-premium" href="<?php echo add_query_arg( array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI'] ); ?>"><?php echo mo_wc_sl('PRO');?></a><br><br>
                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl('Before WooCommerce Checkout Form');?>
                    <input type="checkbox"  /><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>

                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl('After WooCommerce Checkout Form');?>
                    <input type="checkbox"  /><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>
            </div>
            <div style="width:50%; float:right; margin-left: 1%; background:white; border: 1px transparent;">
                <b style="font-size:17px;"><?php echo mo_wc_sl('Registration display options');?> </b><br><br>
                <label class="mo_openid_checkbox_container">
                    <input type="checkbox" id="woocommerce_register_form_start" name="mo_openid_woocommerce_register_form_start" value="1" <?php checked( get_option('mo_openid_woocommerce_register_form_start') == 1 );?> /><?php echo mo_wc_sl("Before WooCommerce Registration Form");?>
                    <span class="mo_openid_checkbox_checkmark"></span>
                </label>
                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl("Before 'Register button' of WooCommerce Registration Form");?>
                    <input type="checkbox"  /><a style="left: 1%; position: relative; text-decoration: none" class="mo-openid-premium" href="<?php echo add_query_arg( array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI'] ); ?>"><?php echo mo_wc_sl('PRO');?></a><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>

                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl('After WooCommerce Registration Form');?>
                    <input type="checkbox"  /><a style="left: 1%; position: relative; text-decoration: none" class="mo-openid-premium" href="<?php echo add_query_arg( array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI'] ); ?>"><?php echo mo_wc_sl('PRO');?></a><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>
                <iframe width="450" height="270" src="https://www.youtube.com/embed/M20AR-wbKNI"></iframe>
            </div>
            <div style="height:available;display: inline; width:100%; background:white; float:right; border: 1px transparent; padding-bottom: 10px;"><br/><b style="padding: 10px"><input type="submit" name="submit" value="<?php echo mo_wc_sl('Save');?>" style="width:150px;background-color:#0867b2;color:white;box-shadow:none;text-shadow: none;" class="button button-primary button-large" /></b></div>
        </form>
    </div>
    <br/>
    <div>
    </div>
    <script>
        //to set heading name
        jQuery('#mo_openid_page_heading').text('<?php echo mo_wc_sl('Woocommerce Integrations');?>');
        var temp = jQuery("<a style=\"left: 1%; padding:4px; position: relative; text-decoration: none\" class=\"mo-openid-premium\" href=\"<?php echo add_query_arg(array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI']); ?>\">PRO</a>");
        jQuery("#mo_openid_page_heading").append(temp);
        var win_height = jQuery('#mo_openid_menu_height').height();
        jQuery(".mo_container").css({height:win_height});
    </script>
    <?php
}