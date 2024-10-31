<?php
/*
Plugin Name: RealTidbits Comment System
Plugin URI: http://realtidbits.com/
Description: The RealTidbits comment system replaces your WordPress comment system with comments powered by RealTidbits.
Author: RealTidbits <support@realtidbits.com>
Version: 1.1.3.6
Author URI: http://realtidbits.com/
*/

define('RTB_COMMENTS_VERSION', '1.1.3.6');


define('RTB_BACKPLANE_KEY', get_option('rtb_comments_backplane_key'));

/*
 * Include Metaboxes
*/

require_once("metaboxes.php");

/*
 * Include Mobile Detect
*/

if(!class_exists('Mobile_Detect')) {
	require_once("mobile_detect.php");
	global $mobile_detect;
	$mobile_detect = new Mobile_Detect();
}

/*
 * Include Widgets
*/

require_once("widgets/rtb-audiorecorder.php");
require_once("widgets/rtb-recentcomments.php");
require_once("widgets/rtb-popularcomments.php");

function rtb_load_widgets() {
    register_widget( 'rtb_audiorecorder_widget' );
	register_widget( 'rtb_recentcomments_widget' );
	register_widget( 'rtb_popularcomments_widget' );
}
add_action( 'widgets_init', 'rtb_load_widgets' );

/**
 * Returns an array of all option identifiers
 * @return array[int]string
 */
function rtb_comments_options() {
    return array(
        # configuration
		'rtb_comments_install_timestamp',
        'rtb_comments_active',
        'rtb_comments_appkey',
        'rtb_comments_secret_key',
        'rtb_comments_backplane_busname',
        'rtb_comments_rpx',
        'rtb_comments_replace',
		'rtb_comments_style',
		'rtb_comments_email_notification',
		'rtb_comments_widget_params',
        # disables automatic sync via cron
        'rtb_comments_manual_sync',
        # disables server side rendering
        'rtb_comments_disable_ssr',
        # the last sync comment id (from get_forum_posts)
        'rtb_comments_last_comment_id',
        'rtb_comments_version',
		'rtb_use_staging'
    );
}

function rtb_request_handler() {
    global $post;
}
add_action('init', 'rtb_request_handler');

//

function rtb_prev_permalink($post_id) {
// if post not published, return
    $post = &get_post($post_id);
    if ($post->post_status != 'publish') {
        return;
    }
    global $rtb_prev_permalinks;
    $rtb_prev_permalinks['post_'.$post_id] = get_permalink($post_id);
}
add_action('pre_post_update', 'rtb_prev_permalink');


/**
 *  Compatibility
 */

if (!function_exists ( '_wp_specialchars' ) ) {
function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Don't bother if there are no specialchars - saves some processing
    if ( !preg_match( '/[&<>"\']/', $string ) ) {
        return $string;
    }

    // Account for the previous behaviour of the function when the $quote_style is not an accepted value
    if ( empty( $quote_style ) ) {
        $quote_style = ENT_NOQUOTES;
    } elseif ( !in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) ) {
        $quote_style = ENT_QUOTES;
    }

    // Store the site charset as a static to avoid multiple calls to wp_load_alloptions()
    if ( !$charset ) {
        static $_charset;
        if ( !isset( $_charset ) ) {
            $alloptions = wp_load_alloptions();
            $_charset = isset( $alloptions['blog_charset'] ) ? $alloptions['blog_charset'] : '';
        }
        $charset = $_charset;
    }
    if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ) ) ) {
        $charset = 'UTF-8';
    }

    $_quote_style = $quote_style;

    if ( $quote_style === 'double' ) {
        $quote_style = ENT_COMPAT;
        $_quote_style = ENT_COMPAT;
    } elseif ( $quote_style === 'single' ) {
        $quote_style = ENT_NOQUOTES;
    }

    // Handle double encoding ourselves
    if ( !$double_encode ) {
        $string = wp_specialchars_decode( $string, $_quote_style );
        $string = preg_replace( '/&(#?x?[0-9a-z]+);/i', '|wp_entity|$1|/wp_entity|', $string );
    }

    $string = @htmlspecialchars( $string, $quote_style, $charset );

    // Handle double encoding ourselves
    if ( !$double_encode ) {
        $string = str_replace( array( '|wp_entity|', '|/wp_entity|' ), array( '&', ';' ), $string );
    }

    // Backwards compatibility
    if ( 'single' === $_quote_style ) {
        $string = str_replace( "'", '&#039;', $string );
    }

    return $string;
}
}

