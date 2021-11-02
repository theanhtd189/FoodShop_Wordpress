<?php
include "mo-wc-openid-social-login-functions.php";
add_action( 'wp_login', 'mo_wc_openid_link_account', 5, 2);
add_action( 'mo_user_register', 'mo_wc_openid_update_role', 1, 2);
add_action( 'mo_user_register','mo_wc_openid_send_email',1, 2 );
add_action('wp_login', 'mo_wc_openid_login_redirect', 11, 2);
add_action( 'delete_user', 'mo_wc_openid_delete_account_linking_rows' );

add_action('manage_users_custom_column', 'mo_wc_openid_delete_profile_column', 9, 3);
add_filter('manage_users_columns', 'mo_wc_openid_add_custom_column1');
add_action('admin_head', 'mo_wc_openid_delete_social_profile_script');
add_action('widgets_init', function () {
    register_widget("mo_wc_openid_login_wid");
});
add_action('widgets_init', function () {
    register_widget("mo_wc_openid_sharing_ver_wid");
});
add_action('widgets_init', function () {
    register_widget("mo_wc_openid_sharing_hor_wid");
});

if (get_option('mo_openid_logout_redirection_enable') == 1) {
    add_filter('logout_url', 'mo_wc_openid_redirect_after_logout', 0, 1);
}


