<?php
/*
 Plugin Name: Dahlia Custom Post Type
 Plugin URI: https://github.com/blobaugh/WordPress-Dahlia-Post-Type
 Description: Custom post type for storing dahlia information. 
 Version: 0.1
 Author: Ben Lobaugh
 Author URI: http://ben.lobaugh.net
 */


/**
 * This files contains the definitions for the Dahlia post type. This post type
 * is designed to contain all of the information on an individual dahlia,
 * including, but not limited to, the following:
 * 

Name - textbox
Size - text box (view lobda code for constraints, select menu?) - stored in db
Type - text box (view lobda code for constraints, select menu?) - stored in db
Height - text box (view lobda code for constraints, select menu?) - stored in db
Color - textbox
Description - wp_editor
Checkboxes for bloom types
-- Show
-- Cut flower
-- Garden
Checkboxes for bloom season time
-- Early
-- Mid
-- Late
Bloom size - text box (view lobda code for constraints, select menu?) - stored in db
Average tuber production - text box - numeric
Average tuber size - text box - numeric
Awards - this needs some thought. maybe make an awards post type and link them here? do later on
Originator - textbox
Originator email - text box (will not be publicly shown, if available a contact form provided)
Country of origination - select box of countries
 * 
 * 
 * 
 * ** This post type should be set for the public to view
 * ** No archive page - user can search by category and such
 * ** Need to include template parts? (shortcodes)
 * ** Option page to let users choose whether do use /dahlia/<dahlia_name> or /<dahlia_name>
 * ** New/Updated Dahlias available to Contributors and up
 * ** Track revisions and who made the edits
 * ** Warn user or low upload limits? - 10 megs should be a good start
 */

$dahliaPostType = new PostTypeDahlia();

class PostTypeDahlia {
    
    public function __construct() {
         //self::registerPostType();
        add_action( 'init', array( &$this, 'registerPostType') );
        add_action( 'add_meta_boxes', array( &$this, 'registerMetaBoxes'), 0 );
        add_action( 'save_post', array( &$this, 'savePost') );
        add_action('admin_menu', array( &$this, 'registerSubmenu'));

    }
    
    public function registerSubmenu() {
        add_submenu_page(
                'edit.php?post_type=dahlia',
                'Settings',
                'Settings',
                'edit_posts',
                'posttype-dahlia-settings',
                array( &$this, 'renderSubmenu')
        );
    }
    
    /**
     * @todo Pretty up with WP stylings
     */
    public function renderSubmenu() {
        $s = '<form action="" method="post">';
        if( isset( $_POST['update-dahlia-settings'] ) ) {//&& 
//            isset ( $_POST['posttype-dahlia-settings-nonce'] ) && 
//            wp_verify_nonce( $_POST['posttype-dahlia-settings-nonce'], plugin_basename( __FILE__ ) ) ) {
            
            update_option( 'dahlia-sizes', trim( $_POST['dahlia-sizes'] ) );
            update_option( 'dahlia-types', trim( $_POST['dahlia-types'] ) );
        }
        // Provide a medocum of security
        wp_nonce_field( plugin_basename( __FILE__ ), 'posttype-dahlia-settings-nonce' );
        
        // Dahlia sizes
        $dahlia_sizes = get_option( 'dahlia-sizes' );
        $s .= 'Valid dahlia sizes: <input type="text" name="dahlia-sizes" value="' . $dahlia_sizes . '"> (Enter multiples seperated by commas)';
        
        
        // Dahlia types
        $dahlia_types = get_option( 'dahlia-types' );
        $s .= '<p>Valid dahlia types: <input type="text" name="dahlia-types" value="' . $dahlia_types . '"> (Enter multiples seperated by commas)';
        
        $s .= '<br/><br/><input type="submit" name="update-dahlia-settings" value="Save Settings"></form>';
        echo $s;
    }
    

