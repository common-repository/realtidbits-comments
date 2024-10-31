<?php
global $rtb_api;

require(ABSPATH . 'wp-includes/version.php');

if ( !current_user_can('manage_options') ) {
    die();
}

// HACK: For old versions of WordPress
if ( !function_exists('wp_nonce_field') ) {
    function wp_nonce_field() {}
}

// Handle uninstallation.
if ( isset($_POST['uninstall']) ) {
    foreach (rtb_comments_options() as $opt) {
        delete_option($opt);
    }
    unset($_POST);
    rtb_uninstall_database();
?>
<div class="wrap">
    <h2><?php echo rtb_i('RealTidbits Comments Uninstalled'); ?></h2>
    <form method="POST" action="?page=rtb-comments">
        <p>RealTidbits Comments has been uninstalled successfully.</p>
        <ul style="list-style: circle;padding-left:20px;">
            <li>Local settings for the plugin were removed.</li>
            <li>Database changes by RealTidbits were reverted.</li>
        </ul>
        <p>If you wish to <a href="?page=rtb-comments&amp;step=1">reinstall</a>, you can do that now.</p>
    </form>
</div>
<?php
die();
}

// Handle advanced options.
if ( isset($_POST['rtb_comments_appkey']) && isset($_POST['rtb_comments_replace']) ) {
	//
    update_option('rtb_comments_appkey', trim(stripslashes($_POST['rtb_comments_appkey'])));
	if(isset($_POST['rtb_comments_secret_key'])) update_option('rtb_comments_secret_key', trim(stripslashes($_POST['rtb_comments_secret_key'])));	
	update_option('rtb_comments_backplane_busname', trim(stripslashes($_POST['rtb_comments_backplane_busname'])));
	update_option('rtb_comments_backplane_key', trim(stripslashes($_POST['rtb_comments_backplane_key'])));
	update_option('rtb_comments_rpx', trim(stripslashes($_POST['rtb_comments_rpx'])));
	update_option('rtb_comments_janrain_app', trim($_POST['rtb_comments_janrain_app']));
    update_option('rtb_comments_replace', $_POST['rtb_comments_replace']);
	update_option('rtb_comments_style', $_POST['rtb_comments_style']);
	update_option('rtb_comments_login', $_POST['rtb_comments_login']);
    update_option('rtb_comments_cc_fix', isset($_POST['rtb_comments_cc_fix']));
    update_option('rtb_comments_manual_sync', isset($_POST['rtb_comments_manual_sync']));
    update_option('rtb_comments_disable_ssr', isset($_POST['rtb_comments_disable_ssr']));
	//
	update_option('rtb_comments_email_notification', trim($_POST['rtb_comments_email_notification']));
	update_option('rtb_comments_widget_params', stripslashes($_POST['rtb_comments_widget_params']));
	
	update_option('rtb_use_staging', isset($_POST['rtb_use_staging']));
	
	//
    echo ('<b>Your settings have been saved.</b>');
}

function curl_download($Url){
    $ch = curl_init();
    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);
    // User agent
    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Download the given URL, and return output
    $output = curl_exec($ch);
    // Close the cURL resource, and free system resources
    curl_close($ch);
    return $output;
}


// set plugin install timestamp\
$install_timstamp = get_option('rtb_comments_install_timestamp');
if(empty($install_timstamp)) {
	update_option('rtb_comments_install_timestamp', time());
};

// handle rtb_comments_active
if (isset($_GET['active'])) {
    update_option('rtb_comments_active', ($_GET['active'] == '1' ? '1' : '0'));
}

?>
        <div>
            <h2>RealTidbits Comments</h2>

<?php
    $rtb_replace = get_option('rtb_comments_replace');
	$rtb_style = get_option('rtb_comments_style');
	$rtb_login = get_option('rtb_comments_login');
    $rtb_comments_appkey = get_option('rtb_comments_appkey');
    $rtb_comments_secret_key = get_option('rtb_comments_secret_key');
	$rtb_comments_backplane_busname = get_option('rtb_comments_backplane_busname');
	$rtb_comments_backplane_key = get_option('rtb_comments_backplane_key');
	$rtb_comments_rpx = get_option('rtb_comments_rpx');
	$rtb_comments_janrain_app = get_option('rtb_comments_janrain_app');
	
	$rtb_comments_email_notification = get_option('rtb_comments_email_notification');
	$rtb_comments_widget_params = get_option('rtb_comments_widget_params');

    $rtb_manual_sync = get_option('rtb_comments_manual_sync');
    $rtb_disable_ssr = get_option('rtb_comments_disable_ssr');
	
	$rtb_use_staging = get_option('rtb_use_staging');