class mo_wc_openid_login_wid extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'mo_wc_openid_login_wid',
            'miniOrange Social Login Widget',
            array(
                'description' => __( 'Login using Social Apps like Google, Facebook, LinkedIn, Microsoft, Instagram.', 'flw' ),
                'customize_selective_refresh' => true,
            )
        );
    }

    public function widget( $args, $instance ) {
        extract( $args );

        echo $args['before_widget'];
        $this->mo_wc_openidloginForm();

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
        return $instance;
    }


    public function mo_wc_openidloginForm(){


        $selected_theme = esc_attr(get_option('mo_openid_login_theme'));
        $appsConfigured = get_option('mo_openid_google_enable') | get_option('mo_openid_salesforce_enable') | get_option('mo_openid_facebook_enable') | get_option('mo_openid_linkedin_enable') | get_option('mo_openid_instagram_enable') | get_option('mo_openid_amazon_enable') | get_option('mo_openid_windowslive_enable') | get_option('mo_openid_twitter_enable') | get_option('mo_openid_vkontakte_enable')| get_option('mo_openid_yahoo_enable');
        $spacebetweenicons = esc_attr(get_option('mo_login_icon_space'));
        $customWidth = esc_attr(get_option('mo_login_icon_custom_width'));
        $customHeight = esc_attr(get_option('mo_login_icon_custom_height'));
        $customSize = esc_attr(get_option('mo_login_icon_custom_size'));
        $customBackground = esc_attr(get_option('mo_login_icon_custom_color'));
        $customTheme = esc_attr(get_option('mo_openid_login_custom_theme'));
        $customTextofTitle = esc_attr(get_option('mo_openid_login_button_customize_text'));
        $customBoundary = esc_attr(get_option('mo_login_icon_custom_boundary'));
        $customLogoutName = esc_attr(get_option('mo_openid_login_widget_customize_logout_name_text'));
        $customLogoutLink = get_option('mo_openid_login_widget_customize_logout_text');
        $customTextColor=esc_attr(get_option('mo_login_openid_login_widget_customize_textcolor'));
        $customText=esc_html(get_option('mo_openid_login_widget_customize_text'));
        $application_pos = get_option('app_pos');

        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $sign_up_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $mo_URL=strstr($sign_up_url, "?", true);
        if($mo_URL) {
            setcookie("mo_openid_signup_url",  $mo_URL, time() + (86400 * 30), "/");}
        else{
            setcookie("mo_openid_signup_url", $sign_up_url, time() + (86400 * 30), "/");}

        if( ! is_user_logged_in() ) {
            $values=array(
                'appsConfigured' => $appsConfigured,
                'selected_apps' => '',
                'application_pos' => $application_pos,
                'customTextColor' => $customTextColor,
                'customText' => $customText,
                'selected_theme' => $selected_theme,
                'view' => '',
                'spacebetweenicons' => $spacebetweenicons,
                'customWidth' => $customWidth,
                'customHeight' => $customHeight,
                'customBoundary' => $customBoundary,
                'buttonText' => $customTextofTitle,
                'customTextofTitle' => $customTextofTitle,
                'customSize' => $customSize,
                'html' => '',
                'customBackground' => $customBackground,
                'customTheme' => $customTheme,
            );
            $html=$this->mo_wc_display_apps($values);
            $html.='<br/>';
            echo $html;

        }
        else {
            global $current_user;
            $current_user = wp_get_current_user();
            $customLogoutName = str_replace('##username##', $current_user->display_name, $customLogoutName);
            $link_with_username = $customLogoutName;
            if (empty($customLogoutName)  || empty($customLogoutLink)) {
                ?>
                <div id="logged_in_user" class="mo_openid_login_wid">
                    <li><?php echo $link_with_username;?> <a href="<?php echo wp_logout_url( site_url() ); ?>" title="<?php _e('Logout','flw');?>"><?php _e($customLogoutLink,'flw');?></a></li>
                </div>
                <?php
            }
            else {
                ?>
                <div id="logged_in_user" class="mo_openid_login_wid">
                    <li><?php echo $link_with_username;?> <a href="<?php echo wp_logout_url( site_url() ); ?>" title="<?php _e('Logout','flw');?>"><?php _e($customLogoutLink,'flw');?></a></li>
                </div>
                <?php
            }
        }
    }

    public function mo_wc_openid_customize_logo(){
        $logo =" <div style='float:left;' class='mo_image_id'>
                <a target='_blank' href='https://www.miniorange.com/'>
                <img alt='logo' src='". plugins_url('/includes/images/miniOrange.png',__FILE__) ."' class='mo_openid_image'>
                </a>
                </div>
                <br/>";
        return $logo;
    }

    public function mo_wc_if_custom_app_exists($app_name){

        if(get_option('mo_openid_apps_list'))
        {
            $appslist = maybe_unserialize(get_option('mo_openid_apps_list'));
            if(isset($appslist[$app_name])) {
                if (get_option('mo_openid_enable_custom_app_' . $app_name))
                    return 'true';
                else
                    return 'false';
            }
            else
                return 'false';
        }
        return 'false';
    }

    public function mo_wc_openidloginFormShortCode( $atts ){
        global $post;
        $html = '';
        $selected_theme = isset( $atts['shape'] )? esc_attr($atts['shape']) : esc_attr(get_option('mo_openid_login_theme'));
        $selected_apps = isset( $atts['apps'] )? esc_attr($atts['apps']) : "";
        $application_pos = get_option('app_pos');
        $appsConfigured = get_option('mo_openid_facebook_enable') | get_option('mo_openid_google_enable') | get_option('mo_openid_vkontakte_enable') | get_option('mo_openid_twitter_enable') | get_option('mo_openid_instagram_enable') | get_option('mo_openid_linkedin_enable') |  get_option('mo_openid_amazon_enable') | get_option('mo_openid_salesforce_enable') | get_option('mo_openid_windowslive_enable') | get_option('mo_openid_yahoo_enable') ;
        $spacebetweenicons = isset( $atts['space'] )? esc_attr(intval($atts['space'])) : esc_attr(intval(get_option('mo_login_icon_space')));
        $customWidth = isset( $atts['width'] )? esc_attr(intval($atts['width'])) : esc_attr(intval(get_option('mo_login_icon_custom_width')));
        $customHeight = isset( $atts['height'] )? esc_attr(intval($atts['height'])) : esc_attr(intval(get_option('mo_login_icon_custom_height')));
        $customSize = isset( $atts['size'] )? esc_attr(intval($atts['size'])) : esc_attr(intval(get_option('mo_login_icon_custom_size')));
        $customBackground = isset( $atts['background'] )? esc_attr($atts['background']) : esc_attr(get_option('mo_login_icon_custom_color'));
        $customTheme = isset( $atts['theme'] )? esc_attr($atts['theme']) : esc_attr(get_option('mo_openid_login_custom_theme'));
        $buttonText = esc_html(get_option('mo_openid_login_button_customize_text'));
        $customTextofTitle = esc_attr(get_option('mo_openid_login_button_customize_text'));
        $logoutUrl = esc_url(wp_logout_url(site_url()));
        $customBoundary = isset( $atts['edge'] )? esc_attr($atts['edge']) : esc_attr(get_option('mo_login_icon_custom_boundary'));
        $customLogoutName = esc_attr(get_option('mo_openid_login_widget_customize_logout_name_text'));
        $customLogoutLink = get_option('mo_openid_login_widget_customize_logout_text');
        $customTextColor= isset( $atts['color'] )? esc_attr($atts['color']) : esc_attr(get_option('mo_login_openid_login_widget_customize_textcolor'));
        $customText=isset( $atts['heading'] )? esc_html($atts['heading']) :esc_html(get_option('mo_openid_login_widget_customize_text'));
        $view=isset( $atts['view'] )? esc_attr($atts['view']) : "";
        $appcnt=isset( $atts['appcnt'] )? esc_attr($atts['appcnt']) : "";
        if($selected_theme == 'longbuttonwithtext'){
            $selected_theme = 'longbutton';
        }

        if($customTheme == 'custombackground'){
            $customTheme = 'custom';
        }

        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $sign_up_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $mo_URL=strstr($sign_up_url, "?", true);
        if($mo_URL) {setcookie("mo_openid_signup_url",  $mo_URL, time() + (86400 * 30), "/");}
        else{setcookie("mo_openid_signup_url", $sign_up_url, time() + (86400 * 30), "/");}
        if( ! is_user_logged_in() ) {

            $values=array(
                'appsConfigured' => $appsConfigured,
                'selected_apps' => $selected_apps,
                'application_pos' => $application_pos,
                'customTextColor' => $customTextColor,
                'customText' => $customText,
                'selected_theme' => $selected_theme,
                'view' => $view,
                'spacebetweenicons' => $spacebetweenicons,
                'customWidth' => $customWidth,
                'customHeight' => $customHeight,
                'customBoundary' => $customBoundary,
                'buttonText' => $buttonText,
                'customTextofTitle' => $customTextofTitle,
                'customSize' => $customSize,
                'html' => $html,
                'customBackground' => $customBackground,
                'customTheme' => $customTheme,
                'appcnt' => $appcnt,
            );
            $html=$this->mo_wc_display_apps($values);

        }else {
            global $current_user;
            $current_user = wp_get_current_user();
            $customLogoutName = str_replace('##username##', $current_user->display_name, $customLogoutName);
            $flw = __($customLogoutLink,"flw");
            if (empty($customLogoutName)  || empty($customLogoutLink)) {
                $html .= '<div id="logged_in_user" class="mo_openid_login_wid">' . $customLogoutName . ' <a href=' . $logoutUrl .' title=" ' . $flw . '"> ' . $flw . '</a></div>';
            }
            else {
                $html .= '<div id="logged_in_user" class="mo_openid_login_wid">' . $customLogoutName . ' <a href=' . $logoutUrl .' title=" ' . $flw . '"> ' . $flw . '</a></div>';
            }
        }
        return $html;
    }

    public function mo_wc_display_apps($values){
        $appsConfigured=$values['appsConfigured'];
        $selected_apps=$values['selected_apps'];
        $application_pos=$values['application_pos'];
        $customTextColor=$values['customTextColor'];
        $customText=$values['customText'];
        $spacebetweenicons=$values['spacebetweenicons'];
        $customWidth=$values['customWidth'];
        $customHeight=$values['customHeight'];
        $customBoundary=$values['customBoundary'];
        $buttonText=$values['buttonText'];
        $customTextofTitle=$values['customTextofTitle'];
        $customSize=$values['customSize'];
        $selected_theme=$values['selected_theme'];
        $html=$values['html'];
        $view=$values['view'];
        $customBackground=$values['customBackground'];
        $customTheme=$values['customTheme'];
        $appcnt=isset($values['appcnt'])?$values['appcnt']:'';

        if( $appsConfigured || $selected_apps!="") {

            if($selected_apps!="")
                $apps=explode(',', $selected_apps);
            else
                $apps=explode('#', $application_pos);

            $this->mo_wc_openid_load_login_script();
            $html .= "<div class='mo-openid-app-icons'>
					 <p style='color:".$customTextColor."; width: fit-content;'> $customText</p>";

            $count = -1;
            if($selected_apps!=""){
                if(mo_wc_openid_is_customer_registered())
                {
                    foreach ($apps as $select_apps) {
                        $app_dis = "";
                        if ($selected_theme == 'longbutton') {
                            if ($view == "horizontal" && isset($appcnt)) {
                                $count++;
                                if ("".$count == "".$appcnt) {
                                    $html .= "<br/>";
                                    $count = 0;
                                }
                            }
                        }
                        $app_values= array(
                            'spacebetweenicons' => $spacebetweenicons,
                            'customWidth' => $customWidth,
                            'customHeight' => $customHeight,
                            'customBoundary' => $customBoundary,
                            'buttonText' => $buttonText,
                            'customTextofTitle' => $customTextofTitle,
                            'customSize' => $customSize,
                            'selected_theme' => $selected_theme,
                            'html' => $html,
                            'view' => $view,
                            'customBackground' => $customBackground,
                            'app_dis' => $app_dis,
                            'customTheme' => $customTheme,
                            'customer_register' => 'yes',
                        );
                        $html=$this->mo_wc_select_app($select_apps,$app_values);
                    }
                }
                else{
                    foreach ($apps as $select_apps) {
                        $app_dis = "";
                        if ($selected_theme == 'longbutton') {
                            if ($view == "horizontal" && isset($appcnt)) {
                                $count++;
                                if ($count == $appcnt) {
                                    $html .= "<br/>";
                                    $count = 0;
                                }
                            }
                        }
                        $app_values= array(
                            'spacebetweenicons' => $spacebetweenicons,
                            'customWidth' => $customWidth,
                            'customHeight' => $customHeight,
                            'customBoundary' => $customBoundary,
                            'buttonText' => $buttonText,
                            'customTextofTitle' => $customTextofTitle,
                            'customSize' => $customSize,
                            'selected_theme' => $selected_theme,
                            'html' => $html,
                            'view' => $view,
                            'customBackground' => $customBackground,
                            'app_dis' => $app_dis,
                            'customTheme' => $customTheme,
                            'customer_register' => 'no',
                        );
                        $html=$this->mo_wc_select_app($select_apps,$app_values);
                    }
                }
            }
            else{
                foreach ($apps as $select_apps) {
                    if (get_option('mo_openid_'.$select_apps.'_enable')) {
                        $app_dis = "";
                        if ($selected_theme == 'longbutton') {
                            if ($view == "horizontal" && isset($appcnt)) {
                                $count++;
                                if ($count == $appcnt) {
                                    $html .= "<br/>";
                                    $count = 0;
                                }
                            }
                        }
                        $app_values= array(
                            'spacebetweenicons' => $spacebetweenicons,
                            'customWidth' => $customWidth,
                            'customHeight' => $customHeight,
                            'customBoundary' => $customBoundary,
                            'buttonText' => $buttonText,
                            'customTextofTitle' => $customTextofTitle,
                            'customSize' => $customSize,
                            'selected_theme' => $selected_theme,
                            'html' => $html,
                            'view' => $view,
                            'customBackground' => $customBackground,
                            'app_dis' => $app_dis,
                            'customTheme' => $customTheme,
                            'customer_register' => 'yes',
                        );
                        $html=$this->mo_wc_select_app($select_apps,$app_values);
                    }
                }
            }
            $html .= '</div> <br>';
        }
        else {
            $html .= '<div>No apps configured. Please contact your administrator.</div>';
        }
        if( $appsConfigured && get_option('moopenid_logo_check') == 1 ){
            $logo_html=$this->mo_wc_openid_customize_logo();
            $html .= $logo_html;
        }
        return $html;
    }

    public function mo_wc_select_app($select_apps,$app_values){
        if( get_option('mo_openid_fonawesome_load') == 1)
        wp_enqueue_style( 'mo-openid-sl-wp-font-awesome',plugins_url('includes/css/mo-font-awesome.min.css', __FILE__), false );
        wp_enqueue_style( 'mo-wp-style-icon',plugins_url('includes/css/mo_openid_login_icons.css?version=7.3.0', __FILE__), false );
        wp_enqueue_style( 'mo-wp-bootstrap-social',plugins_url('includes/css/bootstrap-social.css', __FILE__), false );
        if( get_option('mo_openid_bootstrap_load') == 1)
        wp_enqueue_style( 'mo-wp-bootstrap-main',plugins_url('includes/css/bootstrap.min-preview.css', __FILE__), false );

        $spacebetweenicons = $app_values['spacebetweenicons'];
        $customWidth = $app_values['customWidth'];
        $customHeight = $app_values['customHeight'];
        $customBoundary = $app_values['customBoundary'];
        $buttonText = $app_values['buttonText'];
        $customTextofTitle = $app_values['customTextofTitle'];
        $customSize = $app_values['customSize'];
        $selected_theme = $app_values['selected_theme'];
        $html = $app_values['html'];
        $view = $app_values['view'];
        $customBackground = $app_values['customBackground'];
        $app_dis = $app_values['app_dis'];
        $customTheme = $app_values['customTheme'];
        $customer_register  = $app_values['customer_register'];
        switch ($select_apps) {
            case 'facebook':
            case 'fb':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('facebook'));;
                $custom_app == "false" ? $app_dis = "disable" : $app_dis = "";
                $html = $this->mo_wc_add_apps("fb", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'google':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('google'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("google", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'vkontakte':
            case 'vk':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('vkontakte'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("vk", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'twitter':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('twitter'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("twitter", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'linkedin':
            case 'linkin':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('linkedin'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("linkin", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'instagram':
            case 'insta':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('instagram'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("insta", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'amazon':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('amazon'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("amazon", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'salesforce':
            case 'sforce':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('salesforce'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("sforce", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'windowslive':
            case 'wlive':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('windowslive'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("wlive", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'yahoo':
                $custom_app = esc_attr($this->mo_wc_if_custom_app_exists('yahoo'));
                $app_dis = $this->mo_wc_check_capp_reg_cust($customer_register, $custom_app);
                $html = $this->mo_wc_add_apps("yahoo", $customTheme, $spacebetweenicons, $customWidth, $customHeight, $customBoundary, $buttonText, $customTextofTitle, $customSize, $selected_theme, $custom_app, $html, $view, $customBackground, $app_dis);
                break;
            case 'wordpress':
        }
        return $html;
    }

    public function mo_wc_check_capp_reg_cust($customer_register,$custom_app)
    {
        if($customer_register=='no' && $custom_app =='false')
            return 'disable';
    }
    //for shortcode
    public function mo_wc_add_apps($app_name,$theme,$spacebetweenicons,$customWidth,$customHeight,$customBoundary,$buttonText,$customTextofTitle,$customSize,$selected_theme,$custom_app,$html,$view,$customBackground,$app_dis)
    {
        if($customWidth!=='auto'||$customWidth=='Auto'||$customWidth=='AUTO')
            $customWidth.='px';
        if($theme=="default")
        {
            if($app_name=="fb")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-facebook btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-facebook btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('facebook','" . $custom_app . "');\"";
                    $html .= "> <svg xmlns=\"http://www.w3.org/2000/svg\" style=\"padding-top:" . ($customHeight - 30) . "px;border-right:none;margin-left: 2%;\" ><path fill=\"#fff\" d=\"M22.688 0H1.323C.589 0 0 .589 0 1.322v21.356C0 23.41.59 24 1.323 24h11.505v-9.289H9.693V11.09h3.124V8.422c0-3.1 1.89-4.789 4.658-4.789 1.322 0 2.467.1 2.8.145v3.244h-1.922c-1.5 0-1.801.711-1.801 1.767V11.1h3.59l-.466 3.622h-3.113V24h6.114c.734 0 1.323-.589 1.323-1.322V1.322A1.302 1.302 0 0 0 22.688 0z\"/></svg>" . $buttonText . " Facebook</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Facebook'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('facebook','" . $custom_app . "');\""; $html.="><img alt='Facebook' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/facebook.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="google")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;background: rgb(255,255,255)!important; background:linear-gradient(90deg, rgba(255,255,255,1) 38px, rgb(79, 113, 232) 5%) !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;border-color: rgba(79, 113, 232, 1);border-bottom-width: thin;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-google btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-google btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('google','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 29) . "px !important;border-right:none;' class='mofa'><svg xmlns=\"http://www.w3.org/2000/svg\"><g fill=\"none\" fill-rule=\"evenodd\"><path fill=\"#4285F4\" fill-rule=\"nonzero\" d=\"M20.64 12.2045c0-.6381-.0573-1.2518-.1636-1.8409H12v3.4814h4.8436c-.2086 1.125-.8427 2.0782-1.7959 2.7164v2.2581h2.9087c1.7018-1.5668 2.6836-3.874 2.6836-6.615z\"></path><path fill=\"#34A853\" fill-rule=\"nonzero\" d=\"M12 21c2.43 0 4.4673-.806 5.9564-2.1805l-2.9087-2.2581c-.8059.54-1.8368.859-3.0477.859-2.344 0-4.3282-1.5831-5.036-3.7104H3.9574v2.3318C5.4382 18.9832 8.4818 21 12 21z\"></path><path fill=\"#FBBC05\" fill-rule=\"nonzero\" d=\"M6.964 13.71c-.18-.54-.2822-1.1168-.2822-1.71s.1023-1.17.2823-1.71V7.9582H3.9573A8.9965 8.9965 0 0 0 3 12c0 1.4523.3477 2.8268.9573 4.0418L6.964 13.71z\"></path><path fill=\"#EA4335\" fill-rule=\"nonzero\" d=\"M12 6.5795c1.3214 0 2.5077.4541 3.4405 1.346l2.5813-2.5814C16.4632 3.8918 14.426 3 12 3 8.4818 3 5.4382 5.0168 3.9573 7.9582L6.964 10.29C7.6718 8.1627 9.6559 6.5795 12 6.5795z\"></path><path d=\"M3 3h18v18H3z\"></path></g></svg></i><span style=\"color:#ffffff;\">" . $buttonText . " Google</span></a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow'";if($app_dis!="disable")$html.=" onClick=\"moOpenIdLogin('google','" . $custom_app . "');\""; $html.=" title= ' " . $customTextofTitle . " Google'><img alt='Google' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/google.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="vk")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-vk btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-vk btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('vkontakte','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-vk'></i>" . $buttonText . " Vkontakte</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow'"; if($app_dis!="disable") $html.="onClick=\"moOpenIdLogin('vkontakte','" . $custom_app . "');\""; $html.="title= ' " . $customTextofTitle . " Vkontakte'><img alt='Vkontakte' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/vk.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="twitter")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-twitter btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-twitter btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('twitter','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-twitter'></i>" . $buttonText . " Twitter</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Twitter'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('twitter','" . $custom_app . "');\""; $html.=" ><img alt='Twitter' style=' width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/twitter.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="linkin")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-linkedin btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-linkedin btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('linkedin','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-linkedin'></i>" . $buttonText . " LinkedIn</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " LinkedIn'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('linkedin','" . $custom_app . "');\""; $html.=" ><img alt='LinkedIn' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/linkedin.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="insta")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-instagram btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-instagram btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('instagram','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-instagram'></i>" . $buttonText . " Instagram</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' " . $customTextofTitle . " Instagram'";if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('instagram','" . $custom_app . "');\""; $html.=" ><img alt='Instagram' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/instagram.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="amazon")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-soundcloud btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-soundcloud btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('amazon','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-amazon'></i>" . $buttonText . " Amazon</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Amazon' ";if($app_dis!="disable") $html.="onClick=\"moOpenIdLogin('amazon','" . $custom_app . "');\""; $html.=" ><img alt='Amazon' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/amazon.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="sforce")
            {
                if(!mo_wc_openid_is_customer_registered())
                    $app_dis='disabled';
                else
                    $app_dis='';
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-vimeo btn-custom-dec login-button' ";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-vimeo btn-custom-dec login-button' ";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('salesforce','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-cloud'></i>" . $buttonText . " Salesforce</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Salesforce'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('salesforce','" . $custom_app . "');\""; $html.=" ><img alt='Salesforce' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/salesforce.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="wlive")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-microsoft btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-microsoft btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= " onClick=\"moOpenIdLogin('windowslive','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-windows'></i>" . $buttonText . " Microsoft</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow'  title= ' " . $customTextofTitle . " Microsoft'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('windowslive','" . $custom_app . "');\""; $html.=" ><img alt='Windowslive' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/windowslive.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
            else if($app_name=="yahoo")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' style='margin-left: " . $spacebetweenicons . "px !important;width: " . $customWidth . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom: " . ($spacebetweenicons - 5) . "px !important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-yahoo btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-yahoo btn-custom-dec login-button'";
                    }
                    if($app_dis!="disable")
                        $html .= "onClick=\"moOpenIdLogin('yaahoo','" . $custom_app . "');\"";
                    $html.="> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-yahoo'></i>" . $buttonText . " Yahoo</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " .
                        $customTextofTitle . " Yahoo'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('yaahoo','" . $custom_app . "');\""; $html.=" ><img alt='Yahoo' style='width:" . $customSize . "px !important;height: " . $customSize . "px !important;margin-left: " . ($spacebetweenicons) . "px !important' src='" . plugins_url('includes/images/icons/yahoo.png', __FILE__) . "' class='login-button " . $selected_theme . "' ></a>";
                }
                return $html;
            }
        }

        else if($theme=="custom"){
            if($app_name=="fb")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' "; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('facebook','" . $custom_app . "');\"";$html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-facebook'></i> " . $buttonText . " Facebook</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Facebook'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('facebook','" . $custom_app . "');\"";$html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-facebook custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
            else if($app_name=="google") {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' ";if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('google','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-google'></i> " . $buttonText . " Google</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Google'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('google','" . $custom_app . "');\""; $html.=" title= ' " . $customTextofTitle . "  Google'><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-google custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
            else if($app_name=="vk") {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' "; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('vkontakte','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-vk'></i> " . $buttonText . " Vkontakte</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Vkontakte'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('vkontakte','" . $custom_app . "');\""; $html.=" title= ' " . $customTextofTitle . "  Vkontakte'><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-vk custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
            else if($app_name=="twitter") {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' "; if($app_dis!="disable") $html.="onClick=\"moOpenIdLogin('twitter','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-twitter'></i> " . $buttonText . " Twitter</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Twitter'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('twitter','" . $custom_app . "');\""; $html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-twitter custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
            else if($app_name=="linkin") {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' "; if($app_dis!="disable") $html.="onClick=\"moOpenIdLogin('linkedin','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-linkedin'></i> " . $buttonText . " LinkedIn</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " LinkedIn'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('linkedin','" . $custom_app . "');\""; $html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-linkedin custom-login-button  " . $selected_theme . "' ></i></a>";
                }

                return $html;
            }
            else if($app_name=="insta") {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' "; if($app_dis!="disable") $html.="onClick=\"moOpenIdLogin('instagram','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-instagram'></i> " . $buttonText . " Instagram</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Instagram'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('instagram','" . $custom_app . "');\""; $html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-instagram custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
            else if($app_name=="amazon") {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' "; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('amazon','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-amazon'></i> " . $buttonText . " Amazon</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow'  title= ' " . $customTextofTitle . " Amazon'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('amazon','" . $custom_app . "');\""; $html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-amazon custom-login-button  " . $selected_theme . "' ></i></a>";
                }

                return $html;
            }
            else if($app_name=="sforce")
            {
                if(!mo_wc_openid_is_customer_registered())
                    $app_dis='disabled';
                else
                    $app_dis='';
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' "; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('salesforce','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-cloud'></i> " . $buttonText . " Salesforce</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Salesforce'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('salesforce','" . $custom_app . "');\""; $html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-cloud custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
            else if($app_name=="wlive")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a  rel='nofollow' "; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('windowslive','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-windows'></i> " . $buttonText . " Microsoft</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow'  title= ' " . $customTextofTitle . " Microsoft'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('windowslive','" . $custom_app . "');\""; $html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-windows custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
            else if($app_name=="yahoo")
            {
                if ($selected_theme == 'longbutton') {
                    $html .= "<a rel='nofollow' "; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('yaahoo','" . $custom_app . "');\""; $html.=" style='margin-left: " . $spacebetweenicons . "px !important;width:" . ($customWidth) . " !important;padding-top:" . ($customHeight - 29) . "px !important;padding-bottom:" . ($customHeight - 29) . "px !important;margin-bottom:" . ($spacebetweenicons - 5) . "px !important; background:" . $customBackground . "!important;border-radius: " . $customBoundary . "px !important;'";
                    if ($view == "horizontal") {
                        $html .= "class='btn btn-mo btn-block-inline btn-social btn-customtheme btn-custom-dec login-button'";
                    } else {
                        $html .= "class='btn btn-mo btn-block btn-social btn-customtheme btn-custom-dec login-button'";
                    }
                    $html .= "> <i style='padding-top:" . ($customHeight - 35) . "px !important' class='mofa mofa-yahoo'></i> " . $buttonText . " Yahoo</a>";
                } else {
                    $html .= "<a class='login-button' rel='nofollow' title= ' " . $customTextofTitle . " Yahoo'"; if($app_dis!="disable") $html.=" onClick=\"moOpenIdLogin('yaahoo','" . $custom_app . "');\""; $html.=" ><i style='margin-top:10px;width:" . $customSize . "px !important;height:" . $customSize . "px !important;margin-left:" . ($spacebetweenicons) . "px !important;background:" . $customBackground . " !important;font-size: " . ($customSize - 16) . "px !important;'  class='mofa btn-mo mofa-yahoo custom-login-button  " . $selected_theme . "' ></i></a>";
                }
                return $html;
            }
        }

    }

    private function mo_wc_openid_load_login_script() {
        ?>
        <script type="text/javascript">

            var perfEntries = performance.getEntriesByType("navigation");

            if (perfEntries[0].type === "back_forward") {
                location.reload(true);
            }
            function HandlePopupResult(result) {
                window.location = "<?php echo mo_wc_openid_get_redirect_url();?>";
            }
            function moOpenIdLogin(app_name,is_custom_app) {
                var current_url = window.location.href;
                var cookie_name = "redirect_current_url";
                var d = new Date();
                d.setTime(d.getTime() + (2 * 24 * 60 * 60 * 1000));
                var expires = "expires="+d.toUTCString();
                document.cookie = cookie_name + "=" + current_url + ";" + expires + ";path=/";

                <?php
                if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
                    $http = "https://";
                } else {
                    $http =  "http://";
                }
                ?>
                var base_url = '<?php echo esc_url(site_url());?>';
                var request_uri = '<?php echo $_SERVER['REQUEST_URI'];?>';
                var http = '<?php echo $http;?>';
                var http_host = '<?php echo $_SERVER['HTTP_HOST'];?>';
                var default_nonce = '<?php echo wp_create_nonce( 'mo-openid-get-social-login-nonce' ); ?>';
                var custom_nonce = '<?php echo wp_create_nonce( 'mo-openid-oauth-app-nonce' ); ?>';

                if(is_custom_app == 'false'){
                    if ( request_uri.indexOf('wp-login.php') !=-1){
                        var redirect_url = base_url + '/?option=getmosociallogin&wp_nonce=' + default_nonce + '&app_name=';

                    }else {
                        var redirect_url = http + http_host + request_uri;
                        if(redirect_url.indexOf('?') != -1){
                            redirect_url = redirect_url +'&option=getmosociallogin&wp_nonce=' + default_nonce + '&app_name=';
                        }
                        else
                        {
                            redirect_url = redirect_url +'?option=getmosociallogin&wp_nonce=' + default_nonce + '&app_name=';
                        }
                    }
                }
                else {
                    if ( request_uri.indexOf('wp-login.php') !=-1){
                        var redirect_url = base_url + '/?option=oauthredirect&wp_nonce=' + custom_nonce + '&app_name=';


                    }else {
                        var redirect_url = http + http_host + request_uri;
                        if(redirect_url.indexOf('?') != -1)
                            redirect_url = redirect_url +'&option=oauthredirect&wp_nonce=' + custom_nonce + '&app_name=';
                        else
                            redirect_url = redirect_url +'?option=oauthredirect&wp_nonce=' + custom_nonce + '&app_name=';
                    }

                }
                window.location.href = redirect_url + app_name;
            }
        </script>
        <?php
    }
}

/**
 * Sharing Widget Horizontal
 *
 */
class mo_wc_openid_sharing_hor_wid extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'mo_wc_openid_sharing_hor_wid',
            'miniOrange Sharing - Horizontal',
            array(
                'description' => __( 'Share using horizontal widget. Lets you share with Social Apps like Google, Facebook, LinkedIn, Pinterest, Reddit.', 'flw' ),
                'customize_selective_refresh' => true,
            )
        );
    }

    public function widget( $args, $instance ) {
        extract( $args );

        echo $args['before_widget'];
        $this->mo_wc_show_sharing_buttons_horizontal();

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
        return $instance;
    }

    public function mo_wc_show_sharing_buttons_horizontal(){
        global $post;
        $title = str_replace('+', '%20', urlencode($post->post_title));
        $content=strip_shortcodes( strip_tags( get_the_content() ) );
        $post_content=$content;
        $excerpt = '';
        $landscape = 'horizontal';
        include( plugin_dir_path( __FILE__ ) . 'class-mo-wc-openid-social-share.php');
    }

}


/**
 * Sharing Vertical Widget
 *
 */
class mo_wc_openid_sharing_ver_wid extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'mo_wc_openid_sharing_ver_wid',
            'miniOrange Sharing - Vertical',
            array(
                'description' => __( 'Share using a vertical floating widget. Lets you share with Social Apps like Google, Facebook, LinkedIn, Pinterest, Reddit.', 'flw' ),
                'customize_selective_refresh' => true,
            )
        );
    }

    public function widget( $args, $instance ) {
        extract( $args );
        extract( $instance );

        $wid_title = apply_filters( 'widget_title', $instance['wid_title'] );
        $alignment = apply_filters( 'alignment', isset($instance['alignment'])? $instance['alignment'] : 'left');
        $top_offset = apply_filters( 'top_offset', isset($instance['top_offset'])? $instance['top_offset'] : '100');
        $space_icons = apply_filters( 'space_icons', isset($instance['space_icons'])? $instance['space_icons'] : '10');

        echo $args['before_widget'];
        if ( ! empty( $wid_title ) )
            echo $args['before_title'] . $wid_title . $args['after_title'];

        echo "<div style='display:inline-block !important; overflow: visible; z-index: 10000000; padding: 10px; border-radius: 4px; opacity: 1; -webkit-box-sizing: content-box!important; -moz-box-sizing: content-box!important; box-sizing: content-box!important; width:40px; position:fixed;" .(isset($alignment) && $alignment != '' && isset($instance[$alignment.'_offset']) ? esc_attr($alignment) .': '. ( $instance[$alignment.'_offset'] == '' ? 0 : esc_attr($instance[$alignment.'_offset'] )) .'px;' : '').(isset($top_offset) ? 'top: '. ( $top_offset == '' ? 0 : esc_attr($top_offset )) .'px;' : '') ."'>";

        $this->mo_wc_show_sharing_buttons_vertical($space_icons);

        echo '</div>';

        echo $args['after_widget'];
    }

    /*Called when user changes configuration in Widget Admin Panel*/
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
        $instance['alignment'] = $new_instance['alignment'];
        $instance['left_offset'] = $new_instance['left_offset'];
        $instance['right_offset'] = $new_instance['right_offset'];
        $instance['top_offset'] = $new_instance['top_offset'];
        $instance['space_icons'] = $new_instance['space_icons'];
        return $instance;
    }


    public function mo_wc_show_sharing_buttons_vertical($space_icons){
        global $post;
        if($post->post_title) {
            $title = str_replace('+', '%20', urlencode($post->post_title));
        } else {
            $title = get_bloginfo( 'name' );
        }
        $content=strip_shortcodes( strip_tags( get_the_content() ) );
        $post_content=$content;
        $excerpt = '';
        $landscape = 'vertical';

        include( plugin_dir_path( __FILE__ ) . 'class-mo-wc-openid-social-share.php');
    }

    /** Widget edit form at admin panel */
    function form( $instance ) {
        /* Set up default widget settings. */
        $defaults = array('alignment' => 'left', 'left_offset' => '20', 'right_offset' => '0', 'top_offset' => '100' , 'space_icons' => '10');

        foreach( $instance as $key => $value ){
            $instance[ $key ] = esc_attr( $value );
        }

        $instance = wp_parse_args( (array)$instance, $defaults );
        ?>
        <p>
            <script>
                function moOpenIDVerticalSharingOffset(alignment){
                    if(alignment == 'left'){
                        jQuery('.moVerSharingLeftOffset').css('display', 'block');
                        jQuery('.moVerSharingRightOffset').css('display', 'none');
                    }else{
                        jQuery('.moVerSharingLeftOffset').css('display', 'none');
                        jQuery('.moVerSharingRightOffset').css('display', 'block');
                    }
                }
            </script>
            <label for="<?php echo esc_attr($this->get_field_id( 'alignment' )); ?>">Alignment</label>
            <select onchange="moOpenIDVerticalSharingOffset(this.value)" style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'alignment' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'alignment' )); ?>">
                <option value="left" <?php echo $instance['alignment'] == 'left' ? 'selected' : ''; ?>>Left</option>
                <option value="right" <?php echo $instance['alignment'] == 'right' ? 'selected' : ''; ?>>Right</option>
            </select>
        <div class="moVerSharingLeftOffset" <?php echo $instance['alignment'] == 'right' ? 'style="display: none"' : ''; ?>>
            <label for="<?php echo esc_attr($this->get_field_id( 'left_offset' )); ?>">Left Offset</label>
            <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'left_offset' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'left_offset' )); ?>" type="text" value="<?php echo $instance['left_offset']; ?>" />px<br/>
        </div>
        <div class="moVerSharingRightOffset" <?php echo $instance['alignment'] == 'left' ? 'style="display: none"' : ''; ?>>
            <label for="<?php echo esc_attr($this->get_field_id( 'right_offset' )); ?>">Right Offset</label>
            <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'right_offset' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'right_offset' )); ?>" type="text" value="<?php echo $instance['right_offset']; ?>" />px<br/>
        </div>
        <label for="<?php echo esc_attr($this->get_field_id( 'top_offset' )); ?>">Top Offset</label>
        <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'top_offset' )); ?>" name="<?php echo esc_attr( $this->get_field_name( 'top_offset' )); ?>" type="text" value="<?php echo $instance['top_offset']; ?>" />px<br/>
        <label for="<?php echo esc_attr($this->get_field_id( 'space_icons' )); ?>">Space between icons</label>
        <input style="width: 95%" id="<?php echo esc_attr($this->get_field_id( 'space_icons' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'space_icons' )); ?>" type="text" value="<?php echo $instance['space_icons']; ?>" />px<br/>
        </p>
        <?php
    }

}

