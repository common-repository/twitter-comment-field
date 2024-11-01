<?php
/*
Plugin Name: Twitter Comment Field
Version: 1.0
Plugin URI: http://headway101.com/twitter-comment-field/
Description: A plug-in that adds a twitter field to the comments form and display.
Author: Corey Freeman
Author URI: http://www.coreyfreeman.me/about/
License: GPL
*/
add_filter('comment_form_default_fields','custom_fields');
function custom_fields($fields) {

		$commenter = wp_get_current_commenter();
		$req = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );

		$fields[ 'author' ] = '<p class="comment-form-author">'.
			'<label for="author">' . __( 'Name' ) . '</label>'.
			( $req ? '<span class="required">*</span>' : '' ).
			'<input id="author" name="author" type="text" value="'. esc_attr( $commenter['comment_author'] ) . 
			'" size="30" tabindex="1"' . $aria_req . ' /></p>';
		
		$fields[ 'email' ] = '<p class="comment-form-email">'.
			'<label for="email">' . __( 'Email' ) . '</label>'.
			( $req ? '<span class="required">*</span>' : '' ).
			'<input id="email" name="email" type="text" value="'. esc_attr( $commenter['comment_author_email'] ) . 
			'" size="30"  tabindex="2"' . $aria_req . ' /></p>';
					
		$fields[ 'url' ] = '<p class="comment-form-url">'.
			'<label for="url">' . __( 'Website' ) . '</label>'.
			'<input id="url" name="url" type="text" value="'. esc_attr( $commenter['comment_author_url'] ) . 
			'" size="30"  tabindex="3" /></p>';

	return $fields;
}

// Add fields after default fields above the comment box, always visible

add_action( 'comment_form_after_fields', 'additional_fields' );
add_action( 'comment_form_logged_in_after', 'additional_fields' );


function additional_fields () {
	echo '<p class="comment-form-twitter">'.
	'<label for="twitter">' . __( 'Twitter Handle (No @)' ) . '</label>'.
	'<input id="twitter" name="twitter" type="text" size="30"  tabindex="5" /></p>';
}

// Save the comment meta data along with comment

add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
	if ( ( isset( $_POST['twitter'] ) ) && ( $_POST['twitter'] != '') )
	$twitter = wp_filter_nohtml_kses($_POST['twitter']);
	add_comment_meta( $comment_id, 'twitter', $twitter );
}
//Add an edit option in comment edit screen  

add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'twitter', __( 'Comment Author Twitter Handle' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}
 
function extend_comment_meta_box ( $comment ) {
    $twitter = get_comment_meta( $comment->comment_ID, 'twitter', true );
    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?>

    <p>
        <label for="twitter"><?php _e( 'Twitter Username (no @)' ); ?></label>
        <input type="text" name="twitter" value="<?php echo esc_attr( $twitter ); ?>" class="widefat" />
    </p>
    <?php
}

// Update comment meta data from comment edit screen 

add_action( 'edit_comment', 'extend_comment_edit_metafields' );
function extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;
		
	if ( ( isset( $_POST['twitter'] ) ) && ( $_POST['twitter'] != '') ):
	$twitter = wp_filter_nohtml_kses($_POST['twitter']);
	update_comment_meta( $comment_id, 'twitter', $twitter );
	else :
	delete_comment_meta( $comment_id, 'twitter');
	endif;
	
}

// Add the comment meta (saved earlier) to the comment text 
// You can also output the comment meta values directly in comments template  

add_filter( 'comment_text', 'modify_comment');
function modify_comment( $text ){

	$plugin_url_path = WP_PLUGIN_URL;

	if( $commenttwitter = get_comment_meta( get_comment_ID(), 'twitter', true ) ) {
		$commenttwitter = '<p class="follow-comment-author"><a href="https://twitter.com/'.esc_attr( $commenttwitter ).'" class="twitter-follow-button" data-show-count="false">Follow @CoreyFreeman</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></p>';
		$text = $text.$commenttwitter;
		return $text;
	} 

else {
		return $text;		
	}	 
}