?>
        <p>Version: <?php echo RTB_COMMENTS_VERSION; ?><br />
        Installed: <?php echo date("F j, Y, g:i a", $install_timstamp); ?>
        </p>
        <?php
        if (get_option('rtb_comments_active') === '0') {
            // plugin is not active
            echo '<p class="status">RealTidbits comments are currently disabled. (<strong><a href="?page=rtb-comments&amp;active=1">Enable</a></strong>)</p>';
        } else {
            echo '<p class="status">RealTidbits comments are currently enabled. (<strong><a href="?page=rtb-comments&amp;active=0">Disable</a></strong>)</p>';
        }
        ?>
        <form method="POST">
        <h3>Configuration</h3>
        <table class="form-table">
            <!-- <tr>
                <th scope="row" valign="top"><?php echo rtb_i('New Comment Notification Email'); ?></th>
                <td>
                    <input name="rtb_comments_email_notification" value="<?php echo esc_attr($rtb_comments_email_notification); ?>" tabindex="1" type="text" />
                    <br />
                    <?php echo rtb_i('Enter email to send notifications of new comments. Leave blank for no notifications.'); ?>
                </td>
            </tr>-->
            
            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('appkey'); ?></th>
                <td>
                    <input name="rtb_comments_appkey" value="<?php echo esc_attr($rtb_comments_appkey); ?>" tabindex="1" type="text" style="width:400px;" />
                    <br />
                    <?php echo rtb_i('Application key identifier for your account.'); ?>
                </td>
            </tr>

            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Secret Key'); ?></th>
                <td>
                    <input type="text" name="rtb_comments_secret_key" value="<?php echo esc_attr($rtb_comments_secret_key); ?>" tabindex="2"  width="300">
                    <br />
                    <?php echo rtb_i('This is set for you when going through the installation steps.'); ?>
                </td>
            </tr>

            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Backplane busName'); ?></th>
                <td>
                    <input type="text" name="rtb_comments_backplane_busname" value="<?php echo esc_attr($rtb_comments_backplane_busname); ?>" tabindex="3" style="width:400px;">
                    <br />
                    <?php echo rtb_i('Backplane business name for your appkey.'); ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Backplane Key'); ?></th>
                <td>
                    <input type="text" name="rtb_comments_backplane_key" value="<?php echo esc_attr($rtb_comments_backplane_key); ?>" tabindex="3" style="width:400px;">
                    <br />
                    <?php echo rtb_i('Backplane Key.'); ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('JanRain RPX Url'); ?></th>
                <td>
                    <input type="text" name="rtb_comments_rpx" value="<?php echo esc_attr($rtb_comments_rpx); ?>" tabindex="4" width="300" style="width:400px;">
                    <br />
                    <?php echo rtb_i('JanRain RPX Url.'); ?>
                </td>
            </tr>

            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Use RealTidbits Comments on'); ?></th>
                <td>
                    <select name="rtb_comments_replace" tabindex="5">
                        <option value="new" <?php if('new'==$rtb_replace){echo 'selected';}?>><?php echo rtb_i('On new blog posts only.'); ?></option>
                        <option value="all" <?php if('all'==$rtb_replace){echo 'selected';}?>><?php echo rtb_i('On all existing and new blog posts.'); ?></option>
                    </select>
                    <br />
                    <?php echo rtb_i('NOTE: Your WordPress comments will never be lost.'); ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Comments style'); ?></th>
                <td>
                    <select name="rtb_comments_style" tabindex="5">
                        <option value="standard" <?php if('standard'==$rtb_style){echo 'selected';}?>><?php echo rtb_i('Standard'); ?></option>
                        <option value="inline" <?php if('inline'==$rtb_style){echo 'selected';}?>><?php echo rtb_i('Inline'); ?></option>
                    </select>
                    <br />
                    <?php echo rtb_i('Select the comments style to use in the posts/pages'); ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Login method'); ?></th>
                <td>
                    <select name="rtb_comments_login" tabindex="5">
                        <option value="realtidbits" <?php if('realtidbits'==$rtb_login){echo 'selected';}?>><?php echo rtb_i('Realtidbits'); ?></option>
                        <option value="wordpress" <?php if('wordpress'==$rtb_login){echo 'selected';}?>><?php echo rtb_i('Wordpress'); ?></option>
                    </select>
                    <br />
                </td>
            </tr>

            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Optional Configuration Params'); ?></th>
                <td>