function mo_wc_openid_disabled_register_message()
{
    $message = get_option('mo_openid_register_disabled_message') . ' Go to <a href="' . site_url() . '">Home Page</a>';
    wp_die($message);
}

function mo_wc_openid_get_redirect_url() {

    $current_url = isset($_COOKIE["redirect_current_url"]) ? $_COOKIE["redirect_current_url"]:$_COOKIE["mo_openid_signup_url"];
    $pos = strpos($_SERVER['REQUEST_URI'], '/openidcallback');

    if ($pos === false) {
        $url = str_replace('?option=moopenid','',$_SERVER['REQUEST_URI']);
        $current_url = str_replace('?option=moopenid','',$current_url);

    } else {
        $temp_array1 = explode('/openidcallback',$_SERVER['REQUEST_URI']);
        $url = $temp_array1[0];
        $temp_array2 = explode('/openidcallback',$current_url);
        $current_url = $temp_array2[0];
    }

    $option = get_option( 'mo_openid_login_redirect' );
    $redirect_url = site_url();

    if( $option == 'same' ) {
        if(!is_null($current_url)){
            if(strpos($current_url,get_option('siteurl').'/wp-login.php')!== false)
            {
                $redirect_url=get_option('siteurl');
            }
            else
                $redirect_url = $current_url;
        }
        else{
            if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'){
                $http = "https://";
            } else {
                $http =  "http://";
            }
            $redirect_url = urldecode(html_entity_decode(esc_url($http . $_SERVER["HTTP_HOST"] . $url)));
            if(html_entity_decode(esc_url(remove_query_arg('ss_message', $redirect_url))) == wp_login_url() || strpos($_SERVER['REQUEST_URI'],'wp-login.php') !== FALSE || strpos($_SERVER['REQUEST_URI'],'wp-admin') !== FALSE){
                $redirect_url = site_url().'/';
            }
        }
    } else if( $option == 'homepage' ) {
        $redirect_url = site_url();
    } else if( $option == 'dashboard' ) {
        $redirect_url = admin_url();
    } else if( $option == 'custom' ) {
        $redirect_url = get_option('mo_openid_login_redirect_url');
    }else if($option == 'relative') {
        $redirect_url =  site_url() . (null !== get_option('mo_openid_relative_login_redirect_url')?get_option('mo_openid_relative_login_redirect_url'):'');
    }

    if(strpos($redirect_url,'?') !== FALSE) {
        $redirect_url .= get_option('mo_openid_auto_register_enable') ? '' : '&autoregister=false';
    } else{
        $redirect_url .= get_option('mo_openid_auto_register_enable') ? '' : '?autoregister=false';
    }
    return $redirect_url;
}

