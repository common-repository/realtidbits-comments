<?php
/*
 * RTB - Audio Recorder Widget
 */
class rtb_audiorecorder_widget extends WP_Widget {

	function rtb_audiorecorder_widget() {
		$widget_opts = array(
			'classname' => 'rtb-audiorecorder',
			'description'=> __('Audio Recorder', 'fold')
		);
		$this->WP_Widget('rtb-audiorecorder-widget', __('RealTidBits - Audio Recorder', 'fold'),$widget_opts);
	}
	function widget( $args, $instance ) {
		global $post, $mobile_detect;
		extract( $args );
		
		$title = apply_filters('widget_title', $instance['title'] );
		$rtb_voice_check = get_post_meta($post->ID, 'rtb_voice_check', 1);
		if($rtb_voice_check != "") {
			$voice_content = $rtb_voice_check; //get_post_meta($post->ID, 'fold_media_voice', 1);
			//echo $voice_content;
			if($voice_content != "") {
				echo '
				<a id="m1" class="audio {ogg:\''.$voice_content.'\'}" href="'.$voice_content.'"></a>
				<script>
				jQuery(document).ready(function() {
				  jQuery(".audio").mb_miniPlayer({
					width:70,
					inLine:false,
					addShadow: false,
					showRew: false,
					showTime: false
				  });
				});
				</script>';
			}
		}
		
		if(!is_single() || !is_user_logged_in() || $mobile_detect->isMobile()) { return; }
		
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title; 
			
			
			?>

            <?php echo do_shortcode('[rtb-audiorecorder]')?>

		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
	function form( $instance ) {
		$defaults = array(
			'title' 			=> __('Record your voice', 'fold'),
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'fold'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>

		<?php
	}
}