<?php
if(empty($rtb_comments_widget_params) || $rtb_comments_widget_params == "") {
$rtb_comments_widget_params = '';	
}
?>
                    <textarea name="rtb_comments_widget_params" cols="25" rows="10" style="width:400px;"><?php echo ($rtb_comments_widget_params); ?></textarea>
                    <br />
                    <?php echo rtb_i('Custom javascript configuration params for the comments widget. See the <a href="http://documentation.realtidbits.com/Comments_V3/Administrator_Guide/Parameters">documentation</a> for details.'); ?>
                </td>
            </tr>

            <!--<tr>
                <th scope="row" valign="top"><?php echo rtb_i('Comment Sync'); ?></th>
                <td>
                    <input type="checkbox" name="rtb_manual_sync" <?php if($rtb_manual_sync){echo 'checked="checked"';}?> tabindex="6">
                    <label for="rtb_manual_sync"><?php echo rtb_i('Disable automated comment importing'); ?></label>
                    <br /><?php echo rtb_i('NOTE: If you have problems with WP cron taking too long and large numbers of comments you may wish to disable the automated sync cron. Keep in mind that this means comments will not automatically get synced to your local Wordpress database.'); ?>
                </td>
            </tr>-->

            <!--<tr>
                <th scope="row" valign="top"><?php echo rtb_i('Server Side Rendering'); ?></th>
                <td>
                    <input type="checkbox" name="rtb_disable_ssr" <?php if($rtb_disable_ssr){echo 'checked="checked"';}?> >
                    <label for="rtb_disable_ssr"><?php echo rtb_i('Disable server side rendering of comments'); ?></label>
                    <br /><?php echo rtb_i('NOTE: This will hide comments from nearly all search engines'); ?>
                </td>
            </tr>
            
            
            <tr>
                <th scope="row" valign="top"><?php echo rtb_i('Script Version'); ?></th>
                <td>
                    <input type="checkbox" name="rtb_use_staging" <?php if($rtb_use_staging){echo 'checked="checked"';}?> >
                    <label for="rtb_use_staging"><?php echo rtb_i('Use staging .js version'); ?></label>
                    <br /><?php echo rtb_i('NOTE: Advanced users only.'); ?>
                </td>
            </tr>-->
            
        </table>

        <p class="submit" style="text-align: left">
            <input name="submit" type="submit" value="Save" class="button-primary button" tabindex="4">
        </p>
        </form>
        
        <br/>
        <h3><?php echo rtb_i('Debug Information'); ?></h3>
        <p><?php echo rtb_i('Having problems with the plugin? <a href="%s">Drop us a line</a> and include the following details and we\'ll do what we can.', 'mailto:support@3ones.com'); ?></p>
        <textarea style="width:90%; height:200px;">
        URL: <?php echo get_option('siteurl'); ?> 
PHP Version: <?php echo phpversion(); ?> 
Version: <?php echo $wp_version; ?> 
Active Theme: <?php $theme = get_theme(get_current_theme()); echo $theme['Name'].' '.$theme['Version']; ?> 
Plugin Version: <?php echo RTB_COMMENTS_VERSION; ?> 

Settings:
rtb_is_installed: <?php echo rtb_is_installed(); ?> 

<?php foreach (rtb_comments_options() as $opt) {
    echo $opt.': '.get_option($opt)."\n";
} ?>

Plugins:

<?php
foreach (get_plugins() as $plugin) {
    echo $plugin['Name'].' '.$plugin['Version']."\n";
}
?></textarea><br/>
    </div>
