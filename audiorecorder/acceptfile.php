<?php
// Import VOSD Content
set_time_limit(0);

require_once('../../../../wp-blog-header.php');
require_once("../../../../wp-config.php");
require_once("../../../../wp-includes/wp-db.php");
require_once("../../../../wp-admin/includes/taxonomy.php"); 

if(!isset($_REQUEST['filename']) || !is_user_logged_in())
{
 exit('No file');
}

header('HTTP/1.1 200 OK', true, 200);  

$upload_path = dirname(__FILE__). '/';

$filename = addslashes(str_replace(".", "", $_REQUEST['filename']."_".time()));
$filename_arr = explode("_", $filename);
$post_id = $filename_arr[0];

$fp = fopen($upload_path."/".$filename.".mp3", "wb");

fwrite($fp, file_get_contents('php://input'));

fclose($fp);

$current_user = wp_get_current_user();

$image = array();
$image['name'] = $current_user->ID."u_".$filename.".mp3";
$image['type'] = "audio/mpeg";
$image['tmp_name'] = $upload_path."/".$filename.".mp3";
$image['post_excerpt'] = "";
$image['error'] = 0;
$image['size'] = filesize($filename.".mp3");
$uploads = wp_upload_dir();

if (!copy($image['tmp_name'], $uploads['basedir'].$uploads['subdir']."/".$image['name'])) {
	echo "Error copying file...\n";
} else {
	unlink($image['tmp_name']);	
}

$wp_filetype = wp_check_filetype(basename($image['name']), null );
$attachment = array(
 'guid' => $uploads['basedir'].$uploads['subdir'] . '/' . basename( $image['name'] ), 
 'post_mime_type' => $wp_filetype['type'],
 'post_title' => preg_replace('/\.[^.]+$/', '', basename($image['name'])),
 'post_content' => '',
 'post_status' => 'inherit'
);

$attach_id = wp_insert_attachment( $attachment, $image['name'] );
// you must first include the image.php file
// for the function wp_generate_attachment_metadata() to work
require_once(ABSPATH . 'wp-admin/includes/image.php');
$attach_data = wp_generate_attachment_metadata( $attach_id, $uploads['basedir'].$uploads['subdir'] . '/' . basename( $image['name'] ) );
wp_update_attachment_metadata( $attach_id, $attach_data );

$media_type = substr(strrchr($image['name'],'.'),1);

$mp3 = $uploads['url']."/".$image['name'];
			
add_post_meta($post_id, 'fold_media_voice', $mp3);

exit('done');

?>