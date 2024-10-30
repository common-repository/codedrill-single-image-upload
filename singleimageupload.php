<?php
/**
 * @package Codedrill
 */
/*
Plugin Name: CodeDrill Single Image Upload
Plugin URI: http://www.codedrillinfotech.com
Description: This plugin will allow to upload an image as attachment. And you will get attachment id of the image. <b> Shortcode: [CD_Single_IMAGE_UPLOAD]</b>. It will create thumbnails as defined in wordpress configuration.
Version: 1.0
Author: Pankaj Sharma
Text Domain: single-image-upload
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

Developers can use its code to extend its funtionality.

*/
register_activation_hook( __FILE__, 'CD_create_demo_page' );

function CD_create_demo_page(){
    // Create post object
    $demo_post = array(
      'post_title'    => 'Upload Page Demo',
      'post_content'  => '[CD_Single_IMAGE_UPLOAD]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
    );

    // Insert the post into the database
    wp_insert_post( $demo_post, '' );
}
function CD_single_image_upload(){
	if(isset($_POST['upload']) && isset($_FILES['up_image'])){
		$allowed_file_types = array('image/jpeg', 'image/gif', 'image/png', 'image/jpg');
		/* wp_upload_bits will upload the file to the latest directory in wordpress and will return the path. This is basically replacement of move_uploaded_file in php.
		 * <?php wp_upload_bits( $name, $deprecated, $bits, $time ) ?>
		 * */
		$file = wp_upload_bits( $_FILES['up_image']['name'], null, file_get_contents( $_FILES['up_image']['tmp_name'] ) );

		/* nOW WE WILL CONVERT THIS UPLOADED FILE TO ATTACHMENT */
		$filename = $file['file'];
		
		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ), null );
		if (in_array($filetype['type'], $allowed_file_types)) {
			// Your file handing script here
		} else {
			return 'Please upload valid image file.';
		}		
		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Insert the attachment.
		/** <?php wp_insert_attachment( $attachment, $filename, $parent_post_id ); ?>  **/
		$attach_id = wp_insert_attachment( $attachment, $filename); 
		// now attchment has been done. we can add additional meta
		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Generate the metadata for the attachment, and update the database record.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		echo 'Image has been successfully uploaded. Attachment ID is:'.$attach_id;
	}
	$form		=	'';
	$form		.='<form method="post" enctype="multipart/form-data" action="">
						<lable>Select Image</lable>
						<input type="file" name="up_image" />
						<input type="submit" name="upload" value="Upload" />
	</form>';
	return $form;
}

add_shortcode('CD_Single_IMAGE_UPLOAD','CD_single_image_upload');
