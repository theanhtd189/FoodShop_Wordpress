<?php
function mo_wc_mailchimp_int()
{
    ?>
    <div class="mo_openid_table_layout">
        <form>
            <table id="maichimp_integration"><tr><td>
                        <p><b><?php echo mo_wc_sl('A user is added as a subscriber to a mailing list in MailChimp when that user registers using social login. First name, last name and email are also captured for that user in the Mailing List.');?></b></p>
                        <label class="mo_openid_note_style" style="cursor: auto">For more information please refer to the <a href="https://plugins.miniorange.com/guide-to-configure-mailchimp-integration-with-wordpress-social-login" target="_blank">MailChimp Guide</a>  /  <a href="https://www.youtube.com/watch?v=3Zh5gUX0O_A" target="_blank">MailChimp Video</a></label><br>
                        <b><?php echo mo_wc_sl('List Id');?>:</b><input disabled size="50" class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2;margin-left: 1%" > <br><br>
                        <b><?php echo mo_wc_sl('API Key:');?> </b><input disabled size="50" class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2" type="text" ><br><br>

                        <label class="mo_openid_checkbox_container_disable"><strong><?php echo mo_wc_sl('Ask user for permission to be added in MailChimp Subscriber list');?> </strong>
                            <input  type="checkbox"/>
                            <span class="mo_openid_checkbox_checkmark_disable"></span>
                        </label>

                        (<?php echo mo_wc_sl('If unchecked, user will get subscribed during registration.');?>)
                        <br><br>
                        <b><?php echo mo_wc_sl('Click on Download button to get a list of emails of WordPress users registered on your site. You can import this file in MailChimp.');?><br><br>
                            <input disabled type="button" value="Save "  class="button button-primary button-large" />
                            <a disabled="disabled" style="width:190px;" class="button button-primary button-large">
                                <?php echo mo_wc_sl('Download emails of users');?>
                            </a><br>
                    </td></tr></table>
        </form>
    </div>

<script>
    jQuery('#mo_openid_page_heading').text('<?php echo mo_wc_sl("Mailchimp Integration"); ?>');
    var temp = jQuery("<a style=\"left: 1%; padding:4px; position: relative; text-decoration: none\" class=\"mo-openid-premium\" href=\"<?php echo add_query_arg(array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI']); ?>\">PRO</a>");
    jQuery("#mo_openid_page_heading").append(temp);
    var win_height = jQuery('#mo_openid_menu_height').height();
    jQuery(".mo_container").css({height:win_height});
</script>
    <?php
}