if (!function_exists ( 'wp_check_invalid_utf8' ) ) {
function wp_check_invalid_utf8( $string, $strip = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Store the site charset as a static to avoid multiple calls to get_option()
    static $is_utf8;
    if ( !isset( $is_utf8 ) ) {
        $is_utf8 = in_array( get_option( 'blog_charset' ), array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ) );
    }
    if ( !$is_utf8 ) {
        return $string;
    }

    // Check for support for utf8 in the installed PCRE library once and store the result in a static
    static $utf8_pcre;
    if ( !isset( $utf8_pcre ) ) {
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }
    // We can't demand utf8 in the PCRE installation, so just return the string in those cases
    if ( !$utf8_pcre ) {
        return $string;
    }

    // preg_match fails when it encounters invalid UTF8 in $string
    if ( 1 === @preg_match( '/^./us', $string ) ) {
        return $string;
    }

    // Attempt to strip the bad chars if requested (not recommended)
    if ( $strip && function_exists( 'iconv' ) ) {
        return iconv( 'utf-8', 'utf-8', $string );
    }

    return '';
}
}

if (!function_exists ( 'esc_html' ) ) {
function esc_html( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    return apply_filters( 'esc_html', $safe_text, $text );
}
}

if (!function_exists ( 'esc_attr' ) ) {
function esc_attr( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    return apply_filters( 'attribute_escape', $safe_text, $text );
}
}


/**
 * Tests if required options are configured to display the embed.
 * @return bool
 */
function rtb_is_installed() {
    return get_option('rtb_comments_appkey')
		&& get_option('rtb_comments_backplane_busname')
		&& get_option('rtb_comments_rpx');
}

// http://codex.wordpress.org/Function_Reference/add_submenu_page
// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function )

// Always add management page to the admin menu
function rtb_add_pages() {
     add_submenu_page(
         'edit-comments.php',
         'RealTidbits Comments Config',
         'RealTidbits',
         'moderate_comments',
         'rtb-comments',
         'rtb_manage'
     );
     add_submenu_page(
         'edit-comments.php',
         'RealTidbits Comments Admin',
         'Moderation',
         'moderate_comments',
         'rtb-comments-admin',
         'rtb_manage_admin'
     );
}
add_action('admin_menu', 'rtb_add_pages', 10);

function rtb_manage() {
    /*if (dsq_does_need_update() && isset($_POST['upgrade'])) {
        dsq_install();
    }
    if (dsq_does_need_update() && isset($_POST['uninstall'])) {
        include_once(dirname(__FILE__) . '/upgrade.php');
    } else {
        include_once(dirname(__FILE__) . '/manage.php');
    }*/
	include_once(dirname(__FILE__) . '/manage.php');
}

function rtb_manage_admin() {
	// Display whatever it is you want to show
	
	if ( !rtb_is_installed() || !rtb_can_replace() ) {
       echo '<strong>You must configure and enable RealTidbits Comments.</strong>';
    } else {
?>
<div id="realtidbits-moderation"></div>
<script type="text/javascript">
var rtb_load_cdn = false;
</script>
<script type="text/javascript" src="http://cdn.realtidbits.com/libs/v1/moderation/moderation.js"></script>
<script type="text/javascript">
new RealTidbits.Moderation({
	"target": document.getElementById("realtidbits-moderation"),
	"appkey": "<?php echo strtolower(get_option('rtb_comments_appkey')); ?>",
	"backplane": {
		"busName": "<?php echo get_option('rtb_comments_backplane_busname'); ?>",
		"rpx": "<?php echo strtolower(get_option('rtb_comments_rpx')); ?>"
	},
	"domains": ["<?php echo $_SERVER['HTTP_HOST'] ; ?>"]
});
</script>
<?php
	}
	//
} 

// Create the function to output the contents of our Dashboard Widget