function mo_wc_openid_redirect_after_logout($logout_url)
{
    if (get_option('mo_openid_logout_redirection_enable')) {
        $logout_redirect_option = get_option('mo_openid_logout_redirect');
        $redirect_url = site_url();
        if ($logout_redirect_option == 'homepage') {
            $redirect_url = $logout_url . '&redirect_to=' . home_url();
        } else if ($logout_redirect_option == 'currentpage') {
            if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                $http = "https://";
            } else {
                $http = "http://";
            }
            $redirect_url = $logout_url . '&redirect_to=' . $http . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
        } else if ($logout_redirect_option == 'login') {
            $redirect_url = $logout_url . '&redirect_to=' . site_url() . '/wp-admin';
        } else if ($logout_redirect_option == 'custom') {
            $redirect_url = $logout_url . '&redirect_to=' . site_url() . (null !== get_option('mo_openid_logout_redirect_url') ? get_option('mo_openid_logout_redirect_url') : '');
        }
        return $redirect_url;
    } else {
        return $logout_url;
    }

}

function mo_wc_openid_login_validate(){

    

    if ( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'getmosociallogin' ) !== false ) {

        if (isset($_REQUEST['wp_nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['wp_nonce']);
            if (!wp_verify_nonce($nonce, 'mo-openid-get-social-login-nonce')) {
                wp_die('<strong>ERROR</strong>: Invalid Request.');
            } else {
                mo_wc_openid_initialize_social_login();
            }
        }
    }
    else if( isset($_POST['mo_openid_go_back_registration_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_go_back_registration" ){
        $nonce = sanitize_text_field($_POST['mo_openid_go_back_registration_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-go-back-register-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Invalid Request.');
        } else {
            update_option('mo_openid_verify_customer','true');
        }
    }
    else if ( isset($_POST['mo_openid_custom_form_submitted_nonce']) and isset($_POST['username']) and $_POST['option'] == 'mo_openid_custom_form_submitted' ){
        $nonce = sanitize_text_field($_POST['mo_openid_custom_form_submitted_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-custom-form-submitted-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Invalid Request.' . $nonce);
        } else {
            global $wpdb;
            $db_prefix = $wpdb->prefix;
            $curr_user=get_current_user_id();
            if($curr_user!=0) {
                mo_wc_update_custom_data($curr_user);
                header("Location:".get_option('profile_completion_page'));
                exit;
            }
            $user_picture = sanitize_text_field($_POST["user_picture"]);
            $user_url = sanitize_text_field($_POST["user_url"]);
            $last_name = sanitize_text_field($_POST["last_name"]);
            $username= sanitize_text_field($_POST["username"]);
            $user_email= sanitize_text_field($_POST["user_email"]);
            $random_password= sanitize_text_field($_POST["random_password"]);
            $user_full_name = sanitize_text_field($_POST["user_full_name"]);
            $first_name = sanitize_text_field($_POST["first_name"]);
            $decrypted_app_name = sanitize_text_field($_POST["decrypted_app_name"]);
            $decrypted_user_id = sanitize_text_field($_POST["decrypted_user_id"]);
            $call = sanitize_text_field($_POST["call"]);
            $user_profile_url = sanitize_text_field($_POST["user_profile_url"]);
            $social_app_name = sanitize_text_field($_POST["social_app_name"]);
            $social_user_id = sanitize_text_field($_POST["social_user_id"]);

            $userdata = array(
                'user_login'  => $username,
                'user_email'    => $user_email,
                'user_pass'   =>  $random_password,
                'display_name' => $user_full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'user_url' => $user_url,
            );
			
			// Checking if username already exist
            $user_name_user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users where user_login = %s", $userdata['user_login']));

            if( isset($user_name_user_id) ){
                $email_array = explode('@', $user_email);
                $user_name = $email_array[0];
                $user_name_user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users where user_login = %s", $user_name));
                $i = 1;
                while(!empty($user_name_user_id) ){
                    $uname=$user_name.'_' . $i;
                    $user_name_user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " .$db_prefix."users where user_login = %s", $uname));
                    $i++;
                    if(empty($user_name_user_id)){
                        $userdata['user_login']= $uname;
                        $username =$uname;
                    }
                }

                if($i==1)
                    $userdata['user_login'] = $uname;

                if( isset($user_name_user_id) ){
                    echo '<br/>'."Error Code Existing Username: ".get_option('mo_existing_username_error_message');
                    exit();
                }
            }
			
            $user_id   = wp_insert_user( $userdata);
            if(is_wp_error( $user_id )) {
                print_r($user_id);
                wp_die("Error Code ".$call.": ".get_option('mo_registration_error_message'));
            }

            update_option('mo_openid_user_count',get_option('mo_openid_user_count')+1);

            if($social_app_name!="")
                $appname=$social_app_name;
            else
                $appname=$decrypted_app_name;

            $session_values= array(
                'username' => $username,
                'user_email' => $user_email,
                'user_full_name' => $user_full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'user_url' => $user_url,
                'user_picture' => $user_picture,
                'social_app_name' => $appname,
                'social_user_id' => $social_user_id,
            );

            mo_wc_openid_start_session_login($session_values);
            $user	= get_user_by('id', $user_id );
            mo_wc_update_custom_data($user_id);
            //registration hook
            do_action( 'mo_user_register', $user_id,$user_profile_url);
            mo_wc_openid_link_account($user->user_login, $user);
            mo_wc_openid_login_user($user_id,$user,$user_picture);
        }
    }

    else if(isset($_POST['mo_openid_profile_form_submitted_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_profile_form_submitted"){
        $nonce = sanitize_text_field($_POST['mo_openid_profile_form_submitted_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-profile-form-submitted-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Invalid Request.' . $nonce);
        } else {
            $username = sanitize_text_field($_POST['username_field']);
            $user_email = sanitize_email($_POST['email_field']);
            $user_picture = sanitize_text_field($_POST["user_picture"]);
            $user_url = sanitize_text_field($_POST["user_url"]);
            $last_name = sanitize_text_field($_POST["last_name"]);
            $user_full_name = sanitize_text_field($_POST["user_full_name"]);
            $first_name = sanitize_text_field($_POST["first_name"]);
            $decrypted_app_name = sanitize_text_field($_POST["decrypted_app_name"]);
            $decrypted_user_id = sanitize_text_field($_POST["decrypted_user_id"]);
            mo_wc_openid_save_profile_completion_form($username, $user_email, $first_name, $last_name, $user_full_name, $user_url, $user_picture, $decrypted_app_name, $decrypted_user_id);
        }
    }
    else if( isset($_POST['mo_openid_go_back_login_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_go_back_login" ){
        $nonce = sanitize_text_field($_POST['mo_openid_go_back_login_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-go-back-login-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Invalid Request.');
        } else {
            update_option('mo_openid_registration_status','');
            delete_option('mo_openid_admin_email');
            delete_option('mo_openid_admin_phone');
            delete_option('mo_openid_admin_password');
            delete_option('mo_openid_admin_customer_key');
            delete_option('mo_openid_verify_customer');
        }
    }
    else if(isset($_POST['mo_openid_forgot_password_nonce']) and isset($_POST['option']) and $_POST['option'] == 'mo_openid_forgot_password'){
        $nonce = sanitize_text_field($_POST['mo_openid_forgot_password_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-forgot-password-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Invalid Request.');
        } else {
            $email ='';
            if( mo_wc_openid_check_empty_or_null( $email ) ) {
                if( mo_wc_openid_check_empty_or_null( $_POST['email'] ) ) {
                    update_option( 'mo_openid_message', 'No email provided. Please enter your email below to reset password.');
                    mo_wc_openid_show_error_message();
                    if(get_option('regi_pop_up') =="yes"){
                        update_option("pop_login_msg",get_option("mo_openid_message"));
                        mo_wc_pop_show_verify_password_page();
                    }
                    return;
                } else {
                    $email = sanitize_email($_POST['email']);
                }
            }
            $customer = new MO_WC_CustomerOpenID();
            $content = json_decode($customer->mo_wc_forgot_password($email),true);
            if(strcasecmp($content['status'], 'SUCCESS') == 0){
                update_option( 'mo_openid_message','You password has been reset successfully. Please enter the new password sent to your registered mail here.');
                mo_wc_openid_show_success_message();
                if(get_option('regi_pop_up') =="yes"){
                    update_option("pop_login_msg",get_option("mo_openid_message"));
                    mo_wc_pop_show_verify_password_page();
                }
            }else{
                update_option( 'mo_openid_message','An error occurred while processing your request. Please make sure you are registered in miniOrange with the <b>'. $content['email'] .'</b> email address. ');
                mo_wc_openid_show_error_message();
                if(get_option('regi_pop_up') =="yes"){
                    update_option("pop_login_msg",get_option("mo_openid_message"));
                    mo_wc_pop_show_verify_password_page();
                }
            }
        }
    }
    else if( isset($_POST['mo_openid_connect_register_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_connect_register_customer" ) {	//register the admin to miniOrange
        $nonce = sanitize_text_field($_POST['mo_openid_connect_register_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-connect-register-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Invalid Request.');
        } else {
            mo_wc_openid_register_user();
        }
    }
    else if( isset( $_POST['show_login'] ) )
    {
        mo_wc_pop_show_verify_password_page();
    }

    else if( isset($_POST['mo_openid_show_profile_form_nonce']) and isset( $_POST['option'] ) and strpos( $_POST['option'], 'mo_openid_show_profile_form' ) !== false ) {
        $nonce = sanitize_text_field($_POST['mo_openid_show_profile_form_nonce']);
        if (!wp_verify_nonce($nonce, 'mo-openid-user-show-profile-form-nonce')) {
            wp_die('<strong>ERROR</strong>: Invalid Request.');
        } else {

            $user_details = array(
                'username' => sanitize_text_field($_POST['username_field']),
                'user_email' => sanitize_email($_POST['email_field']),
                'user_full_name' => sanitize_text_field($_POST["user_full_name"]),
                'first_name' => sanitize_text_field($_POST["first_name"]),
                'last_name' => sanitize_text_field($_POST["last_name"]),
                'user_url' => sanitize_text_field($_POST["user_url"]),
                'user_picture' => sanitize_text_field($_POST["user_picture"]),
                'social_app_name' => sanitize_text_field($_POST["decrypted_app_name"]),
                'social_user_id' => sanitize_text_field($_POST["decrypted_user_id"]),
            );
            echo mo_wc_openid_profile_completion_form($user_details, '1');
            exit;
        }
    }

    else if( isset( $_POST['option'] ) and strpos( $_POST['option'], 'mo_openid_subscriber_form' ) !== false ){
        $userid = sanitize_text_field($_POST["userid"]);
        $user = get_user_by('id', $userid);

        do_action( 'miniorange_collect_attributes_for_authenticated_user', $user, mo_wc_openid_get_redirect_url());
        do_action('wp_login', $user->user_login, $user);
    }

    else if((isset($_POST['action'])) && (strpos($_POST['action'], 'delete_social_profile_data') !== false) && isset($_POST['user_id'])){
        // delete first name, last name, user_url and profile_url from usermeta
        $id = sanitize_text_field($_POST['user_id']);
        mo_wc_openid_delete_social_profile($id);
    }
    else if ( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oauthredirect' ) !== false ) {
        if(isset($_REQUEST['wp_nonce'])){
            $nonce = sanitize_text_field($_REQUEST['wp_nonce']);
            if ( ! wp_verify_nonce( $nonce, 'mo-openid-oauth-app-nonce' ) ) {
                wp_die('<strong>ERROR</strong>: Invalid Request.');
            }
            else {
                $appname = sanitize_text_field($_REQUEST['app_name']);
                mo_wc_openid_custom_app_oauth_redirect($appname);
            }
        }
    }

    else if( isset($_POST['mo_openid_user_otp_validation_nonce']) and isset( $_POST['otp_field']) and $_POST['option'] == 'mo_openid_otp_validation' )
    {
        $nonce = sanitize_text_field($_POST['mo_openid_user_otp_validation_nonce']);
        if ( ! wp_verify_nonce( $nonce, 'mo-openid-user-otp-validation-nonce' ) ) {
            wp_die('<strong>ERROR</strong>: Invalid Request.');
        } else {
            $username = sanitize_text_field($_POST["username_field"]);
            $user_email = sanitize_email($_POST["email_field"]);
            $transaction_id = sanitize_text_field($_POST["transaction_id"]);
            $otp_token = sanitize_text_field($_POST['otp_field']);
            $user_picture = sanitize_text_field($_POST["user_picture"]);
            $user_url = sanitize_text_field($_POST["user_url"]);
            $last_name = sanitize_text_field($_POST["last_name"]);
            $user_full_name = sanitize_text_field($_POST["user_full_name"]);
            $first_name = sanitize_text_field($_POST["first_name"]);
            $decrypted_app_name = sanitize_text_field($_POST["decrypted_app_name"]);
            $decrypted_user_id = sanitize_text_field($_POST["decrypted_user_id"]);
            if (isset($_POST['resend_otp'])) {
                $send_content = mo_wc_send_otp_token($user_email);
                if ($send_content['status'] == 'FAILURE') {
                    $message = "Error Code 3: " . get_option('mo_email_failure_message');
                    wp_die($message);
                }
                $transaction_id = $send_content['tId'];
                echo mo_wc_openid_validate_otp_form($username, $user_email, $transaction_id, $user_picture, $user_url, $last_name, $user_full_name, $first_name, $decrypted_app_name, $decrypted_user_id);

                exit;
            }

            mo_wc_openid_social_login_validate_otp($username, $user_email, $first_name, $last_name, $user_full_name, $user_url, $user_picture, $decrypted_app_name, $decrypted_user_id, $otp_token, $transaction_id);
        }
    }

    else if( isset($_POST['mo_openid_connect_verify_nonce']) and isset( $_POST['option'] ) and $_POST['option'] == "mo_openid_connect_verify_customer" ) {    //register the admin to miniOrange
        $nonce = sanitize_text_field($_POST['mo_openid_connect_verify_nonce']);
        if (!wp_verify_nonce($nonce, 'mo-openid-connect-verify-nonce')) {
            wp_die('<strong>ERROR</strong>: Invalid Request.');
        }
        else {
            mo_wc_register_old_user();
        }
    }

    else if ( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'moopenid' ) !== false ){
        mo_wc_openid_process_social_login();
    }

    else if( strpos( $_SERVER['REQUEST_URI'], "openidcallback") !== false ||((strpos( $_SERVER['REQUEST_URI'], "oauth_token")!== false)&&(strpos( $_SERVER['REQUEST_URI'], "oauth_verifier") ))) {
        mo_wc_openid_process_custom_app_callback();
    }
}

function mo_wc_get_current_customer(){
    $customer = new MO_WC_CustomerOpenID();
    $content = $customer->mo_wc_get_customer_key();
    $customerKey = json_decode( $content, true );
    if( isset($customerKey) ) {
        update_option( 'mo_openid_admin_customer_key', $customerKey['id'] );
        update_option( 'mo_openid_admin_api_key', $customerKey['apiKey'] );
        update_option( 'mo_openid_customer_token', $customerKey['token'] );
        update_option('mo_openid_admin_password', '' );
        update_option( 'mo_openid_message', 'Your account has been retrieved successfully.' );
        delete_option('mo_openid_verify_customer');
        delete_option('mo_openid_new_registration');
        if($_POST['action']=='mo_register_new_user')
            wp_send_json(["success" => 'Your account has been retrieved successfully.']);
        else
            mo_wc_openid_show_success_message();
    } else {
        update_option( 'mo_openid_message', 'You already have an account with miniOrange. Please enter a valid password.');
        update_option('mo_openid_verify_customer', 'true');
        delete_option('mo_openid_new_registration');
        if($_POST['action']=='mo_register_new_user')
            wp_send_json(["error" => 'You already have an account with miniOrange. Please enter a valid password.']);
        else {
            mo_wc_openid_show_error_message();
            if (get_option('regi_pop_up') == "yes") {
                update_option("pop_login_msg", get_option("mo_openid_message"));
                mo_wc_pop_show_verify_password_page();
            }
        }
    }
}

function mo_wc_encrypt_data($data, $key) {

    return base64_encode(openssl_encrypt($data, 'aes-128-ecb', $key, OPENSSL_RAW_DATA));

}

function mo_wc_openid_update_role($user_id='', $user_url=''){
    // save the profile url in user meta // this was added to save facebook url in user meta as it is more than 100 chars
    update_user_meta($user_id, 'moopenid_user_profile_url',$user_url);
    if(get_option('mo_openid_customised_field_enable') != 1 || get_option('mo_openid_update_role_addon') != 1) {
        if (get_option('mo_openid_login_role_mapping')) {
            $user = get_user_by('ID', $user_id);
            $user->set_role(get_option('mo_openid_login_role_mapping'));
        }
    }
}

function mo_wc_openid_login_redirect($username = '', $user = NULL){
    mo_wc_openid_start_session();
    if(is_string($username) && $username && is_object($user) && !empty($user->ID) && ($user_id = $user->ID) && isset($_SESSION['mo_login']) && $_SESSION['mo_login']){
        $_SESSION['mo_login'] = false;
        wp_set_auth_cookie( $user_id, true );
        $redirect_url = mo_wc_openid_get_redirect_url();
        wp_redirect($redirect_url);
        exit;
    }
}

function mo_wc_openid_delete_profile_column($value, $columnName, $userId){
    if('mo_openid_delete_profile_data' == $columnName){
        global $wpdb;
        $socialUser = $wpdb->get_var($wpdb->prepare('SELECT id FROM '. $wpdb->prefix .'mo_openid_linked_user WHERE user_id = %d ', $userId));

        if($socialUser > 0 && !get_user_meta($userId,'mo_openid_data_deleted')){
            return '<a href="javascript:void(0)" onclick="javascript:moOpenidDeleteSocialProfile(this, '. $userId .')">Delete</a>';
        }
        else
            return '<p>NA</p>';
    }
    if('mo_openid_linked_social_app' == $columnName){
        global $wpdb;
        $socialUser = $wpdb->get_var($wpdb->prepare('SELECT id FROM '. $wpdb->prefix .'mo_openid_linked_user WHERE user_id = %d ', $userId));
        $a=$wpdb->get_col('SELECT all linked_social_app FROM '. $wpdb->prefix .'mo_openid_linked_user where user_id='.$userId);
        $b='';
        foreach ($a as $x=>$y)
        {
            if($y=='facebook') {$y='Facebook';}if($y=='google') {$y='Google';}if($y=='instagram'){$y='Instagram';}if($y=='linkedin'){$y='LinkedIn';}if($y=='amazon') {$y='Amazon';}
            if($y=='vkontakte'){$y='vKontakte';} if($y=='twitter'){$y='Twitter';}if($y=='salesforce'){$y='Salesforce';}if($y=='windowslive'){$y='Windows Live';}if($y=='yahoo'){$y='Yahoo';}

            $b=$b.' '.$y.'<br>';
        }
        if($socialUser > 0){
            return $b;
        }
    }
}

function mo_wc_openid_link_account( $username, $user ){
    if($user){
        $userid = $user->ID;
    }
    mo_wc_openid_start_session();

    $user_email =  isset($_SESSION['user_email']) ? sanitize_text_field($_SESSION['user_email']):'';
    $social_app_identifier = isset($_SESSION['social_user_id']) ? sanitize_text_field($_SESSION['social_user_id']):'';
    $social_app_name = isset($_SESSION['social_app_name']) ? sanitize_text_field($_SESSION['social_app_name']):'';
    if(empty($user_email)){
        $user_email=$user->user_email;
    }
    //if user is coming through default wordpress login, do not proceed further and return
    if(isset($userid) && empty($social_app_identifier) && empty($social_app_name) ) {
        return;
    }
    elseif(!isset($userid)){
        return;
    }

    global $wpdb;
    $db_prefix = $wpdb->prefix;
    $linked_email_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM ".$db_prefix."mo_openid_linked_user where linked_email = '%s' AND linked_social_app = '%s'",$user_email,$social_app_name));

    // if a user with given email and social app name doesn't already exist in the mo_openid_linked_user table
    if(!isset($linked_email_id)){
        mo_wc_openid_insert_query($social_app_name,$user_email,$userid,$social_app_identifier);
    }
}