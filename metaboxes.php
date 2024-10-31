<?php

function rtb_meta_box_add() {
	add_meta_box( 'rtb_audiorecorder_meta', __('Audio Recorder', 'fold'), 'rtb_meta_box_display', 'post', 'side', 'core' );
}
add_action( 'add_meta_boxes', 'rtb_meta_box_add' );

function rtb_meta_box_save( $post_id ) {
	// Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	// if our nonce isn't there, or we can't verify it, bail
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;

	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;

	// now we can actually save the data
	$allowed = array(
		'a' => array( // on allow a tags
			'href' => array() // and those anchors can only have href attribute
		)
	);
	
	if( isset( $_POST['rtb_voice_check'] ) && $_POST['rtb_voice_check'] != '' ) {
		update_post_meta( $post_id, 'rtb_voice_check', wp_kses( $_POST['rtb_voice_check'], $allowed ) );
	} else {
		delete_post_meta($post_id, 'rtb_voice_check');
	};
	
	if( isset( $_POST['rtb_voice_delete'] ) && !empty($_POST['rtb_voice_delete']) ) {
		foreach($_POST['rtb_voice_delete'] as $key) {
			delete_post_meta($post_id, 'fold_media_voice', $key);
		}
	};

}
add_action( 'save_post', 'rtb_meta_box_save' );

function rtb_meta_box_display( $post ) {
	$values = get_post_custom( $post->ID );
	$rtb_voice_check = isset( $values['rtb_voice_check'] ) ? $values['rtb_voice_check'][0] : '';
	
	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
	
	?>
    <input type="hidden" name="rtb_voice_before" id="rtb_voice_before" value="<?php echo $rtb_voice_check?>" />
    <table cellpadding="5" cellspacing="0" border="0" width="100%">
    	<tr>
            <td><?php _e('Select which audio should be used.')?></td>
            <td>Del</td>
        </tr>
        <tr>
            <td>
            <input type="radio" name="rtb_voice_check" id="rtb_voice_check" value="" <?php echo ($rtb_voice_check == "" ? 'checked="checked"' : ''); ?> />
            None</td>
            <td></td>
        </tr>
    <?php
	$voice_content = get_post_meta($post->ID, 'fold_media_voice');
	
	foreach($voice_content as $key) {
	?>
    	
        <tr>
            <td>
            <input type="radio" name="rtb_voice_check" id="rtb_voice_check" value="<?php echo $key?>" <?php echo ($rtb_voice_check == $key ? 'checked="checked"' : ''); ?> />
            <a href="<?php echo $key?>" target="_blank"><?php echo substr($key, strrpos($key, "/")+1)?></a></td>
            <td align="center"><input type="checkbox" name="rtb_voice_delete[]" id="rtb_voice_delete[]" value="<?php echo $key?>" /></td>
        </tr>
   
	<?php
	}
	?>
    </table>
    <?php
}