function rtb_comments_dashboard_widget_function() {
	// Display whatever it is you want to show
	
	if ( !rtb_is_installed() || !rtb_can_replace() ) {
       echo '<strong>You must configure and enable RealTidbits Comments.</strong>';
    } else {
?>
<div id="dashboard-moderation"></div>
<script type="text/javascript" src="http://cdn.realtidbits.com/libs/v1/moderation/moderation.js"></script>
<script type="text/javascript">
new RealTidbits.Moderation({
	"target": document.getElementById("dashboard-moderation"),
	"appkey": "<?php echo get_option('rtb_comments_appkey'); ?>",
	"backplane": {
		"busName": "<?php echo get_option('rtb_comments_backplane_busname'); ?>",
		"rpx": "<?php echo get_option('rtb_comments_rpx'); ?>"
	}
});
</script>
<?php
	}
	//
} 

function rtb_audiorecordings_dashboard_widget_function() {
	global $wpdb;
	
	?>
    <table cellpadding="5" cellspacing="0" border="0" width="100%">
    <?php
	$wpdb->query("
        SELECT `post_id`, `meta_value`
        FROM $wpdb->postmeta
        WHERE `meta_key` = 'fold_media_voice'
		ORDER BY `meta_id` DESC
		LIMIT 10
    ");
    foreach($wpdb->last_result as $k => $v){
		?>
        <tr>
            <td><a href="<?php echo get_edit_post_link($v->post_id)?>"><?php echo get_the_title($v->post_id)?></a></td>
            <td>
                <a href="<?php echo $v->meta_value?>" target="_blank"><?php echo substr($v->meta_value, strrpos($v->meta_value, "/")+1)?></a>
            </td>
            
        </tr>
        <?php
    };
	?>
    </table>
    <?php
}

// Create the function use in the action hook

function rtb_comments_add_dashboard_widgets() {
	wp_add_dashboard_widget('rtb_comments_dashboard_widget', 'Recent RealTidbits Comments', 'rtb_comments_dashboard_widget_function');	
	wp_add_dashboard_widget('rtb_audiorecordings_dashboard_widget', 'Recent Audio Recordings', 'rtb_audiorecordings_dashboard_widget_function');	
} 

// Hook into the 'wp_dashboard_setup' action to register our other functions

add_action('wp_dashboard_setup', 'rtb_comments_add_dashboard_widgets' );

/**
 * @return bool
 */
function rtb_can_replace() {
    global $post;

    if (get_option('rtb_comments_active') === '0'){ return false; }

    $replace = get_option('rtb_comments_replace');

    if ( is_feed() )                       { return false; }
    if ( 'draft' == $post->post_status )   { return false; }
    if ( !get_option('rtb_comments_replace') ) { return false; }
    if ( 'all' == $replace )          { return true; }

	if($replace = 'new') {
		// check install date
		$install_timestamp = (int) get_option('rtb_comments_install_timestamp');
		$post_timestamp = strtotime($post->post_date);
		// if post is younger then plugin install timestamp
		if($post_timestamp < $install_timestamp) {return false;}
		return true;
	}
	
	if ( !isset($post->comment_count) ) {
		$num_comments = 0;
	};

    return ( ('empty' == $replace && 0 == $num_comments)
        || ('closed' == $replace && 'closed' == $post->comment_status) );
}

//
// ugly global hack for comments closing
$EMBED = false;
function rtb_comments_template($value) {
    global $EMBED;
    global $post;
    global $comments;
	
	/*if (!comments_open()) {
		return $value;
	}*/
    if ( !( is_singular() && ( have_comments() || 'open' == $post->comment_status ) ) ) {
        return;
    }
	
    if ( !rtb_is_installed() || !rtb_can_replace() ) {
        return $value;
    }
	
    $EMBED = true;
	if(get_option('rtb_comments_style') == "inline") {
		return dirname(__FILE__) . '/inline.php';
	} else {
		return dirname(__FILE__) . '/comments.php';
	}
};

function rtb_content_inline_comments($content) {

	$notesmarkers = '<div id="rtb-notes-markers"></div>';
	return '<div id="rtb-notes-text-wrap">'.$content.$notesmarkers.'</div>';
    
}

add_filter( 'the_content', 'rtb_content_inline_comments'); 

function rtb_comments_number($count) {
    global $post;
	
    return $count;
}

function rtb_comments_text($comment_text) {
    global $post;

    return $comment_text;
}

//
function rtb_plugin_action_links($links, $file) {
    $plugin_file = basename(__FILE__);
    if (basename($file) == $plugin_file) {
        $settings_link = '<a href="edit-comments.php?page=rtb-comments">'.__('Settings', 'rtb-comments').'</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'rtb_plugin_action_links', 10, 2);

// Javascript Files

function rtb_enqueue_scripts() {
	global $rtb;
	if ( !is_admin() ){ 
		wp_enqueue_script( 'backplane', 'http://cdn.echoenabled.com/sdk/v3/backplane.js');
		wp_enqueue_script( 'echo-loader', 'http://cdn.echoenabled.com/sdk/v3/loader.js' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'rtb', rtb_plugin_url( 'js/pushquote.js' ), array('jquery'), '1.0', true); 
		//wp_enqueue_script( 'jrecorder', rtb_plugin_url( 'js/jRecorder.js' ),
		//	array('jquery'), '1.0', true); 
		wp_localize_script( 'rtb', 'PushquoteAjax', array( 'show_credits' => (is_array($rtb) ? $rtb['show_credits'] : 0 ) ) );
		
		//wp_enqueue_script( 'environment', 'http://cdn.echoenabled.com/sdk/v3/environment.pack.js', array( 'jquery' ), '1.0' );
		//wp_enqueue_script( 'streamserver', 'http://cdn.echoenabled.com/sdk/v3/streamserver.pack.js', array( 'jquery' ), '1.0' );
		//wp_enqueue_script( 'identityserver', 'http://cdn.echoenabled.com/sdk/v3/identityserver.pack.js', array( 'jquery' ), '1.0' );
		
		wp_enqueue_script( 'jplayer', rtb_plugin_url( 'js/jquery.jplayer.js' ), array('jquery'), '1.0', true); 
		wp_enqueue_script( 'miniPlayer', rtb_plugin_url( 'js/jquery.mb.miniPlayer.js' ), array('jquery'), '1.0', true); 
		wp_localize_script( 'rtb_inline', 'RTBInlineAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'postNotesNonce' => wp_create_nonce( 'ajax-get-events-nonce' ) ) );
	}
}

add_action( 'init', 'rtb_enqueue_scripts' );

// Styles 

function rtb_enqueue_styles() {	
  global $post, $rtb, $wp_registered_widgets,$wp_widget_factory;
  
  wp_enqueue_style( 'rtb_pushquote', rtb_plugin_url( 'css/pushquote.css' ),
	false, '1.0', 'all');
  wp_enqueue_style( 'rtb_audiorecorder', rtb_plugin_url( 'css/audiorecorder.css' ),
	false, '1.0', 'all');
  wp_enqueue_style( 'rtb_miniplayer', rtb_plugin_url( 'css/miniplayer.css' ),
	false, '1.0', 'all');
}
add_action( 'wp', 'rtb_enqueue_styles' );

// Get Plugin URL

function rtb_plugin_url( $path = '' ) {
	global $wp_version;
	if ( version_compare( $wp_version, '2.8', '<' ) ) { // Using WordPress 2.7
		$folder = dirname( plugin_basename( __FILE__ ) );
		if ( '.' != $folder )
			$path = path_join( ltrim( $folder, '/' ), $path );

		return plugins_url( $path );
	}
	return plugins_url( $path, __FILE__ );
}

function rtb_cur_page($tld = false) {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		if($tld) {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
	} else {
		if($tld) {
			$pageURL .= $_SERVER["SERVER_NAME"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
	}
	return $pageURL;
}

function rtb_backplane_init() {
	echo '<script type="text/javascript">';
	if(!is_user_logged_in()) {
		echo 'Backplane.resetCookieChannel();';
	}
	echo '
	// init Backplane
	Backplane.init({
	  "serverBaseURL" : "http://api.echoenabled.com/v1",
	  "busName": "'.get_option('rtb_comments_backplane_busname').'"
	});
	// save channelID to a cookie
	document.cookie = "bp_channel_id="+Backplane.getChannelID()+"; path=/";
	</script>';	
}

function rtb_get_avatar_url($get_avatar){
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
}

function rtb_get_user_avatar($user_id, $size = 96, $default = "", $alt = "") {
	$user_avatar = get_user_meta($user_id, 'theme_user_avatar', true);
	
	if($user_avatar != "") {
		return '<img alt="'.$alt.'" src=\''.$user_avatar.'\' width="'.$size.'" height="'.$size.'" />';	
	} else {
		return get_avatar( $user_id, $size, $default, $alt );
	}
}

function rtb_backplane_after_login($user_login, $user) {
	// include backplane lib
	include('backplane.php');
	//print_r($user);
	//echo get_option('rtb_comments_backplane_busname');
	//die();
	// get cookie created by the backplane script
	
	if(empty($user)) {
		$user = get_user_by('login', $user_login);
	}
	
	$bp_channel_id = $_COOKIE['bp_channel_id'];
	// send message to BP server
	$bp = new Backplane(get_option('rtb_comments_backplane_busname'), RTB_BACKPLANE_KEY);
	$rsp = $bp->send(array(
	  "source" => rtb_cur_page(true), // ex: "http://example.com/"
	  "type" => "identity/login",
	  "channel" => $bp_channel_id,
	  "user_id_url" => rtb_cur_page(true).'/?author='.$user->ID, // ex: "http://example.com/user/1234567"
	  "display_name" => $user->display_name, // ex: "Some User"
	  "photo" => rtb_get_avatar_url(rtb_get_user_avatar( $user->ID )) // ex: "http://cdn.example.com/images/1234567.jpg"
	));
	
	if($user->caps['administrator']) {
	
		// update account user privs
		// arg1 = user URL id used to log user in via backplane API
		
		include('OAuth.php'); // php OAuth library
		
		include('echoOAuth.php'); // php OAuth library
		
		
		$result = method_user_update(strtolower(get_option('rtb_comments_appkey')), get_option('rtb_comments_secret_key'), rtb_cur_page(true).'/?author='.$user->ID, 'roles', 'administrator');
		
	}
}

function rtb_backplane_after_logout() {
	
	// include the backplane lib
	include('backplane.php');
	// init the backplane class
	$bp = new Backplane(get_option('rtb_comments_backplane_busname'), RTB_BACKPLANE_KEY);
	// get the channel id cookie
	$bp_channel_id = $_COOKIE['bp_channel_id'];
	// send to the backplane server
	$rsp = $bp->send(array(
		"source" => rtb_cur_page(true), // ex: "http://example.com/"
		"type" => "identity/logout",
		"channel" => $bp_channel_id,
		"user_id_url" => rtb_cur_page(true).'/?author='.wp_get_current_user()->data->ID // ex: "http://example.com/user/1234567"
	));
	
	//die("Logout".$rsp);
}

if(get_option('rtb_comments_login') == 'wordpress') {
	add_action('wp_head', 'rtb_backplane_init');
	add_action('wp_login', 'rtb_backplane_after_login', 10, 2);
	add_action('clear_auth_cookie', 'rtb_backplane_after_logout');
}

/**
 * Wrapper for built-in __() which pulls all text from
 * the disqus domain and supports variable interpolation.
 */
function rtb_i($text, $params=null) {
    if (!is_array($params))
    {
        $params = func_get_args();
        $params = array_slice($params, 1);
    }
    return vsprintf(__($text), $params);
}

/* filters */

add_filter('comments_template', 'rtb_comments_template');
add_filter('comments_number', 'rtb_comments_text');
add_filter('get_comments_number', 'rtb_comments_number');

/**
 * Hide the default comment form to stop spammers by marking all comments
 * as closed.
 */
function rtb_comments_open($open, $post_id=null) {
    global $EMBED;
    if ($EMBED) return false;
    return $open;
}
add_filter('comments_open', 'rtb_comments_open');

/**
* Disable internal Wordpress commenting if RTB is enabled - this prevents spam bots from
* commenting using POST requests to /wp-comments-post.php.
*
* @param int $comment_post_ID
* @return int
*/
function rtb_pre_comment_on_post($comment_post_ID) {
    if (rtb_can_replace()) {
        wp_die( 'Sorry, the built-in commenting system is disabled because RealTidbits is active.' );
    }
    return $comment_post_ID;
}
add_action('pre_comment_on_post', 'rtb_pre_comment_on_post');

// includes

include("buttons.php");
include("shortcodes.php");
?>