    public function registerPostType() {
        $labels = array(
            'name' => __('Dahlias'),
            'singular_name' => __('Dahlia'),
            'add_new' => __('Add Dahlia'),
            'add_new_item' => __('Add New Dahlia'),
            'edit_item' => __('Edit Dahlia'),
            'new_item' => __('New Dahlia'),
            'all_items' => __('All Dahlias'),
            'view_item' => __('View Dahlia'),
            'search_items' => __('Search Dahlia'),
            'not_found' =>  __('No dahlias found'),
            'not_found_in_trash' => __('No dahlias found in Trash'), 
            'parent_item_colon' => '',
            'menu_name' => 'Dahlias'

        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true, 
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'dahlia', 'with_front' => true),
            'capability_type' => 'post',
            'has_archive' => true, 
            'hierarchical' => false,
            'menu_position' => null,
            'menu_icon' => plugin_dir_url( __FILE__ ) . 'icon.jpg',
            'supports' => array( 'revistions', 'title', 'editor', 'thumbnail', 'comments' )
        ); 
        register_post_type( 'dahlia', $args) ;
    } // end function registerPostType
    
     public function registerMetaBoxes() { 
        add_meta_box( 
            'dahlia_meta',
            __( 'Dahlia Information' ),
            array( &$this, 'renderMetaBoxes' ),
            'dahlia',
            'normal',
            'high'
        );
    }


    /**
     * @todo Add contextual help
     * @todo Add verification to form input
     * @global type $post 
     */
    public function renderMetaBoxes()  {
        global $post;
        $s = '';
        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), 'posttype-dahlia-nonce' );
        
        // Dahlia Sizes
        $dahlia_sizes = explode(',', get_option( 'dahlia-sizes' ));
        $ds = get_post_meta( $post->ID, 'dahlia-size', true );
        $s .= '<p><b>Size:</b> <select name="dahlia-size">';
        foreach( $dahlia_sizes AS $size ) {
            $s .= '<option value="' . $size . '"' . selected( $ds, $size, false ) . '>' . $size . '</option>';
        }
        $s .= '</select></p>';
        
        
        // Dahlia types
        $dahlia_types = explode(',', get_option( 'dahlia-types' ));
        $dt = get_post_meta( $post->ID, 'dahlia-type', true );
        $s .= '<p><b>Type:</b> <select name="dahlia-type">';
        foreach( $dahlia_types AS $type ) {
            $s .= '<option value="' . $type . '"' . selected( $dt, $type, false ) . '>' . $type . '</option>';
        }
        $s .= '</select></p>';
        
        
        // Dahlia Height
        $dahlia_height = get_post_meta( $post->ID, 'dahlia-height', true );
        $s .= '<p><b>Height in feet:</b> <select name="dahlia-height">';
        for( $i = 1; $i < 11; $i++ ) {
           $s .= '<option value="' . $i . '"' . selected( $dahlia_height, $i, false ) . '>' . $i . '</option>'; 
        }
        $s .= '</select></p>';
        
        // Dahlia color
        $s .= '<p><b>Color:</b> <input type="text" name="dahlia-color" value="' . get_post_meta( $post->ID, 'dahlia-color', true ) . '"></p>';
        
        // Bloom type
        $bloom_type = get_post_meta( $post->ID, 'dahlia-bloom-type', true );
        if( '' == $bloom_type ) $bloom_type = array();
        $s .= '<p><b>Bloom Type:</b><br/>';
        $s .= '<label><input type="checkbox" name="dahlia-bloom-type[]" value="Show" '; if( 'Show' == $bloom_type || in_array( 'Show', $bloom_type )) { $s .= 'checked="checked"'; } $s .= '> Show</label><br/>';
        $s .= '<label><input type="checkbox" name="dahlia-bloom-type[]" value="Cut" '; if( 'Cut' == $bloom_type || in_array( 'Cut', $bloom_type )) { $s .= 'checked="checked"'; } $s .= '> Cut Flower</label><br/>';
        $s .= '<label><input type="checkbox" name="dahlia-bloom-type[]" value="Garden" '; if( 'Garden' == $bloom_type || in_array( 'Garden', $bloom_type )) { $s .= 'checked="checked"'; } $s .= '> Garden</label><br/>';
        
        
        // Bloom season
        $bloom_season = get_post_meta( $post->ID, 'dahlia-bloom-season', true );
        if( '' == $bloom_season ) $bloom_season = array();
        $s .= '<p><b>Bloom Season:</b><br/>';
        $s .= '<label><input type="checkbox" name="dahlia-bloom-season[]" value="Early" '; if( 'Early' == $bloom_season || in_array( 'Early', $bloom_season )) { $s .= 'checked="checked"'; } $s .= '> Early</label><br/>';
        $s .= '<label><input type="checkbox" name="dahlia-bloom-season[]" value="Mid" '; if( 'Mid' == $bloom_season || in_array( 'Mid', $bloom_season )) { $s .= 'checked="checked"'; } $s .= '> Mid</label><br/>';
        $s .= '<label><input type="checkbox" name="dahlia-bloom-season[]" value="Late" '; if( 'Late' == $bloom_season || in_array( 'Late', $bloom_season )) { $s .= 'checked="checked"'; } $s .= '> Late</label><br/>';
        
        
        // Tuber production
        $dahlia_tuber_production = get_post_meta( $post->ID, 'dahlia-tuber-production', true );
        $s .= '<p><b>Average tuber production:</b> <select name="dahlia-tuber-production">';
