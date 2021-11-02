<?php

function mo_wc_openid_linkSocialAcc(){

    ?><br/>
    <div class="mo_openid_table_layout">
        <form>
            <table>
                <tr>
                    <td colspan="2">
                        <div>
                            <label class="mo_openid_note_style"> <?php echo mo_wc_sl('Enable account linking to let your users link their Social accounts with existing WordPress account. Users will be prompted with the option to either link to any existing account using WordPress login page or register as a new user.');?></label><br/>
                            <label style="cursor: auto" class="mo_openid_checkbox_container"><?php echo mo_wc_sl('Enable linking of Social Accounts');?>
                                <input type="checkbox" disabled />
                                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr><td colspan="2"></td></tr>
                    <tr id="account_link_customized_text"><td colspan="2"><h3 style="float: left"><?php echo mo_wc_sl('Customize Text for Account Linking');?></h3><a style="cursor: pointer; color: red; float: right;margin-right: 325px;margin-top: 20px" onclick="customize_account_linking_img()"><?php echo mo_wc_sl('Preview Account Linking form');?></a></td></tr>
                    <tr id="acc_link_img"><td colspan="2"></td></tr>
                    <tr id="account_link_customized_text"><td class="mo_openid_fix_fontsize" style="width: 40%">1. <?php echo mo_wc_sl('Enter title of Account linking form');?>:</td><td><input disabled class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2" type="text" /></td></tr>
                    <tr id="account_link_customized_text"><td class="mo_openid_fix_fontsize" style="width: 40%">2.<?php echo mo_wc_sl(' Enter button text for create new user');?>:</td><td><input disabled class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2" type="text""/></td></tr>
                    <tr id="account_link_customized_text">
                        <td class="mo_openid_fix_fontsize" style="width: 40%">
                            3.<?php echo mo_wc_sl( 'Enter button text for Link to existing user:');?></td><td><input disabled class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2" type="text""/></td></tr>
                <tr><td></td></tr>
                    <tr id="account_link_customized_text"><td class="mo_openid_fix_fontsize" colspan="2">4. <?php echo mo_wc_sl('Enter instruction to Create New Account :');?><br/><input disabled class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2;width: 98%" type="text"/>
                        </td>
                    </tr>
                <tr><td></td></tr>
                    <tr id="account_link_customized_text">
                        <td class="mo_openid_fix_fontsize" colspan="2">
                            5.<?php echo mo_wc_sl(' Enter instructions to link to an existing account :');?><br/><input disabled class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2;width: 98%" type="text"/>
                        </td>
                    </tr>
                <tr><td></td></tr>
                    <tr id="account_link_customized_text"><td disabled class="mo_openid_fix_fontsize" colspan="2">6.<?php echo mo_wc_sl('Enter extra instructions for account linking ');?>:<br/><input disabled class="mo_openid_textfield_css" style="border: 1px solid ;border-color: #0867b2;width: 98%" style="width:98%;margin-left: 0px;" type="text" />
                        </td>
                    </tr>
                        <tr id="disp_logo"><td colspan="2"> <br/>
                                <label class="mo_openid_checkbox_container"><?php echo mo_wc_sl('Display miniOrange logo with social login icons on account completion forms');?>
                                    <input  type="checkbox"?>  />
                                    <span class="mo_openid_checkbox_checkmark_disable"></span>
                                </label>
                                <br/></td></tr>
                                  <tr id="save_btn"><td><br/><input disabled type="submit" name="submit" value="<?php echo mo_wc_sl('Save');?>" style="width:150px;text-shadow: none;background-color:#0867b2;color:white;box-shadow:none;" class="button button-primary button-large" /></td></tr>
                    <tr id="acc_link"><td> </td></tr>
            </table>
        </form>
    </div>
    <script>
        //to set heading name
        jQuery('#mo_openid_page_heading').text('<?php echo mo_wc_sl('Link Social Account');?>');
        var win_height = jQuery('#mo_openid_menu_height').height();
        //win_height=win_height+18;
        jQuery(".mo_container").css({height:win_height});
        var temp = jQuery("<a style=\"left: 1%; padding:4px; position: relative; text-decoration: none\" class=\"mo-openid-premium\" href=\"<?php echo add_query_arg(array('tab' => 'licensing_plans'), $_SERVER['REQUEST_URI']); ?>\">PRO</a>");
        jQuery("#mo_openid_page_heading").append(temp);

        var custom_link;
        var custom_link_img;
        var custom_profile_img;
        id=document.getElementById('account_linking_enable');
        var checkbox1 = document.getElementById('account_linking_enable');
        jQuery(document).ready(function(){
                custom_link= 1;
                custom_link_img=1;
            }
        );
        function customize_account_linking_img(){
            if(custom_link_img==1){
                jQuery("<tr id=\"account_linking_img\"><td colspan=\"2\"><img style=\"margin-top: 15px;margin-left: 15px;\" src=\"<?php echo plugin_dir_url(dirname(__DIR__));?>includes/images/account_linking.png\"></td></tr>").insertBefore(jQuery("#acc_link_img"));
                custom_link_img=2;
            }else{
                jQuery("#account_linking_img").remove();
                custom_link_img=1;
            }
        }
    </script>
    <?php
}