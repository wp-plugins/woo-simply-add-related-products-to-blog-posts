<?php

/*
Plugin Name: Woo Related Products on Posts
Plugin URI: http://www.mattshirk.com/
Description: Allows Display of Related WooCommerce Products on Posts.
Author: Matt Shirk
Version: 1.0
Author URI: http://www.mattshirk.com/
*/


global $wpdb;
	$tnme = $wpdb->prefix . 'posts';
	$relateddProds = $wpdb->get_results( "SELECT post_title, ID FROM $tnme WHERE post_type = 'product' GROUP BY ID ASC", OBJECT );


/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta() {
	add_meta_box( 'prfx_meta', __( 'Add WooCommerce Related Products to Bottom of This Post', 'prfx-textdomain' ), 'prfx_meta_callback', 'post' );
}
add_action( 'add_meta_boxes', 'prfx_custom_meta' );

function custom_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 9999 );

/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback( $post ) {

	wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
	$prfx_stored_meta = get_post_meta( $post->ID );
?>
<p>
		<span class="prfx-row-title"><?php _e( 'Select Related Products that will appear at the bottom of this post', 'prfx-textdomain' )?></span>
		<div class="prfx-row-content">

	<?php
	$productCount = 0;
	global $relateddProds;
	foreach ($relateddProds as $relateddProd) {
		$prodpostid = $relateddProd->ID;
		$prodposttitle = $relateddProd->post_title;
		$productCount++;
		$itemnm = 'meta-checkbox' . $productCount;
		?>

		<label class="relprodsadmin" for="<?php echo $itemnm;?>">
				<input type="checkbox" name="<?php echo $itemnm;?>" id="<?php echo $itemnm;?>" value="yes" <?php if ( isset ( $prfx_stored_meta[$itemnm] ) ) checked( $prfx_stored_meta[$itemnm][0], $prodpostid ); ?> />
				<?php _e( $prodposttitle, 'prfx-textdomain' )?>
			</label>

	<?php }
	?>
        
		</div>
	</p>


	<?php
}



/**
 * Saves the custom meta input
 */
function prfx_meta_save( $post_id ) {
 
	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}
 
	// Checks for input and sanitizes/saves if needed
	if( isset( $_POST[ 'meta-text' ] ) ) {
		update_post_meta( $post_id, 'meta-text', sanitize_text_field( $_POST[ 'meta-text' ] ) );
	}

	
	$productCountz = 0;
	global $relateddProds;
	foreach ($relateddProds as $relateddProd) {
		$rr = $relateddProd->ID;
		$productCountz++;
		$itemnmz = 'meta-checkbox' . $productCountz;
		// Checks for input and saves
	if( isset( $_POST[ $itemnmz ] ) ) {
		update_post_meta( $post_id, $itemnmz, $rr );
	} else {
		update_post_meta( $post_id, $itemnmz, '' );
	}

	}

	
}
add_action( 'save_post', 'prfx_meta_save' );




// define the woocommerce_after_main_content callback
function displayStuff($content) 
{
	
    if( !is_archive() && 'post' == get_post_type() ) {

   
	$productCountzz = 0;
	global $relateddProds;
	$content = $content . '<div class="relprodscontainer"><h4 class="relprodtit">Related Products:</h4>';
	foreach ($relateddProds as $relateddProd) {
$productCountzz++;
$yo_id = $relateddProd->ID;
		$itemnmzz = 'meta-checkbox' . $productCountzz;
		$key_yo_value = get_post_meta( get_the_ID(), $itemnmzz, true );
		
// Check if the custom field has a value.
if ( ! empty( $key_yo_value ) ) {
	$yo_tit = get_the_title($yo_id);
	$yo_img = get_the_post_thumbnail( $yo_id, 'thumbnail',array('title' => '' . $yo_tit . '','alt' => '' . $yo_tit . '') );
	$yo_link = get_permalink($yo_id); 

   $content = $content . '<div class="relprods" style="display:inline-block;"><a href="' . $yo_link . '">' . $yo_img . '</a></div>';

}

}

echo $content . '</div>';
} else {echo $content;}

}
        
// add the action
add_filter( 'the_content', 'displayStuff', 10, 2 );


/**
 * Adds the meta box stylesheet when appropriate
 */
function prfx_admin_styles(){
	global $typenow;
	if( $typenow == 'post' ) {
		wp_enqueue_style( 'prfx_meta_box_styles', plugin_dir_url( __FILE__ ) . 'meta-box-styles.css' );
	}
}
add_action( 'admin_print_styles', 'prfx_admin_styles' );


