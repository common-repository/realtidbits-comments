<?php
/*
 * RTB - Popular Comments Widget
 */
class rtb_popularcomments_widget extends WP_Widget {

	function rtb_popularcomments_widget() {
		$widget_opts = array(
			'classname' => 'rtb-popularcomments',
			'description'=> __('Popular Comments', 'fold')
		);
		$this->WP_Widget('rtb-popularcomments-widget', __('RealTidBits - Popular Comments', 'fold'),$widget_opts);
	}
	function widget( $args, $instance ) {
		global $post;
		extract( $args );
		
		$title = apply_filters('widget_title', $instance['title'] );
		$number_comments = $instance['number_comments'];
		
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title; 
			
			?>
            <!-- RealTidbits.Comments -->
            <div id="rtb-popular-comments">Loading comments...</div>
            <script type="text/javascript">
            Backplane.init({
                "serverBaseURL" : "http://api.echoenabled.com/v1",
                "busName": "<?php echo get_option('rtb_comments_backplane_busname'); ?>"
            });
            </script>
            <style type="text/css">
            #rtb-popular-comments .echo-streamserver-controls-stream-item-markers,
            #rtb-popular-comments .echo-streamserver-controls-stream-item-tags,
            #rtb-popular-comments .echo-streamserver-controls-stream-item-button-Reply,
            #rtb-popular-comments .echo-streamserver-controls-stream-item-modeSwitch,
			#rtb-popular-comments .rtb-apps-comments-submit,
			#rtb-popular-comments .rtb-apps-comments-streamHeader,
			#rtb-popular-comments .echo-streamserver-controls-stream-item-itemContainerControls,
			#rtb-popular-comments .echo-streamserver-controls-stream-item-plugin-Moderation-status {
                display:none !important;
            }
            </style>
            <script type="text/javascript">
			jQuery(document).ready(function() {
				Echo.Loader.initApplication({
					"script": "https://dev.realtidbits.com/libs/v3/comments/core.js",
					"component": "RTB.Apps.Comments",
					"backplane": {
						"busName": "<?php echo get_option('rtb_comments_backplane_busname'); ?>",
						"serverBaseURL": "https://api.echoenabled.com/v1"
					},
					"config": {
						"cdnBaseURL": {
							"sdk": "",
							"RTB": (window.location.protocol === "https:" ? "https://" : "http://") + "dev.realtidbits.com/libs/v3"
						},
						"target": document.getElementById("rtb-popular-comments"),
						"appkey": "<?php echo get_option('rtb_comments_appkey'); ?>",
						"targetURL": "<?php echo home_url()?>/*",
						"streamQuery": "childrenof:<?php echo home_url()?>/* type:'http://activitystrea.ms/schema/1.0/comment' -markers:page sortOrder:likesDescending children state:Untouched,ModeratorApproved user.state:Untouched,ModeratorApproved itemsPerPage:<?php echo $number_comments?> -source:Twitter,Facebook",
						"janrain": { "appUrl": "<?php echo get_option('rtb_comments_rpx'); ?>" },
						"settings": {
							"plugins": {
								"stream": [{"name": "rtbWidgetCommentsPlugin"}]
							}
							
						}
					}
				});
				
            });
			
            </script>
            
            <!-- End of RealTidbits.Comments -->
            <?php
			
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number_comments'] = strip_tags( $new_instance['number_comments'] );
		return $instance;
	}
	function form( $instance ) {
		$defaults = array(
			'title' 			=> __('Popular Comments', 'fold'),
			'number_comments' 			=> __(10, 'fold')
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'fold'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>
        
        <p>
			<label for="<?php echo $this->get_field_id( 'number_comments' ); ?>"><?php _e('Number of Comments:', 'fold'); ?></label>
			<input type="number" min="0" max="999" id="<?php echo $this->get_field_id( 'number_comments' ); ?>" name="<?php echo $this->get_field_name( 'number_comments' ); ?>" value="<?php echo $instance['number_comments']; ?>" style="width:90%;" />
		</p>

		<?php
	}
}