//        for( $i = 1; $i < 16; $i++ ) {
//           $s .= '<option value="' . $i . '"' . selected( $dahlia_tuber_production, $i, false ) . '>' . $i . '</option>'; 
//        }
        $s .= '<option value="1to4"' . selected( $dahlia_tuber_production, '1to4', false ) . '>1 to 4</option>'; 
        $s .= '<option value="5to7"' . selected( $dahlia_tuber_production, '5to7', false ) . '>5 to 7</option>'; 
        $s .= '<option value="8ormore"' . selected( $dahlia_tuber_production, '8ormore', false ) . '>8 or more</option>'; 
        $s .= '</select></p>';
        
        
        // Tuber size
        $dahlia_tuber_size = get_post_meta( $post->ID, 'dahlia-tuber-size', true );
        $s .= '<p><b>Average tuber size in inches:</b> <select name="dahlia-tuber-size">';
//        for( $i = 1; $i < 11; $i++ ) {
//           $s .= '<option value="' . $i . '"' . selected( $dahlia_tuber_size, $i, false ) . '>' . $i . '</option>'; 
//        }
        $s .= '<option value="Small"' . selected( $dahlia_tuber_size, 'Small', false ) . '>Small</option>'; 
        $s .= '<option value="Medium"' . selected( $dahlia_tuber_size, 'Medium', false ) . '>Medium</option>'; 
        $s .= '<option value="Large"' . selected( $dahlia_tuber_size, 'Large', false ) . '>Large</option>'; 
        $s .= '</select></p>';
        
        // Originator name
        $s .= '<p><b>Originator:</b> <input type="text" name="dahlia-originator" value="' . get_post_meta( $post->ID, 'dahlia-originator', true ) . '"></p>';
        
        // Originator email
        $s .= '<p><b>Originator email:</b> <input type="text" name="dahlia-originator-email" value="' . get_post_meta( $post->ID, 'dahlia-originator-email', true ) . '"> (not publicly displayed)</p>';
        
        // Country or origination
        $s .= '<p><b>Country of origination:</b> <input type="text" name="dahlia-originator-country" value="' . get_post_meta( $post->ID, 'dahlia-originator-country', true ) . '"></p>';
        
        // Year of origination
        $s .= '<p><b>Year of origination:</b> <input type="text" name="dahlia-originator-year" value="' . get_post_meta( $post->ID, 'dahlia-originator-year', true ) . '"></p>';
        
        
        // Awards
        $s .= '<p><b>Awards:</b><br/><textarea name="dahlia-awards">' . get_post_meta( $post->ID, 'dahlia-awards', true ) . '</textarea></p>';
        
        echo $s;

//        $field_value = get_post_meta( $post->ID, '_wp_editor_test_1', false );
//        wp_editor( $field_value[0], '_wp_editor_test_1' );
    }
    
    public function savePost( $PostId ) {  
        //  // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        if ( ( isset ( $_POST['posttype-dahlia-nonce'] ) ) && ( ! wp_verify_nonce( $_POST['posttype-dahlia-nonce'], plugin_basename( __FILE__ ) ) ) )
            return;
        
        update_post_meta( $PostId, 'dahlia-size', $_POST['dahlia-size'] );
        update_post_meta( $PostId, 'dahlia-type', $_POST['dahlia-type'] );
        update_post_meta( $PostId, 'dahlia-height', $_POST['dahlia-height'] );
        update_post_meta( $PostId, 'dahlia-color', $_POST['dahlia-color'] );
        update_post_meta( $PostId, 'dahlia-bloom-type', $_POST['dahlia-bloom-type'] );
        update_post_meta( $PostId, 'dahlia-bloom-season', $_POST['dahlia-bloom-season'] );
        update_post_meta( $PostId, 'dahlia-tuber-production', $_POST['dahlia-tuber-production'] );
        update_post_meta( $PostId, 'dahlia-tuber-size', $_POST['dahlia-tuber-size'] );
        update_post_meta( $PostId, 'dahlia-originator', $_POST['dahlia-originator'] );
        update_post_meta( $PostId, 'dahlia-originator-email', $_POST['dahlia-originator-email'] );
        update_post_meta( $PostId, 'dahlia-originator-country', $_POST['dahlia-originator-country'] );
        update_post_meta( $PostId, 'dahlia-originator-year', $_POST['dahlia-originator-year'] );

//        // Check permissions
//        if ( ( isset ( $_POST['post_type'] ) ) && ( 'page' == $_POST['post_type'] )  ) {
//            if ( ! current_user_can( 'edit_page', $post_id ) ) {
//            return;
//            }    
//        }
//        else {
//            if ( ! current_user_can( 'edit_post', $post_id ) ) {
//            return;
//            }
//        }

        // OK, we're authenticated: we need to find and save the data
//        if ( isset ( $_POST['_wp_editor_test_1'] ) ) {
//            update_post_meta( $PostId, '_wp_editor_test_1', $_POST['_wp_editor_test_1'] );
//        }
        
        update_post_meta( $PostId, 'dahlia-awards', $_POST['dahlia-awards'] );
    }
} // end class

