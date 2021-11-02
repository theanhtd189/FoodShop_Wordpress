<?php
function mo_wc_buddypress_int()
{
    ?>
    <table id="buddypress_mapping" style="width:100%"><tr><td>
                        
                    <?php
                        echo "<div style='text-align: center'><p>You have not setup attribute mapping for any apps yet. Please click on <b>Add Application</b> to configure mapping for each app.</p>";
                        echo "</div>";
                        echo "<br><div style='text-align: center'><input style='text-align:center;' type=\"button\" name=\"add_app\" id=\"add_app\" class=\"button button-primary button-large\" value=\"Add Application\"  /></div>";
                        
                    ?></td></tr></table>

        <br>
        <div class="mo_openid_highlight">
        <h3 style="margin-left: 2%;line-height: 210%;color: white;" id="mo_openid_send_email_user_collapse"><?php echo mo_wc_sl('BuddyPress Display Options');?></h3>
        </div>
        <div class="mo_openid_table_layout" id="mo_openid_disp_opt_tour"><br/>
        <div style="width:40%; background:white; float:left; border: 1px transparent;">
        <b style="font-size: 17px;"><?php echo mo_wc_sl('BuddyPress / BuddyBoss display options');?> <a style="left: 1%; position: relative; text-decoration: none" class="mo-openid-premium" href="<?php echo add_query_arg( array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI'] ); ?>"><?php echo mo_wc_sl('PRO');?></a></b><br><br>

                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl('Before BuddyPress / BuddyBoss Registration Form');?>
                    <input type="checkbox"  /><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>

                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl('Before BuddyPress Account Details');?>
                    <input type="checkbox"  /><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>

                <label class="mo_openid_checkbox_container_disable"><?php echo mo_wc_sl('After BuddyPress / BuddyBoss Registration Form');?>
                    <input type="checkbox"  /><br><br>
                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                </label>
        </div>  
        <div style="width:50%; float:right; margin-left: 1%; background:white; border: 1px transparent;">
            <iframe width="450" height="270" src="https://www.youtube.com/embed/Iia1skKRYBU"></iframe>
        </div>
        </div>      
        <br/>

<script>
    jQuery('#mo_openid_page_heading').text('<?php echo mo_wc_sl("BuddyPress Extended Attributes Mapping"); ?>');
    var temp = jQuery("<a style=\"left: 1%; padding:4px; position: relative; text-decoration: none\" class=\"mo-openid-premium\" href=\"<?php echo add_query_arg(array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI']); ?>\">PRO</a>");
    jQuery("#mo_openid_page_heading").append(temp);
    var win_height = jQuery('#mo_openid_menu_height').height();
    jQuery(".mo_container").css({height:win_height});
</script>
    <?php
}
