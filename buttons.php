<?php
function realtidbitsApp_addbuttons() {
   // Don't bother doing this stuff if the current user lacks permissions
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
 
   // Add only in Rich Editor mode
   if ( get_user_option('rich_editing') == 'true') {
     add_filter("mce_external_plugins", "add_realtidbitsApp_tinymce_plugin");
     add_filter('mce_buttons', 'register_realtidbitsApp_button');
   }
}
 
function register_realtidbitsApp_button($buttons) {
   array_push($buttons, "pushquote");
   array_push($buttons, "pinboard");
   array_push($buttons, "forum");
   array_push($buttons, "gallery");
   array_push($buttons, "liveblog");
   return $buttons;
}
 
// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function add_realtidbitsApp_tinymce_plugin($plugin_array) {
   $plugin_array['pushquote'] = rtb_plugin_url().'/tinymce/pushquote.js';
   $plugin_array['pinboard'] = rtb_plugin_url().'/tinymce/pinboard.js';
   $plugin_array['forum'] = rtb_plugin_url().'/tinymce/forum.js';
   $plugin_array['gallery'] = rtb_plugin_url().'/tinymce/gallery.js';
   $plugin_array['liveblog'] = rtb_plugin_url().'/tinymce/liveblog.js';
   return $plugin_array;
}
 
// init process for button control
add_action('init', 'realtidbitsApp_addbuttons');

$new_general_setting = new new_general_setting();
 
class new_general_setting {
    function new_general_setting( ) {
        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
    }
    function register_fields() {
        register_setting( 'general', 'realtidbitsApp_options' );
        add_settings_field('pushquotes_show_credits', '<label for="show_credits">'.__('Show PushQuotes credits text?' ).'</label>' , array(&$this, 'fields_html') , 'general' );
    }
    function fields_html() {
		global $realtidbitsApp;
		if(is_array($realtidbitsApp)) {
        	$value = $realtidbitsApp['show_credits'];
		} else {
			$value = 0;
		}
        echo '<input type="checkbox" id="realtidbitsApp_options[show_credits]" name="realtidbitsApp_options[show_credits]" value="1" '.($value ? "checked='checked'" : "").' />';
    }
}
?>