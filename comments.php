<style>
.comments_closed {display:none;}
</style>
<div id="realtidbits-comments"></div>
<!--<script type="text/javascript" src="http://cdn.echoenabled.com/sdk/v3/loader.js"></script>-->
<script type="text/javascript">
jQuery(document).ready(function() {
	Echo.Loader.initApplication({
		"script": "http://cdn.realtidbits.com/libs/v3/comments/comments.min.js",
		"component": "RTB.Apps.Comments",
		"backplane": {
			"busName": "<?php echo get_option('rtb_comments_backplane_busname'); ?>",
			"serverBaseURL": "https://api.echoenabled.com/v1"
		},
		"config": {
			"target": document.getElementById("realtidbits-comments"),
			"appkey": "<?php echo get_option('rtb_comments_appkey'); ?>",
			"targetURL": "<?php echo get_permalink();?>",
			"janrain": {
				"appUrl": "<?php echo get_option('rtb_comments_rpx'); ?>"
			},
			"settings": {
				"stream": {
					"streamQueryURLs": [
						window.location.protocol + "//" + document.domain + "/<?php echo the_ID();?>",
						document.location.href
					],
					"queryParams": {
						"main": {"source": "-source:Twitter,Facebook -provider:'Idea Melt'", "bozo": true, "premod": true, "type": "type:'http://activitystrea.ms/schema/1.0/comment'"}	
					}
				},
				"submit": {
				   "showControlButtons": true
				},
				"auth": {
				  "loginButton": <?php if(get_option('rtb_comments_login') == 'wordpress') { echo 'false'; } else { echo 'true'; }?>
				}
				<?php echo (substr(trim(get_option('rtb_comments_widget_params')), 0, 1) != ',' ? "," : "").get_option('rtb_comments_widget_params'); ?>
			}
		}
	});
});
</script>

<?php
//window.location.protocol + "//" + document.domain + "/<?php echo the_ID(); 
?>